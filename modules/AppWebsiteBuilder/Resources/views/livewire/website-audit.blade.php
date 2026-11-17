<div class="space-y-6">
    @include('appwebsitebuilder::partials.website-builder-session-toasts')

    @php
        $projectName = $project?->name ?? __('Project');
    @endphp

    <x-ui.sub-header
        :eyebrow="$projectName.' / '.__('Website Builder')"
        :title="__('Website audit')"
        :description="__('Enter a site URL and get feedback on what is correct and what to improve.')"
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
        @php
            $auditScore = max(0, min(100, (int) ($auditResult['score'] ?? 0)));
            $auditFixes = is_array($auditResult['fixes'] ?? null) ? $auditResult['fixes'] : [];
            $auditStrengths = is_array($auditResult['strengths'] ?? null) ? $auditResult['strengths'] : [];
            $failedCount = count($auditFixes);
            $passedCount = count($auditStrengths);
            $warningsCount = 0;
            $statTotal = max(1, $failedCount + $warningsCount + $passedCount);
            $ringRadius = 54;
            $ringCircumference = round(2 * pi() * $ringRadius, 2);
            $ringOffset = round($ringCircumference * (1 - $auditScore / 100), 2);
            $ringColor = $auditScore >= 75 ? '#22c55e' : ($auditScore >= 50 ? '#f59e0b' : '#ef4444');
            $severityLabel = static function (?string $area): string {
                $needle = strtolower((string) $area);
                if ($needle === '' || str_contains($needle, 'general')) {
                    return __('MEDIUM');
                }
                if (str_contains($needle, 'publish') || str_contains($needle, 'trust') || str_contains($needle, 'seo')) {
                    return __('HIGH');
                }

                return __('MEDIUM');
            };
            $severityClasses = static function (?string $area): string {
                $needle = strtolower((string) $area);
                $isHigh = $needle === '' || str_contains($needle, 'general')
                    ? false
                    : (str_contains($needle, 'publish') || str_contains($needle, 'trust') || str_contains($needle, 'seo'));

                return $isHigh
                    ? 'bg-rose-100 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300'
                    : 'bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-200';
            };
        @endphp

        <section
            id="website-audit-report"
            class="overflow-hidden rounded-[1.9rem] border shadow-[0_30px_80px_-50px_rgba(15,23,42,0.28)]"
            style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-base) 96%, transparent);"
        >
            <div
                class="flex flex-col gap-3 border-b px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6"
                style="border-color: rgba(var(--theme-border-color-rgb), 0.45);"
            >
                <div class="min-w-0">
                    <p class="text-xs font-medium" style="color: var(--theme-muted-text-color);">
                        {{ ! empty($auditResult['used_ai']) ? __('AI review') : __('Checklist review') }}
                        · {{ __('Generated') }} {{ $auditReportGeneratedAt ?? now()->format('M d, Y H:i') }}
                    </p>
                    <h2 class="mt-1 break-all text-lg font-semibold tracking-tight sm:text-xl" style="color: var(--theme-header-text-color);">
                        {{ $auditResult['target_label'] ?? '' }}
                    </h2>
                    <p class="mt-0.5 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Your site quality score') }}</p>
                </div>
                <div class="flex shrink-0 items-center gap-2 self-start sm:self-center">
                    @if ($auditRunId)
                        <x-ui.button
                            :href="route('portal.agents.website.audit.download', $auditRunId)"
                            variant="outline"
                            size="sm"
                        >
                            <i class="fa-solid fa-download mr-2 text-[11px]"></i>
                            {{ __('Download PDF') }}
                        </x-ui.button>
                    @endif
                    <button
                        type="button"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-[0.85rem] border transition hover:bg-[color-mix(in_srgb,var(--theme-surface-soft)_100%,transparent)]"
                        style="border-color: rgba(var(--theme-border-color-rgb), 0.65); color: var(--theme-muted-text-color);"
                        wire:click="closeAuditReport"
                        title="{{ __('Close report') }}"
                        aria-label="{{ __('Close report') }}"
                    >
                        <i class="fa-solid fa-xmark text-base"></i>
                    </button>
                </div>
            </div>

            <div class="grid gap-6 border-b p-5 sm:p-6 lg:grid-cols-[minmax(200px,240px)_1fr] lg:gap-8" style="border-color: rgba(var(--theme-border-color-rgb), 0.45);">
                <div class="flex flex-col items-center justify-center text-center">
                    <div class="relative h-[140px] w-[140px]">
                        <svg class="h-full w-full -rotate-90" viewBox="0 0 120 120" aria-hidden="true">
                            <circle
                                cx="60"
                                cy="60"
                                r="{{ $ringRadius }}"
                                fill="none"
                                stroke="rgba(var(--theme-border-color-rgb), 0.35)"
                                stroke-width="10"
                            />
                            <circle
                                cx="60"
                                cy="60"
                                r="{{ $ringRadius }}"
                                fill="none"
                                stroke="{{ $ringColor }}"
                                stroke-width="10"
                                stroke-linecap="round"
                                stroke-dasharray="{{ $ringCircumference }}"
                                stroke-dashoffset="{{ $ringOffset }}"
                            />
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-3xl font-bold tabular-nums leading-none" style="color: var(--theme-header-text-color);">{{ $auditScore }}</span>
                            <span class="mt-1 text-[10px] font-bold uppercase tracking-wider" style="color: var(--theme-muted-text-color);">/ 100</span>
                        </div>
                    </div>
                    <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide" style="color: var(--theme-muted-text-color);">{{ __('Site score') }}</p>
                </div>

                <div class="flex min-w-0 flex-col justify-center gap-3">
                    @if (filled($auditResult['summary'] ?? null))
                        <p class="line-clamp-3 text-sm leading-snug" style="color: var(--theme-header-text-color);">{{ $auditResult['summary'] }}</p>
                    @endif

                    <div class="max-w-md space-y-1.5" role="list">
                        @if ($failedCount > 0)
                            <div class="flex items-center gap-2">
                                <span class="w-[4.25rem] shrink-0 text-xs font-semibold tabular-nums text-rose-600 dark:text-rose-400">
                                    {{ $failedCount }} {{ __('Failed') }}
                                </span>
                                <div class="h-1.5 min-w-0 flex-1 overflow-hidden rounded-full" style="background: rgba(var(--theme-border-color-rgb), 0.25);">
                                    <div
                                        class="h-full rounded-full bg-rose-500"
                                        style="width: {{ max(4, round(($failedCount / $statTotal) * 100)) }}%;"
                                    ></div>
                                </div>
                            </div>
                        @endif
                        @if ($warningsCount > 0)
                            <div class="flex items-center gap-2">
                                <span class="w-[4.25rem] shrink-0 text-xs font-semibold tabular-nums text-amber-600 dark:text-amber-400">
                                    {{ $warningsCount }} {{ __('Warn') }}
                                </span>
                                <div class="h-1.5 min-w-0 flex-1 overflow-hidden rounded-full" style="background: rgba(var(--theme-border-color-rgb), 0.25);">
                                    <div
                                        class="h-full rounded-full bg-amber-400"
                                        style="width: {{ max(4, round(($warningsCount / $statTotal) * 100)) }}%;"
                                    ></div>
                                </div>
                            </div>
                        @endif
                        @if ($passedCount > 0)
                            <div class="flex items-center gap-2">
                                <span class="w-[4.25rem] shrink-0 text-xs font-semibold tabular-nums text-emerald-600 dark:text-emerald-400">
                                    {{ $passedCount }} {{ __('Pass') }}
                                </span>
                                <div class="h-1.5 min-w-0 flex-1 overflow-hidden rounded-full" style="background: rgba(var(--theme-border-color-rgb), 0.25);">
                                    <div
                                        class="h-full rounded-full bg-emerald-500"
                                        style="width: {{ max(4, round(($passedCount / $statTotal) * 100)) }}%;"
                                    ></div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if ($passedCount > 0)
                <details class="group border-b px-5 py-3 sm:px-6" style="border-color: rgba(var(--theme-border-color-rgb), 0.45);">
                    <summary class="cursor-pointer list-none text-sm font-semibold text-emerald-700 marker:content-none dark:text-emerald-400">
                        <span class="inline-flex items-center gap-2">
                            <i class="fa-solid fa-chevron-right text-[10px] transition group-open:rotate-90"></i>
                            {{ __('Passed checks') }} ({{ $passedCount }})
                        </span>
                    </summary>
                    <ul class="mb-2 mt-3 space-y-1.5 text-sm" style="color: var(--theme-header-text-color);">
                        @foreach ($auditStrengths as $line)
                            <li class="flex gap-2">
                                <i class="fa-solid fa-circle-check mt-0.5 shrink-0 text-emerald-600"></i>
                                <span>{{ $line }}</span>
                            </li>
                        @endforeach
                    </ul>
                </details>
            @endif

            @if ($failedCount > 0)
                <div class="p-5 sm:p-6">
                    <h3 class="text-base font-semibold" style="color: var(--theme-header-text-color);">{{ __('Issues to fix') }}</h3>
                    <ul
                        class="mt-4 max-h-[28rem] space-y-0 divide-y overflow-y-auto rounded-xl border"
                        style="border-color: rgba(var(--theme-border-color-rgb), 0.5);"
                    >
                        @foreach ($auditFixes as $fix)
                            @php
                                $area = (string) ($fix['area'] ?? __('General'));
                                $hasDetails = filled($fix['recommendation'] ?? null);
                            @endphp
                            <li
                                class="px-4 py-3.5"
                                style="background: color-mix(in srgb, var(--theme-surface-base) 98%, transparent);"
                            >
                                <div class="flex flex-wrap items-start gap-2 gap-y-1">
                                    <span class="inline-flex shrink-0 rounded px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide {{ $severityClasses($area) }}">
                                        {{ $severityLabel($area) }}
                                    </span>
                                    <span class="text-[10px] font-bold uppercase tracking-wide" style="color: var(--theme-muted-text-color);">{{ $area }}</span>
                                </div>
                                <p class="mt-2 text-sm font-semibold leading-snug" style="color: var(--theme-header-text-color);">
                                    {{ $fix['issue'] ?? '' }}
                                </p>
                                @if ($hasDetails)
                                    <p class="mt-2 text-sm leading-relaxed" style="color: var(--theme-muted-text-color);">
                                        {{ $fix['recommendation'] }}
                                    </p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </section>
    @endif

    <x-ui.card class="space-y-4 p-6 sm:p-8">
        <h2 class="text-base font-semibold" style="color: var(--theme-header-text-color);">{{ __('Audit history') }}</h2>
        @if (count($auditHistory ?? []) === 0)
            <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('Past audits for this project will appear here after you run an audit.') }}</p>
        @else
            <ul
                class="divide-y"
                style="border-color: rgba(var(--theme-border-color-rgb), 0.4);"
                x-data="{ openMenuId: null }"
                x-on:keydown.escape.window="openMenuId = null"
                x-on:click.outside="openMenuId = null"
            >
                @foreach ($auditHistory as $entry)
                    @php
                        $historyRunId = (int) $entry['run_id'];
                    @endphp
                    <li
                        wire:key="audit-history-{{ $historyRunId }}"
                        class="flex flex-col gap-3 py-4 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="min-w-0 flex-1">
                            <p class="break-all text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $entry['target_label'] }}</p>
                            <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">
                                {{ ! empty($entry['used_ai']) ? __('AI review') : __('Checklist review') }}
                                @if (! empty($entry['created_at']))
                                    · {{ $entry['created_at'] }}
                                @endif
                            </p>
                        </div>
                        <div class="flex shrink-0 items-center gap-4">
                            <div class="text-right">
                                <p class="text-xl font-bold tabular-nums" style="color: var(--theme-accent);">{{ (int) ($entry['score'] ?? 0) }}</p>
                                <p class="text-[10px] font-semibold uppercase tracking-wide" style="color: var(--theme-muted-text-color);">{{ __('Score') }}</p>
                            </div>
                            <div
                                wire:key="audit-history-actions-{{ $historyRunId }}"
                                class="relative inline-block text-left"
                                x-bind:class="openMenuId === {{ $historyRunId }} ? 'z-[60]' : 'z-0'"
                            >
                                <x-ui.button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    x-on:click.stop="openMenuId = openMenuId === {{ $historyRunId }} ? null : {{ $historyRunId }}"
                                >
                                    {{ __('Actions') }}
                                    <i class="fa-solid fa-chevron-down text-[10px]"></i>
                                </x-ui.button>

                                <div
                                    x-cloak
                                    x-show="openMenuId === {{ $historyRunId }}"
                                    x-transition.origin.top.right
                                    x-on:click.stop
                                    class="absolute right-0 z-50 mt-2 w-max min-w-[11rem] overflow-hidden rounded-[1.1rem] border p-1.5 shadow-[0_18px_38px_-18px_rgba(15,23,42,0.22)]"
                                    style="border-color: color-mix(in srgb, var(--theme-border-color) 78%, transparent 22%); background-color: var(--theme-surface-base);"
                                >
                                    <x-ui.dropdown-menu-item
                                        icon="fa-solid fa-eye"
                                        wire:click="loadAuditRun({{ $historyRunId }})"
                                        x-on:click="openMenuId = null"
                                        :close="false"
                                    >
                                        {{ __('View report') }}
                                    </x-ui.dropdown-menu-item>
                                    <x-ui.dropdown-menu-item
                                        icon="fa-solid fa-download"
                                        :href="route('portal.agents.website.audit.download', $historyRunId)"
                                        x-on:click="openMenuId = null"
                                        :close="false"
                                    >
                                        {{ __('Download PDF') }}
                                    </x-ui.dropdown-menu-item>
                                    <x-ui.dropdown-menu-divider />
                                    <x-ui.dropdown-menu-item
                                        icon="fa-solid fa-trash-can"
                                        variant="danger"
                                        wire:click="openDeleteAuditModal({{ $historyRunId }})"
                                        x-on:click="openMenuId = null"
                                        :close="false"
                                    >
                                        {{ __('Delete') }}
                                    </x-ui.dropdown-menu-item>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-ui.card>

    @if ($deleteAuditModalOpen)
        <div
            class="fixed inset-0 z-[120] flex items-end justify-center overflow-y-auto p-4 sm:items-center sm:p-6"
            wire:keydown.escape.window="closeDeleteAuditModal"
        >
            <div
                class="absolute inset-0 bg-white/55 backdrop-blur-[6px] dark:bg-slate-950/55"
                wire:click="closeDeleteAuditModal"
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
                                {{ __('Delete audit report?') }}
                            </h3>
                            <p class="mt-2 text-[15px] leading-7" style="color: var(--theme-muted-text-color);">
                                {{ __('This will permanently remove the audit for') }}
                                <span class="break-all font-semibold" style="color: var(--theme-header-text-color);">{{ $deleteAuditLabel }}</span>.
                                {{ __('This cannot be undone.') }}
                            </p>
                        </div>

                        <button type="button" style="color: var(--theme-muted-text-color);" wire:click="closeDeleteAuditModal">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>

                    <div
                        class="border-t px-5 py-4 sm:px-6"
                        style="border-color: color-mix(in srgb, var(--theme-border-color) 52%, transparent); background-color: color-mix(in srgb, var(--theme-surface-base) 72%, transparent);"
                    >
                        <div class="flex items-center justify-end gap-3">
                            <x-ui.button type="button" variant="outline" wire:click="closeDeleteAuditModal">
                                <i class="fa-solid fa-xmark"></i>
                                {{ __('Cancel') }}
                            </x-ui.button>
                            <x-ui.button
                                type="button"
                                variant="danger"
                                wire:click="deleteAuditRun"
                                wire:loading.attr="disabled"
                                wire:target="deleteAuditRun"
                            >
                                <span wire:loading.remove wire:target="deleteAuditRun">
                                    <i class="fa-solid fa-trash-can"></i>
                                    {{ __('Delete report') }}
                                </span>
                                <span wire:loading wire:target="deleteAuditRun">{{ __('Deleting…') }}</span>
                            </x-ui.button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
