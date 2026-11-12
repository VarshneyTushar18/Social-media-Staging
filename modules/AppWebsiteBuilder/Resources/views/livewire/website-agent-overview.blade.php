<div class="space-y-6">
    @include('appwebsitebuilder::partials.website-builder-session-toasts')

    @php
        $projectName = $project?->name ?? __('Project');
        $agentLabel = (string) ($agent['label'] ?? __('Website Builder'));
        $agentDescription = (string) ($agent['description'] ?? '');
    @endphp

    <x-ui.sub-header
        :eyebrow="$projectName.' / '.$agentLabel"
        :title="__('Website Builder overview')"
        :description="__('Create pages, publish sites, and manage your web presence for this project.')"
    >
        <x-slot:actions>
            <span
                class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold"
                style="background: rgba(124, 58, 237, 0.12); color: #7c3aed;"
            >
                <span class="h-2 w-2 rounded-full bg-current"></span>
                {{ __('Agent ready') }}
            </span>
        </x-slot:actions>
    </x-ui.sub-header>

    <x-ui.card class="p-6 sm:p-8">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-start">
            <span
                class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl text-lg font-bold text-white shadow-sm"
                style="background: {{ $agent['color'] ?? '#7c3aed' }};"
            >
                {{ $agent['short'] ?? 'WB' }}
            </span>
            <div class="min-w-0 space-y-3">
                <p class="text-[11px] font-semibold uppercase tracking-[0.22em]" style="color: var(--theme-muted-text-color);">
                    {{ __('What this agent does') }}
                </p>
                <p class="text-lg font-semibold tracking-tight" style="color: var(--theme-header-text-color);">
                    {{ $agentDescription }}
                </p>
                <p class="text-sm leading-relaxed" style="color: var(--theme-muted-text-color);">
                    {{ __('Use Overview for context, then open Workspace → Dashboard to create a site from a template, or Domains for custom URLs.') }}
                </p>
            </div>
        </div>
    </x-ui.card>

    <div class="grid gap-4 sm:grid-cols-3">
        @foreach ([
            ['label' => __('Sites'), 'value' => $metrics['sites'], 'hint' => __('In this project')],
            ['label' => __('Published'), 'value' => $metrics['published'], 'hint' => __('Live on the web')],
            ['label' => __('Drafts'), 'value' => $metrics['drafts'], 'hint' => __('Not published yet')],
        ] as $card)
            <x-ui.card class="space-y-2 p-5">
                <p class="text-xs font-semibold uppercase tracking-wide" style="color: var(--theme-muted-text-color);">{{ $card['label'] }}</p>
                <p class="text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ $card['value'] }}</p>
                <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ $card['hint'] }}</p>
            </x-ui.card>
        @endforeach
    </div>
</div>
