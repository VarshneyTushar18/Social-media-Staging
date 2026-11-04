@props([
    'sections' => [],
    'footer' => null,
    'homeHref' => null,
])

@php
    $sidebarBrandDarkUrl = brand_option_url('website_logo_brand_dark', 'public/img/logo-brand-dark.png');
    $sidebarBrandLightUrl = brand_option_url('website_logo_brand_light', 'public/img/logo-brand-light.png');
    $sidebarLogoDarkUrl = brand_option_url('website_logo_dark', 'public/img/logo-dark.png');
    $sidebarLogoLightUrl = brand_option_url('website_logo_light', 'public/img/logo-light.png');
    $sidebarLogoFallback = brand_asset_url('', 'public/img/logo-brand-dark.png');
@endphp

<aside
    class="fixed inset-y-0 left-0 z-[140] hidden overflow-visible transition-[width] duration-[520ms] ease-[cubic-bezier(0.16,1,0.3,1)] will-change-[width] lg:block"
    x-bind:class="!sidebarPanelExpanded ? 'w-[70px] min-w-[70px] max-w-[70px]' : 'w-[14.75rem] min-w-[14.75rem] max-w-[14.75rem]'"
    x-on:mouseenter="startSidebarHover()"
    x-on:mouseleave="endSidebarHover()"
>
    <style>
        .app-sidebar-scroll {
            scrollbar-gutter: stable;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .app-sidebar-scroll::-webkit-scrollbar {
            display: none;
        }
    </style>

    <div
        class="flex h-full w-full flex-col border-r border-slate-300/70 bg-[var(--theme-sidebar-bg)] transition-[box-shadow] duration-[520ms] ease-[cubic-bezier(0.16,1,0.3,1)] dark:border-slate-800 dark:bg-[var(--theme-sidebar-bg)]"
        style="color: var(--theme-sidebar-text-color); border-color: var(--theme-border-color);"
    >
        <div class="relative h-[76px] px-5 py-4" x-bind:class="sidebarPanelExpanded ? 'px-5 py-4' : 'px-3 py-4'">
            <a
                href="{{ $homeHref ?: route('portal.dashboard') }}"
                wire:navigate
                class="flex h-10 items-center rounded-[0.85rem] outline-none transition hover:opacity-85 focus-visible:ring-2 focus-visible:ring-[var(--theme-accent)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--theme-sidebar-bg)]"
                x-bind:class="sidebarPanelExpanded ? 'justify-start' : 'justify-center'"
                aria-label="{{ __('Open dashboard') }}"
            >
                <div x-cloak x-show="sidebarContentVisible">
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
                </div>

                <div x-cloak x-show="!sidebarContentVisible">
                    <img
                        x-show="appearanceResolved !== 'dark'"
                        src="{{ $sidebarLogoDarkUrl }}"
                        alt="{{ config('app.name', 'Stackposts') }}"
                        class="block h-8 w-8 max-w-none shrink-0"
                        onerror="this.onerror=null;this.src='{{ $sidebarLogoFallback }}';"
                    >
                    <img
                        x-show="appearanceResolved === 'dark'"
                        src="{{ $sidebarLogoLightUrl }}"
                        alt="{{ config('app.name', 'Stackposts') }}"
                        class="block h-8 w-8 max-w-none shrink-0"
                        onerror="this.onerror=null;this.src='{{ $sidebarLogoFallback }}';"
                    >
                </div>
            </a>

            <button
                type="button"
                class="absolute -right-3 top-1/2 inline-flex h-6 w-6 -translate-y-1/2 items-center justify-center rounded-md border border-slate-300/80 bg-white text-slate-500 shadow-[0_6px_14px_-12px_rgba(15,23,42,0.2)] transition hover:border-slate-400 hover:bg-slate-50 hover:text-slate-800 dark:border-slate-700 dark:bg-slate-900 dark:hover:border-slate-600 dark:hover:text-slate-200"
                x-on:click="toggleSidebar"
                x-bind:title="sidebarCollapsed ? 'Expand menu' : 'Collapse menu'"
            >
                <i class="fa-solid text-[7px]" x-bind:class="sidebarCollapsed ? 'fa-angles-right' : 'fa-angles-left'"></i>
            </button>
        </div>

        <div class="app-sidebar-scroll flex-1 overflow-y-auto px-3 pt-2 pb-2">
            <x-layout.sidebar-menu :sections="$sections" mode="desktop" />
        </div>

        @if ($footer)
            <div class="border-t border-slate-300/70 dark:border-slate-800" style="border-color: var(--theme-border-color);" x-bind:class="sidebarContentVisible ? 'p-4' : 'flex justify-center p-1.5'">
                {{ $footer }}
            </div>
        @endif
    </div>
</aside>
