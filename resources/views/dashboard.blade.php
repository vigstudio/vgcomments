@extends('vgcomment::layouts.app')
@section('content')
    <header>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-gray-900">{{ __('vgcomment::admin.dashboard') }}</h1>
        </div>
    </header>
    <main>
        <div class="mx-auto max-w-7xl sm:px-2 lg:px-3" x-data="{
            tab: '{{ request()->get('status') ? request()->get('status') : 'all' }}',
        }">
            <!-- Replace with your content -->
            <div class="px-4 py-8 sm:px-0" id="app">
                <div class="px-4 sm:px-6 lg:px-8">
                    <div class="my-10">
                        <div class="sm:hidden">
                            <label for="tabs" class="sr-only">{{ __('vgcomment:admin.select_tab') }}</label>
                            <!-- Use an "onChange" listener to redirect the user to the selected tab URL. -->
                            <select id="tabs" name="tabs" @change="tab = $event.target.value;" class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach ($tabs as $key)
                                    <option value="{{ $key }}">{{ __('vgcomment::admin.' . $key) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="hidden sm:block">
                            <nav class="isolate flex divide-x divide-gray-200 rounded-lg shadow" aria-label="Tabs">
                                @foreach ($tabs as $key)
                                    <a href="{{ route('vgcomments.admin.dashboard', ['status' => $key]) }}"
                                       x-bind:class="{
                                           'text-gray-500 hover:text-gray-700 group relative min-w-0 flex-1 overflow-hidden bg-white py-4 px-4 text-sm font-medium text-center hover:bg-gray-50 focus:z-10': tab !== '{{ $key }}',
                                           'text-gray-900 rounded-l-lg group relative min-w-0 flex-1 overflow-hidden bg-white py-4 px-4 text-sm font-medium text-center hover:bg-gray-50 focus:z-10': tab === '{{ $key }}',
                                       }"
                                       aria-current="page">
                                        <span>{{ __('vgcomment::admin.' . $key) }}</span>
                                        <span x-show="tab !== '{{ $key }}'" aria-hidden="true" class="bg-transparent absolute inset-x-0 bottom-0 h-0.5"></span>
                                        <span x-show="tab === '{{ $key }}'" aria-hidden="true" class="bg-indigo-500 absolute inset-x-0 bottom-0 h-0.5"></span>
                                    </a>
                                @endforeach
                            </nav>
                        </div>
                    </div>

                    <div class="sm:flex sm:items-center">
                        <div class="sm:flex-auto">
                            <h1 class="text-xl font-semibold text-gray-900">{{ __('vgcomment::admin.comments_table') }} ({{ $comments->total() }})</h1>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-col">
                        <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                            <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-300">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">{{ __('vgcomment::admin.author_name') }}</th>
                                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{ __('vgcomment::admin.user_agent') }}</th>
                                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{ __('vgcomment::admin.content') }}</th>
                                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{ __('vgcomment::admin.status') }}</th>
                                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                                    <span class="sr-only">Edit</span>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 bg-white">
                                            @foreach ($comments as $comment)
                                                <tr>
                                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                                        <div class="flex items-center">
                                                            <div class="h-10 w-10 flex-shrink-0">
                                                                <img class="h-10 w-10 rounded-full" src="{{ $comment->getAuthorAvatarAttribute() }}" alt="">
                                                            </div>
                                                            <div class="ml-4">
                                                                <div class="font-medium text-gray-900">{{ $comment->author_name }}</div>
                                                                <div class="text-gray-500">{{ $comment->author_email }}</div>
                                                                <div class="text-gray-500">{{ $comment->author_ip }}</div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-3 py-4 text-sm text-gray-500">
                                                        <div class="text-gray-900">{{ $comment->time }}</div>
                                                        <div class="text-gray-500">{{ $comment->user_agent }}</div>
                                                    </td>
                                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 min-w-[400px]">
                                                        <div class="text-gray-500">{{ Str::limit($comment->content, 50, '........') }}</div>
                                                        @includeWhen(!$comment->trashed(), 'vgcomment::dashboard._td-content', ['comment' => $comment])
                                                    </td>
                                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                        <span class="inline-flex rounded-full bg-green-100 px-2 text-xs font-semibold leading-5 text-green-800">{{ $comment->status_name }}</span>
                                                    </td>
                                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                                        @include('vgcomment::dashboard._td-action', ['comment' => $comment])
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- /End replace -->
        </div>
    </main>
@endsection
