@props([
    'icon' => 'bi-inbox',
    'title' => 'Nothing here yet',
    'message' => null,
])

<div class="empty-state">
    <i class="bi {{ $icon }}"></i>
    <p class="fw-semibold mb-1">{{ $title }}</p>
    @if ($message)
        <p class="small mb-3">{{ $message }}</p>
    @endif
    {{ $slot }}
</div>
