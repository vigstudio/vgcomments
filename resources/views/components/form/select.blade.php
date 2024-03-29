@props([
    'options' => [],
    'value' => null,
])
<select {{ $attributes }} class="block w-full max-w-lg mt-2 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:max-w-xs sm:text-sm">
    @foreach ($options as $option)
        <option @selected($option == $value) value="{{ $option }}">{{ $option }}</option>
    @endforeach
</select>
