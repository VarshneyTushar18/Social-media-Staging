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
            $wire.closePublishDomainModal();
        },
        backFromGodaddy() {
            this.publishStep = 'providers';
            $wire.closePublishDomainModal();
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
        confirmCustomDomain() {
            if (! this.selectedProviderKey) {
                return;
            }

            if (! ['godaddy', 'namecheap'].includes(this.selectedProviderKey)) {
                return;
            }

            $wire.openPublishDomainModal(this.publishSiteId, this.selectedProviderKey).then(() => {
                this.publishStep = 'domainWizard';
            });
        },
    }"
    x-init="Livewire.on('close-publish-site-modal', () => closePublishSite())"
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
                        class="relative min-h-[22rem] overflow-hidden rounded-[var(--theme-card-radius,0.875rem)] border sm:min-h-[18.5rem]"
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

                        <div class="absolute inset-0 flex min-h-0 flex-col p-4 sm:p-5">
                            <div class="flex shrink-0 items-start justify-between gap-2">
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

                            <div class="mt-auto flex min-h-0 w-full flex-col gap-2 pt-3">
                                <div class="min-h-0 space-y-1">
                                    <p class="line-clamp-2 text-lg font-semibold leading-snug text-white">{{ $card['name'] }}</p>
                                    @if (filled($card['headline'] ?? null) && ($card['headline'] ?? '') !== $card['name'])
                                        <p class="line-clamp-1 text-sm font-semibold leading-snug text-white/90">{{ $card['headline'] }}</p>
                                    @endif
                                    <p class="line-clamp-2 text-sm leading-relaxed text-white/80">{{ $card['summary'] }}</p>
                                    @if (filled($card['updated_label'] ?? null))
                                        <p class="line-clamp-1 text-[11px] text-white/60">{{ __('Updated :time', ['time' => $card['updated_label']]) }}</p>
                                    @endif
                                </div>
                                <div class="grid shrink-0 grid-cols-3 gap-1.5 sm:gap-2">
                                    <x-ui.button
                                        href="{{ route('portal.agents.website.sites.preview', $card['id']) }}"
                                        variant="outline"
                                        size="sm"
                                        wire:navigate
                                        class="!h-9 w-full justify-center !border-white/30 !bg-white/10 !px-2 !text-white hover:!bg-white/20"
                                    >
                                        <i class="fa-regular fa-eye text-[11px]"></i>
                                        <span class="truncate">{{ __('Preview') }}</span>
                                    </x-ui.button>
                                    @if ($card['is_published'] && filled($card['public_url'] ?? null))
                                        <x-ui.button
                                            :href="$card['public_url']"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            size="sm"
                                            class="!h-9 w-full justify-center !border-0 !px-2 !text-white"
                                            style="background: rgba(16, 185, 129, 0.85);"
                                        >
                                            <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i>
                                            <span class="truncate">{{ __('Visit') }}</span>
                                        </x-ui.button>
                                    @elseif (! $card['is_published'])
                                        <x-ui.button
                                            type="button"
                                            size="sm"
                                            class="!h-9 w-full justify-center !border-0 !px-2 !text-white"
                                            style="background: rgba(16, 185, 129, 0.85);"
                                            x-on:click.stop="openPublishSite({{ $card['id'] }}, {{ \Illuminate\Support\Js::from($card['name']) }})"
                                        >
                                            <i class="fa-solid fa-rocket text-[11px]"></i>
                                            <span class="truncate">{{ __('Publish') }}</span>
                                        </x-ui.button>
                                    @else
                                        <span class="h-9 w-full" aria-hidden="true"></span>
                                    @endif
                                    <x-ui.button
                                        type="button"
                                        variant="danger"
                                        size="sm"
                                        wire:key="delete-site-{{ $card['id'] }}"
                                        wire:click.stop="openDeleteSiteModal({{ $card['id'] }})"
                                        class="!h-9 w-full justify-center !px-2"
                                    >
                                        <i class="fa-solid fa-trash-can text-[11px]"></i>
                                        <span class="truncate">{{ __('Delete') }}</span>
                                    </x-ui.button>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    @endif

    <style>
        .publish-modal-option {
            border-color: var(--theme-border-color);
            background-color: var(--theme-button-soft-bg);
            transition:
                transform 0.2s ease,
                border-color 0.2s ease,
                background-color 0.2s ease,
                box-shadow 0.2s ease;
        }

        .publish-modal-option:hover {
            border-color: rgba(var(--theme-accent-rgb), 0.42);
            background-color: rgba(var(--theme-accent-rgb), 0.09);
            box-shadow: 0 10px 28px -14px rgba(var(--theme-accent-rgb), 0.35);
            transform: translateY(-2px);
        }

        .publish-modal-option:active {
            transform: translateY(0);
        }

        .publish-modal-option:focus-visible {
            outline: 2px solid rgba(var(--theme-accent-rgb), 0.45);
            outline-offset: 2px;
        }

        .publish-modal-option.is-selected {
            border-color: var(--theme-accent);
            background-color: rgba(var(--theme-accent-rgb), 0.12);
            box-shadow: 0 0 0 1px rgba(var(--theme-accent-rgb), 0.18);
        }

        .publish-modal-option.is-selected:hover {
            background-color: rgba(var(--theme-accent-rgb), 0.18);
            border-color: var(--theme-accent);
        }
    </style>

    <template x-teleport="body">
        <div
            x-cloak
            x-show="publishSiteOpen && publishStep !== 'domainWizard'"
            class="fixed inset-0 z-[120] flex items-end justify-center overflow-y-auto p-4 sm:items-center sm:p-6"
            x-on:keydown.escape.window="if (publishSiteOpen && publishStep !== 'domainWizard') closePublishSite()"
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
                            class="publish-modal-option flex w-full items-center gap-4 rounded-xl border p-4 text-left"
                            x-on:click="openDomainProviders()"
                        >
                            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-white" style="background: var(--theme-accent);">
                                <i class="fa-solid fa-globe"></i>
                            </span>
                            <span class="min-w-0 text-sm font-bold" style="color: var(--theme-header-text-color);">{{ __('Custom domain') }}</span>
                        </button>
                        <button
                            type="button"
                            class="publish-modal-option flex w-full items-center gap-4 rounded-xl border p-4 text-left"
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
                                class="publish-modal-option flex w-full items-center gap-4 rounded-xl border p-4 text-left"
                                x-bind:class="selectedProviderKey === @js($provider['key']) ? 'is-selected' : ''"
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
                                    x-show="publishStep === 'providers' && selectedProviderKey"
                                    x-cloak
                                    x-on:click="confirmCustomDomain()"
                                >
                                    {{ __('Continue') }}
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

    </template>

    <div
        x-cloak
        x-show="publishSiteOpen && publishStep === 'domainWizard'"
        class="fixed inset-0 z-[120] flex items-end justify-center overflow-x-hidden overflow-y-auto p-4 sm:items-center sm:p-6"
        x-on:keydown.escape.window="if (publishSiteOpen && publishStep === 'domainWizard') closePublishSite()"
    >
        <div class="absolute inset-0 bg-white/55 backdrop-blur-[6px] dark:bg-slate-950/55" x-on:click="closePublishSite()"></div>

        <div x-transition.opacity.scale.90 class="relative w-full min-w-0 max-w-3xl">
            <div
                class="max-w-full overflow-hidden rounded-[1.15rem] border shadow-[0_32px_80px_-34px_rgba(15,23,42,0.32)]"
                style="border-color: color-mix(in srgb, var(--theme-border-color) 58%, transparent); background-color: var(--theme-surface-overlay);"
            >
                @php
                    $publishProviderMeta = \Modules\AppWebsiteBuilder\Support\WebsiteDomainProviders::find($publishDomainProvider);
                    $publishProviderLabel = $publishProviderMeta['name'] ?? ucfirst($publishDomainProvider);
                @endphp
                <div
                    class="grid grid-cols-[1fr_auto_1fr] items-center gap-3 border-b px-4 py-3 sm:px-5"
                    style="border-color: color-mix(in srgb, var(--theme-border-color) 52%, transparent);"
                >
                    <button
                        type="button"
                        class="inline-flex items-center justify-self-start gap-1.5 text-xs font-semibold"
                        style="color: var(--theme-muted-text-color);"
                        x-on:click="backFromGodaddy()"
                    >
                        <i class="fa-solid fa-arrow-left text-[10px]"></i>
                        {{ __('Back to providers') }}
                    </button>
                    <div class="min-w-0 text-center">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">
                            {{ __('Provider') }}
                        </p>
                        <p class="truncate text-sm font-bold" style="color: var(--theme-header-text-color);">
                            <i class="fa-solid fa-globe mr-1 text-[11px]" style="color: var(--theme-accent);"></i>
                            {{ $publishProviderLabel }}
                        </p>
                    </div>
                    <button
                        type="button"
                        class="inline-flex justify-self-end"
                        style="color: var(--theme-muted-text-color);"
                        x-on:click="closePublishSite()"
                    >
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                <div class="max-h-[min(85vh,42rem)] w-full min-w-0 overflow-x-hidden overflow-y-auto">
                    @if ($publishDomainSiteId)
                        @php($godaddyPublishSite = \Modules\AppWebsiteBuilder\Models\WebsiteSite::query()->find($publishDomainSiteId))
                        @if ($godaddyPublishSite)
                            <livewire:modules.app-website-builder.livewire.website-publish-wizard
                                :website-site="$godaddyPublishSite"
                                :embedded="true"
                                :domain-provider="$publishDomainProvider"
                                wire:key="publish-unified-{{ $publishDomainSiteId }}-{{ $publishDomainProvider }}"
                            />
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($deleteModalOpen)
        <div
            class="fixed inset-0 z-[120] flex items-end justify-center overflow-y-auto p-4 sm:items-center sm:p-6"
            wire:keydown.escape="closeDeleteSiteModal"
        >
            <div
                class="absolute inset-0 bg-white/55 backdrop-blur-[6px] dark:bg-slate-950/55"
                wire:click="closeDeleteSiteModal"
            ></div>

            <div class="relative w-full max-w-[26rem]" wire:click.stop>
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
                                <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ $deleteSiteName }}</span>.
                                {{ __('This cannot be undone.') }}
                            </p>
                        </div>

                        <button type="button" style="color: var(--theme-muted-text-color);" wire:click="closeDeleteSiteModal">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>

                    <div
                        class="border-t px-5 py-4 sm:px-6"
                        style="border-color: color-mix(in srgb, var(--theme-border-color) 52%, transparent); background-color: color-mix(in srgb, var(--theme-surface-base) 72%, transparent);"
                    >
                        <div class="flex items-center justify-end gap-3">
                            <x-ui.button type="button" variant="outline" wire:click="closeDeleteSiteModal">
                                <i class="fa-solid fa-xmark"></i>
                                {{ __('Cancel') }}
                            </x-ui.button>
                            <x-ui.button
                                type="button"
                                variant="danger"
                                wire:click="deleteSite"
                                wire:loading.attr="disabled"
                                wire:target="deleteSite"
                            >
                                <span wire:loading.remove wire:target="deleteSite">
                                    <i class="fa-solid fa-trash-can"></i>
                                    {{ __('Delete site') }}
                                </span>
                                <span wire:loading wire:target="deleteSite">{{ __('Deleting…') }}</span>
                            </x-ui.button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
