<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Lupad  Admin — Municipality of Manolo Fortich, Bukidnon">

    <title>Admin — </title>

    <link rel="icon" type="image/png" href="{{ asset('images/branding/lupad-logo-color.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/branding/lupad-logo-color.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-gray-200 font-sans text-gray-800 antialiased">
    {{ $slot }}
    <x-site-footer />
    @livewireScripts
</body>
</html>
