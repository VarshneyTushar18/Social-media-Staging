@php
    $selectedList = old('selected_agents', $selectedAgents);
    if (! is_array($selectedList)) {
        $selectedList = ['social'];
    }
    $selectedList = array_values(array_unique(array_filter($selectedList)));
    if ($selectedList === []) {
        $selectedList = ['social'];
    }
    $selectedCount = count($selectedList);
@endphp

<div
    class="space-y-6"
    x-data="{
        max: {{ (int) $maxAgents }},
        selected: @js($selectedList),
        limitMessage: @js(__('You can select up to :count agents.', ['count' => $maxAgents])),
        minMessage: @js(__('Keep at least one agent selected.')),
        isOn(key) {
            return this.selected.includes(key);
        },
        count() {
            return this.selected.length;
        },
        toggle(key) {
            const idx = this.selected.indexOf(key);
            if (idx >= 0) {
                if (this.selected.length <= 1) {
                    window.alert(this.minMessage);
                    return;
                }
                this.selected.splice(idx, 1);
            } else if (this.selected.length >= this.max) {
                window.alert(this.limitMessage);
            } else {
                this.selected.push(key);
            }
        },
        cardStyle(key) {
            return this.isOn(key)
                ? 'border-color: rgba(var(--theme-accent-rgb), 0.45); background-color: rgba(var(--theme-accent-rgb), 0.08); box-shadow: 0 0 0 1px rgba(var(--theme-accent-rgb), 0.35);'
                : 'border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.96);';
        },
    }"
