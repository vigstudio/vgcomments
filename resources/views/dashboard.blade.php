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
                    <div class="sm:flex sm:items-center">
                        <div class="sm:flex-auto">
                            <h1 class="text-xl font-semibold text-gray-900">{{ __('vgcomment::admin.comments_table') }} ({{ $comments->total() }})</h1>
                        </div>
                    </div>
                    <div class="mt-8 flex flex-col">
                        <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                            <div>
                                <div class="sm:hidden">
                                    <label for="tabs" class="sr-only">Select a tab</label>
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

                            <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                                    <table class="table-admin">
                                        <thead>
                                            <tr>
                                                <th>{{ __('vgcomment::admin.author_name') }}</th>
                                                <th>{{ __('vgcomment::admin.author_email') }}</th>
                                                <th>{{ __('vgcomment::admin.author_ip') }}</th>
                                                <th>{{ __('vgcomment::admin.user_agent') }}</th>
                                                <th>{{ __('vgcomment::admin.content') }}</th>
                                                <th>{{ __('vgcomment::admin.url') }}</th>
                                                <th>{{ __('vgcomment::admin.status') }}</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($comments as $comment)
                                                <tr>
                                                    <th>{{ $comment->author_name }}</th>
                                                    <td>{{ $comment->author_email }}</td>
                                                    <td>{{ $comment->author_ip }}</td>
                                                    <td>{{ $comment->user_agent }}</td>
                                                    <td>{{ $comment->content }}</td>
                                                    <td>
                                                        <a href="{{ $comment->url }}" target="_blank" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">{{ $comment->url }}</a>
                                                    </td>
                                                    <td>{{ $comment->status_name }}</td>
                                                    <td>

                                                        @if ($comment->status == 'pending')
                                                            <form method="POST" action="{{ route('vgcomments.admin.comment.update', $comment->id) }}">
                                                                @csrf
                                                                @method('PUT')
                                                                <input type="hidden" name="status" value="approved">
                                                                <button type="submit" class="btn-success" title="{{ __('vgcomment::admin.approved') }}">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                                                    </svg>
                                                                    <span>{{ __('vgcomment::admin.approved') }}</span>
                                                                </button>
                                                            </form>
                                                        @endif

                                                        <form method="POST" action="{{ route('vgcomments.admin.comment.delete', $comment->id) }}">
                                                            @csrf
                                                            @method('DELETE')

                                                            <button type="submit" class="btn-danger">
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 p-1">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                                                </svg>
                                                                <span>{{ __('vgcomment::admin.delete') }}</span>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-5">
                                    {{ $comments->links('vgcomment::layouts.pagination.tailwind') }}
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
