@props(['value'])

<label {{ $attributes->merge(['style' => 'display: block; font-weight: 500; font-size: 0.875rem; color: #cbd5e1;']) }}>
    {{ $value ?? $slot }}
</label>
