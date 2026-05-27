@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['style' => 'font-size: 0.875rem; color: #fca5a5; list-style: none; padding: 0; margin: 4px 0 0 0;']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
