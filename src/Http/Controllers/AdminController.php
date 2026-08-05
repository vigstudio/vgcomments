<?php

namespace Vigstudio\VgComment\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;
use Vigstudio\VgComment\Facades\CommentServiceFacade;
use Vigstudio\VgComment\Models\Comment;
use Vigstudio\VgComment\Repositories\Interface\SettingInterface;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('vgcomment-moderate');
    }

    public function dashboard(Request $request)
    {
        $filters = [
            'status' => $request->input('status', 'all'),
            'q' => trim((string) $request->input('q', '')),
            'page_id' => trim((string) $request->input('page_id', '')),
            'from' => $request->input('from'),
            'to' => $request->input('to'),
        ];

        $comments = CommentServiceFacade::getAdmin($filters, false);
        $counts = CommentServiceFacade::getAdminStatusCounts();

        $tabs = collect(['all', 'pending', 'approved', 'spam', 'trash', 'reported', 'deleted'])
            ->mapWithKeys(fn (string $key) => [
                $key => [
                    'key' => $key,
                    'label' => __('vgcomment::admin.'.$key),
                    'count' => $counts[$key] ?? 0,
                ],
            ])
            ->all();

        return view('vgcomment::dashboard', compact('comments', 'tabs', 'filters', 'counts'));
    }

    public function bulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'action' => ['required', 'string', Rule::in([
                'approve',
                'pending',
                'spam',
                'trash',
                'delete',
                'restore',
                'force_delete',
            ])],
        ]);

        $ids = $validated['ids'];
        $action = $validated['action'];

        $affected = match ($action) {
            'approve' => CommentServiceFacade::bulkUpdateStatus($ids, Comment::STATUS_APPROVED),
            'pending' => CommentServiceFacade::bulkUpdateStatus($ids, Comment::STATUS_PENDING),
            'spam' => CommentServiceFacade::bulkUpdateStatus($ids, Comment::STATUS_SPAM),
            'trash' => CommentServiceFacade::bulkUpdateStatus($ids, Comment::STATUS_TRASH),
            'delete' => CommentServiceFacade::bulkDelete($ids),
            'restore' => CommentServiceFacade::bulkRestore($ids),
            'force_delete' => CommentServiceFacade::bulkForceDelete($ids),
        };

        return back()->with('success', trans('vgcomment::admin.bulk_success', ['count' => $affected]));
    }

    public function setting()
    {
        $disks = collect(Config::get('filesystems.disks'))->map(function ($disk, $key) {
            return $key;
        })->toArray();

        $config = [
            'general' => [
                'prefix' => $this->buildValue('string', 'prefix'),
                'user_column_name' => $this->buildValue('string', 'user_column_name'),
                'user_column_email' => $this->buildValue('string', 'user_column_email'),
                'user_column_url' => $this->buildValue('string', 'user_column_url'),
                'user_column_avatar_url' => $this->buildValue('string', 'user_column_avatar_url'),
                'broadcast' => $this->buildValue('boolean', 'broadcast'),
                'allow_guests' => $this->buildValue('boolean', 'allow_guests'),
                'gravatar' => $this->buildValue('boolean', 'gravatar'),
                'gravatar_imageset' => $this->buildValue('select', 'gravatar_imageset', ['mm', 'identicon', 'monsterid', 'wavatar', 'retro', 'robohash', 'blank']),
                'min_length' => $this->buildValue('number', 'min_length'),
                'max_length' => $this->buildValue('number', 'max_length'),
                'throttle_max_rate' => $this->buildValue('number', 'throttle_max_rate'),
                'throttle_per_minutes' => $this->buildValue('number', 'throttle_per_minutes'),
                'disk_filesystem' => $this->buildValue('select', 'disk_filesystem', $disks),
            ],
            'moderation' => [
                'moderation' => $this->buildValue('boolean', 'moderation'),
                'moderation_keys' => $this->buildValue('array', 'moderation_keys'),
                'blacklist_keys' => $this->buildValue('array', 'blacklist_keys'),
                'censor' => $this->buildValue('boolean', 'censor'),
                'censors_text' => $this->buildValue('array', 'censors_text'),
            ],
            'protection' => [
                'max_links' => $this->buildValue('number', 'max_links'),
                'duplicates_check' => $this->buildValue('boolean', 'duplicates_check'),
                'report_status' => $this->buildValue('select', 'report_status', ['pending', 'approved', 'rejected']),
                'max_reports' => $this->buildValue('number', 'max_reports'),
                'upload_rules' => $this->buildValue('array', 'upload_rules'),
                'upload_rules_max' => $this->buildValue('number', 'upload_rules_max'),
                'nsfw' => $this->buildValue('boolean', 'nsfw'),
                'nsfw_api_user' => $this->buildValue('string', 'nsfw_api_user'),
                'nsfw_api_key' => $this->buildValue('string', 'nsfw_api_key'),
                'recaptcha' => $this->buildValue('boolean', 'recaptcha'),
                'recaptcha_key' => $this->buildValue('string', 'recaptcha_key'),
                'recaptcha_secret' => $this->buildValue('string', 'recaptcha_secret'),
            ],
        ];

        return view('vgcomment::setting', compact('config'));
    }

    public function updateSetting(Request $request, SettingInterface $settingRepository)
    {
        $sanitizeList = static function (?array $items): array {
            return collect($items ?? [])
                ->map(fn ($item) => is_string($item) ? trim($item) : $item)
                ->filter(fn ($item) => is_string($item) && $item !== '')
                ->unique(fn ($item) => mb_strtolower($item))
                ->values()
                ->all();
        };

        $request->merge([
            'censors_text' => $sanitizeList($request->input('censors_text')),
            'moderation_keys' => $sanitizeList($request->input('moderation_keys')),
            'blacklist_keys' => $sanitizeList($request->input('blacklist_keys')),
            'upload_rules' => $sanitizeList($request->input('upload_rules')),
        ]);

        $request->validate([
            'prefix' => 'required|string',
            'broadcast' => 'required',
            'allow_guests' => 'required',
            'gravatar' => 'required',
            'gravatar_imageset' => 'required|string',
            'min_length' => 'required|integer|min:1',
            'max_length' => 'required|integer|gte:min_length',
            'throttle_max_rate' => 'required|integer|min:1',
            'throttle_per_minutes' => 'required|integer|min:1',
            'moderation' => 'required',
            'moderation_keys' => 'nullable|array',
            'moderation_keys.*' => 'required|string|min:1|max:100',
            'blacklist_keys' => 'nullable|array',
            'blacklist_keys.*' => 'required|string|min:1|max:100',
            'censor' => 'required',
            'censors_text' => 'nullable|array',
            'censors_text.*' => 'required|string|min:1|max:100',
            'max_links' => 'required|integer|min:0',
            'duplicates_check' => 'required',
            'report_status' => 'required|string',
            'max_reports' => 'required|integer|min:1',
            'disk_filesystem' => 'required|string',
            'upload_rules' => 'nullable|array',
            'upload_rules.*' => 'required|string|min:1|max:255',
            'upload_rules_max' => 'required|integer|min:1',
            'user_column_name' => 'required|string',
            'user_column_email' => 'required|string',
            'user_column_url' => 'required|string',
            'user_column_avatar_url' => 'required|string',
            'nsfw' => 'required',
            'nsfw_api_user' => 'nullable|string',
            'nsfw_api_key' => 'nullable|string',
            'recaptcha' => 'required',
            'recaptcha_key' => 'nullable|string',
            'recaptcha_secret' => 'nullable|string',
        ]);

        $settingRepository->set($request);

        return back()->with('success', __('vgcomment::admin.settings_saved'));
    }

    public function updateComment(Request $request, $id): RedirectResponse
    {
        $comment = Comment::withTrashed()->findOrFail($id);

        $validated = $request->validate([
            'status' => 'nullable|string|in:'.implode(',', Comment::STATUSES),
            'content' => 'nullable|string|max:5000',
        ]);

        $payload = array_filter($validated, fn ($value) => ! is_null($value) && $value !== '');

        if ($payload === []) {
            return back()->with('error', __('vgcomment::admin.nothing_to_update'));
        }

        if ($comment->trashed() && isset($payload['status'])) {
            CommentServiceFacade::restoreCommentTree($comment);
            $comment->refresh();
        }

        $comment->update($payload);
        Comment::flushQueryCache();

        return back()->with('success', __('vgcomment::admin.comment_updated'));
    }

    public function deleteComment($id): RedirectResponse
    {
        $comment = Comment::findOrFail($id);

        CommentServiceFacade::softDeleteComment($comment);
        Comment::flushQueryCache();

        return back()->with('success', __('vgcomment::admin.comment_deleted'));
    }

    public function restoreComment($id): RedirectResponse
    {
        $comment = Comment::onlyTrashed()->findOrFail($id);

        CommentServiceFacade::restoreCommentTree($comment, true);
        Comment::flushQueryCache();

        return back()->with('success', __('vgcomment::admin.comment_restored'));
    }

    public function forceDeleteComment($id): RedirectResponse
    {
        $comment = Comment::onlyTrashed()->findOrFail($id);

        if (is_null($comment->parent_id)) {
            $comment->replies()->withTrashed()->forceDelete();
        } else {
            Comment::withTrashed()->where('parent_id', $comment->id)->forceDelete();
        }

        $comment->reactions()->forceDelete();
        $comment->votes()->forceDelete();
        $comment->reports()->forceDelete();
        $comment->files()->forceDelete();
        $comment->forceDelete();
        Comment::flushQueryCache();

        return back()->with('success', __('vgcomment::admin.comment_force_deleted'));
    }

    protected function buildValue($type, $key, $options = null): array
    {
        $value = Config::get('vgcomment.'.$key) ?? '';

        if ($type === 'array') {
            $value = collect(is_array($value) ? $value : [])
                ->map(fn ($item) => is_string($item) ? trim($item) : $item)
                ->filter(fn ($item) => is_string($item) && $item !== '')
                ->values()
                ->all();
        }

        return [
            'type' => $type,
            'value' => $value,
            'options' => $options,
        ];
    }
}
