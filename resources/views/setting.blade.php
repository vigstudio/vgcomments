@extends('vgcomment::layouts.app')

@section('title', __('vgcomment::admin.setting') . ' · VgComments')
@section('heading', __('vgcomment::admin.setting'))
@section('subheading', __('vgcomment::admin.settings_subtitle'))

@section('content')
<div x-data="{ tab: @js(old('tab', 'general')) }" class="admin-settings space-y-5">
    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-semibold">{{ __('vgcomment::admin.settings_invalid') }}</p>
            <ul class="mt-2 list-disc space-y-1 pl-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('vgcomments.admin.setting.post') }}" method="post" class="space-y-5">
        @csrf
        <input type="hidden" name="tab" :value="tab">

        <div class="admin-settings__tabs">
            <div class="sm:hidden p-3">
                <label class="sr-only">{{ __('vgcomment::admin.select_tab') }}</label>
                <select class="admin-field" @change="tab = $event.target.value">
                    @foreach ($config as $key => $section)
                        <option value="{{ $key }}" :selected="tab === '{{ $key }}'">{{ __('vgcomment::admin.'.$key) }}</option>
                    @endforeach
                </select>
            </div>
            <nav class="hidden sm:flex" aria-label="Tabs">
                @foreach ($config as $key => $section)
                    <button
                        type="button"
                        class="admin-settings__tab"
                        :class="tab === '{{ $key }}' && 'admin-settings__tab--active'"
                        @click="tab = '{{ $key }}'"
                    >
                        {{ __('vgcomment::admin.'.$key) }}
                    </button>
                @endforeach
            </nav>
        </div>

        @foreach ($config as $configKey => $section)
            <section x-show="tab === '{{ $configKey }}'" x-cloak class="admin-settings__panel">
                <header class="admin-settings__panel-head">
                    <div>
                        <h2>{{ __('vgcomment::admin.'.$configKey) }}</h2>
                        <p>{{ __('vgcomment::admin.'.$configKey.'_help') }}</p>
                    </div>
                </header>

                <div class="admin-settings__fields">
                    @foreach ($section as $key => $value)
                        <div class="admin-settings__field">
                            <div class="admin-settings__meta">
                                <label for="setting-{{ $key }}">{{ trans('vgcomment::admin.'.$key.'_label') }}</label>
                                <p>{{ trans('vgcomment::admin.'.$key.'_description') }}</p>
                            </div>
                            <div class="admin-settings__control">
                                @if ($value['type'] == 'string')
                                    <x-vgcomment::form.input id="setting-{{ $key }}" type="text" name="{{ $key }}" :value="old($key, $value['value'])" />
                                @elseif ($value['type'] == 'number')
                                    <x-vgcomment::form.input id="setting-{{ $key }}" type="number" name="{{ $key }}" :value="old($key, $value['value'])" />
                                @elseif ($value['type'] == 'boolean')
                                    <x-vgcomment::form.boolean id="setting-{{ $key }}" name="{{ $key }}" :value="old($key, $value['value'])" />
                                @elseif ($value['type'] == 'select')
                                    <x-vgcomment::form.select id="setting-{{ $key }}" name="{{ $key }}" :options="$value['options']" :value="old($key, $value['value'])" />
                                @elseif ($value['type'] == 'array')
                                    <x-vgcomment::form.array :key="$key" :value="old($key, $value['value'])" />
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach

        <div class="admin-settings__footer">
            <p>{{ __('vgcomment::admin.settings_footer_hint') }}</p>
            <button class="btn-primary" type="submit">{{ __('vgcomment::admin.save_settings') }}</button>
        </div>
    </form>
</div>
@endsection
