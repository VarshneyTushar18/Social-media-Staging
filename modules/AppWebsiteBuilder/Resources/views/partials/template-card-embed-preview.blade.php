@props([
    'embedUrl' => '',
    'priority' => false,
])

<div
    {{ $attributes->merge(['class' => 'relative h-full w-full overflow-hidden']) }}
    style="background: rgba(var(--theme-border-color-rgb), 0.14);"
    data-tc-preview
    @if ($embedUrl !== '')
        data-tc-embed="{{ $embedUrl }}"
    @endif
    @if ($priority)
        data-tc-priority="1"
    @endif
>
    <div
        data-tc-stage
        class="pointer-events-none absolute left-1/2 top-0"
        style="will-change: transform;"
    >
        <iframe
            data-tc-iframe
            title="{{ __('Template preview') }}"
            class="block border-0"
            width="1280"
            height="3200"
        ></iframe>
    </div>
</div>
