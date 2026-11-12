<div class="space-y-6">
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

    <x-ui.card class="p-6 sm:p-8">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-start">
            <span
                class="inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl text-lg text-white"
                style="background: #7c3aed;"
            >
                <i class="fa-solid fa-link"></i>
            </span>
            <div class="min-w-0 space-y-3">
                <p class="text-lg font-semibold tracking-tight" style="color: var(--theme-header-text-color);">
                    {{ __('Domain management is on the way') }}
                </p>
                <p class="text-sm leading-relaxed" style="color: var(--theme-muted-text-color);">
                    {{ __('We are building DNS verification, SSL, and custom domain mapping. Once live, you will attach domains to each site from this page.') }}
                </p>
                <ul class="list-disc space-y-1 pl-5 text-sm" style="color: var(--theme-muted-text-color);">
                    <li>{{ __('Buy or connect an existing domain') }}</li>
                    <li>{{ __('Automatic SSL certificates') }}</li>
                    <li>{{ __('Subdomains for staging and production') }}</li>
                </ul>
            </div>
        </div>
    </x-ui.card>

    <div class="grid gap-4 sm:grid-cols-3">
        @foreach ([
            ['label' => __('Connected'), 'value' => '—'],
            ['label' => __('Pending DNS'), 'value' => '—'],
            ['label' => __('SSL status'), 'value' => '—'],
        ] as $card)
            <x-ui.card class="space-y-2 p-5 opacity-80">
                <p class="text-xs font-semibold uppercase tracking-wide" style="color: var(--theme-muted-text-color);">{{ $card['label'] }}</p>
                <p class="text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ $card['value'] }}</p>
                <p class="text-xs" style="color: var(--theme-muted-text-color);">{{ __('Coming soon') }}</p>
            </x-ui.card>
        @endforeach
    </div>
</div>
