@props([
    'sections' => [],
    'dashboardSwitch' => null,
    'homeHref' => null,
])

@php
    $sidebarBrandDarkUrl = brand_option_url('website_logo_brand_dark', 'public/img/logo-brand-dark.png');
    $sidebarBrandLightUrl = brand_option_url('website_logo_brand_light', 'public/img/logo-brand-light.png');
    $sidebarLogoFallback = brand_asset_url('', 'public/img/logo-brand-dark.png');
@endphp

<div
    x-cloak
    x-show="sidebarOpen"
    class="fixed inset-0 lg:hidden"
    style="z-index: 140;"
    x-on:keydown.escape.window="sidebarOpen = false"
>
    <div class="absolute inset-0 bg-slate-950/60" x-on:click="sidebarOpen = false"></div>

    <div
        class="relative flex h-full w-[14.75rem] max-w-[85vw] flex-col border-r border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-950"
        style="z-index: 141; color: var(--theme-sidebar-text-color); border-color: var(--theme-border-color); background: var(--theme-sidebar-bg);"
        x-data="{ sidebarContentVisible: true, sidebarCollapsed: false, sidebarPanelExpanded: true }"
    >
        <div class="relative h-[76px] px-5 py-4">
            <a
                href="{{ $homeHref ?: route('portal.dashboard') }}"
                wire:navigate
                class="flex h-10 items-center justify-start rounded-[0.85rem] outline-none transition hover:opacity-85 focus-visible:ring-2 focus-visible:ring-[var(--theme-accent)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--theme-sidebar-bg)]"
                aria-label="{{ __('Open dashboard') }}"
                x-on:click="sidebarOpen = false"
            >
                <img
                    x-show="appearanceResolved !== 'dark'"
                    src="{{ $sidebarBrandDarkUrl }}"
                    alt="{{ config('app.name', 'Stackposts') }}"
                    class="block h-8 w-auto max-w-none shrink-0"
                    onerror="this.onerror=null;this.src='{{ $sidebarLogoFallback }}';"
                >
                <img
                    x-show="appearanceResolved === 'dark'"
                    src="{{ $sidebarBrandLightUrl }}"
                    alt="{{ config('app.name', 'Stackposts') }}"
                    class="block h-8 w-auto max-w-none shrink-0"
                    onerror="this.onerror=null;this.src='{{ $sidebarLogoFallback }}';"
                >
            </a>

            <button
                type="button"
                class="absolute -right-3 top-1/2 inline-flex h-6 w-6 -translate-y-1/2 items-center justify-center rounded-md border border-slate-300/80 bg-white text-slate-500 shadow-[0_6px_14px_-12px_rgba(15,23,42,0.2)] transition hover:border-slate-400 hover:bg-slate-50 hover:text-slate-800 dark:border-slate-700 dark:bg-slate-900 dark:hover:border-slate-600 dark:hover:text-slate-200"
                x-on:click="sidebarOpen = false"
                title="Close menu"
            >
                <i class="fa-solid fa-xmark text-[11px]"></i>
            </button>
        </div>

        @if (auth()->check() && view()->exists('appagencyonboarding::components.sidebar-main-dashboard-hub'))
            @include('appagencyonboarding::components.sidebar-main-dashboard-hub')
        @endif

        <div class="app-sidebar-scroll min-h-0 flex-1 overflow-y-auto px-3 pt-2 pb-3">
            <x-layout.sidebar-menu :sections="$sections" mode="desktop" />

            @if ($dashboardSwitch)
                <div class="mt-4 border-t pt-4" style="border-color: var(--theme-border-color);">
                    <x-ui.button
                        :href="$dashboardSwitch['route']"
                        class="w-full justify-center"
                        size="sm"
                        wire:navigate
                    >
                        <i class="fa-solid fa-arrow-right-arrow-left mr-2 text-[12px]"></i>
                        {{ __($dashboardSwitch['label']) }}
                    </x-ui.button>
                </div>
            @endif
        </div>
    </div>
</div>
