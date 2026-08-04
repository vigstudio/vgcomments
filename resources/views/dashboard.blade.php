@extends('vgcomment::layouts.app')

@section('title', __('vgcomment::admin.dashboard') . ' · VgComments')
@section('heading', __('vgcomment::admin.comments'))
@section('subheading', __('vgcomment::admin.dashboard_subtitle'))

@section('content')
@php
    $activeStatus = $filters['status'] ?? 'all';
    $queryBase = array_filter([
        'q' => $filters['q'] ?? null,
        'page_id' => $filters['page_id'] ?? null,
        'from' => $filters['from'] ?? null,
        'to' => $filters['to'] ?? null,
    ], fn ($value) => filled($value));
@endphp

<div
    x-data="{
        selected: [],
        drawer: null,
        toggleAll(event) {
            const ids = Array.from(document.querySelectorAll('[data-comment-id]')).map((el) => Number(el.dataset.commentId));
            this.selected = event.target.checked ? ids : [];
        },
        toggleOne(id, checked) {
            if (checked) {
                if (!this.selected.includes(id)) this.selected.push(id);
            } else {
                this.selected = this.selected.filter((item) => item !== id);
            }
        },
        openDrawer(comment) {
            this.drawer = comment;
        },
        closeDrawer() {
            this.drawer = null;
        },
        confirmBulk(action) {
            if (!this.selected.length) return false;
            const risky = ['delete', 'force_delete', 'spam', 'trash'].includes(action);
            if (!risky) return true;
            return window.confirm(@js(__('vgcomment::admin.bulk_confirm')));
        }
    }"
    class="space-y-6"
