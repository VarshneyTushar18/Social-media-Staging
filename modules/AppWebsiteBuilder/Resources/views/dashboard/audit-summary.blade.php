@php
    use Illuminate\Support\Facades\Route;

    $metrics = $metrics ?? [];
    $creditActions = $creditActions ?? [];
    $creditsUrl = Route::has('portal.credits') ? route('portal.credits', ['agent' => 'website']) : null;
@endphp

<x-ui.dashboard-module :eyebrow="__('Website audit')" :title="null" :description="null">
    <div class="max-w-full overflow-hidden rounded-[1.55rem] border p-4 sm:p-5 xl:p-6" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background: color-mix(in srgb, var(--theme-surface-overlay) 94%, transparent); box-shadow: 0 22px 44px -40px rgba(15,23,42,0.16);">
        <div class="flex min-w-0 flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0">
                <h3 class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Site audits & AI credits') }}</h3>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-ui.button :href="route('portal.agents.website.audit')" size="sm" wire:navigate>
                    <i class="fa-solid fa-magnifying-glass-chart mr-2 text-[11px]"></i>
                    {{ __('Run audit') }}
                </x-ui.button>
                @if ($creditsUrl)
                    <x-ui.button :href="$creditsUrl" variant="outline" size="sm" wire:navigate>
                        {{ __('Credit history') }}
                    </x-ui.button>
                @endif
            </div>
        </div>

        <div class="mt-5 grid gap-4 lg:grid-cols-2">
            <div class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.card class="space-y-1 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide" style="color: var(--theme-muted-text-color);">{{ __('Audits (all time)') }}</p>
                        <p class="text-2xl font-semibold tabular-nums" style="color: var(--theme-header-text-color);">{{ number_format((int) ($metrics['audits_total'] ?? 0)) }}</p>
                    </x-ui.card>
                    <x-ui.card class="space-y-1 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide" style="color: var(--theme-muted-text-color);">{{ __('This month') }}</p>
                        <p class="text-2xl font-semibold tabular-nums" style="color: var(--theme-header-text-color);">{{ number_format((int) ($metrics['audits_this_month'] ?? 0)) }}</p>
                    </x-ui.card>
                </div>

                @if (is_array($lastAudit ?? null) && filled($lastAudit['target_label'] ?? null))
                    <x-ui.card class="space-y-2 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide" style="color: var(--theme-muted-text-color);">{{ __('Latest audit') }}</p>
                        <p class="break-all text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $lastAudit['target_label'] }}</p>
                        <p class="text-sm" style="color: var(--theme-muted-text-color);">
                            {{ __('Score') }}: <span class="font-semibold tabular-nums" style="color: var(--theme-accent);">{{ (int) ($lastAudit['score'] ?? 0) }}</span>
                            · {{ ! empty($lastAudit['used_ai']) ? __('AI review') : __('Checklist') }}
                            @if (! empty($lastAudit['created_at']))
                                · {{ $lastAudit['created_at'] }}
                            @endif
                        </p>
                    </x-ui.card>
                @endif
            </div>

            <x-ui.card class="space-y-3 p-4">
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('AI credit usage') }}</p>
                <p class="text-xs leading-relaxed" style="color: var(--theme-muted-text-color);">{{ __('Per-run cost from your plan; “Used” is lifetime credits spent on that action.') }}</p>
                <ul class="space-y-3">
                    @foreach ($creditActions as $action)
                        <li class="flex flex-wrap items-center justify-between gap-2 rounded-xl border px-3 py-2.5" style="border-color: rgba(var(--theme-border-color-rgb), 0.45);">
                            <span class="text-sm font-medium" style="color: var(--theme-header-text-color);">{{ $action['label'] }}</span>
                            <span class="text-xs font-semibold tabular-nums" style="color: var(--theme-muted-text-color);">
                                {{ __(':cost credits per run · :used used', ['cost' => number_format((int) ($action['cost'] ?? 0)), 'used' => number_format((int) ($action['used'] ?? 0))]) }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </x-ui.card>
        </div>
    </div>
</x-ui.dashboard-module>