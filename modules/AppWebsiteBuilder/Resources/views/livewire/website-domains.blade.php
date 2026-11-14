<div
    class="space-y-6"
    x-data="{
        removeQueueOpen: false,
        removeQueueSiteId: null,
        removeQueueSiteName: '',
        openRemoveQueue(id, name) {
            this.removeQueueSiteId = id;
            this.removeQueueSiteName = name;
            this.removeQueueOpen = true;
        },
        closeRemoveQueue() {
            this.removeQueueOpen = false;
            this.removeQueueSiteId = null;
            this.removeQueueSiteName = '';
        },
    }"
>
    @include('appwebsitebuilder::partials.website-builder-session-toasts')

    @php
        $projectName = $project?->name ?? __('Project');
    @endphp

    <x-ui.sub-header
        :eyebrow="$projectName.' / '.__('Website Builder')"
        :title="__('Domains')"
        :description="__('Connect custom domains and DNS for sites in this project.')"
    >
        <x-slot:actions>
            <span
                class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold"
                style="background: rgba(var(--theme-border-color-rgb), 0.35); color: var(--theme-muted-text-color);"
            >
                {{ __('In development') }}
            </span>
        </x-slot:actions>
    </x-ui.sub-header>

    @if (count($domainSetupQueue) > 0)
        <section
            class="rounded-[1.9rem] border p-5 shadow-[0_30px_80px_-50px_rgba(15,23,42,0.28)] sm:p-6"
            style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-base) 96%, transparent);"
        >
            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-muted-text-color);">
                        {{ __('Website Builder') }}
                    </p>
                    <h2 class="mt-1 text-lg font-semibold tracking-tight" style="color: var(--theme-header-text-color);">
                        {{ __('Domain setup queue') }}
                    </h2>
                    <p class="mt-2 max-w-2xl text-sm leading-relaxed" style="color: var(--theme-muted-text-color);">
                        {{ __('Sites waiting for domain or subdomain setup appear here after you publish from the dashboard.') }}
                    </p>
                </div>
                <p class="text-sm font-semibold tabular-nums" style="color: var(--theme-header-text-color);">
                    {{ trans_choice(':count item in queue|:count items in queue', count($domainSetupQueue), ['count' => count($domainSetupQueue)]) }}
                </p>
            </div>

            <div class="space-y-3">
                @foreach ($domainSetupQueue as $item)
                    <article
                        wire:key="domain-queue-{{ $item['id'] }}-{{ $item['mode'] }}"
                        class="flex flex-col gap-4 rounded-[1.35rem] border p-4 sm:flex-row sm:items-center sm:p-5"
                        style="border-color: rgba(var(--theme-border-color-rgb), 0.58); background-color: color-mix(in srgb, var(--theme-surface-base) 98%, transparent);"
                    >
                        <div class="flex min-w-0 flex-1 items-center gap-4">
                            <div
                                class="relative h-[4.75rem] w-[4.75rem] shrink-0 overflow-hidden rounded-[0.95rem] border"
                                style="border-color: rgba(var(--theme-border-color-rgb), 0.58);"
                            >
                                <img
                                    src="{{ $item['preview_url'] }}"
                                    alt=""
                                    class="h-full w-full object-cover"
                                    loading="lazy"
                                    onerror="this.onerror=null;this.src=@js($item['preview_fallback']);"
                                />
                                <span
                                    class="absolute bottom-1 right-1 h-2 w-2 rounded-full ring-2 ring-white/80"
                                    style="background: {{ $item['accent'] }};"
                                    aria-hidden="true"
                                ></span>
                            </div>
                            <div class="min-w-0 flex-1 space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-base font-semibold leading-snug" style="color: var(--theme-header-text-color);">
                                        {{ $item['name'] }}
                                    </p>
                                    <span
                                        class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em]"
                                        style="background: rgba(124, 58, 237, 0.12); color: #7c3aed;"
                                    >
                                        {{ $item['mode_label'] }}
                                    </span>
                                    <span
                                        class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em]"
                                        style="background: rgba(59, 130, 246, 0.12); color: #2563eb;"
                                    >
                                        {{ $item['status_label'] }}
                                    </span>
                                </div>
                                <p class="text-sm leading-relaxed" style="color: var(--theme-muted-text-color);">
                                    {{ $item['detail'] }}
                                </p>
                                <p class="text-xs" style="color: var(--theme-muted-text-color);">
                                    {{ __('Added :time', ['time' => $item['queued_label']]) }}
                                </p>
                                @if (! empty($item['selected_provider']))
                                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">
                                        {{ __('Provider') }}:
                                        <span style="color: var(--theme-muted-text-color);">{{ $item['selected_provider']['name'] }}</span>
                                    </p>
                                @elseif (! empty($item['domain_providers']))
                                    <div class="flex flex-col gap-2 pt-1">
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">
                                            {{ __('Buy a domain') }}
                                        </p>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($item['domain_providers'] as $provider)
                                                <a
                                                    href="{{ $provider['url'] }}"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-sm font-semibold transition hover:-translate-y-px"
                                                    style="border-color: rgba(var(--theme-border-color-rgb), 0.58); background: rgba(var(--theme-border-color-rgb), 0.12); color: var(--theme-header-text-color);"
                                                >
                                                    {{ $provider['name'] }}
                                                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px]" style="color: var(--theme-muted-text-color);"></i>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-end gap-2 sm:shrink-0">
                            <x-ui.button
                                :href="$item['preview_route']"
                                variant="outline"
                                size="sm"
                                wire:navigate
                            >
                                <i class="fa-regular fa-eye text-[11px]"></i>
                                {{ __('Preview site') }}
                            </x-ui.button>
                            <x-ui.button
                                type="button"
                                variant="outline"
                                size="sm"
                                data-site-id="{{ $item['id'] }}"
                                data-site-name="{{ e($item['name']) }}"
                                x-on:click="openRemoveQueue(Number($el.dataset.siteId), $el.dataset.siteName)"
                            >
                                <i class="fa-solid fa-xmark text-[11px]"></i>
                                {{ __('Remove') }}
                            </x-ui.button>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <div class="grid gap-4 sm:grid-cols-3">
        @foreach ([
            ['key' => 'published', 'label' => __('Published'), 'value' => $queueSummary['published'] > 0 ? (string) $queueSummary['published'] : '—'],
            ['key' => 'pending_dns', 'label' => __('Pending DNS'), 'value' => $queueSummary['pending_dns'] > 0 ? (string) $queueSummary['pending_dns'] : '—'],
            ['key' => 'ssl', 'label' => __('SSL status'), 'value' => '—'],
        ] as $card)
            <x-ui.card class="space-y-2 p-5 opacity-80">
                <p class="text-xs font-semibold uppercase tracking-wide" style="color: var(--theme-muted-text-color);">{{ $card['label'] }}</p>
                <p class="text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ $card['value'] }}</p>
                <p class="text-xs" style="color: var(--theme-muted-text-color);">
                    @if ($card['key'] === 'published' && $queueSummary['published'] > 0)
                        {{ __('Live on platform subdomain — not external DNS') }}
                    @elseif ($card['key'] === 'pending_dns' && $queueSummary['pending_dns'] > 0)
                        {{ __('From setup queue') }}
                    @elseif ($card['key'] === 'published')
                        {{ __('No sites published yet') }}
                    @else
                        {{ __('Coming soon') }}
                    @endif
                </p>
            </x-ui.card>
        @endforeach
    </div>

    @if (count($publishedSubdomains) > 0)
        <section
            class="rounded-[1.9rem] border p-5 shadow-[0_30px_80px_-50px_rgba(15,23,42,0.28)] sm:p-6"
            style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-base) 96%, transparent);"
        >
            <div class="mb-5 min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-muted-text-color);">
                    {{ __('Website Builder') }}
                </p>
                <h2 class="mt-1 text-lg font-semibold tracking-tight" style="color: var(--theme-header-text-color);">
                    {{ __('Live platform subdomains') }}
                </h2>
                <p class="mt-2 max-w-2xl text-sm leading-relaxed" style="color: var(--theme-muted-text-color);">
                    {{ __('Sites with a platform subdomain slug. Visitors can open the public URL without signing in. This is not the same as connecting a domain at your registrar.') }}
                </p>
            </div>

            <div class="space-y-3">
                @foreach ($publishedSubdomains as $item)
                    <article
                        wire:key="live-subdomain-{{ $item['id'] }}"
                        class="flex flex-col gap-4 rounded-[1.35rem] border p-4 sm:flex-row sm:items-center sm:p-5"
                        style="border-color: rgba(16, 185, 129, 0.35); background-color: color-mix(in srgb, var(--theme-surface-base) 98%, transparent);"
                    >
                        <div class="flex min-w-0 flex-1 items-center gap-4">
                            <div
                                class="relative h-[4.75rem] w-[4.75rem] shrink-0 overflow-hidden rounded-[0.95rem] border"
                                style="border-color: rgba(var(--theme-border-color-rgb), 0.58);"
                            >
                                <img
                                    src="{{ $item['preview_url'] }}"
                                    alt=""
                                    class="h-full w-full object-cover"
                                    loading="lazy"
                                    onerror="this.onerror=null;this.src=@js($item['preview_fallback']);"
                                />
                                <span
                                    class="absolute bottom-1 right-1 h-2 w-2 rounded-full ring-2 ring-white/80"
                                    style="background: {{ $item['accent'] }};"
                                    aria-hidden="true"
                                ></span>
                            </div>
                            <div class="min-w-0 flex-1 space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-base font-semibold leading-snug" style="color: var(--theme-header-text-color);">
                                        {{ $item['name'] }}
                                    </p>
                                    <span
                                        class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em]"
                                        style="background: rgba(124, 58, 237, 0.12); color: #7c3aed;"
                                    >
                                        {{ __('Subdomain') }}
                                    </span>
                                    <span
                                        class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em]"
                                        style="background: rgba(16, 185, 129, 0.15); color: #059669;"
                                        title="{{ __('Published and publicly reachable at this app URL') }}"
                                    >
                                        {{ $item['status_label'] }}
                                    </span>
                                </div>
                                @if (filled($item['public_url']))
                                    <a
                                        href="{{ $item['public_url'] }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-2 break-all text-sm font-semibold transition hover:opacity-90"
                                        style="color: #059669;"
                                    >
                                        {{ $item['public_url'] }}
                                    </a>
                                @endif
                                @if (filled($item['published_label']))
                                    <p class="text-xs" style="color: var(--theme-muted-text-color);">
                                        {{ __('Published :time', ['time' => $item['published_label']]) }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-end gap-2 sm:shrink-0">
                            @if (filled($item['public_url']))
                                <x-ui.button
                                    :href="$item['public_url']"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    size="sm"
                                >
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i>
                                    {{ __('Visit') }}
                                </x-ui.button>
                            @endif
                            <x-ui.button
                                type="button"
                                variant="outline"
                                size="sm"
                                wire:click="unpublishSubdomain({{ (int) $item['id'] }})"
                                wire:confirm="{{ __('Remove this subdomain? The site will stay as a draft on your dashboard and the public URL will stop working.') }}"
                                wire:loading.attr="disabled"
                                wire:target="unpublishSubdomain"
                            >
                                <i class="fa-solid fa-link-slash text-[11px]"></i>
                                {{ __('Unpublish') }}
                            </x-ui.button>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @if (count($publishedCustomDomains) > 0)
        <section
            class="rounded-[1.9rem] border p-5 shadow-[0_30px_80px_-50px_rgba(15,23,42,0.28)] sm:p-6"
            style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-base) 96%, transparent);"
        >
            <div class="mb-5 min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-muted-text-color);">
                    {{ __('Website Builder') }}
                </p>
                <h2 class="mt-1 text-lg font-semibold tracking-tight" style="color: var(--theme-header-text-color);">
                    {{ __('Live custom domains') }}
                </h2>
                <p class="mt-2 max-w-2xl text-sm leading-relaxed" style="color: var(--theme-muted-text-color);">
                    {{ __('Sites published on your own domain. Map DNS at your registrar; they are not served from a platform /sites/ URL.') }}
                </p>
            </div>

            <div class="space-y-3">
                @foreach ($publishedCustomDomains as $item)
                    <article
                        wire:key="live-custom-{{ $item['id'] }}"
                        class="flex flex-col gap-4 rounded-[1.35rem] border p-4 sm:flex-row sm:items-center sm:p-5"
                        style="border-color: rgba(59, 130, 246, 0.35); background-color: color-mix(in srgb, var(--theme-surface-base) 98%, transparent);"
                    >
                        <div class="flex min-w-0 flex-1 items-center gap-4">
                            <div
                                class="relative h-[4.75rem] w-[4.75rem] shrink-0 overflow-hidden rounded-[0.95rem] border"
                                style="border-color: rgba(var(--theme-border-color-rgb), 0.58);"
                            >
                                <img
                                    src="{{ $item['preview_url'] }}"
                                    alt=""
                                    class="h-full w-full object-cover"
                                    loading="lazy"
                                    onerror="this.onerror=null;this.src=@js($item['preview_fallback']);"
                                />
                                <span
                                    class="absolute bottom-1 right-1 h-2 w-2 rounded-full ring-2 ring-white/80"
                                    style="background: {{ $item['accent'] }};"
                                    aria-hidden="true"
                                ></span>
                            </div>
                            <div class="min-w-0 flex-1 space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-base font-semibold leading-snug" style="color: var(--theme-header-text-color);">
                                        {{ $item['name'] }}
                                    </p>
                                    <span
                                        class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em]"
                                        style="background: rgba(59, 130, 246, 0.12); color: #2563eb;"
                                    >
                                        {{ __('Custom domain') }}
                                    </span>
                                    <span
                                        class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em]"
                                        style="background: rgba(16, 185, 129, 0.15); color: #059669;"
                                    >
                                        {{ $item['status_label'] }}
                                    </span>
                                </div>
                                @if (filled($item['public_url']))
                                    <a
                                        href="{{ $item['public_url'] }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-2 break-all text-sm font-semibold transition hover:opacity-90"
                                        style="color: #2563eb;"
                                    >
                                        {{ $item['public_url'] }}
                                    </a>
                                @endif
                                @if (filled($item['published_label']))
                                    <p class="text-xs" style="color: var(--theme-muted-text-color);">
                                        {{ __('Published :time', ['time' => $item['published_label']]) }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-end gap-2 sm:shrink-0">
                            @if (filled($item['public_url']))
                                <x-ui.button
                                    :href="$item['public_url']"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    size="sm"
                                >
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i>
                                    {{ __('Visit') }}
                                </x-ui.button>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <template x-teleport="body">
        <div
            x-cloak
            x-show="removeQueueOpen"
            class="fixed inset-0 z-[120] flex items-end justify-center overflow-y-auto p-4 sm:items-center sm:p-6"
            x-on:keydown.escape.window="closeRemoveQueue()"
        >
            <div class="absolute inset-0 bg-white/55 backdrop-blur-[6px] dark:bg-slate-950/55" x-on:click="closeRemoveQueue()"></div>

            <div x-show="removeQueueOpen" x-transition.opacity.scale.90 class="relative w-full max-w-[26rem]">
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
                                {{ __('Remove from queue?') }}
                            </h3>
                            <p class="mt-2 text-[15px] leading-7" style="color: var(--theme-muted-text-color);">
                                {{ __('This will remove the domain setup for') }}
                                <span class="font-semibold" style="color: var(--theme-header-text-color);" x-text="removeQueueSiteName"></span>
                                {{ __('from your queue. You can publish again from the dashboard anytime.') }}
                            </p>
                        </div>

                        <button type="button" style="color: var(--theme-muted-text-color);" x-on:click="closeRemoveQueue()">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>

                    <div
                        class="border-t px-5 py-4 sm:px-6"
                        style="border-color: color-mix(in srgb, var(--theme-border-color) 52%, transparent); background-color: color-mix(in srgb, var(--theme-surface-base) 72%, transparent);"
                    >
                        <div class="flex items-center justify-end gap-3">
                            <x-ui.button type="button" variant="outline" x-on:click="closeRemoveQueue()">
                                <i class="fa-solid fa-xmark"></i>
                                {{ __('Cancel') }}
                            </x-ui.button>
                            <x-ui.button
                                type="button"
                                variant="danger"
                                wire:loading.attr="disabled"
                                wire:target="dismissQueueItem"
                                x-on:click="$wire.dismissQueueItem(removeQueueSiteId); closeRemoveQueue()"
                            >
                                <i class="fa-solid fa-trash-can"></i>
                                {{ __('Remove') }}
                            </x-ui.button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
