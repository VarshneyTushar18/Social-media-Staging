@php
    $userName = trim((string) ($user?->name ?? $user?->username ?? $user?->email ?? __('User')));
    $agentLabel = (string) ($agent['label'] ?? __('Agent'));
    $projectName = $project?->name ?? __('your project');
@endphp

<div class="space-y-6">
    <x-ui.sub-header
        :eyebrow="$projectName.' / '.$agentLabel"
        :title="__('Hello, :name', ['name' => $userName])"
        :description="__('Welcome to your :agent workspace for this project.', ['agent' => $agentLabel])"
    >
        <x-slot:actions>
            <span
                class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold"
                style="background: rgba(var(--theme-accent-rgb), 0.12); color: var(--theme-accent);"
            >
                <span class="h-2 w-2 rounded-full bg-current"></span>
                {{ $agentLabel }}
            </span>
        </x-slot:actions>
    </x-ui.sub-header>

    <x-ui.card class="p-6 sm:p-8">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-center">
            <span
                class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl text-lg font-bold text-white shadow-sm"
                style="background: {{ $agent['color'] ?? 'var(--theme-accent)' }};"
            >
                {{ $agent['short'] ?? '?' }}
            </span>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-[0.22em]" style="color: var(--theme-muted-text-color);">
                    {{ __('Signed in as') }}
                </p>
                <p class="mt-1 text-2xl font-semibold tracking-tight" style="color: var(--theme-header-text-color);">
                    {{ $userName }}
                </p>
                <p class="mt-2 text-sm" style="color: var(--theme-muted-text-color);">
                    {{ __(':agent · :project', ['agent' => $agentLabel, 'project' => $projectName]) }}
                </p>
            </div>
        </div>
    </x-ui.card>
</div>
