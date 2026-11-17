@php
    $metrics = $metrics ?? [];
    $templateCount = (int) ($metrics['templates'] ?? 0);
@endphp

<x-ui.dashboard-module :eyebrow="__('Templates')" :title="null" :description="null">
    <div
        class="flex min-w-0 flex-col gap-5 rounded-[1.55rem] border p-4 sm:flex-row sm:items-center sm:justify-between sm:gap-6 sm:p-5 xl:p-6"
        style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background: color-mix(in srgb, var(--theme-surface-overlay) 94%, transparent); box-shadow: 0 22px 44px -40px rgba(15,23,42,0.16);"
    >
        <div class="min-w-0 flex-1">
            <h3 class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Template gallery') }}</h3>
            <p class="mt-1 max-w-2xl text-sm leading-relaxed" style="color: var(--theme-muted-text-color);">
                {{ trans_choice(':count starter layout available — pick one and customize in the site editor.|:count starter layouts available — pick one and customize in the site editor.', $templateCount, ['count' => number_format($templateCount)]) }}
            </p>
        </div>

        <div class="flex w-full shrink-0 flex-col gap-3 sm:w-auto sm:flex-row sm:items-center">
            <div
                class="inline-flex items-center gap-2 rounded-lg border px-3 py-1.5"
                style="border-color: rgba(var(--theme-accent-rgb), 0.2); background: rgba(var(--theme-accent-rgb), 0.06);"
            >
                <p class="text-lg font-semibold leading-none tabular-nums" style="color: var(--theme-header-text-color);">{{ number_format($templateCount) }}</p>
                <p class="text-[10px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Layouts') }}</p>
            </div>

            <x-ui.button :href="route('portal.agents.website.templates')" size="sm" class="w-full justify-center sm:w-auto" wire:navigate>
                <i class="fa-solid fa-layer-group mr-2 text-[11px]"></i>
                {{ __('Browse templates') }}
            </x-ui.button>
        </div>
    </div>
</x-ui.dashboard-module>