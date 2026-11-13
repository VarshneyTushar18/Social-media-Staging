<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ current_locale_direction() }}">
<head>
    @include(theme_view('partials.head', 'app'), ['title' => $title ?? null])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>
    <style>
        [x-cloak]{display:none!important}
        html, body { margin: 0; padding: 0; overflow: hidden; background: #fff; }
    </style>
</head>
<body class="antialiased bg-white text-slate-900">
    {{ $slot }}
    @livewireScripts
</body>
</html>
