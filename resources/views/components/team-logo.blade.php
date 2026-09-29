@props([
    'team',
    'size' => 44,
])

@php
    $hasLogo = filled($team->logo_url ?? null);
@endphp

<span {{ $attributes->class(['team-logo', 'initials' => ! $hasLogo]) }}
      style="width: {{ $size }}px; height: {{ $size }}px; font-size: {{ max(10, (int) round($size * 0.34)) }}px;@if (! $hasLogo) background: {{ $team->primary_color ?? '#1b4dff' }};@endif">
    @if ($hasLogo)
        <img src="{{ $team->logo_url }}" alt="{{ $team->name }} logo">
    @else
        {{ $team->initials }}
    @endif
</span>
