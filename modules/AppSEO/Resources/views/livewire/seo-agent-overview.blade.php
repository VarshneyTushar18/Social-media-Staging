<div class="space-y-6">
    @php
        $projectName = $project?->name ?? __('Project');
        $agentLabel = (string) ($agent['label'] ?? __('SEO'));
        $agentDescription = (string) ($agent['description'] ?? '');
        $agentColor = (string) ($agent['color'] ?? '#0d9488');
        $agentShort = (string) ($agent['short'] ?? 'SE');
    @endphp

    <x-ui.sub-header
        :eyebrow="$projectName.' / '.$agentLabel"
        :title="$agentLabel.' '.__('overview')"
        :description="$agentDescription !== '' ? $agentDescription : __('Your dedicated workspace for this agent and project.')"
    >
        <x-slot:actions>
            <span
                class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold"
                style="background: rgba(13, 148, 136, 0.12); color: #0d9488;"
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
                style="background: {{ $agentColor }};"
            >
                {{ $agentShort }}
            </span>
            <div class="min-w-0 space-y-3">
                <p class="text-[11px] font-semibold uppercase tracking-[0.22em]" style="color: var(--theme-muted-text-color);">
                    {{ __('What this agent does') }}
                </p>
                <p class="text-lg font-semibold tracking-tight" style="color: var(--theme-header-text-color);">
                    {{ $agentDescription }}
                </p>
                <p class="text-sm leading-relaxed" style="color: var(--theme-muted-text-color);">
                    {{ __('Open Workspace → Dashboard to run audits, or Keywords for rank tracking when it is ready.') }}
                </p>
            </div>
        </div>
    </x-ui.card>
</div>
