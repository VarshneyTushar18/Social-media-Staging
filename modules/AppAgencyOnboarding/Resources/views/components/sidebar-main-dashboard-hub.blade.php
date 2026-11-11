@php
    use Illuminate\Support\Facades\Route;

    $showHub = auth()->check()
        && request()->routeIs('portal.*')
        && class_exists(\Modules\AppAgencyOnboarding\Support\AgencyOnboarding::class)
        && app(\Modules\AppAgencyOnboarding\Support\AgencyOnboarding::class)->isComplete(auth()->user());

    $dashboardUrl = Route::has('portal.main') ? route('portal.main') : route('portal.dashboard');
    $onMainDashboard = request()->routeIs('portal.main', 'portal.main.*');

    $cardStyle = $onMainDashboard
        ? 'border-color: rgba(var(--theme-accent-rgb), 0.32); background: color-mix(in srgb, var(--theme-accent) 6%, var(--theme-sidebar-bg) 94%);'
        : 'border-color: rgba(var(--theme-border-color-rgb), 0.5); background: color-mix(in srgb, var(--theme-surface-overlay) 18%, var(--theme-sidebar-bg) 82%);';
@endphp

@if ($showHub)
    <div class="px-3 pb-2.5" x-bind:class="sidebarPanelExpanded ? '' : 'flex justify-center'">
        <a
            href="{{ $dashboardUrl }}"
            wire:navigate
            class="inline-flex h-9 w-9 items-center justify-center rounded-[0.6rem] text-[10px] font-bold tracking-tight shadow-sm transition hover:opacity-90"
            style="background: var(--theme-accent); color: #ffffff;"
            x-cloak
            x-show="!sidebarContentVisible"
            title="{{ __('Main dashboard') }}"
            aria-label="{{ __('Main dashboard') }}"
        >
            {{ __('HQ') }}
        </a>

        <a
            href="{{ $dashboardUrl }}"
            wire:navigate
            class="group flex w-full items-center gap-2.5 rounded-[0.85rem] border px-2 py-2 transition-colors duration-150 outline-none focus-visible:ring-2 focus-visible:ring-[var(--theme-accent)]"
            style="{{ $cardStyle }}"
            x-cloak
            x-show="sidebarContentVisible"
        >
            <span
                class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-[0.55rem] text-[10px] font-bold tracking-tight shadow-sm"
                style="background: var(--theme-accent); color: #ffffff;"
            >
                {{ __('HQ') }}
            </span>
            <span
                class="truncate text-[12px] font-semibold leading-none tracking-[-0.01em]"
                style="color: var(--theme-header-text-color);"
            >
                {{ __('Main dashboard') }}
            </span>
        </a>
    </div>
@endif
