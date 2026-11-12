@props(['step' => 1, 'buildPath' => ''])

@php
    $isTemplatePath = $buildPath === 'template';
    $isPromptPath = $buildPath === 'ai';

    $progress = match (true) {
        $step === 1 => $isTemplatePath ? 33 : ($isPromptPath ? 50 : 33),
        $step === 2 => $isTemplatePath ? 66 : 100,
        default => 100,
    };
@endphp

<div class="mb-6 space-y-3">
    <div class="flex items-center justify-between gap-2 text-[11px] font-semibold sm:gap-3 sm:text-xs">
        <span @style($step === 1 ? 'color: var(--theme-accent);' : 'color: var(--theme-muted-text-color);')">{{ __('Step 1 — Method') }}</span>
        @if ($isTemplatePath)
            <span @style($step === 2 ? 'color: var(--theme-accent);' : 'color: var(--theme-muted-text-color);')">{{ __('Step 2 — Type') }}</span>
            <span @style($step === 3 ? 'color: var(--theme-accent);' : 'color: var(--theme-muted-text-color);')">{{ __('Step 3 — Template') }}</span>
        @elseif ($isPromptPath)
            <span @style($step === 2 ? 'color: var(--theme-accent);' : 'color: var(--theme-muted-text-color);')">{{ __('Step 2 — Prompt') }}</span>
        @else
            <span style="color: var(--theme-muted-text-color);">{{ __('Step 2') }}</span>
        @endif
    </div>
    <div
        class="h-1.5 w-full overflow-hidden rounded-full"
        style="background-color: rgba(var(--theme-border-color-rgb), 0.45);"
        role="progressbar"
        aria-valuenow="{{ $progress }}"
        aria-valuemin="0"
        aria-valuemax="100"
    >
        <div
            class="h-full rounded-full transition-all duration-300"
            style="width: {{ $progress }}%; background-color: var(--theme-accent);"
        ></div>
    </div>
</div>
