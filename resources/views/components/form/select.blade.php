@props([
    'options' => [],
    'value' => null,
])
<select {{ $attributes->merge(['class' => 'admin-field admin-field--select']) }}>
    @foreach ($options as $option)
        <option @selected((string) $option === (string) $value) value="{{ $option }}">{{ $option }}</option>
    @endforeach
</select>
