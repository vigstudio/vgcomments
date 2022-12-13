<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>VgComment - Admin</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap">

    <link href="{{ asset('vendor/vgcomments/css/style.css') }}" rel="stylesheet">
</head>

<body class="font-sans antialiased">
    <!--
  This example requires updating your template:

  ```
  <html class="h-full">
  <body class="h-full">
  ```
-->
    <div class="min-h-full">
        <nav class="border-b border-gray-200 bg-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between">
                    <div class="flex">
                        <div class="flex flex-shrink-0 items-center">
                            <img class="block h-8 w-auto lg:hidden" src="https://tailwindui.com/img/logos/mark.svg?color=indigo&shade=600" alt="Your Company">
                            <img class="hidden h-8 w-auto lg:block" src="https://tailwindui.com/img/logos/mark.svg?color=indigo&shade=600" alt="Your Company">
                        </div>
                        <div class="hidden sm:-my-px sm:ml-6 sm:flex sm:space-x-8">
                            <a href="{{ route('vgcomments.admin.dashboard') }}" @class([
                                'border-indigo-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium' => request()->routeIs(
                                    'vgcomments.admin.dashboard'
                                ),
                                'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium' => !request()->routeIs(
                                    'vgcomments.admin.dashboard'
                                ),
                            ])>Dashboard</a>

                            <a href="{{ route('vgcomments.admin.setting') }}" @class([
                                'border-indigo-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium' => request()->routeIs(
                                    'vgcomments.admin.setting'
                                ),
                                'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium' => !request()->routeIs(
                                    'vgcomments.admin.setting'
                                ),
                            ])>Setting</a>

                        </div>
                    </div>

                    <div class="-mr-2 flex items-center sm:hidden">
                        <!-- Mobile menu button -->
                        <button type="button" class="inline-flex items-center justify-center rounded-md bg-white p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" aria-controls="mobile-menu" aria-expanded="false">
                            <span class="sr-only">Open main menu</span>

                            <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>

                            <svg class="hidden h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu, show/hide based on menu state. -->
            <div class="sm:hidden" id="mobile-menu">
                <div class="space-y-1 pt-2 pb-3">
                    <a href="{{ route('vgcomments.admin.dashboard') }}"
                       @class([
                           'bg-indigo-50 border-indigo-500 text-indigo-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium' => request()->routeIs(
                               'vgcomments.admin.dashboard'
                           ),
                           'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 block pl-3 pr-4 py-2 border-l-4 text-base font-medium' => !request()->routeIs(
                               'vgcomments.admin.dashboard'
                           ),
                       ])>Dashboard</a>

                    <a href="{{ route('vgcomments.admin.setting') }}" @class([
                        'bg-indigo-50 border-indigo-500 text-indigo-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium' => request()->routeIs(
                            'vgcomments.admin.setting'
                        ),
                        'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 block pl-3 pr-4 py-2 border-l-4 text-base font-medium' => !request()->routeIs(
                            'vgcomments.admin.setting'
                        ),
                    ])>Setting</a>

                </div>

            </div>
        </nav>

        <div class="py-10">
            <header>
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <h1 class="text-3xl font-bold leading-tight tracking-tight text-gray-900">VgComment Admin</h1>
                </div>
            </header>
            <main>
                <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <!-- Replace with your content -->
                    <div class="px-4 py-8 sm:px-0" id="app">
                        @yield('content')
                    </div>
                    <!-- /End replace -->
                </div>
            </main>
        </div>
    </div>



</body>

<script>
    window.prefix = "/{{ config('vgcomment.prefix') }}/admin";
</script>
<script src="{{ asset('vendor/vgcomments/js/app.js') }}"></script>

</html>
