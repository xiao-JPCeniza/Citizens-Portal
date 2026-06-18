@php
    $logoPath = public_path('images/branding/lupad-logo-white.png');
    $logoSrc = $message->embed($logoPath);
@endphp

<img
    src="{{ $logoSrc }}"
    alt="LUPAD — Municipality of Manolo Fortich, Bukidnon"
    width="160"
    height="48"
    style="display: block; height: 48px; width: auto; max-width: 180px; border: 0;"
>
