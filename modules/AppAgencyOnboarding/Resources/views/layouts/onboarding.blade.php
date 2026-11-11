<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include(theme_view('partials.head', 'guest'))
        @livewireStyles
        <style>
            body { background: #f4f4f5; }
        </style>
    </head>
    <body class="min-h-screen antialiased text-zinc-900">
        <div class="mx-auto min-h-screen max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
            {{ $slot }}
        </div>
        @livewireScripts
    </body>
</html>
