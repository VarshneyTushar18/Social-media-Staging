@php
    $projectName = $project?->name ?? __('Project');
    $hasPackages = Route::has('portal.packages');
    $hasBilling = Route::has('portal.billing');
@endphp

<div class="space-y-6">
    <x-ui.sub-header
        :eyebrow="$projectName.' / '.__('Main dashboard')"
        :title="__('Main dashboard overview')"
        :description="__('Your plan, agents for this project, and billing shortcuts. Open an agent dashboard for day-to-day work.')"
    >
        <x-slot:actions>
            @if ($hasPackages)
                <x-ui.button href="{{ route('portal.packages') }}" variant="outline" wire:navigate>
                    {{ __('Upgrade plan') }}
                </x-ui.button>
            @endif
        </x-slot:actions>
    </x-ui.sub-header>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.card class="space-y-2 p-5">
            <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Current plan') }}</p>
            <p class="text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ $metrics['plan_name'] }}</p>
            @if ($metrics['plan_price'])
                <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ $metrics['plan_price'] }}</p>
            @endif
        </x-ui.card>
        <x-ui.card class="space-y-2 p-5">
            <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Active agents') }}</p>
            <p class="text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ $metrics['agents_label'] }}</p>
            <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ $metrics['agents_hint'] }}</p>
        </x-ui.card>
        <x-ui.card class="space-y-2 p-5">
            <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Plan expires') }}</p>
            <p class="text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ $metrics['expiry'] }}</p>
            <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('Access window') }}</p>
        </x-ui.card>
        <x-ui.card class="space-y-2 p-5">
            <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Projects') }}</p>
            <p class="text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ $metrics['projects_count'] }}</p>
            <p class="text-sm truncate" style="color: var(--theme-muted-text-color);">{{ $metrics['projects_hint'] }}</p>
        </x-ui.card>
    </div>

    <section id="main-dashboard-agents" class="space-y-4">
        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Agents') }}</p>
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($agentCards as $agent)
                <x-ui.card class="flex flex-col gap-4 p-5">
                    <div class="flex items-start justify-between gap-3">
                        <span
                            class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-sm font-bold text-white"
                            style="background: {{ $agent['color'] }};"
                        >
                            {{ $agent['short'] }}
                        </span>
                        <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $agent['is_on'] ? 'bg-emerald-400/12 text-emerald-500' : 'bg-slate-400/10 text-slate-500' }}">
                            {{ $agent['is_on'] ? __('Active') : __('Off') }}
                        </span>
                    </div>
                    <div>
                        <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $agent['label'] }}</p>
                        <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $agent['description'] }}</p>
                    </div>
                    @if ($agent['is_on'] && $agent['open_url'])
                        <x-ui.button :href="$agent['open_url']" class="mt-auto w-full justify-center" wire:navigate>
                            {{ __('Open dashboard') }}
                        </x-ui.button>
                    @elseif ($agent['allowed_by_plan'] && $hasPackages)
                        <x-ui.button :href="route('portal.packages')" variant="outline" class="mt-auto w-full justify-center" wire:navigate>
                            {{ __('Activate agent') }}
                        </x-ui.button>
                    @else
                        <x-ui.button :href="$hasPackages ? route('portal.packages') : '#'" variant="outline" class="mt-auto w-full justify-center" wire:navigate>
                            {{ __('Upgrade to unlock') }}
                        </x-ui.button>
                    @endif
                </x-ui.card>
            @endforeach
        </div>
    </section>

    <div class="grid gap-5 xl:grid-cols-2">
        <x-ui.card class="space-y-4 p-5 sm:p-6">
            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Plans & pricing') }}</p>
            <p class="text-sm leading-6" style="color: var(--theme-muted-text-color);">
                {{ __('Your subscription is :plan. Compare tiers, limits, and checkout options.', ['plan' => $metrics['plan_name']]) }}
            </p>
            @if ($hasPackages)
                <x-ui.button href="{{ route('portal.packages') }}" variant="outline" wire:navigate>
                    {{ __('View plans') }}
                </x-ui.button>
            @endif
        </x-ui.card>

        <x-ui.card class="space-y-4 p-5 sm:p-6">
            <div class="flex items-center justify-between gap-3">
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Recent invoices') }}</p>
                @if ($hasBilling)
                    <x-ui.button href="{{ route('portal.billing') }}" variant="outline" size="sm" wire:navigate>{{ __('Billing') }}</x-ui.button>
                @endif
            </div>
            @if ($recentInvoices->isEmpty())
                <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('No payments recorded yet.') }}</p>
            @else
                <ul class="divide-y text-sm" style="border-color: var(--theme-border-color);">
                    @foreach ($recentInvoices as $invoice)
                        <li class="flex items-center justify-between gap-3 py-2.5">
                            <span style="color: var(--theme-header-text-color);">#{{ $invoice->id }}</span>
                            <span style="color: var(--theme-muted-text-color);">{{ $invoice->created?->format('M j, Y') }}</span>
                            <span class="font-semibold" style="color: var(--theme-header-text-color);">
                                {{ number_format((float) $invoice->amount, 2) }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>
    </div>

</div>
