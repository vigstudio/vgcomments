<?php

namespace Vigstudio\VgComment\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Vigstudio\VgComment\Facades\CommentServiceFacade;
use Vigstudio\VgComment\Repositories\Interface\SettingInterface;
use Illuminate\Support\Facades\Config;
use Vigstudio\VgComment\Models\Comment;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('vgcomment-moderate');
    }

    public function dashboard(Request $request)
    {
        $comments = CommentServiceFacade::getAdmin($request->all());
        $tabs = [
            'all',
            'pending',
            'approved',
            'spam',
            'trash',
            'reported',
            'deleted',
        ];

        return view('vgcomment::dashboard', compact('comments', 'tabs'));
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
        $request->validate([
            'prefix' => 'required|string',
            'allow_guests' => 'required',
            'gravatar' => 'required',
            'gravatar_imageset' => 'required|string',
            'min_length' => 'required|integer',
            'max_length' => 'required|integer',
            'throttle_max_rate' => 'required|integer',
            'throttle_per_minutes' => 'required|integer',
            'moderation' => 'required',
            'moderation_keys' => 'array',
            'moderation_keys.*' => 'string',
            'blacklist_keys' => 'array',
            'blacklist_keys.*' => 'string',
            'censor' => 'required',
            'censors_text' => 'array',
            'censors_text.*' => 'string',
            'max_links' => 'required|integer',
            'duplicates_check' => 'required',
            'report_status' => 'required|string',
            'max_reports' => 'required|integer',
            'disk_filesystem' => 'required|string',
            'upload_rules' => 'required|array',
            'upload_rules.*' => 'string',
            'upload_rules_max' => 'required|integer',
            'user_column_name' => 'required|string',
            'user_column_email' => 'required|string',
            'user_column_url' => 'required|string',
            'user_column_avatar_url' => 'required|string',
            'nsfw' => 'required',
            'nsfw_api_user' => 'string',
            'nsfw_api_key' => 'string',
            'recaptcha' => 'required',
            'recaptcha_key' => 'string',
            'recaptcha_secret' => 'string',
        ]);

        $request->merge([
            'censors_text' => $request->censors_text ?? [],
            'moderation_keys' => $request->moderation_keys ?? [],
            'blacklist_keys' => $request->blacklist_keys ?? [],
            'upload_rules' => $request->upload_rules ?? [],
        ]);

        $settingRepository->set($request);

        return back()->with('success', 'Update success');
    }

    public function updateComment(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);

        $comment->update($request->all());

        return back()->with('success', 'Update success');
    }

    public function deleteComment($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->replies()->delete();
        $comment->delete();

        return back()->with('success', 'Update success');
    }

    public function restoreComment($id)
    {
        $comment = Comment::onlyTrashed()->findOrFail($id);
        $comment->restore();

        $comment->replies()->onlyTrashed()->restore();

        return back()->with('success', 'Update success');
    }

    public function forceDeleteComment($id)
    {
        $comment = Comment::onlyTrashed()->findOrFail($id);
        $comment->replies()->forceDelete();
        $comment->reactions()->forceDelete();
        $comment->reports()->forceDelete();
        $comment->files()->forceDelete();
        $comment->forceDelete();

        return back()->with('success', 'Update success');
    }

    protected function buildValue($type, $key, $options = null)
    {
        return [
            'type' => $type,
            'value' => Config::get('vgcomment.' . $key) ?? '',
            'options' => $options,
        ];
    }
}
