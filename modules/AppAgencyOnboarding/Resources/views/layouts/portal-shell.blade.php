@component(theme_view('layouts.app', 'app'), [
    'title' => $title ?? null,
    'shellArea' => 'user',
    'hideSidebar' => true,
])
    {{ $slot }}
@endcomponent