>
    <div class="grid grid-cols-2 gap-3 md:grid-cols-4 xl:grid-cols-7">
        @foreach ($tabs as $tab)
            <a
                href="{{ route('vgcomments.admin.dashboard', array_merge($queryBase, ['status' => $tab['key']])) }}"
                @class([
                    'admin-stat',
                    'admin-stat--active' => $activeStatus === $tab['key'],
                ])
            >
                <span class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $tab['label'] }}</span>
                <span class="text-2xl font-semibold text-slate-900">{{ number_format($tab['count']) }}</span>
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('vgcomments.admin.dashboard') }}" class="admin-card p-4">
        <input type="hidden" name="status" value="{{ $activeStatus }}">
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
            <div class="xl:col-span-2">
                <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('vgcomment::admin.search') }}</label>
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('vgcomment::admin.search_placeholder') }}" class="admin-input">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('vgcomment::admin.page_id') }}</label>
                <input type="text" name="page_id" value="{{ $filters['page_id'] ?? '' }}" class="admin-input">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('vgcomment::admin.from') }}</label>
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="admin-input">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('vgcomment::admin.to') }}</label>
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="admin-input">
            </div>
        </div>
        <div class="mt-4 flex flex-wrap items-center gap-2">
            <button type="submit" class="btn-primary">{{ __('vgcomment::admin.apply_filters') }}</button>
            <a href="{{ route('vgcomments.admin.dashboard', ['status' => $activeStatus]) }}" class="btn-secondary">{{ __('vgcomment::admin.reset_filters') }}</a>
            <div class="sm:hidden grow">
                <label class="sr-only">{{ __('vgcomment::admin.select_tab') }}</label>
                <select
                    class="admin-input"
                    onchange="window.location.href=this.value"
                >
                    @foreach ($tabs as $tab)
                        <option
                            value="{{ route('vgcomments.admin.dashboard', array_merge($queryBase, ['status' => $tab['key']])) }}"
                            @selected($activeStatus === $tab['key'])
                        >
                            {{ $tab['label'] }} ({{ $tab['count'] }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>

    <div
        x-show="selected.length"
        x-cloak
        class="admin-card sticky top-20 z-20 flex flex-wrap items-center gap-2 border-sky-200 bg-sky-50/90 p-3 backdrop-blur"
    >
        <p class="mr-2 text-sm font-medium text-slate-700">
            <span x-text="selected.length"></span> {{ __('vgcomment::admin.selected') }}
        </p>

        @foreach ([
            'approve' => __('vgcomment::admin.approved'),
            'pending' => __('vgcomment::admin.pending'),
            'spam' => __('vgcomment::admin.spam'),
            'trash' => __('vgcomment::admin.trash'),
            'delete' => __('vgcomment::admin.delete'),
            'restore' => __('vgcomment::admin.restore'),
            'force_delete' => __('vgcomment::admin.force_delete'),
        ] as $action => $label)
            <form method="POST" action="{{ route('vgcomments.admin.comments.bulk') }}" @submit="if (!confirmBulk('{{ $action }}')) $event.preventDefault()">
                @csrf
                <input type="hidden" name="action" value="{{ $action }}">
                <template x-for="id in selected" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
                <button type="submit" @class([
                    'btn-success' => $action === 'approve',
                    'btn-orange' => $action === 'pending',
                    'btn-danger' => in_array($action, ['spam', 'trash', 'delete', 'force_delete'], true),
                    'btn' => $action === 'restore',
                ])>{{ $label }}</button>
            </form>
        @endforeach
    </div>

    <div class="admin-card overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-slate-900">
                {{ __('vgcomment::admin.comments_table') }}
                <span class="font-normal text-slate-500">({{ number_format($comments->total()) }})</span>
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left">
                            <input type="checkbox" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500" @change="toggleAll($event)">
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('vgcomment::admin.author_name') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('vgcomment::admin.content') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('vgcomment::admin.status') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('vgcomment::admin.has_report') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('vgcomment::admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($comments as $comment)
                        @php
                            $drawerPayload = [
                                'id' => $comment->id,
                                'uuid' => $comment->uuid,
                                'author_name' => $comment->author_name,
                                'author_email' => $comment->author_email,
                                'author_ip' => $comment->author_ip,
                                'avatar' => $comment->getAuthorAvatarAttribute(),
                                'content' => $comment->content,
                                'content_html' => $comment->content_html,
                                'status' => $comment->trashed() ? 'deleted' : $comment->status,
                                'reports_count' => $comment->reports_count ?? 0,
                                'page_id' => $comment->page_id,
                                'url' => $comment->url,
                                'time' => optional($comment->created_at)->diffForHumans(),
                                'user_agent' => \Illuminate\Support\Str::limit((string) $comment->user_agent, 120),
                                'trashed' => $comment->trashed(),
                            ];
                        @endphp
                        <tr class="hover:bg-slate-50/80" data-comment-id="{{ $comment->id }}">
                            <td class="px-4 py-4 align-top">
                                <input
                                    type="checkbox"
                                    class="rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                                    :checked="selected.includes({{ $comment->id }})"
                                    @change="toggleOne({{ $comment->id }}, $event.target.checked)"
                                >
                            </td>
                            <td class="px-4 py-4 align-top">
                                <div class="flex items-start gap-3">
                                    <img src="{{ $comment->getAuthorAvatarAttribute() }}" alt="" class="h-10 w-10 rounded-full object-cover ring-1 ring-slate-200">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-slate-900">{{ $comment->author_name ?: '—' }}</p>
                                        <p class="truncate text-xs text-slate-500">{{ $comment->author_email ?: '—' }}</p>
                                        <p class="truncate text-xs text-slate-400">{{ $comment->author_ip }}</p>
                                        <p class="mt-1 text-xs text-slate-400">{{ optional($comment->created_at)->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 align-top">
                                @if ($comment->parent_id)
                                    <p class="mb-1 text-xs font-medium text-slate-500">
                                        {{ __('vgcomment::admin.reply_to', ['id' => $comment->parent_id]) }}
                                        @if ($comment->relationLoaded('parent') && $comment->parent)
                                            <span class="font-normal text-slate-400">· {{ \Illuminate\Support\Str::limit($comment->parent->author_name ?: $comment->parent->content, 40) }}</span>
                                        @endif
                                    </p>
                                @endif
                                <p class="max-w-xl text-sm text-slate-700">{{ \Illuminate\Support\Str::limit($comment->content, 160) }}</p>
                                @if ($comment->page_id)
                                    <p class="mt-1 text-xs text-slate-400">page: {{ $comment->page_id }}</p>
                                @endif
                                @include('vgcomment::dashboard._td-content', ['comment' => $comment])
                            </td>
                            <td class="px-4 py-4 align-top">
                                @include('vgcomment::dashboard._td-status', ['comment' => $comment])
                            </td>
                            <td class="px-4 py-4 align-top">
                                <span @class([
                                    'admin-badge',
                                    'bg-red-50 text-red-700' => ($comment->reports_count ?? 0) > 0,
                                    'bg-slate-100 text-slate-500' => ($comment->reports_count ?? 0) === 0,
                                ])>{{ $comment->reports_count ?? 0 }}</span>
                            </td>
                            <td class="px-4 py-4 align-top text-right">
                                <div class="flex flex-wrap justify-end gap-1">
                                    <button type="button" class="btn" @click="openDrawer(@js($drawerPayload))">{{ __('vgcomment::admin.edit') }}</button>
                                    @include('vgcomment::dashboard._td-action', ['comment' => $comment])
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-16 text-center text-sm text-slate-500">
                                {{ __('vgcomment::admin.empty_comments') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($comments->hasPages())
            <div class="border-t border-slate-200 px-4 py-3">
                {{ $comments->links('vgcomment::layouts.pagination.tailwind') }}
            </div>
        @endif
    </div>

    <div
        x-show="drawer"
        x-cloak
        class="fixed inset-0 z-50"
        @keydown.escape.window="closeDrawer()"
    >
        <div class="absolute inset-0 bg-slate-900/40" @click="closeDrawer()"></div>
        <aside class="admin-drawer ml-auto flex h-full flex-col" x-show="drawer" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <h3 class="text-base font-semibold text-slate-900" x-text="drawer?.author_name || '—'"></h3>
                    <p class="text-xs text-slate-500" x-text="drawer?.time"></p>
                </div>
                <button type="button" class="btn-secondary" @click="closeDrawer()">{{ __('vgcomment::admin.close') }}</button>
            </div>

            <div class="flex-1 space-y-5 overflow-y-auto px-5 py-5" x-show="drawer">
                <div class="rounded-lg bg-slate-50 p-3 text-sm text-slate-700" x-html="drawer?.content_html"></div>

                <dl class="grid grid-cols-1 gap-3 text-sm">
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-400">Email</dt>
                        <dd class="text-slate-700" x-text="drawer?.author_email || '—'"></dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-400">IP</dt>
                        <dd class="text-slate-700" x-text="drawer?.author_ip || '—'"></dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-400">{{ __('vgcomment::admin.page_id') }}</dt>
                        <dd class="text-slate-700" x-text="drawer?.page_id || '—'"></dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-400">User agent</dt>
                        <dd class="break-all text-slate-700" x-text="drawer?.user_agent || '—'"></dd>
                    </div>
                </dl>

                <template x-if="drawer && !drawer.trashed">
                    <form method="POST" :action="'{{ url(trim(config('vgcomment.prefix'), '/').'/admin/comment') }}/' + drawer.id + '/update'" :key="'edit-' + drawer.id">
                        @csrf
                        @method('PUT')
                        <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('vgcomment::admin.status') }}</label>
                        <select name="status" class="admin-input mb-3">
                            @foreach (\Vigstudio\VgComment\Models\Comment::STATUSES as $status)
                                <option value="{{ $status }}" :selected="drawer.status === '{{ $status }}'">{{ __('vgcomment::admin.'.$status) }}</option>
                            @endforeach
                        </select>
                        <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('vgcomment::admin.content') }}</label>
                        <textarea name="content" rows="6" class="admin-input" :value="drawer.content"></textarea>
                        <button type="submit" class="btn-primary mt-3 w-full">{{ __('vgcomment::admin.save_changes') }}</button>
                    </form>
                </template>

                <a x-show="drawer?.url" :href="drawer?.url" target="_blank" rel="noopener" class="btn-secondary inline-flex w-full">{{ __('vgcomment::admin.view_page') }}</a>
            </div>
        </aside>
    </div>
</div>
@endsection
