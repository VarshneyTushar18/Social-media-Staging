<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ current_locale_direction() }}">
<head>
    @include(theme_view('partials.head', 'app'), ['title' => $site->name])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>
</head>
<body class="antialiased bg-white text-slate-900">
    @include('appwebsitebuilder::partials.landing-site', [
        'site' => $site,
        'content' => $content,
        'publicMode' => true,
    ])
</body>
</html>
