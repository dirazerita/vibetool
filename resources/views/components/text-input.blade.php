@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-md shadow-sm', 'style' => 'background-color: #151e2d; border: 1px solid #2d3a4a; color: #e2e8f0; padding: 8px 12px; border-radius: 6px; width: 100%;']) }}>
