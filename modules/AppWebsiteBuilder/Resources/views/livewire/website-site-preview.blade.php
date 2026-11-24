<div
    @class([
        'flex min-h-0 flex-col',
        'min-h-[calc(100dvh-5rem)] py-4 sm:py-5' => $codeMode,
        'min-h-[70vh] py-6 sm:py-8' => ! $codeMode,
    ])
    x-data="{ device: 'laptop' }"
>
    @include('appwebsitebuilder::partials.website-builder-session-toasts')
    <div @class([
        'mx-auto w-full max-w-[1400px] px-4 sm:px-6',
        'flex min-h-0 flex-1 flex-col' => $codeMode,
    ])>
        <div @class([
            'grid grid-cols-1 items-center gap-4',
            'md:grid-cols-[1fr_auto_1fr]' => ! $codeMode,
            'md:grid-cols-[1fr_auto]' => $codeMode,
            'mb-3 shrink-0' => $codeMode,
            'mb-5' => ! $codeMode,
        ])>
            <div class="flex justify-start">
                <x-ui.button
                    href="{{ route('portal.agents.website.dashboard') }}"
                    variant="secondary"
                    size="sm"
                    wire:navigate
                >
                    <i class="fa-solid fa-arrow-left mr-2 text-[10px]"></i>
                    {{ __('Dashboard') }}
                </x-ui.button>
            </div>

            @unless ($codeMode)
                <div class="flex justify-center">
                    <div
                        class="inline-flex rounded-[var(--theme-input-radius,0.75rem)] border p-1 shadow-sm"
                        style="border-color: rgba(var(--theme-border-color-rgb), 0.65); background: var(--theme-surface-overlay);"
                        role="tablist"
                        aria-label="{{ __('Device preview') }}"
                    >
                        @foreach ([
                            ['id' => 'laptop', 'icon' => 'fa-laptop', 'label' => __('Laptop')],
                            ['id' => 'tablet', 'icon' => 'fa-tablet-screen-button', 'label' => __('Tablet')],
                            ['id' => 'mobile', 'icon' => 'fa-mobile-screen', 'label' => __('Mobile')],
                        ] as $deviceOption)
                            <button
                                type="button"
                                role="tab"
                                class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-semibold transition"
                                x-bind:aria-selected="device === @js($deviceOption['id'])"
                                x-on:click="device = @js($deviceOption['id'])"
                                x-bind:style="device === @js($deviceOption['id'])
                                    ? 'background-color: var(--theme-accent); color: #fff;'
                                    : 'color: var(--theme-header-text-color);'"
                            >
                                <i class="fa-solid {{ $deviceOption['icon'] }}"></i>
                                <span class="hidden sm:inline">{{ $deviceOption['label'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endunless

            <div @class([
                'flex items-center justify-end md:justify-self-end',
                'md:col-start-2' => $codeMode,
            ])>
                <div
                    class="inline-flex rounded-[var(--theme-input-radius,0.75rem)] border p-0.5 shadow-sm"
                    style="border-color: rgba(var(--theme-border-color-rgb), 0.65); background: var(--theme-surface-overlay);"
                >
                    <button
                        type="button"
                        class="rounded-lg px-3 py-1.5 text-xs font-semibold transition"
                        wire:click="setCodeMode(false)"
                        @style($codeMode ? 'color: var(--theme-muted-text-color);' : 'background-color: var(--theme-accent); color: #fff;')
                    >
                        {{ __('Preview') }}
                    </button>
                    <button
                        type="button"
                        class="rounded-lg px-3 py-1.5 text-xs font-semibold transition"
                        wire:click="setCodeMode(true)"
                        @style($codeMode ? 'background-color: var(--theme-accent); color: #fff;' : 'color: var(--theme-muted-text-color);')
                    >
                        <i class="fa-solid fa-code mr-1 text-[10px]"></i>
                        {{ __('Code') }}
                    </button>
                </div>
            </div>
        </div>

        <div @class([
            'gap-5',
            'flex min-h-0 flex-1 flex-col' => $codeMode,
            'grid' => ! $codeMode,
        ])>
            @if ($codeMode)
                <x-ui.card
                    :padding="'none'"
                    tone="soft"
                    class="flex min-h-0 w-full min-w-0 flex-1 flex-col overflow-hidden p-4 sm:p-5"
                    x-data="{
                        downloadIndexHtml() {
                            const blob = new Blob([$refs.siteExportCode.value], { type: 'text/html;charset=utf-8' });
                            const link = document.createElement('a');
                            link.href = URL.createObjectURL(blob);
                            link.download = 'index.html';
                            link.click();
                            URL.revokeObjectURL(link.href);
                        },
                    }"
                >
                    <div class="mb-3 flex shrink-0 justify-end border-b pb-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.55);">
                        <x-ui.button
                            type="button"
                            size="sm"
                            class="shrink-0"
                            x-on:click="downloadIndexHtml()"
                        >
                            {{ __('Export') }}
                        </x-ui.button>
                    </div>
                    <textarea
                        x-ref="siteExportCode"
                        readonly
                        spellcheck="false"
                        class="website-code-export block min-h-[12rem] w-full min-w-0 flex-1 resize-none overflow-auto rounded-lg border p-3 font-mono text-xs leading-relaxed outline-none focus:ring-2 focus:ring-[color:var(--theme-accent)]"
                        aria-label="{{ __('Exported HTML source') }}"
                    >{{ $exportHtml }}</textarea>
                </x-ui.card>
            @else
            <x-ui.card class="overflow-hidden border-0 p-3 shadow-sm sm:p-4" style="background: rgba(var(--theme-border-color-rgb), 0.2);">
            <div class="flex justify-center">
                <div
                    class="w-full overflow-hidden p-3 shadow-lg transition-all duration-300 sm:p-4"
                    style="background: rgba(15, 23, 42, 0.92); border-radius: var(--theme-input-radius, 0.75rem);"
                    x-bind:class="{
                        'max-w-[1200px]': device === 'laptop',
                        'max-w-[820px]': device === 'tablet',
                        'max-w-[390px]': device === 'mobile',
                    }"
                    x-bind:style="device === 'mobile' ? 'border-radius: 1.75rem;' : 'border-radius: var(--theme-input-radius, 0.75rem);'"
                >
                    <div class="mb-2 flex items-center justify-between gap-2 px-1">
                        <div class="flex min-w-0 items-center gap-1.5">
                            <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-red-400" x-show="device === 'laptop'" x-cloak></span>
                            <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-amber-400" x-show="device === 'laptop'" x-cloak></span>
                            <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-emerald-400" x-show="device === 'laptop'" x-cloak></span>
                            <span class="truncate text-[10px] text-slate-400" x-bind:class="device === 'laptop' ? 'ml-2' : ''">
                                @if (filled(data_get($content, 'template_name')))
                                    {{ data_get($content, 'template_name') }} ·
                                @endif
                                {{ $site->name }} — {{ __('preview') }}
                            </span>
                        </div>
                        <a
                            href="{{ route('portal.agents.website.sites.open', $site) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-slate-400 transition hover:bg-white/10 hover:text-white"
                            title="{{ __('Open website in new tab') }}"
                            aria-label="{{ __('Open website in new tab') }}"
                        >
                            <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i>
                        </a>
                    </div>

                    <div
                        class="preview-device-frame max-h-[75vh] overflow-y-auto overflow-x-hidden bg-white shadow-inner"
                        x-bind:class="device === 'mobile' ? 'rounded-[1.25rem]' : 'rounded-lg'"
                        x-bind:data-device="device"
                    >
                        @include('appwebsitebuilder::partials.landing-site', [
                            'site' => $site,
                            'content' => $content,
                        ])
                    </div>
                </div>
            </div>
        </x-ui.card>
            @endif
        </div>
    </div>

    <style>
        .website-code-export {
            border-color: rgba(var(--theme-border-color-rgb), 0.65);
            background-color: var(--theme-surface-overlay);
            color: var(--theme-header-text-color);
        }
        [x-cloak]{display:none!important}
        .preview-device-frame[data-device="mobile"] .lp-nav-desktop { display: none !important; }
        .preview-device-frame[data-device="mobile"] .lp-nav-toggle { display: inline-flex !important; }
        .preview-device-frame[data-device="laptop"] .lp-nav-mobile-panel,
        .preview-device-frame[data-device="tablet"] .lp-nav-mobile-panel,
        .preview-device-frame[data-device="laptop"] .lp-nav-mobile-backdrop,
        .preview-device-frame[data-device="tablet"] .lp-nav-mobile-backdrop { display: none !important; }
        @media (min-width: 768px) {
            .preview-device-frame:not([data-device="mobile"]) .lp-nav-mobile-panel { display: none !important; }
        }
        .preview-device-frame[data-device="mobile"] .lp-grid-2,
        .preview-device-frame[data-device="mobile"] .lp-grid-3,
        .preview-device-frame[data-device="mobile"] .lp-grid-4,
        .preview-device-frame[data-device="mobile"] .lp-highlight-grid,
        .preview-device-frame[data-device="mobile"] .lp-cq-split { grid-template-columns: minmax(0, 1fr) !important; }
        .preview-device-frame[data-device="mobile"] .lp-footer-main { flex-direction: column !important; align-items: stretch !important; }
        .preview-device-frame[data-device="mobile"] .lp-cq-contact-actions { flex-direction: column !important; align-items: stretch !important; }
        .preview-device-frame[data-device="mobile"] .lp-hero-aside { display: none !important; }
        .preview-device-frame[data-device="mobile"] .lp-hero-overlap-media,
        .preview-device-frame[data-device="tablet"] .lp-hero-overlap-media {
            margin-bottom: 0 !important;
        }
        .preview-device-frame[data-device="mobile"] .lp-hero-overlap-copy,
        .preview-device-frame[data-device="tablet"] .lp-hero-overlap-copy {
            padding-top: 0 !important;
        }
        .preview-device-frame[data-device="mobile"] .lp-hero-overlap-img,
        .preview-device-frame[data-device="tablet"] .lp-hero-overlap-img {
            aspect-ratio: 4 / 3 !important;
        }
        .preview-device-frame[data-device="tablet"] .lp-grid-3 { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
        .preview-device-frame[data-device="laptop"] .lp-nav-toggle { display: none !important; }
        .preview-device-frame[data-device="laptop"] .lp-nav-desktop { display: flex !important; }
        .preview-device-frame[data-device="tablet"] .lp-nav-toggle { display: none !important; }
        .preview-device-frame[data-device="tablet"] .lp-nav-desktop { display: flex !important; }
    </style>
</div>
