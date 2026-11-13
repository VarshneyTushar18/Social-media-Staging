@php
    $sessionToastVariants = [
        'success' => 'success',
        'status' => 'success',
        'error' => 'danger',
        'warning' => 'warning',
    ];
@endphp

@foreach ($sessionToastVariants as $sessionKey => $variant)
    @if (session($sessionKey))
        <x-ui.alert :variant="$variant" :description="session($sessionKey)" />
    @endif
@endforeach
