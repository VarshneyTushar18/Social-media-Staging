@props([
    'embedded' => false,
    'publishStepNumber' => 1,
    'domainProvider' => 'godaddy',
    'providerLabel' => 'GoDaddy',
    'displayHost' => null,
    'bareDomain' => null,
])

@php
    $hostFormatter = is_callable($displayHost)
        ? $displayHost
        : fn (string $domain): string => app(
            $domainProvider === 'namecheap'
                ? \Modules\AppWebsiteBuilder\Support\NamecheapDomainClient::class
                : \Modules\AppWebsiteBuilder\Support\GoDaddyDomainClient::class
        )->displayHost($domain);

    $bareHostFormatter = is_callable($bareDomain)
        ? $bareDomain
        : fn (string $domain): string => \Modules\AppWebsiteBuilder\Support\WebsiteDomainInputValidator::normalizeRegisteredDomain($domain);

    $dnsRecordStatusRows = $dnsRecordStatuses ?? [];

    $dnsStatusFor = function (string $type, string $host) use ($dnsRecordStatusRows): ?string {
        foreach ($dnsRecordStatusRows as $row) {
            if (strcasecmp((string) ($row['type'] ?? ''), $type) === 0 && (string) ($row['host'] ?? '') === $host) {
                return (string) ($row['status'] ?? 'pending');
            }
        }

        return null;
    };

    $dnsStatusLabel = fn (?string $status): string => match ($status) {
        'connected' => __('Connected'),
        'wrong' => __('Wrong value'),
        default => __('Pending'),
    };

    $stepTitles = [
        'search' => __('Find your domain'),
        'review' => __('Confirm your domain'),
        'register' => __('Register at :provider', ['provider' => $providerLabel]),
        'dns' => __('Connect DNS'),
        'done' => __('You’re all set'),
    ];
    $stepDescriptions = [
        'search' => __('Search a business name or type a domain. We’ll show available options.'),
        'review' => __('You’ll complete checkout on :provider, then connect DNS in the next steps.', ['provider' => $providerLabel]),
        'register' => __('Checkout and payment happen on :provider. After purchase, return here for DNS records.', ['provider' => $providerLabel]),
        'dns' => __('Add these records in your :provider DNS panel, then publish your site.', ['provider' => $providerLabel]),
        'done' => __('When DNS propagates, visitors can open your site on your custom domain.'),
    ];
@endphp

