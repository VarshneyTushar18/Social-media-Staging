<div class="space-y-6">
    @include('appwebsitebuilder::partials.website-builder-session-toasts')

    @php
        $projectName = $project?->name ?? __('Project');
    @endphp

    <x-ui.sub-header
        :eyebrow="$projectName.' / '.__('Website Builder')"
        :title="__('Website audit')"
        :description="__('Enter a site URL — including your published builder link — and get feedback on what is correct and what to improve.')"
    />

    <x-ui.card class="space-y-5 p-6 sm:p-8">
        <div class="max-w-xl space-y-4">
            <x-ui.input
                wire:model="auditUrl"
                :label="__('Website URL')"
                placeholder="https://example.com"
                autocomplete="off"
            />
            @error('auditUrl')
                <p class="text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror

            <x-ui.button
                type="button"
                wire:click="runWebsiteAudit"
                wire:loading.attr="disabled"
                wire:target="runWebsiteAudit"
            >
                <span wire:loading.remove wire:target="runWebsiteAudit">
                    <i class="fa-solid fa-wand-magic-sparkles mr-2 text-[11px]"></i>
                    {{ __('Run audit') }}
                </span>
                <span wire:loading wire:target="runWebsiteAudit">{{ __('Analysing…') }}</span>
            </x-ui.button>
        </div>
    </x-ui.card>

    @if (is_array($auditResult))
        <section
            class="space-y-5 rounded-[1.9rem] border p-5 shadow-[0_30px_80px_-50px_rgba(15,23,42,0.28)] sm:p-6"
            style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-base) 96%, transparent);"
        >
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-wide" style="color: var(--theme-muted-text-color);">{{ __('Results for') }}</p>
                    <p class="text-base font-semibold break-all" style="color: var(--theme-header-text-color);">{{ $auditResult['target_label'] ?? '' }}</p>
                    @if (! empty($auditResult['used_ai']))
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('AI review') }}</p>
                    @else
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Checklist review (AI unavailable)') }}</p>
                    @endif
                </div>
                <div class="text-right">
                    <p class="text-3xl font-bold tabular-nums" style="color: var(--theme-accent);">{{ (int) ($auditResult['score'] ?? 0) }}</p>
                    <p class="text-xs font-semibold uppercase tracking-wide" style="color: var(--theme-muted-text-color);">{{ __('Score') }}</p>
                </div>
            </div>

            @if (filled($auditResult['summary'] ?? null))
                <p class="text-sm leading-relaxed" style="color: var(--theme-muted-text-color);">{{ $auditResult['summary'] }}</p>
            @endif

            @if (count($auditResult['strengths'] ?? []) > 0)
                <div>
                    <h3 class="mb-2 text-sm font-semibold" style="color: #059669;">{{ __('What is correct') }}</h3>
                    <ul class="space-y-1.5 text-sm" style="color: var(--theme-header-text-color);">
                        @foreach ($auditResult['strengths'] as $line)
                            <li class="flex gap-2">
                                <i class="fa-solid fa-circle-check mt-0.5 shrink-0 text-emerald-600"></i>
                                <span>{{ $line }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (count($auditResult['fixes'] ?? []) > 0)
                <div>
                    <h3 class="mb-2 text-sm font-semibold" style="color: #d97706;">{{ __('What to correct') }}</h3>
                    <ul class="space-y-3">
                        @foreach ($auditResult['fixes'] as $fix)
                            <li
                                class="rounded-lg border px-3 py-2.5 text-sm"
                                style="border-color: rgba(217, 119, 6, 0.35); background: rgba(217, 119, 6, 0.06);"
                            >
                                <p class="text-[10px] font-bold uppercase tracking-wide" style="color: #b45309;">{{ $fix['area'] ?? __('General') }}</p>
                                <p class="mt-1 font-semibold" style="color: var(--theme-header-text-color);">{{ $fix['issue'] ?? '' }}</p>
                                @if (filled($fix['recommendation'] ?? null))
                                    <p class="mt-1" style="color: var(--theme-muted-text-color);">{{ $fix['recommendation'] }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </section>
    @endif
</div>
