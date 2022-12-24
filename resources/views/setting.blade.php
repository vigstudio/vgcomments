@extends('vgcomment::layouts.app')
@section('content')
    <header>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-gray-900">{{ __('vgcomment::admin.setting') }}</h1>
        </div>
    </header>
    <main>
        <div class="mx-auto max-w-7xl sm:px-2 lg:px-3">
            <!-- Replace with your content -->
            {{-- @dd($config) --}}
            <div class="px-4 sm:px-6 lg:px-8">

                @if ($errors->any())
                    @foreach ($errors->all() as $error)
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">{{ $error }}</strong>
                            <span class="block sm:inline">{{ $error }}</span>
                        </div>
                    @endforeach
                @endif
                <form action="{{ route('vgcomments.admin.setting.post') }}" method="post">
                    @csrf
                    @foreach ($config as $key => $value)
                        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-5 border-t border-gray-200 mt-6">
                            <label for="last-name" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">{{ trans('vgcomment::admin.' . $key . '_label') }}</label>
                            <div class="mt-1 sm:col-span-2 sm:mt-0">
                                @if (gettype($value) == 'string')
                                    <x-vgcomment::form.input type="text" name="{{ $key }}" value="{{ $value }}" />
                                @elseif(gettype($value) == 'integer')
                                    <x-vgcomment::form.input type="number" name="{{ $key }}" value="{{ $value }}" />
                                @elseif(gettype($value) == 'boolean')
                                    <x-vgcomment::form.select name="{{ $key }}" value="{{ $value }}" />
                                @elseif(gettype($value) == 'array')
                                    <div x-data='{
                                        items: @json($value),
                                        pushItem (){
                                            this.items.push("");
                                            console.log(this.items);
                                        }
                                    }'>

                                        <div x-for="(item, key) in items" :key="key">
                                            <x-vgcomment::form.input type="text" name="{{ $key }}[]" x-bind:value="item" />
                                            <button type="button" :data-key="key" class="inline-flex items-center rounded border border-transparent bg-indigo-100 px-2.5 py-1.5 text-xs font-medium text-indigo-700 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 mt-5">Remove</button>
                                        </div>

                                        <button type="button" x-on:click='pushItem()' class="inline-flex items-center rounded border border-transparent bg-indigo-100 px-2.5 py-1.5 text-xs font-medium text-indigo-700 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 mt-5">+ Add</button>
                                    </div>
                                @endif

                                <p class="mt-2 text-sm text-gray-500">{{ trans('vgcomment::admin.' . $key . '_description') }}</p>
                            </div>

                        </div>
                    @endforeach

                    <button class="btn-orange" type="submit">Submit</button>
                </form>

            </div>
            <!-- /End replace -->
        </div>
    </main>
@endsection