<div @class([
    'w-full min-w-0 max-w-full space-y-4',
    'pb-4 sm:pb-5' => $embedded,
])>
    @if ($step !== 'done')
        <div @class(['w-full min-w-0', 'px-4 pt-4 sm:px-5 sm:pt-5' => $embedded])>
            @include('appwebsitebuilder::partials.website-publish-stepper', [
                'current' => $publishStepNumber,
                'compact' => $embedded,
            ])
        </div>
    @endif

    @if ($resumePendingSelection && filled($selectedDomain ?? null) && $step === 'search')
        <div
            class="flex flex-wrap items-center justify-between gap-3 rounded-xl border px-3 py-2 text-sm"
            style="border-color: var(--theme-border-color); background: rgba(var(--theme-border-color-rgb), 0.08);"
        >
            <div class="flex min-w-0 flex-1 flex-wrap items-center gap-1.5 sm:gap-2">
                <span class="text-xs font-semibold uppercase tracking-wide" style="color: var(--theme-muted-text-color);">{{ __('Selected') }}</span>
                <span class="truncate font-semibold" style="color: var(--theme-accent);">{{ $bareHostFormatter($selectedDomain) }}</span>
                <button
                    type="button"
                    wire:click="clearSelectedDomain"
                    wire:loading.attr="disabled"
                    wire:target="clearSelectedDomain"
                    class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md transition hover:opacity-80"
                    style="color: var(--theme-muted-text-color);"
                    title="{{ __('Remove selection') }}"
                    aria-label="{{ __('Remove selection') }}"
                >
                    <i class="fa-solid fa-xmark text-[11px]"></i>
                </button>
            </div>
            @if ($step === 'search')
                <x-ui.button
                    type="button"
                    size="sm"
                    wire:click="continueWithSelectedDomain"
                    wire:loading.attr="disabled"
                    wire:target="continueWithSelectedDomain"
                    class="shrink-0"
                >
                    <span wire:loading.remove wire:target="continueWithSelectedDomain">{{ __('Continue') }}</span>
                    <span wire:loading wire:target="continueWithSelectedDomain">{{ __('Continue…') }}</span>
                </x-ui.button>
            @endif
        </div>
    @endif

    <x-ui.card
        :padding="$embedded ? 'none' : 'md'"
        @class([
            'flex w-full min-w-0 max-w-full flex-col',
            'overflow-visible' => $embedded,
            'overflow-hidden' => ! $embedded,
            'mb-0 !rounded-none !border-0 !bg-transparent !shadow-none' => $embedded,
        ])
    >
        @unless ($embedded)
            <div class="space-y-1 border-b px-5 py-4 sm:px-6" style="border-color: var(--theme-border-color);">
                <h2 class="text-base font-semibold sm:text-lg" style="color: var(--theme-header-text-color);">
                    {{ $stepTitles[$step] ?? __('Publish') }}
                </h2>
                <p class="text-sm leading-relaxed" style="color: var(--theme-muted-text-color);">
                    {{ $stepDescriptions[$step] ?? '' }}
                </p>
            </div>
        @else
            <div class="border-b px-4 py-3 sm:px-5" style="border-color: var(--theme-border-color);">
                <h2 class="text-sm font-semibold sm:text-base" style="color: var(--theme-header-text-color);">
                    {{ $stepTitles[$step] ?? __('Publish') }}
                </h2>
            </div>
        @endunless

        <div class="flex-1 space-y-4 px-4 py-4 sm:px-5 sm:py-5">
            @if ($step === 'search')
                <div class="w-full space-y-2 text-left">
                    @unless ($embedded)
                        <label for="publish-domain-search" class="block text-xs font-semibold uppercase tracking-wide" style="color: var(--theme-muted-text-color);">
                            {{ __('Search domains') }}
                        </label>
                    @endunless
                    <div class="grid w-full min-w-0 grid-cols-[minmax(0,1fr)_auto] items-stretch gap-2">
                        <input
                            id="publish-domain-search"
                            type="text"
                            wire:model="searchQuery"
                            wire:keydown.enter.prevent="searchDomains"
                            class="col-start-1 h-11 min-w-0 w-full rounded-xl border px-4 text-sm font-semibold sm:h-12"
                            style="border-color: var(--theme-border-color); background: var(--theme-button-soft-bg); color: var(--theme-header-text-color);"
                            placeholder="yourbrand.com"
                            autocomplete="off"
                            inputmode="search"
                            enterkeyhint="search"
                        />
                        <button
                            type="button"
                            wire:click="searchDomains"
                            wire:loading.attr="disabled"
                            wire:target="searchDomains"
                            class="col-start-2 inline-flex h-11 min-w-[5.5rem] items-center justify-center whitespace-nowrap rounded-xl px-4 text-sm font-semibold text-white shadow-sm transition hover:opacity-95 disabled:cursor-not-allowed disabled:opacity-60 sm:h-12 sm:min-w-[6.5rem] sm:px-5"
                            style="background-color: #6366f1; border: 1px solid #4f46e5;"
                        >
                            <span wire:loading.remove wire:target="searchDomains">{{ __('Search') }}</span>
                            <span wire:loading wire:target="searchDomains">{{ __('Searching…') }}</span>
                        </button>
                    </div>
                    @if ($searchInputError)
                        <p class="text-sm" style="color: #f87171;">{{ $searchInputError }}</p>
                    @endif
                </div>

                @error('search')
                    <x-ui.alert variant="danger" :description="$message" inline />
                @enderror

                <p
                    wire:loading.delay.none
                    wire:target="searchDomains"
                    class="inline-flex items-center gap-2 text-sm font-medium"
                    style="color: var(--theme-muted-text-color);"
                    aria-live="polite"
                    aria-busy="true"
                >
                    <i class="fa-solid fa-spinner fa-spin text-xs" style="color: var(--theme-accent);"></i>
                    {{ __('Searching domains…') }}
                </p>

                @if ($searchError && ! $searchInputError)
                    <div wire:loading.remove wire:target="searchDomains">
                        <x-ui.alert variant="danger" :description="$searchError" inline />
                    </div>
                @endif

                @if (count($searchResults) > 0)
                    <ul
                        wire:loading.delay.none.remove
                        wire:target="searchDomains"
                        class="divide-y rounded-xl border max-h-[min(55vh,28rem)] overflow-y-auto"
                        style="border-color: var(--theme-border-color);"
                    >
                        @foreach ($searchResults as $loopIndex => $row)
                            <li
                                wire:key="domain-{{ $row['domain'] }}"
                                class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 transition"
                            >
                                <div class="min-w-0 flex min-w-0 flex-wrap items-center gap-2">
                                    <p class="truncate font-semibold" style="color: var(--theme-header-text-color);">
                                        {{ $bareHostFormatter($row['domain']) }}
                                    </p>
                                    @if ($loopIndex === 0)
                                        <span class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase" style="background: rgba(var(--theme-accent-rgb), 0.15); color: var(--theme-accent);">{{ __('Suggested') }}</span>
                                    @endif
                                </div>
                                <x-ui.button
                                    type="button"
                                    size="sm"
                                    wire:click="selectDomain({{ \Illuminate\Support\Js::from($row['domain']) }}, {{ \Illuminate\Support\Js::from($row['checkout_url']) }})"
                                >
                                    {{ __('Select') }}
                                </x-ui.button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            @endif

            @if ($step === 'review' && $selectedDomain)
                <div class="rounded-xl border p-4 sm:p-5" style="border-color: var(--theme-border-color); background: rgba(var(--theme-border-color-rgb), 0.06);">
                    <p class="text-base font-semibold tracking-tight sm:text-lg" style="color: var(--theme-accent);">
                        {{ $bareHostFormatter($selectedDomain) }}
                    </p>
                </div>
                <ul class="space-y-2 text-sm" style="color: var(--theme-muted-text-color);">
                    <li class="flex gap-2"><i class="fa-solid fa-check mt-0.5 text-[11px]" style="color: var(--theme-accent);"></i>{{ __('Registration and payment on :provider’s secure checkout', ['provider' => $providerLabel]) }}</li>
                    <li class="flex gap-2"><i class="fa-solid fa-check mt-0.5 text-[11px]" style="color: var(--theme-accent);"></i>{{ __('DNS connection guided in the final step') }}</li>
                    <li class="flex gap-2"><i class="fa-solid fa-check mt-0.5 text-[11px]" style="color: var(--theme-accent);"></i>{{ __('Your site stays on your platform subdomain until DNS is live') }}</li>
                </ul>
            @endif

            @if ($step === 'register' && $selectedDomain)
                <div class="flex items-start gap-3 rounded-xl border p-4" style="border-color: var(--theme-border-color);">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-lg" style="background: rgba(var(--theme-accent-rgb), 0.12); color: var(--theme-accent);">
                        <i class="fa-solid fa-shield-halved"></i>
                    </span>
                    <div class="min-w-0 space-y-1">
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Secured by :provider', ['provider' => $providerLabel]) }}</p>
                        <p class="text-sm leading-relaxed" style="color: var(--theme-muted-text-color);">
                            {{ __('We open checkout in a new tab. Complete purchase there, then continue with DNS in this window.') }}
                        </p>
                    </div>
                </div>
            @endif

            @if ($step === 'dns' && $selectedDomain)
                <p class="text-sm font-medium" style="color: var(--theme-header-text-color);">
                    {{ __('DNS for :domain', ['domain' => $bareHostFormatter($selectedDomain)]) }}
                </p>
                <p class="text-xs leading-relaxed" style="color: var(--theme-muted-text-color);">
                    {{ __('DNS changes can take 24–48 hours. Use Verify DNS after you save records at :provider.', ['provider' => $providerLabel]) }}
                </p>
                @if ($dnsVerifyMessage)
                    <x-ui.alert
                        :variant="$dnsVerified ? 'success' : 'warning'"
                        :description="$dnsVerifyMessage"
                        inline
                    />
                @endif
                <div class="overflow-x-auto rounded-xl border" style="border-color: var(--theme-border-color);">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs uppercase" style="color: var(--theme-muted-text-color);">
                            <tr>
                                <th class="px-3 py-2">{{ __('Type') }}</th>
                                <th class="px-3 py-2">{{ __('Host') }}</th>
                                <th class="px-3 py-2">{{ __('Points to') }}</th>
                                <th class="px-3 py-2">{{ __('Status') }}</th>
                                <th class="w-10 px-2 py-2"><span class="sr-only">{{ __('Copy') }}</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dnsRecords as $record)
                                @php
                                    $copyValue = is_string($record['points_to'] ?? null) && ! str_contains((string) $record['points_to'], ' ') ? $record['points_to'] : null;
                                    $rowStatus = $dnsStatusFor((string) $record['type'], (string) $record['host']);
                                @endphp
                                <tr wire:key="dns-{{ $record['type'] }}-{{ $record['host'] }}" class="border-t" style="border-color: var(--theme-border-color);">
                                    <td class="px-3 py-2 font-mono text-xs">{{ $record['type'] }}</td>
                                    <td class="px-3 py-2 font-mono text-xs">{{ $record['host'] }}</td>
                                    <td class="px-3 py-2 font-mono text-xs break-all">{{ $record['points_to'] }}</td>
                                    <td class="px-3 py-2 text-xs">
                                        <span @class([
                                            'rounded px-2 py-0.5 font-semibold uppercase tracking-wide',
                                            'bg-emerald-500/15 text-emerald-400' => $rowStatus === 'connected',
                                            'bg-rose-500/15 text-rose-400' => $rowStatus === 'wrong',
                                            'bg-amber-500/15 text-amber-300' => $rowStatus !== 'connected' && $rowStatus !== 'wrong',
                                        ])>
                                            {{ $dnsStatusLabel($rowStatus) }}
                                        </span>
                                    </td>
                                    <td class="px-2 py-2">
                                        @if ($copyValue)
                                            <button
                                                type="button"
                                                class="rounded-lg p-2 text-xs transition hover:opacity-80"
                                                style="color: var(--theme-muted-text-color);"
                                                title="{{ __('Copy value') }}"
                                                x-data
                                                x-on:click="navigator.clipboard?.writeText({{ \Illuminate\Support\Js::from($copyValue) }})"
                                            >
                                                <i class="fa-regular fa-copy"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($platformUrl)
                    <p class="text-xs" style="color: var(--theme-muted-text-color);">
                        {{ __('Until DNS propagates, preview at') }}
                        <a href="{{ $platformUrl }}" target="_blank" rel="noopener noreferrer" class="font-semibold underline">{{ $platformUrl }}</a>
                    </p>
                @endif
                @error('domain')
                    <x-ui.alert variant="danger" :description="$message" inline />
                @enderror
            @endif

            @if ($step === 'done')
                <div class="py-4 text-center">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full text-2xl" style="background: rgba(var(--theme-accent-rgb), 0.15); color: var(--theme-accent);">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    @if ($customUrl)
                        <p class="text-sm" style="color: var(--theme-muted-text-color);">
                            {{ __('Your site will be live at') }}
                        </p>
                        <a href="{{ $customUrl }}" target="_blank" rel="noopener noreferrer" class="mt-1 inline-block text-lg font-semibold underline" style="color: var(--theme-accent);">{{ $customUrl }}</a>
                    @endif
                </div>
            @endif
        </div>

        @if ($step !== 'done' && ! ($step === 'search' && ($resumePendingSelection || count($searchResults) === 0)))
            <div
                class="flex flex-wrap items-center justify-between gap-3 border-t px-4 py-3 sm:px-5 sm:py-4"
                style="border-color: var(--theme-border-color); background: rgba(var(--theme-border-color-rgb), 0.06);"
            >
                <div>
                    @if ($step !== 'search')
                        <x-ui.button type="button" variant="outline" size="sm" wire:click="goBack">
                            <i class="fa-solid fa-arrow-left text-[10px]"></i>
                            {{ __('Back') }}
                        </x-ui.button>
                    @endif
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @if ($step === 'search' && filled(trim($searchQuery ?? '')) && count($searchResults) > 0 && blank($selectedDomain ?? null))
                        <span class="text-xs" style="color: var(--theme-muted-text-color);">{{ __('Select a domain to continue') }}</span>
                    @endif
                    @if ($step === 'review' && $selectedDomain)
                        <x-ui.button type="button" variant="outline" wire:click="goBack">{{ __('Search again') }}</x-ui.button>
                        <x-ui.button
                            type="button"
                            wire:click="goToRegister"
                            wire:loading.attr="disabled"
                            wire:target="goToRegister"
                        >
                            <span wire:loading.remove wire:target="goToRegister">{{ __('Continue') }}</span>
                            <span wire:loading wire:target="goToRegister">{{ __('Checking…') }}</span>
                        </x-ui.button>
                    @endif
                    @if ($step === 'register' && $selectedDomain)
                        <x-ui.button type="button" wire:click="openRegistrarCheckout">
                            <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i>
                            {{ __('Open :provider checkout', ['provider' => $providerLabel]) }}
                        </x-ui.button>
                    @endif
                    @if ($step === 'dns' && $selectedDomain)
                        <x-ui.button
                            type="button"
                            variant="outline"
                            wire:click="verifyDns"
                            wire:loading.attr="disabled"
                            wire:target="verifyDns"
                        >
                            <span wire:loading.remove wire:target="verifyDns">{{ __('Verify DNS') }}</span>
                            <span wire:loading wire:target="verifyDns">{{ __('Verifying…') }}</span>
                        </x-ui.button>
                        @if ($dnsVerified)
                            <x-ui.button
                                type="button"
                                wire:click="connectAndPublish"
                                wire:loading.attr="disabled"
                                wire:target="connectAndPublish"
                            >
                                <span wire:loading.remove wire:target="connectAndPublish">{{ __('Save & publish site') }}</span>
                                <span wire:loading wire:target="connectAndPublish">{{ __('Publishing…') }}</span>
                            </x-ui.button>
                        @else
                            <x-ui.button type="button" disabled>
                                {{ __('Save & publish site') }}
                            </x-ui.button>
                        @endif
                    @endif
                </div>
            </div>
        @elseif (! $embedded)
            <div class="border-t px-5 py-4 text-center sm:px-6" style="border-color: var(--theme-border-color);">
                <x-ui.button href="{{ route('portal.agents.website.dashboard') }}" wire:navigate>{{ __('Back to dashboard') }}</x-ui.button>
            </div>
        @endif
    </x-ui.card>
</div>
