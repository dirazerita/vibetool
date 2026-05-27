@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['style' => 'font-weight: 500; font-size: 0.875rem; color: #86efac;']) }}>
        {{ $status }}
    </div>
@endif
