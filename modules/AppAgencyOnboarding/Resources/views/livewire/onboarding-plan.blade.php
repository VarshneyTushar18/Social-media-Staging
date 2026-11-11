<div class="space-y-6">
    @include('appagencyonboarding::partials.stepper', ['current' => $currentStep])

    <section
        class="overflow-hidden rounded-[1.75rem] border"
        style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
            radial-gradient(circle at 0% 0%, rgba(var(--theme-accent-rgb), 0.18), transparent 34%),
            radial-gradient(circle at 86% 18%, rgba(var(--theme-success-color-rgb), 0.10), transparent 32%),
            linear-gradient(135deg, rgba(var(--theme-surface-base-rgb,255,255,255),0.98), rgba(var(--theme-surface-base-rgb,255,255,255),0.94));
        "
    >
        <div class="px-5 py-6 sm:px-8">
            <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-accent);">{{ __('Onboarding') }}</p>
            <h1 class="mt-2 text-[1.8rem] font-semibold tracking-[-0.05em] sm:text-[2rem]" style="color: var(--theme-header-text-color);">{{ __('Choose your plan') }}</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7" style="color: var(--theme-muted-text-color);">
                @if (config('modules.appagencyonboarding.skip_payment_during_onboarding', true))
                    {{ __('Pick a package to continue setup. You can pay or upgrade later from billing.') }}
                @else
                    {{ __('Pick a Stackposts package for your agency. You can change or upgrade later from billing.') }}
                @endif
            </p>
        </div>
    </section>

    @error('plan')
        <x-ui.card class="border px-5 py-4" style="border-color: rgba(var(--theme-danger-color-rgb, 239, 68, 68), 0.35);">
            <p class="text-sm font-medium" style="color: var(--theme-danger-color);">{{ $message }}</p>
        </x-ui.card>
    @enderror

    @if (! $hasPlans)
        <x-ui.card class="px-6 py-4 text-center text-sm" style="color: var(--theme-warning-color);">
            {{ __('No active plans are configured yet. Ask an admin to enable plans in the admin area, then refresh this page.') }}
        </x-ui.card>
    @else
        <x-ui.card class="overflow-visible p-0">
            <div x-data="{ type: {{ $defaultType }} }">
                @if ($planTypes->count() > 1)
                    <div class="flex justify-center border-b px-6 py-5 sm:px-8" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                        <div class="inline-flex items-center gap-1 rounded-full border p-1.5 shadow-[inset_0_1px_0_rgba(255,255,255,0.08)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.72);">
                            @foreach ($planTypes as $typeKey => $typeLabel)
                                <button
                                    type="button"
                                    x-on:click="type = {{ $typeKey }}"
                                    x-bind:class="type === {{ $typeKey }} ? 'text-white shadow-[0_12px_28px_-18px_rgba(var(--theme-accent-rgb),0.58)]' : ''"
                                    x-bind:style="type === {{ $typeKey }}
                                        ? 'background-color: var(--theme-accent);'
                                        : 'color: var(--theme-muted-text-color);'"
                                    class="rounded-full px-5 py-2.5 text-sm font-semibold transition"
                                >
                                    {{ $typeLabel }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="px-4 py-6 sm:px-6 lg:px-8">
                    @foreach ($planTypes as $typeKey => $typeLabel)
                        @php
                            $plansForType = collect($pricing[$typeKey] ?? []);
                        @endphp
                        <div x-cloak x-show="type === {{ $typeKey }}" x-transition.opacity.duration.200ms style="display: none;">
                            <div class="flex gap-4 overflow-x-auto pb-2 snap-x snap-mandatory [-ms-overflow-style:none] [scrollbar-width:thin] xl:grid xl:snap-none xl:overflow-visible" style="grid-template-columns: repeat({{ max(1, $plansForType->count()) }}, minmax(0, 1fr));">
                                @foreach ($plansForType as $plan)
                                    @php
                                        $planTarget = $plan['model']->slug ?? $plan['id'];
                                        $isCurrentPlan = (int) ($user?->plan_id ?? 0) === (int) $plan['id'];
                                        $ctaLabel = $isCurrentPlan
                                            ? __('Continue')
                                            : ($plan['free_plan'] ? __('Start for Free') : __('Choose Plan'));
                                    @endphp
                                    <article class="relative flex w-[min(100%,280px)] shrink-0 snap-start flex-col rounded-[1.5rem] border p-5 sm:w-[min(100%,300px)] sm:p-6 xl:min-w-0 xl:w-auto xl:shrink {{ $plan['featured'] ? 'shadow-[0_28px_72px_-44px_rgba(var(--theme-accent-rgb),0.4)]' : 'shadow-[0_22px_50px_-38px_rgba(15,23,42,0.16)]' }}" style="border-color: {{ $plan['featured'] ? 'rgba(var(--theme-accent-rgb),0.22)' : 'rgba(var(--theme-border-color-rgb),0.68)' }}; background: {{ $plan['featured'] ? 'linear-gradient(180deg, rgba(var(--theme-accent-rgb),0.10) 0%, rgba(var(--theme-surface-base-rgb,255,255,255),0.96) 36%)' : 'rgba(var(--theme-surface-base-rgb,255,255,255),0.96)' }};">
                                        <div class="flex items-start justify-between gap-2">
                                            <span class="text-base font-semibold leading-tight" style="color: var(--theme-header-text-color);">{{ $plan['name'] }}</span>
                                            @if ($plan['featured'])
                                                <span class="shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide" style="border-color: rgba(var(--theme-accent-rgb),0.18); color: var(--theme-accent);">{{ __('Popular') }}</span>
                                            @elseif ($isCurrentPlan)
                                                <span class="shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide" style="border-color: rgba(var(--theme-success-color-rgb),0.22); color: var(--theme-success-color);">{{ __('Active') }}</span>
                                            @endif
                                        </div>

                                        <p class="mt-3 line-clamp-3 text-xs leading-5 sm:text-sm sm:leading-6" style="color: var(--theme-muted-text-color);">
                                            {{ $plan['desc'] ?: __('Core features and limits for this tier.') }}
                                        </p>

                                        <div class="mt-5">
                                            <p class="text-2xl font-semibold tracking-tight sm:text-3xl" style="color: var(--theme-header-text-color);">
                                                {{ $plan['free_plan'] ? ($plan['currency_symbol'] ?: '$').'0' : ($plan['currency_symbol'] ?: '$').rtrim(rtrim(number_format((float) $plan['price'], 2), '0'), '.') }}
                                                <span class="text-sm font-medium" style="color: var(--theme-muted-text-color);">/{{ strtolower($typeLabel) }}</span>
                                            </p>
                                        </div>

                                        <ul class="mt-4 flex-1 space-y-1.5 text-xs sm:text-sm" style="color: var(--theme-muted-text-color);">
                                            @foreach (collect($plan['features'] ?? [])->take(5) as $feature)
                                                @continue(! ($feature['check'] ?? false))
                                                <li class="flex items-start gap-2">
                                                    <i class="fa-solid fa-check mt-0.5 text-[10px]" style="color: var(--theme-success-color);"></i>
                                                    <span class="min-w-0">{{ __((string) ($feature['label'] ?? '')) }}</span>
                                                </li>
                                            @endforeach
                                        </ul>

                                        <div class="mt-6">
                                            @if ($isCurrentPlan)
                                                <form action="{{ route('portal.onboarding.plan.store') }}" method="post">
                                                    @csrf
                                                    <input type="hidden" name="action" value="continue">
                                                    <x-ui.button type="submit" class="w-full justify-center">
                                                        {{ $ctaLabel }}
                                                    </x-ui.button>
                                                </form>
                                            @elseif ($plan['free_plan'])
                                                <form action="{{ route('portal.onboarding.plan.store') }}" method="post">
                                                    @csrf
                                                    <input type="hidden" name="plan_id" value="{{ (int) $plan['id'] }}">
                                                    <input type="hidden" name="require_free" value="1">
                                                    <x-ui.button type="submit" class="w-full justify-center">
                                                        {{ $ctaLabel }}
                                                    </x-ui.button>
                                                </form>
                                            @elseif (config('modules.appagencyonboarding.skip_payment_during_onboarding', true))
                                                <form action="{{ route('portal.onboarding.plan.store') }}" method="post">
                                                    @csrf
                                                    <input type="hidden" name="plan_id" value="{{ (int) $plan['id'] }}">
                                                    <input type="hidden" name="require_free" value="0">
                                                    <x-ui.button type="submit" class="w-full justify-center">
                                                        {{ $ctaLabel }}
                                                    </x-ui.button>
                                                </form>
                                            @else
                                                <x-ui.button href="{{ route('payment.index', ['plan' => $planTarget, 'onboarding' => 1]) }}" class="w-full justify-center">
                                                    {{ $ctaLabel }}
                                                </x-ui.button>
                                            @endif
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </x-ui.card>
    @endif
</div>
