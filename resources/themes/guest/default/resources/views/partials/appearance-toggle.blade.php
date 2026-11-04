@php
    $supportsDark = (string) theme_setting('supports_dark_mode', 'guest', '1') !== '0';
    $allowToggle = (string) theme_setting('allow_user_appearance_toggle', 'guest', '1') !== '0';
@endphp

@if ($supportsDark && $allowToggle)
    <div
        x-data="{
            mode: window.themeMode?.getMode?.() || 'system',
            resolved: window.themeMode?.getResolved?.() || 'dark',
            sync(state = null) {
                this.mode = state?.mode || window.themeMode?.getMode?.() || 'system';
                this.resolved = state?.resolved || window.themeMode?.getResolved?.() || 'dark';
            },
            setMode(mode) {
                const state = window.themeMode?.setMode?.(mode);
                this.sync(state);
            }
        }"
        x-init="
            sync();
            window.addEventListener('theme-mode-changed', (event) => sync(event.detail));
        "
        class="guest-marketing-action-button inline-flex h-11 items-center gap-1 rounded-full border px-1.5 py-1 shadow-[inset_0_1px_0_rgba(255,255,255,0.04)]"
        style="border-color: var(--theme-header-panel-border); background: var(--theme-header-panel-bg);"
    >
        <button
            type="button"
            x-on:click="setMode('light')"
            x-bind:class="mode === 'light' ? 'shadow-sm' : ''"
            class="inline-flex h-9 w-9 items-center justify-center rounded-full transition"
            x-bind:style="(mode === 'light' || (mode === 'system' && resolved === 'light')) ? 'background: var(--theme-header-active); color: var(--theme-body-bg); box-shadow: 0 8px 18px -14px rgba(15,23,42,0.35);' : 'color: var(--theme-muted-text-color);'"
            aria-label="{{ __('Light mode') }}"
            title="{{ __('Light mode') }}"
        >
            <i class="fa-solid fa-sun text-sm"></i>
        </button>
        <button
            type="button"
            x-on:click="setMode('dark')"
            x-bind:class="mode === 'dark' ? 'shadow-sm' : ''"
            class="inline-flex h-9 w-9 items-center justify-center rounded-full transition"
            x-bind:style="(mode === 'dark' || (mode === 'system' && resolved === 'dark')) ? 'background: var(--theme-header-active); color: var(--theme-body-bg); box-shadow: 0 8px 18px -14px rgba(0,0,0,0.45);' : 'color: var(--theme-muted-text-color);'"
            aria-label="{{ __('Dark mode') }}"
            title="{{ __('Dark mode') }}"
        >
            <i class="fa-solid fa-moon text-sm"></i>
        </button>
    </div>
@endif
