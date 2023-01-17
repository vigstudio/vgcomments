@extends('vgcomment::layouts.app')
@section('content')
    <header>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-gray-900">{{ __('vgcomment::admin.setting') }}</h1>
        </div>
    </header>
    <main>
        <div class="mx-auto max-w-7xl sm:px-2 lg:px-3" x-data="{
            tab: 'general',
        }">
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
                    <div>
                        <div class="sm:hidden">
                            <label for="tabs" class="sr-only">Select a tab</label>
                            <!-- Use an "onChange" listener to redirect the user to the selected tab URL. -->
                            <select id="tabs" name="tabs" @change="tab = $event.target.value;" class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach ($config as $key => $tab)
                                    <option value="{{ $key }}">{{ __('vgcomment::admin.' . $key) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="hidden sm:block">
                            <nav class="isolate flex divide-x divide-gray-200 rounded-lg shadow" aria-label="Tabs">
                                @foreach ($config as $key => $tab)
                                    <a x-bind:href="'#' + tab"
                                       x-on:click.prevent="tab = '{{ $key }}'"
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


                    @foreach ($config as $configKey => $tab)
                        <div x-show="tab === '{{ $configKey }}'">
                            @foreach ($tab as $key => $value)
                                <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-5 border-t border-gray-200 mt-6">
                                    <label for="last-name" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">{{ trans('vgcomment::admin.' . $key . '_label') }}</label>
                                    <div class="mt-1 sm:col-span-2 sm:mt-0">
                                        @if ($value['type'] == 'string')
                                            <x-vgcomment::form.input type="text" name="{{ $key }}" :value="$value['value']" />
                                        @elseif($value['type'] == 'number')
                                            <x-vgcomment::form.input type="number" name="{{ $key }}" :value="$value['value']" />
                                        @elseif($value['type'] == 'boolean')
                                            <x-vgcomment::form.boolean name="{{ $key }}" :value="$value['value']" />
                                        @elseif($value['type'] == 'select')
                                            <x-vgcomment::form.select name="{{ $key }}" :options="$value['options']" :value="$value['value']" />
                                        @elseif($value['type'] == 'array')
                                            <x-vgcomment::form.array name="{{ $key }}" :key="$key" :value="$value['value']" />
                                        @endif
                                        <p class="mt-2 text-sm text-gray-500">{{ trans('vgcomment::admin.' . $key . '_description') }}</p>
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    @endforeach

                    <button class="btn-orange mt-10" type="submit">Submit</button>
                </form>

            </div>
            <!-- /End replace -->
        </div>
    </main>
@endsection