>
    @include('appagencyonboarding::partials.stepper', ['current' => $currentStep])

    <section
        class="overflow-hidden rounded-[1.75rem] border"
        style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
            radial-gradient(circle at 0% 0%, rgba(var(--theme-accent-rgb), 0.18), transparent 34%),
            radial-gradient(circle at 86% 18%, rgba(var(--theme-success-color-rgb), 0.10), transparent 32%),
            linear-gradient(135deg, rgba(var(--theme-surface-base-rgb,255,255,255),0.98), rgba(var(--theme-surface-base-rgb,255,255,255),0.94));
        "
    >
        <div class="grid gap-6 px-5 py-6 sm:px-8 lg:grid-cols-[minmax(0,1fr)_minmax(260px,0.38fr)] lg:items-center">
            <div class="flex min-w-0 gap-4">
                <div
                    class="flex h-14 w-14 shrink-0 items-center justify-center rounded-[1rem]"
                    style="background-color: rgba(var(--theme-accent-rgb), 0.14); color: var(--theme-accent);"
                >
                    <i class="fa-solid fa-sliders text-xl"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-accent);">{{ __('Onboarding') }}</p>
                    <h1 class="mt-2 text-[1.8rem] font-semibold tracking-[-0.05em] sm:text-[2rem]" style="color: var(--theme-header-text-color);">
                        {{ __('Set up :name', ['name' => $project?->name ?? __('your project')]) }}
                    </h1>
                    <p class="mt-3 max-w-2xl text-sm leading-7" style="color: var(--theme-muted-text-color);">
                        {{ __('Tell the agents what you need, then switch on the ones you want. Each agent gets its own dashboard.') }}
                    </p>
                </div>
            </div>

            <div
                class="rounded-[1.2rem] border p-4 shadow-sm"
                style="border-color: rgba(var(--theme-border-color-rgb), 0.64); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.96);"
            >
                <p class="text-[11px] font-semibold uppercase tracking-[0.22em]" style="color: var(--theme-muted-text-color);">{{ __('Agents') }}</p>
                <p class="mt-2 text-base font-semibold" style="color: var(--theme-header-text-color);">
                    <span x-text="count()"></span> / {{ $maxAgents }} {{ __('selected') }}
                </p>
                <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Limited by your plan') }}</p>
            </div>
        </div>
    </section>

    @if ($errors->any())
        <x-ui.card class="border px-5 py-4" style="border-color: rgba(var(--theme-danger-color-rgb, 239, 68, 68), 0.35);">
            <p class="text-sm font-medium" style="color: var(--theme-danger-color);">{{ $errors->first() }}</p>
        </x-ui.card>
    @endif

    <form action="{{ route('portal.onboarding.setup.store') }}" method="post" class="space-y-5">
        @csrf

        <x-ui.card class="p-5 sm:p-7">
            <p class="text-base font-semibold" style="color: var(--theme-header-text-color);">1. {{ __('Requirements') }}</p>
            <div class="mt-5 space-y-5">
                <div class="space-y-2.5">
                    <x-ui.label for="setup-goals">{{ __('Goals for this project') }}</x-ui.label>
                    <textarea
                        id="setup-goals"
                        name="goals"
                        rows="3"
                        class="w-full border px-4 py-3 text-sm font-medium shadow-[0_1px_2px_rgba(15,23,42,0.04)] outline-none transition duration-200 focus:border-[var(--theme-accent)] focus:ring-4 focus:ring-[color:rgba(var(--theme-accent-rgb),0.10)]"
                        style="border-radius: var(--theme-input-radius, 0.75rem); border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);"
                    >{{ old('goals', $goals) }}</textarea>
                </div>

                <div class="space-y-2.5">
                    <x-ui.label for="setup-audience">{{ __('Target audience') }}</x-ui.label>
                    <input
                        id="setup-audience"
                        name="target_audience"
                        type="text"
                        value="{{ old('target_audience', $target_audience) }}"
                        class="flex h-11 w-full border px-4 text-sm font-medium shadow-[0_1px_2px_rgba(15,23,42,0.04)] outline-none transition duration-200 focus:border-[var(--theme-accent)] focus:ring-4 focus:ring-[color:rgba(var(--theme-accent-rgb),0.10)]"
                        style="border-radius: var(--theme-input-radius, 0.75rem); border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);"
                    />
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="space-y-2.5">
                        <x-ui.label for="setup-budget">{{ __('Monthly budget') }}</x-ui.label>
                        <div class="relative">
                            <select
                                id="setup-budget"
                                name="monthly_budget"
                                class="flex h-11 w-full appearance-none border px-4 pr-14 text-sm font-medium shadow-[0_1px_2px_rgba(15,23,42,0.04)] outline-none transition duration-200 focus:border-[var(--theme-accent)] focus:ring-4 focus:ring-[color:rgba(var(--theme-accent-rgb),0.10)]"
                                style="border-radius: var(--theme-input-radius, 0.75rem); border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);"
                            >
                                @foreach ($budgetOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('monthly_budget', $monthly_budget) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute inset-y-0 right-4 inline-flex items-center" style="color: var(--theme-muted-text-color);">
                                <i class="fa-solid fa-chevron-down text-xs"></i>
                            </span>
                        </div>
                    </div>
                    <div class="space-y-2.5">
                        <x-ui.label for="setup-tone">{{ __('Brand tone') }}</x-ui.label>
                        <div class="relative">
                            <select
                                id="setup-tone"
                                name="brand_tone"
                                class="flex h-11 w-full appearance-none border px-4 pr-14 text-sm font-medium shadow-[0_1px_2px_rgba(15,23,42,0.04)] outline-none transition duration-200 focus:border-[var(--theme-accent)] focus:ring-4 focus:ring-[color:rgba(var(--theme-accent-rgb),0.10)]"
                                style="border-radius: var(--theme-input-radius, 0.75rem); border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);"
                            >
                                @foreach ($toneOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('brand_tone', $brand_tone) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute inset-y-0 right-4 inline-flex items-center" style="color: var(--theme-muted-text-color);">
                                <i class="fa-solid fa-chevron-down text-xs"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="p-5 sm:p-7">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <p class="text-base font-semibold" style="color: var(--theme-header-text-color);">2. {{ __('Choose agents') }}</p>
                <p class="text-sm font-medium" style="color: var(--theme-muted-text-color);">
                    <span x-text="count()"></span> {{ __('of') }} {{ $maxAgents }} {{ __('selected') }}
                </p>
            </div>

            @error('selected_agents')
                <p class="mt-3 text-sm font-medium" style="color: var(--theme-danger-color);">{{ $message }}</p>
            @enderror

            <p class="mt-2 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Click a card to select or deselect. Social is fully live; others show your name and agent on the dashboard preview.') }}</p>

            <template x-for="key in selected" :key="key">
                <input type="hidden" name="selected_agents[]" :value="key">
            </template>

            <div class="mt-5 grid gap-4 md:grid-cols-2">
                @foreach ($agents as $key => $agent)
                    @php
                        $allowed = in_array($key, $allowedAgents, true);
                    @endphp
                    <button
                        type="button"
                        @disabled(! $allowed)
                        x-on:click="toggle(@js($key))"
                        @class([
                            'relative w-full rounded-[1.25rem] border p-5 text-left transition',
                            'cursor-pointer hover:shadow-sm' => $allowed,
                            'cursor-not-allowed opacity-60' => ! $allowed,
                        ])
                        x-bind:style="cardStyle(@js($key))"
                        x-bind:aria-pressed="isOn(@js($key))"
                    >
                        <span
                            class="absolute right-4 top-4 flex h-6 w-6 items-center justify-center rounded-md border transition"
                            x-bind:style="isOn(@js($key))
                                ? 'background-color: var(--theme-accent); border-color: var(--theme-accent); color: white;'
                                : 'border-color: var(--theme-border-color);'"
                        >
                            <i class="fa-solid fa-check text-[10px]" x-show="isOn(@js($key))" x-cloak></i>
                        </span>
                        <span
                            class="flex h-10 w-10 items-center justify-center rounded-xl text-sm font-bold text-white"
                            style="background: {{ $agent['color'] }};"
                        >
                            {{ $agent['short'] }}
                        </span>
                        <p class="mt-4 text-base font-semibold" style="color: var(--theme-header-text-color);">{{ $agent['label'] }}</p>
                        <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $agent['description'] }}</p>
                    </button>
                @endforeach
            </div>
        </x-ui.card>

        <div class="flex flex-col-reverse gap-3 pb-4 sm:flex-row sm:items-center sm:justify-between">
            <x-ui.button
                type="button"
                variant="outline"
                x-on:click.stop="window.location.href = @js(route('portal.onboarding.project'))"
            >
                <i class="fa-solid fa-arrow-left text-xs"></i>
                {{ __('Back') }}
            </x-ui.button>
            <x-ui.button type="submit">
                <i class="fa-solid fa-rocket text-xs"></i>
                {{ __('Launch dashboard') }}
            </x-ui.button>
        </div>
    </form>
</div>
