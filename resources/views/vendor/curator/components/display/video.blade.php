@props([
    'controls' => true,
    'src' => null,
    'lazy' => true,
])

<video
    src="{{ $item->url }}"
    @if ($controls)
        controls
    @endif
    preload="metadata"
    muted
    playsinline
    onloadedmetadata="this.currentTime=0.001"
    {{ $attributes->merge() }}
></video>