@props([
    'label',
    'value',
    'meta' => null,
    'icon' => null,
    'tone' => 'navy',
])

<div class="stat-tile">
    <div class="d-flex justify-content-between align-items-start gap-2">
        <div>
            <div class="stat-label">{{ $label }}</div>
            <div class="stat-value">{{ $value }}</div>
            @if ($meta)
                <div class="stat-meta">{{ $meta }}</div>
            @endif
        </div>
        @if ($icon)
            <i class="bi {{ $icon }} fs-4 text-{{ $tone === 'navy' ? 'primary' : $tone }} opacity-50"></i>
        @endif
    </div>
</div>
