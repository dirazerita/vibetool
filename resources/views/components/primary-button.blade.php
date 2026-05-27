<button {{ $attributes->merge(['type' => 'submit', 'style' => 'display: inline-flex; align-items: center; padding: 10px 20px; background: linear-gradient(135deg, #4f46e5, #7c3aed); border: none; border-radius: 8px; font-weight: 600; font-size: 0.875rem; color: #ffffff; text-transform: uppercase; letter-spacing: 0.05em; cursor: pointer; transition: all 0.2s;']) }}>
    {{ $slot }}
</button>
