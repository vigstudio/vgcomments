@extends('vgcomment::layouts.app')
@section('content')
    <header>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-gray-900">{{ __('vgcomment::admin.setting') }}</h1>
        </div>
    </header>
    <main>
        <div class="mx-auto max-w-7xl sm:px-2 lg:px-3" x-data>
            <!-- Replace with your content -->
            {{-- @dd($config) --}}
            <div class="px-4 sm:px-6 lg:px-8">

                <form action="" method="post">
                    @csrf
                    @foreach ($config as $key => $value)
                        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-5 border-t border-gray-200 mt-6">
                            <label for="last-name" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">{{ $key }}</label>
                            <div class="mt-1 sm:col-span-2 sm:mt-0">
                                @if (gettype($value) == 'string')
                                    <x-vgcomment::form.input type="text" name="{{ $key }}" value="{{ $value }}" />
                                @elseif(gettype($value) == 'integer')
                                    <x-vgcomment::form.input type="number" name="{{ $key }}" value="{{ $value }}" />
                                @elseif(gettype($value) == 'boolean')
                                    <x-vgcomment::form.select name="{{ $key }}" value="{{ $value }}" />
                                @elseif(gettype($value) == 'array')
                                    @foreach ($value as $itemKey => $item)
                                        <x-vgcomment::form.input type="text" name="{{ $key }}[{{ $itemKey }}]" value="{{ $item }}" />
                                    @endforeach
                                @endif
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
