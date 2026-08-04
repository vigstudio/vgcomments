<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'VgComments Admin')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link href="{{ asset('vendor/vgcomments/css/style.css') }}?v={{ @filemtime(public_path('vendor/vgcomments/css/style.css')) ?: time() }}" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
</head>
<body class="admin-shell h-full font-sans antialiased" style="font-family: Figtree, ui-sans-serif, system-ui, sans-serif;">
<div
    class="min-h-full"
    x-data="{
        sidebarOpen: false,
        toast: @js(session('success') ? ['type' => 'success', 'message' => session('success')] : (session('error') ? ['type' => 'error', 'message' => session('error')] : null)),
        init() {
            if (this.toast) {
                setTimeout(() => this.toast = null, 4200);
            }
        }
    }"
>
    <div
        x-show="sidebarOpen"
        x-cloak
        class="admin-overlay"
        @click="sidebarOpen = false"
    ></div>

    <aside
        class="admin-sidebar -translate-x-full lg:translate-x-0"
        :class="{ 'translate-x-0': sidebarOpen }"
    >
        <div class="admin-sidebar__brand">
            <span class="admin-sidebar__logo">VG</span>
            <div>
                <p class="admin-sidebar__title">VgComments</p>
                <p class="admin-sidebar__subtitle">Moderation</p>
            </div>
        </div>

        <nav class="admin-sidebar__nav">
            <a
                href="{{ route('vgcomments.admin.dashboard') }}"
                @class([
                    'admin-nav-link' => true,
                    'admin-nav-link--active' => request()->routeIs('vgcomments.admin.dashboard'),
                ])
            >
                <span>{{ __('vgcomment::admin.comments') }}</span>
            </a>
            <a
                href="{{ route('vgcomments.admin.setting') }}"
                @class([
                    'admin-nav-link' => true,
                    'admin-nav-link--active' => request()->routeIs('vgcomments.admin.setting*'),
                ])
            >
                <span>{{ __('vgcomment::admin.setting') }}</span>
            </a>
        </nav>

        <div class="admin-sidebar__footer">
            <a href="{{ url('/') }}">← {{ __('vgcomment::admin.back_to_site') }}</a>
        </div>
    </aside>

    <div class="lg:pl-64">
        <header class="admin-topbar">
            <div class="flex h-16 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        class="inline-flex rounded-md border border-slate-300 bg-white p-2 text-slate-700 hover:bg-slate-50 lg:hidden"
                        @click="sidebarOpen = true"
                    >
                        <span class="sr-only">Open sidebar</span>
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                    </button>
                    <div>
                        <h1 class="text-lg font-semibold text-slate-900">@yield('heading', __('vgcomment::admin.dashboard'))</h1>
                        <p class="hidden text-sm text-slate-600 sm:block">@yield('subheading', __('vgcomment::admin.dashboard_subtitle'))</p>
                    </div>
                </div>
                <div class="text-sm font-medium text-slate-700">
                    @auth
                        {{ auth()->user()->name ?? auth()->user()->email ?? 'Moderator' }}
                    @endauth
                </div>
            </div>
        </header>

        <main class="px-4 py-6 sm:px-6 lg:px-8">
            @yield('content')
        </main>
    </div>

    <div
        x-show="toast"
        x-cloak
        x-transition
        class="pointer-events-none fixed inset-x-0 bottom-6 z-[60] flex justify-center px-4 sm:inset-x-auto sm:right-6 sm:justify-end"
    >
        <div
            class="pointer-events-auto max-w-sm rounded-lg border px-4 py-3 shadow-lg"
            :class="toast?.type === 'error' ? 'border-red-200 bg-red-50 text-red-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800'"
        >
            <p class="text-sm font-medium" x-text="toast?.message"></p>
        </div>
    </div>
</div>

<style>[x-cloak]{display:none!important}</style>
<script src="{{ asset('vendor/vgcomments/js/app.js') }}?v={{ @filemtime(public_path('vendor/vgcomments/js/app.js')) ?: time() }}"></script>
</body>
</html>
