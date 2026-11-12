<div
    class="space-y-6"
    x-data="{
        deleteSiteOpen: false,
        deleteSiteId: null,
        deleteSiteName: '',
        openDeleteSite(id, name) {
            this.deleteSiteId = id;
            this.deleteSiteName = name;
            this.deleteSiteOpen = true;
        },
        closeDeleteSite() {
            this.deleteSiteOpen = false;
            this.deleteSiteId = null;
            this.deleteSiteName = '';
        },
    }"
>
    @include('appwebsitebuilder::partials.website-builder-session-toasts')

    @php
        $projectName = $project?->name ?? __('Project');
    @endphp

    <x-ui.sub-header
        :eyebrow="$projectName.' / '.__('Website Builder')"
        :title="__('Website dashboard')"
        :description="__('AI and template sites in this project.')"
    >
        <x-slot:actions>
            <x-ui.button href="{{ route('portal.agents.website.sites.create') }}" wire:navigate>
                <i class="fa-solid fa-plus mr-2 text-[11px]"></i>
                {{ __('Add new website') }}
            </x-ui.button>
        </x-slot:actions>
    </x-ui.sub-header>

    @if ($sites->isEmpty())
        <x-ui.card class="p-6 sm:p-8">
            <div class="flex flex-col items-center gap-4 py-6 text-center sm:py-10">
                <span
                    class="inline-flex h-14 w-14 items-center justify-center rounded-2xl text-lg font-bold text-white"
                    style="background: #7c3aed;"
                >WB</span>
                <div class="max-w-md space-y-2">
                    <p class="text-lg font-semibold" style="color: var(--theme-header-text-color);">
                        {{ __('No websites yet') }}
                    </p>
                    <p class="text-sm leading-relaxed" style="color: var(--theme-muted-text-color);">
                        {{ __('Start from a template to add your first site. Counts on this dashboard only include sites you create here—not the project profile URL from onboarding.') }}
                    </p>
                </div>
                <x-ui.button href="{{ route('portal.agents.website.sites.create') }}" wire:navigate>
                    <i class="fa-solid fa-plus mr-2 text-[11px]"></i>
                    {{ __('Add new website') }}
                </x-ui.button>
            </div>
        </x-ui.card>
    @else
        <div class="space-y-4">
            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Your sites') }}</p>

            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($siteCards as $card)
                    <article
                        wire:key="site-card-{{ $card['id'] }}"
                        class="relative aspect-[4/5] overflow-hidden rounded-[var(--theme-card-radius,0.875rem)] border sm:aspect-[16/10]"
                        style="border-color: var(--theme-border-color); background: rgba(var(--theme-border-color-rgb), 0.25);"
                    >
                        <img
                            src="{{ $card['preview_url'] }}"
                            alt=""
                            class="absolute inset-0 h-full w-full object-cover"
                            loading="lazy"
                            onerror="this.onerror=null;this.src=@js($card['preview_fallback']);"
                        />
                        <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-slate-950/95 via-slate-950/55 to-slate-950/15"></div>

                        <div class="absolute inset-0 flex flex-col p-4 sm:p-5">
                            <div class="flex items-start justify-between gap-2">
                                <span
                                    class="inline-flex h-2.5 w-2.5 shrink-0 rounded-full ring-2 ring-white/80"
                                    style="background: {{ $card['accent'] }};"
                                    aria-hidden="true"
                                ></span>
                                <span
                                    class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white"
                                    style="{{ $card['is_published']
                                        ? 'background: rgba(16, 185, 129, 0.35);'
                                        : 'background: rgba(255, 255, 255, 0.15);' }}"
                                >{{ $card['is_published'] ? __('Published') : __('Draft') }}</span>
                            </div>

                            <div class="mt-auto space-y-1.5">
                                <p class="text-lg font-semibold leading-snug text-white">{{ $card['name'] }}</p>
                                @if (filled($card['headline'] ?? null) && ($card['headline'] ?? '') !== $card['name'])
                                    <p class="text-sm font-semibold leading-snug text-white/90 line-clamp-2">{{ $card['headline'] }}</p>
                                @endif
                                <p class="text-sm leading-relaxed text-white/80 line-clamp-2">{{ $card['summary'] }}</p>
                                @if (filled($card['updated_label'] ?? null))
                                    <p class="text-[11px] text-white/60">{{ __('Updated :time', ['time' => $card['updated_label']]) }}</p>
                                @endif

                                <div class="flex flex-wrap items-center gap-2 pt-2">
                                    <x-ui.button
                                        href="{{ route('portal.agents.website.sites.preview', $card['id']) }}"
                                        variant="outline"
                                        size="sm"
                                        wire:navigate
                                        class="!border-white/30 !bg-white/10 !text-white hover:!bg-white/20"
                                    >
                                        <i class="fa-regular fa-eye text-[11px]"></i>
                                        {{ __('Preview') }}
                                    </x-ui.button>
                                    <x-ui.button
                                        type="button"
                                        variant="danger"
                                        size="sm"
                                        data-site-id="{{ $card['id'] }}"
                                        data-site-name="{{ e($card['name']) }}"
                                        x-on:click="openDeleteSite(Number($el.dataset.siteId), $el.dataset.siteName)"
                                    >
                                        <i class="fa-solid fa-trash-can text-[11px]"></i>
                                        {{ __('Delete') }}
                                    </x-ui.button>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    @endif

    <template x-teleport="body">
        <div
            x-cloak
            x-show="deleteSiteOpen"
            class="fixed inset-0 z-[120] flex items-end justify-center overflow-y-auto p-4 sm:items-center sm:p-6"
            x-on:keydown.escape.window="closeDeleteSite()"
        >
            <div class="absolute inset-0 bg-white/55 backdrop-blur-[6px] dark:bg-slate-950/55" x-on:click="closeDeleteSite()"></div>

            <div x-show="deleteSiteOpen" x-transition.opacity.scale.90 class="relative w-full max-w-[26rem]">
                <div
                    class="overflow-hidden rounded-[1.15rem] border shadow-[0_32px_80px_-34px_rgba(15,23,42,0.32)]"
                    style="border-color: color-mix(in srgb, var(--theme-border-color) 58%, transparent); background-color: var(--theme-surface-overlay);"
                >
                    <div
                        class="flex items-start justify-between gap-4 border-b px-5 py-4 sm:px-6 sm:py-5"
                        style="border-color: color-mix(in srgb, var(--theme-border-color) 52%, transparent);"
                    >
                        <div class="min-w-0">
                            <h3 class="text-[1.05rem] font-semibold tracking-[-0.02em]" style="color: var(--theme-header-text-color);">
                                {{ __('Delete site?') }}
                            </h3>
                            <p class="mt-2 text-[15px] leading-7" style="color: var(--theme-muted-text-color);">
                                {{ __('This will permanently remove') }}
                                <span class="font-semibold" style="color: var(--theme-header-text-color);" x-text="deleteSiteName"></span>.
                                {{ __('This cannot be undone.') }}
                            </p>
                        </div>

                        <button type="button" style="color: var(--theme-muted-text-color);" x-on:click="closeDeleteSite()">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>

                    <div
                        class="border-t px-5 py-4 sm:px-6"
                        style="border-color: color-mix(in srgb, var(--theme-border-color) 52%, transparent); background-color: color-mix(in srgb, var(--theme-surface-base) 72%, transparent);"
                    >
                        <div class="flex items-center justify-end gap-3">
                            <x-ui.button type="button" variant="outline" x-on:click="closeDeleteSite()">
                                <i class="fa-solid fa-xmark"></i>
                                {{ __('Cancel') }}
                            </x-ui.button>
                            <x-ui.button
                                type="button"
                                variant="danger"
                                wire:loading.attr="disabled"
                                wire:target="deleteSite"
                                x-on:click="$wire.deleteSite(deleteSiteId); closeDeleteSite()"
                            >
                                <i class="fa-solid fa-trash-can"></i>
                                {{ __('Delete site') }}
                            </x-ui.button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
