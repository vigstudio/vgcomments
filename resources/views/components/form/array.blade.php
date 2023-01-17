@props([
    'key' => null,
    'value' => [],
])

<div x-data='{
    items: @json($value),
    value: "",
    addKey (){
        this.items.push(this.value);
        this.value = "";
    },
    removeKey (key){
        this.items.splice(key, 1);
    }
}'>
    <div class="mt-1 flex rounded-md ">
        <x-vgcomment::form.input type="text" placeholder="Input key" x-model="value" @keydown.enter.prevent="addKey()" />
        <span class="pl-3 pt-3 align-middle">
            <button type="button" @click.prevent="addKey()" class="inline-flex items-center rounded border border-transparent bg-indigo-100 px-2.5 py-1.5 text-xs font-medium text-indigo-700 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                {{ __('vgcomment::admin.add') }}
            </button>
        </span>
    </div>

    <template x-for="(item, index) in items" :key="index">
        <div class="mt-3 ml-1 text-xs inline-flex items-center font-bold leading-sm px-3 py-1 rounded-full bg-white text-gray-700 border">
            <span x-text="item"></span>
            <button type="button" x-on:click="removeKey(index)" class="pl-3 inline-flex items-center rounded-full border border-transparent">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <x-vgcomment::form.input type="hidden" name="{{ $key }}[]" x-bind:value="item" />
        </div>

    </template>

</div>
