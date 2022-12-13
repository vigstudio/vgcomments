@extends('vgcomment::layouts.app')
@section('content')
    <div class="px-4 sm:px-6 lg:px-8">

        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-xl font-semibold text-gray-900">Setting</h1>
            </div>
        </div>

        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-5">
            <label for="last-name" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">Prefix Route</label>
            <div class="mt-1 sm:col-span-2 sm:mt-0">
                <input type="text" name="prefix" class="block w-full max-w-lg rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:max-w-xs sm:text-sm">
            </div>
        </div>

        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-5">
            <label for="last-name" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">Minium number of characters allowed in a comment</label>
            <div class="mt-1 sm:col-span-2 sm:mt-0">
                <input type="number" name="min_length" class="block w-full max-w-lg rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:max-w-xs sm:text-sm">
            </div>
        </div>

        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-5">
            <label for="last-name" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">Maximum number of characters allowed in a comment</label>
            <div class="mt-1 sm:col-span-2 sm:mt-0">
                <input type="number" name="max_length" class="block w-full max-w-lg rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:max-w-xs sm:text-sm">
            </div>
        </div>

    </div>
@endsection
