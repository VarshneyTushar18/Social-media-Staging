@php
    $metrics = $metrics ?? [];
@endphp

<x-ui.dashboard-module :eyebrow="__('Domains')" :title="null" :description="null">
    <div class="max-w-full overflow-hidden rounded-[1.55rem] border p-4 sm:p-5 xl:p-6" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background: color-mix(in srgb, var(--theme-surface-overlay) 94%, transparent); box-shadow: 0 22px 44px -40px rgba(15,23,42,0.16);">
        <div class="flex min-w-0 flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0">
                <h3 class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Publishing & URLs') }}</h3>
                <p class="mt-1 text-sm leading-relaxed" style="color: var(--theme-muted-text-color);">{{ __('Stackposts subdomains, custom domains, and in-progress domain setup for this project.') }}</p>
            </div>
            <x-ui.button :href="route('portal.agents.website.domains')" size="sm" wire:navigate>
                <i class="fa-solid fa-link mr-2 text-[11px]"></i>
                {{ __('Manage domains') }}
            </x-ui.button>
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['label' => __('Live subdomains'), 'value' => $metrics['subdomain_live'] ?? 0],
                ['label' => __('Custom domains'), 'value' => $metrics['custom_connected'] ?? 0],
                ['label' => __('Pending setup'), 'value' => $metrics['pending_custom'] ?? 0],
                ['label' => __('Published sites'), 'value' => $metrics['published'] ?? 0],
            ] as $card)
                <x-ui.card class="space-y-1 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide" style="color: var(--theme-muted-text-color);">{{ $card['label'] }}</p>
                    <p class="text-2xl font-semibold tabular-nums" style="color: var(--theme-header-text-color);">{{ number_format((int) $card['value']) }}</p>
                </x-ui.card>
            @endforeach
        </div>
    </div>
</x-ui.dashboard-module>