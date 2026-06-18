@props([
    'variant' => 'white',
])

@php
    $src = match ($variant) {
        'color' => asset('images/branding/lupad-logo-color.png'),
        'black' => asset('images/branding/lupad-logo-black.png'),
        default => asset('images/branding/lupad-logo-white.png'),
    };
@endphp

<img
    src="{{ $src }}"
    alt="LUPAD — Municipality of Manolo Fortich, Bukidnon"
    {{ $attributes->merge(['class' => 'h-12 w-auto shrink-0 object-contain sm:h-14']) }}
>
