<div
    class="space-y-6"
    x-data="{
        previewName: @js(old('name', $name)),
        previewWebsite: @js(old('website', $website)),
        previewIndustry: @js(old('industry', $industry)),
        industryLabels: @js($industries),
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
        <div class="grid gap-6 px-5 py-6 sm:px-8 lg:grid-cols-[minmax(0,1fr)_minmax(280px,0.42fr)] lg:items-center">
            <div class="flex min-w-0 gap-4">
                <div
                    class="flex h-14 w-14 shrink-0 items-center justify-center rounded-[1rem]"
                    style="background-color: rgba(var(--theme-accent-rgb), 0.14); color: var(--theme-accent);"
                >
                    <i class="fa-solid fa-folder-plus text-xl"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-accent);">{{ __('Onboarding') }}</p>
                    <h1 class="mt-2 text-[1.8rem] font-semibold tracking-[-0.05em] sm:text-[2rem]" style="color: var(--theme-header-text-color);">{{ __('Add a project') }}</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-7" style="color: var(--theme-muted-text-color);">
                        {{ __('A project is one brand or client. Each project gets its own agents and dashboards.') }}
                    </p>
                </div>
            </div>

            <div
                class="rounded-[1.2rem] border p-4 shadow-sm"
                style="border-color: rgba(var(--theme-border-color-rgb), 0.64); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.96);"
            >
                <p class="text-[11px] font-semibold uppercase tracking-[0.22em]" style="color: var(--theme-muted-text-color);">{{ __('Current plan') }}</p>
                <p class="mt-2 text-base font-semibold" style="color: var(--theme-header-text-color);">{{ $planName }}</p>
                <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Agent limits follow this package.') }}</p>
            </div>
        </div>
    </section>

    <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(280px,0.38fr)] lg:items-start">
        <x-ui.card class="p-5 sm:p-7">
            <form action="{{ route('portal.onboarding.project.store') }}" method="post" class="space-y-5">
                @csrf
                @if ($errors->any())
                    <div class="rounded-xl border px-4 py-3 text-sm" style="border-color: rgba(var(--theme-danger-color-rgb, 239, 68, 68), 0.35); color: var(--theme-danger-color);">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="space-y-2.5">
                    <x-ui.label for="project-name">{{ __('Project name') }}</x-ui.label>
                    <input
                        id="project-name"
                        name="name"
                        type="text"
                        value="{{ old('name', $name) }}"
                        required
                        x-on:input="previewName = $event.target.value"
                        autocomplete="organization"
                        class="flex h-11 w-full border px-4 text-sm font-medium shadow-[0_1px_2px_rgba(15,23,42,0.04)] outline-none transition duration-200 focus:border-[var(--theme-accent)] focus:ring-4 focus:ring-[color:rgba(var(--theme-accent-rgb),0.10)]"
                        style="border-radius: var(--theme-input-radius, 0.75rem); border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);"
                    />
                    @error('name')
                        <p class="text-sm font-medium" style="color: var(--theme-danger-color);">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2.5">
                    <x-ui.label for="project-website">{{ __('Website') }}</x-ui.label>
                    <input
                        id="project-website"
                        name="website"
                        type="text"
                        value="{{ old('website', $website) }}"
                        x-on:input="previewWebsite = $event.target.value"
                        autocomplete="url"
                        class="flex h-11 w-full border px-4 text-sm font-medium shadow-[0_1px_2px_rgba(15,23,42,0.04)] outline-none transition duration-200 focus:border-[var(--theme-accent)] focus:ring-4 focus:ring-[color:rgba(var(--theme-accent-rgb),0.10)]"
                        style="border-radius: var(--theme-input-radius, 0.75rem); border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);"
                    />
                    <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('Optional — used in previews and reports') }}</p>
                </div>

                <div class="space-y-2.5">
                    <x-ui.label for="project-industry">{{ __('Industry') }}</x-ui.label>
                    <div class="relative">
                        <select
                            id="project-industry"
                            name="industry"
                            x-on:change="previewIndustry = $event.target.value"
                            class="flex h-11 w-full appearance-none border px-4 pr-14 text-sm font-medium shadow-[0_1px_2px_rgba(15,23,42,0.04)] outline-none transition duration-200 focus:border-[var(--theme-accent)] focus:ring-4 focus:ring-[color:rgba(var(--theme-accent-rgb),0.10)]"
                            style="border-radius: var(--theme-input-radius, 0.75rem); border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);"
                        >
                            @foreach ($industries as $value => $label)
                                <option value="{{ $value }}" @selected(old('industry', $industry) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <span class="pointer-events-none absolute inset-y-0 right-4 inline-flex items-center" style="color: var(--theme-muted-text-color);">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </span>
                    </div>
                </div>

                <div
                    class="flex flex-col-reverse gap-3 border-t pt-6 sm:flex-row sm:items-center sm:justify-between"
                    style="border-color: rgba(var(--theme-border-color-rgb), 0.45);"
                >
                    <x-ui.button
                        type="button"
                        variant="outline"
                        x-on:click.stop="window.location.href = @js(route('portal.onboarding.plan'))"
                    >
                        <i class="fa-solid fa-arrow-left text-xs"></i>
                        {{ __('Back') }}
                    </x-ui.button>
                    <x-ui.button type="submit">
                        {{ __('Create project') }}
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card class="p-5 sm:p-6">
            <p class="text-[11px] font-semibold uppercase tracking-[0.22em]" style="color: var(--theme-muted-text-color);">{{ __('Preview') }}</p>
            <div class="mt-4 flex items-center gap-3">
                <span
                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[var(--theme-card-radius,0.85rem)] text-lg font-semibold text-white"
                    style="background: var(--theme-accent);"
                >
                    <span x-text="(previewName || '').trim() !== '' ? (previewName || '').trim().charAt(0).toUpperCase() : 'P'"></span>
                </span>
                <div class="min-w-0">
                    <p class="truncate font-semibold" style="color: var(--theme-header-text-color);" x-text="(previewName || '').trim() !== '' ? previewName : @js(__('Your project'))"></p>
                    <p class="truncate text-sm" style="color: var(--theme-muted-text-color);" x-text="(previewWebsite || '').trim() !== '' ? previewWebsite : @js(__('website.com'))"></p>
                </div>
            </div>
            <dl class="mt-6 space-y-3 text-sm">
                <div class="flex justify-between gap-4 border-b pb-2" style="border-color: rgba(var(--theme-border-color-rgb), 0.4);">
                    <dt style="color: var(--theme-muted-text-color);">{{ __('Industry') }}</dt>
                    <dd class="font-semibold" style="color: var(--theme-header-text-color);" x-text="industryLabels[previewIndustry] || previewIndustry"></dd>
                </div>
                <div class="flex justify-between gap-4 border-b pb-2" style="border-color: rgba(var(--theme-border-color-rgb), 0.4);">
                    <dt style="color: var(--theme-muted-text-color);">{{ __('Plan') }}</dt>
                    <dd class="font-semibold" style="color: var(--theme-header-text-color);">{{ $planName }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt style="color: var(--theme-muted-text-color);">{{ __('Agents') }}</dt>
                    <dd class="font-semibold" style="color: var(--theme-accent);">{{ __('Choose next') }}</dd>
                </div>
            </dl>
        </x-ui.card>
    </div>
</div>
