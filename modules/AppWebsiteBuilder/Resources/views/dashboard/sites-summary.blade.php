@php
    $metrics = $metrics ?? [];
@endphp

<x-ui.dashboard-module :eyebrow="__('Sites')" :title="null" :description="null">
    <div class="space-y-4">
        <div class="max-w-full overflow-hidden rounded-[1.55rem] border" style="border-color: rgba(124, 58, 237, 0.12); background: linear-gradient(180deg, color-mix(in srgb, var(--theme-surface-overlay) 96%, rgba(124, 58, 237, 0.04)), color-mix(in srgb, var(--theme-surface-base) 95%, transparent)); box-shadow: 0 26px 60px -52px rgba(15,23,42,0.22);">
            <div class="flex min-w-0 flex-col gap-5 p-4 sm:p-5 xl:p-6">
                <div class="flex min-w-0 flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Dashboard') }}</p>
                        <h3 class="mt-2 text-lg font-semibold tracking-tight" style="color: var(--theme-header-text-color);">{{ __('Sites in this project') }}</h3>
                        <p class="mt-1 text-sm leading-relaxed" style="color: var(--theme-muted-text-color);">{{ __('Create from templates or AI, then publish and manage domains from the workspace.') }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <x-ui.button :href="route('portal.agents.website.dashboard')" size="sm" wire:navigate>
                            <i class="fa-solid fa-chart-pie mr-2 text-[11px]"></i>
                            {{ __('Open dashboard') }}
                        </x-ui.button>
                        <x-ui.button :href="route('portal.agents.website.sites.create')" variant="outline" size="sm" wire:navigate>
                            <i class="fa-solid fa-plus mr-2 text-[11px]"></i>
                            {{ __('Add new website') }}
                        </x-ui.button>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    @foreach ([
                        ['label' => __('Sites'), 'value' => $metrics['sites'] ?? 0],
                        ['label' => __('Published'), 'value' => $metrics['published'] ?? 0],
                        ['label' => __('Drafts'), 'value' => $metrics['drafts'] ?? 0],
                        ['label' => __('Template built'), 'value' => $metrics['template_built'] ?? 0],
                        ['label' => __('AI built'), 'value' => $metrics['ai_built'] ?? 0],
                    ] as $card)
                        <x-ui.card class="space-y-1 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide" style="color: var(--theme-muted-text-color);">{{ $card['label'] }}</p>
                            <p class="text-2xl font-semibold tabular-nums" style="color: var(--theme-header-text-color);">{{ number_format((int) $card['value']) }}</p>
                        </x-ui.card>
                    @endforeach
                </div>

                @if (count($recentSites ?? []) > 0)
                    <x-ui.card class="space-y-3 p-4">
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Recently updated') }}</p>
                        <ul class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), 0.4);">
                            @foreach ($recentSites as $site)
                                <li class="py-3 first:pt-0 last:pb-0">
                                    <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $site['name'] }}</p>
                                    <p class="text-xs capitalize" style="color: var(--theme-muted-text-color);">
                                        {{ $site['status'] }} · {{ $site['build_method'] }}
                                        @if (! empty($site['updated_at']))
                                            · {{ $site['updated_at'] }}
                                        @endif
                                    </p>
                                </li>
                            @endforeach
                        </ul>
                    </x-ui.card>
                @endif
            </div>
        </div>
    </div>
</x-ui.dashboard-module>