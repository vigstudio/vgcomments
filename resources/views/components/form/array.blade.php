@props([
    'key' => null,
    'value' => [],
    'placeholder' => null,
])

@php
    $items = collect(is_array($value) ? $value : [])
        ->map(fn ($item) => is_string($item) ? trim($item) : $item)
        ->filter(fn ($item) => is_string($item) && $item !== '')
        ->values()
        ->all();
@endphp

<div
    class="admin-tags"
    x-data="{
        items: @js($items),
        draft: '',
        error: '',
        add() {
            const value = this.draft.trim();
            if (!value) {
                this.error = @js(__('vgcomment::admin.tag_empty'));
                return;
            }
            if (this.items.some((item) => item.toLowerCase() === value.toLowerCase())) {
                this.error = @js(__('vgcomment::admin.tag_duplicate'));
                return;
            }
            this.items.push(value);
            this.draft = '';
            this.error = '';
        },
        remove(index) {
            this.items.splice(index, 1);
            this.error = '';
        }
    }"
>
    <div class="admin-tags__composer">
        <input
            type="text"
            class="admin-field"
            placeholder="{{ $placeholder ?? __('vgcomment::admin.tag_placeholder') }}"
            x-model="draft"
            @keydown.enter.prevent="add()"
            @input="error = ''"
        >
        <button type="button" class="btn-primary" @click.prevent="add()">
            {{ __('vgcomment::admin.add') }}
        </button>
    </div>

    <p class="admin-tags__error" x-show="error" x-text="error" x-cloak></p>

    <div class="admin-tags__list" x-show="items.length">
        <template x-for="(item, index) in items" :key="item + '-' + index">
            <span class="admin-chip">
                <span x-text="item"></span>
                <button type="button" class="admin-chip__remove" @click="remove(index)" :aria-label="'Remove ' + item">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5">
                        <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                    </svg>
                </button>
                <input type="hidden" name="{{ $key }}[]" :value="item">
            </span>
        </template>
    </div>

    <p class="admin-tags__empty" x-show="!items.length" x-cloak>{{ __('vgcomment::admin.tag_empty_list') }}</p>
</div>
