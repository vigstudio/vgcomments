<select {{ $attributes }} class="block w-full max-w-lg mt-2 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:max-w-xs sm:text-sm">
    <option @selected($value) value="true">{{ __('vgcomment::admin.true') }}</option>
    <option @selected(!$value) value="false">{{ __('vgcomment::admin.false') }}</option>
</select>
