<div class="space-y-6">
    @php
        $projectName = $project?->name ?? __('Project');
        $agentColor = $agent['color'] ?? '#0d9488';
    @endphp

    <x-ui.sub-header
        :eyebrow="$projectName.' / '.__('SEO')"
        :title="__('SEO dashboard')"
        :description="__('Audits, tracked keywords, and content briefs for this project.')"
    >
        <x-slot:actions>
            <x-ui.button type="button" wire:click="openNewAudit">
                <i class="fa-solid fa-plus mr-2 text-[11px]"></i>
                {{ __('Start site audit') }}
            </x-ui.button>
        </x-slot:actions>
    </x-ui.sub-header>

    @if (session('success'))
        <x-ui.alert variant="success" :description="session('success')" inline />
    @endif

    <div class="grid gap-4 sm:grid-cols-3">
        @foreach ([
            ['label' => __('Audits'), 'value' => $metrics['audits'], 'hint' => __('Completed scans')],
            ['label' => __('Keywords tracked'), 'value' => $metrics['keywords'], 'hint' => __('Rank monitoring')],
            ['label' => __('Content briefs'), 'value' => $metrics['briefs'], 'hint' => __('Ready to write')],
        ] as $card)
            <x-ui.card class="space-y-2 p-5">
                <p class="text-xs font-semibold uppercase tracking-wide" style="color: var(--theme-muted-text-color);">{{ $card['label'] }}</p>
                <p class="text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ $card['value'] }}</p>
                <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ $card['hint'] }}</p>
            </x-ui.card>
        @endforeach
    </div>

    <x-ui.card class="p-6 sm:p-8">
        <div class="flex flex-col items-center gap-4 py-6 text-center sm:py-10">
            <span
                class="inline-flex h-14 w-14 items-center justify-center rounded-2xl text-lg font-bold text-white"
                style="background: {{ $agentColor }};"
            >SE</span>
            <div class="max-w-md space-y-2">
                <p class="text-lg font-semibold" style="color: var(--theme-header-text-color);">
                    {{ __('No SEO work started yet') }}
                </p>
                <p class="text-sm leading-relaxed" style="color: var(--theme-muted-text-color);">
                    {{ __('Run your first audit or add keywords from the Workspace. Metrics stay at zero until you create them here.') }}
                </p>
            </div>
            <x-ui.button type="button" wire:click="openNewAudit">
                <i class="fa-solid fa-plus mr-2 text-[11px]"></i>
                {{ __('Start site audit') }}
            </x-ui.button>
        </div>
    </x-ui.card>

    @if ($showNewAuditModal)
        <div
            class="fixed inset-0 z-[80] flex items-center justify-center p-4"
            style="background: rgba(15, 23, 42, 0.55);"
            wire:keydown.escape.window="closeNewAudit"
        >
            <div
                class="w-full max-w-md rounded-2xl border p-6 shadow-xl"
                style="border-color: rgba(var(--theme-border-color-rgb), 0.65); background: var(--theme-surface-overlay);"
                wire:click.outside="closeNewAudit"
            >
                <h2 class="text-lg font-semibold" style="color: var(--theme-header-text-color);">
                    {{ __('Start site audit') }}
                </h2>
                <p class="mt-2 text-sm" style="color: var(--theme-muted-text-color);">
                    {{ __('Name the property you want to audit (site URL or label).') }}
                </p>

                <form wire:submit="startAudit" class="mt-5 space-y-4">
                    <x-ui.input
                        wire:model="auditSiteName"
                        :label="__('Site or property name')"
                        :placeholder="__('e.g. Main marketing site')"
                        autofocus
                    />
                    @error('auditSiteName')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <div class="flex justify-end gap-2 pt-2">
                        <x-ui.button type="button" variant="outline" wire:click="closeNewAudit">
                            {{ __('Cancel') }}
                        </x-ui.button>
                        <x-ui.button type="submit">
                            {{ __('Start audit') }}
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
