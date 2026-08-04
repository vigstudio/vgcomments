@props([
    'value' => false,
])

@php
    $checked = filter_var($value, FILTER_VALIDATE_BOOLEAN);
    $name = $attributes->get('name');
@endphp

<label
    class="admin-toggle"
    x-data="{ on: @js($checked) }"
>
    <input type="hidden" name="{{ $name }}" value="false">
    <input
        type="checkbox"
        name="{{ $name }}"
        value="true"
        class="peer sr-only"
        x-model="on"
        {{ $attributes->except(['name', 'value']) }}
    >
    <span class="admin-toggle__track" :class="on && 'admin-toggle__track--on'" aria-hidden="true">
        <span class="admin-toggle__thumb"></span>
    </span>
    <span class="admin-toggle__label" x-text="on ? @js(__('vgcomment::admin.enabled')) : @js(__('vgcomment::admin.disabled'))"></span>
</label>
