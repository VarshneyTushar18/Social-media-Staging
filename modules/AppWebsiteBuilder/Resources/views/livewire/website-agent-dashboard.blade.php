<div
    class="space-y-6"
    x-data="{
        publishSiteOpen: false,
        publishSiteId: null,
        publishSiteName: '',
        publishStep: 'choice',
        domainProviders: @js($domainProviders),
        publishUrlPattern: @js($publishUrlPattern),
        selectedProviderKey: null,
        subdomainSlug: '',
        suggestSlugFromName(name) {
            return (name || '')
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '')
                .slice(0, 63);
        },
        subdomainPreviewUrl() {
            const slug = this.subdomainSlug || 'your-site';

            return this.publishUrlPattern.replace('{slug}', slug);
        },
        openPublishSite(id, name) {
            this.publishSiteId = id;
            this.publishSiteName = name;
            this.publishStep = 'choice';
            this.selectedProviderKey = null;
            this.subdomainSlug = '';
            this.publishSiteOpen = true;
        },
        closePublishSite() {
            this.publishSiteOpen = false;
            this.publishSiteId = null;
            this.publishSiteName = '';
            this.publishStep = 'choice';
            this.selectedProviderKey = null;
            this.subdomainSlug = '';
        },
        openDomainProviders() {
            this.publishStep = 'providers';
            this.selectedProviderKey = null;
        },
        openSubdomainPublish() {
            this.publishStep = 'subdomain';
            this.subdomainSlug = this.suggestSlugFromName(this.publishSiteName);
        },
        backPublishChoice() {
            this.publishStep = 'choice';
            this.selectedProviderKey = null;
            this.subdomainSlug = '';
        },
        confirmPublishSubdomain() {
            if (! this.subdomainSlug) {
                return;
            }

            $wire.publishSubdomain(this.publishSiteId, this.subdomainSlug).then(() => this.closePublishSite());
        },
        connectCustomDomain() {
            if (! this.selectedProviderKey) {
                return;
            }

            const provider = this.domainProviders.find((entry) => entry.key === this.selectedProviderKey);

            if (! provider?.url) {
                return;
            }

            window.open(provider.url, '_blank', 'noopener,noreferrer');
            $wire.continuePublish(this.publishSiteId, 'custom', this.selectedProviderKey);
            this.closePublishSite();
        },
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
                                <div class="flex flex-wrap items-center justify-center gap-2 pt-2">
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
                                    @if ($card['is_published'] && filled($card['public_url'] ?? null))
                                        <x-ui.button
                                            :href="$card['public_url']"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            size="sm"
                                            class="!text-white"
                                            style="background: rgba(16, 185, 129, 0.85);"
                                        >
                                            <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i>
                                            {{ __('Visit') }}
                                        </x-ui.button>
                                    @elseif (! $card['is_published'])
                                        <x-ui.button
                                            type="button"
                                            size="sm"
                                            data-site-id="{{ $card['id'] }}"
                                            data-site-name="{{ e($card['name']) }}"
                                            class="!text-white"
                                            style="background: rgba(16, 185, 129, 0.85);"
                                            x-on:click="openPublishSite(Number($el.dataset.siteId), $el.dataset.siteName)"
                                        >
                                            <i class="fa-solid fa-rocket text-[11px]"></i>
                                            {{ __('Publish') }}
                                        </x-ui.button>
                                    @endif
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
            x-show="publishSiteOpen"
            class="fixed inset-0 z-[120] flex items-end justify-center overflow-y-auto p-4 sm:items-center sm:p-6"
            x-on:keydown.escape.window="closePublishSite()"
        >
            <div class="absolute inset-0 bg-white/55 backdrop-blur-[6px] dark:bg-slate-950/55" x-on:click="closePublishSite()"></div>

            <div x-show="publishSiteOpen" x-transition.opacity.scale.90 class="relative w-full max-w-[32rem]">
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
                                <span x-show="publishStep === 'choice'">{{ __('Publish website') }}</span>
                                <span x-show="publishStep === 'providers'" x-cloak>{{ __('Buy a domain') }}</span>
                                <span x-show="publishStep === 'subdomain'" x-cloak>{{ __('Choose subdomain') }}</span>
                            </h3>
                            <p class="mt-2 text-sm leading-relaxed" style="color: var(--theme-muted-text-color);">
                                <span x-show="publishStep === 'choice'">
                                    {{ __('Choose how') }}
                                    <span class="font-semibold" style="color: var(--theme-header-text-color);" x-text="publishSiteName"></span>
                                    {{ __('should go live.') }}
                                </span>
                                <span x-show="publishStep === 'providers'" x-cloak>
                                    {{ __('Pick a provider for') }}
                                    <span class="font-semibold" style="color: var(--theme-header-text-color);" x-text="publishSiteName"></span>.
                                </span>
                                <span x-show="publishStep === 'subdomain'" x-cloak>
                                    {{ __('Pick a unique name for') }}
                                    <span class="font-semibold" style="color: var(--theme-header-text-color);" x-text="publishSiteName"></span>.
                                    @if (filled($publishDomain))
                                        {{ __('It will be served on our platform domain.') }}
                                    @else
                                        {{ __('It will be served under') }}
                                        <span class="font-semibold" style="color: var(--theme-header-text-color);">/{{ trim(config('modules.appwebsitebuilder.publish_path_prefix', 'sites'), '/') }}/</span>
                                    @endif
                                </span>
                            </p>
                        </div>
                        <button type="button" style="color: var(--theme-muted-text-color);" x-on:click="closePublishSite()">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>

                    <div class="space-y-3 px-5 py-5 sm:px-6" x-show="publishStep === 'choice'">
                        <button
                            type="button"
                            class="flex w-full items-center gap-4 rounded-xl border p-4 text-left transition hover:-translate-y-px"
                            style="border-color: var(--theme-border-color); background: var(--theme-button-soft-bg);"
                            x-on:click="openDomainProviders()"
                        >
                            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-white" style="background: var(--theme-accent);">
                                <i class="fa-solid fa-globe"></i>
                            </span>
                            <span class="min-w-0 text-sm font-bold" style="color: var(--theme-header-text-color);">{{ __('Custom domain') }}</span>
                        </button>
                        <button
                            type="button"
                            class="flex w-full items-center gap-4 rounded-xl border p-4 text-left transition hover:-translate-y-px"
                            style="border-color: var(--theme-border-color); background: var(--theme-button-soft-bg);"
                            x-on:click="openSubdomainPublish()"
                        >
                            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-white" style="background: #7c3aed;">
                                <i class="fa-solid fa-link"></i>
                            </span>
                            <span class="min-w-0 text-sm font-bold" style="color: var(--theme-header-text-color);">{{ __('Subdomain') }}</span>
                        </button>
                    </div>

                    <div class="space-y-4 px-5 py-5 sm:px-6" x-show="publishStep === 'subdomain'" x-cloak>
                        <div class="min-w-0 space-y-2">
                            <label class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">
                                {{ __('Subdomain') }}
                            </label>
                            <input
                                type="text"
                                x-model="subdomainSlug"
                                autocomplete="off"
                                spellcheck="false"
                                class="h-12 w-full rounded-xl border px-4 text-sm font-semibold shadow-sm outline-none focus:ring-2"
                                style="border-color: var(--theme-border-color); background: var(--theme-button-soft-bg); color: var(--theme-header-text-color);"
                                placeholder="ecommerce"
                            />
                            @error('subdomain_slug')
                                <p class="text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="rounded-xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.58); background: rgba(var(--theme-border-color-rgb), 0.08);">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Live URL') }}</p>
                            <p class="mt-1 break-all text-sm font-semibold" style="color: var(--theme-header-text-color);" x-text="subdomainPreviewUrl()"></p>
                        </div>
                    </div>

                    <div class="space-y-3 px-5 py-5 sm:px-6" x-show="publishStep === 'providers'" x-cloak>
                        @foreach ($domainProviders as $provider)
                            <button
                                type="button"
                                class="flex w-full items-center gap-4 rounded-xl border p-4 text-left transition hover:-translate-y-px"
                                x-bind:style="selectedProviderKey === @js($provider['key'])
                                    ? 'border-color: var(--theme-accent); background: rgba(var(--theme-accent-rgb), 0.12);'
                                    : 'border-color: var(--theme-border-color); background: var(--theme-button-soft-bg);'"
                                x-on:click="selectedProviderKey = @js($provider['key'])"
                            >
                                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-white" style="background: var(--theme-accent);">
                                    <i class="fa-solid fa-globe text-sm"></i>
                                </span>
                                <span class="text-sm font-bold" style="color: var(--theme-header-text-color);">{{ $provider['name'] }}</span>
                            </button>
                        @endforeach
                    </div>

                    <div
                        class="border-t px-5 py-4 sm:px-6"
                        style="border-color: color-mix(in srgb, var(--theme-border-color) 52%, transparent); background-color: color-mix(in srgb, var(--theme-surface-base) 72%, transparent);"
                    >
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <x-ui.button
                                    type="button"
                                    variant="outline"
                                    x-show="publishStep === 'providers' || publishStep === 'subdomain'"
                                    x-cloak
                                    x-on:click="backPublishChoice()"
                                >
                                    <i class="fa-solid fa-arrow-left text-[11px]"></i>
                                    {{ __('Back') }}
                                </x-ui.button>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <x-ui.button
                                    type="button"
                                    x-show="publishStep === 'subdomain'"
                                    x-cloak
                                    x-bind:disabled="! subdomainSlug"
                                    wire:loading.attr="disabled"
                                    wire:target="publishSubdomain"
                                    x-on:click="confirmPublishSubdomain()"
                                >
                                    {{ __('Publish site') }}
                                </x-ui.button>
                                <x-ui.button
                                    type="button"
                                    x-show="publishStep === 'providers'"
                                    x-cloak
                                    x-bind:disabled="! selectedProviderKey"
                                    x-on:click="connectCustomDomain()"
                                >
                                    {{ __('Connect domain') }}
                                </x-ui.button>
                                <x-ui.button type="button" variant="outline" x-on:click="closePublishSite()">
                                    {{ __('Cancel') }}
                                </x-ui.button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
