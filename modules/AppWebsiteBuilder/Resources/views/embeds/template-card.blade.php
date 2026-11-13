@php
    $cardMode = $cardMode ?? true;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ current_locale_direction() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=1280, initial-scale=1">
    <title>{{ $title ?? __('Preview') }}</title>
    @if ($cardMode)
        {!! theme_vite('app', ['assets/css/landing-preview.css']) !!}
        <link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/fontawesome.css') }}">
        <link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/solid.css') }}">
        <link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/brands.css') }}">
        <style>
            html, body { margin: 0; padding: 0; overflow: hidden; background: #fff; }
            .lp-embed-card .lp-nav-toggle,
            .lp-embed-card .lp-nav-mobile-panel,
            .lp-embed-card .lp-nav-mobile-backdrop { display: none !important; }
            .lp-embed-card .lp-nav-desktop { display: flex !important; }
            .lp-embed-card #template-card-embed-root { min-height: 0; }
        </style>
    @else
        <link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/fontawesome.css') }}">
        <link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/solid.css') }}">
        <link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/regular.css') }}">
        <link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/brands.css') }}">
        {!! theme_vite('app', ['assets/css/app.css', 'assets/css/browser.css']) !!}
        <style>
            html, body { margin: 0; padding: 0; overflow: hidden; background: #fff; }
        </style>
    @endif
</head>
<body class="bg-white antialiased text-slate-900">
    <div id="template-card-embed-root" class="w-[1280px]">
        @include('appwebsitebuilder::partials.landing-site', [
            'site' => $site,
            'content' => $content,
            'embedCardMode' => $cardMode,
        ])
    </div>
</body>
</html>
