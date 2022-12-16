@extends('vgcomment::layouts.app')
@section('content')
    <header>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-gray-900">{{ __('vgcomment::admin.dashboard') }}</h1>
        </div>
    </header>
    <main>
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
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
                                                    <td>{{ $comment->status_name }}</td>
                                                    <td>
                                                        <button type="button" class="btn">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 p-1">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            </svg>
                                                        </button>

                                                        <button type="button" class="btn">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 p-1">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                            </svg>

                                                        </button>


                                                        <button type="button" class="btn">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 p-1">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                                            </svg>
                                                        </button>
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
