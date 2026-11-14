<div class="@if ($step === 3 && $buildPath === 'template') space-y-4 @else space-y-6 @endif">
    @include('appwebsitebuilder::partials.website-builder-session-toasts')
    <x-ui.sub-header
        :eyebrow="$projectName.' / '.__('Website Builder')"
        :title="match (true) {
            $step === 1 => __('How do you want to create your website?'),
            $step === 2 && $buildPath === 'template' => __('What type of website do you want to create?'),
            $step === 3 && $buildPath === 'template' => __('Choose a template'),
            default => __('Write your prompt'),
        }"
        :description="match (true) {
            $step === 1 => __('Start with AI and your own prompt, or pick a designer template.'),
            $step === 2 && $buildPath === 'template' => __('Enter your business or site type. We will tailor templates to match.'),
            $step === 3 && $buildPath === 'template' => __('Pick a layout. We will fill it with copy for your business type.'),
            default => __('Describe your business in your own words. AI builds layout, copy, and images from your prompt.'),
        }"
    />

    @if ($step === 3 && $buildPath === 'template')
        <div class="relative w-full min-w-0">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                <x-ui.button href="{{ route('portal.agents.website.dashboard') }}" variant="secondary" size="sm" wire:navigate>
                    <i class="fa-solid fa-arrow-left mr-2 text-[10px]"></i>
                    {{ __('Dashboard') }}
                </x-ui.button>
                <button
                    type="button"
                    wire:click="backToTypeStep"
                    class="text-sm font-medium transition hover:opacity-80"
                    style="color: var(--theme-muted-text-color);"
                    @disabled($isCreatingFromTemplate)
                >
                    <i class="fa-solid fa-arrow-left mr-1 text-xs"></i>
                    {{ __('Back to website type') }}
                </button>
            </div>

            <x-ui.card class="w-full p-5 sm:p-8 lg:p-10">
                @include('appwebsitebuilder::partials.create-website-progress', ['step' => $step, 'buildPath' => $buildPath])

                @if ($siteType !== '')
                    <div class="mt-2 space-y-1">
                        <p class="text-sm" style="color: var(--theme-muted-text-color);">
                            {{ __('Creating:') }}
                            <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ $siteType }}</span>
                        </p>
                        @if ($templates !== [])
                            <p class="text-xs" style="color: var(--theme-muted-text-color);">
                                {{ __('Templates below are chosen for this website type.') }}
                            </p>
                        @endif
                    </div>
                @endif

                @error('generate')
                    <x-ui.alert class="mt-4" variant="danger" :description="$message" inline />
                @enderror

                @if ($isCreatingFromTemplate)
                    <x-ui.alert
                        class="mt-4"
                        variant="info"
                        :description="__('Building your website from the template…')"
                        inline
                    />
                @endif

                @if ($templates === [])
                    <x-ui.alert
                        class="mt-6"
                        variant="warning"
                        :description="__('Templates not available. Pick an example below.')"
                        inline
                    />
                @else
                <div
                    @class([
                        'mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-3',
                        'pointer-events-none opacity-60' => $isCreatingFromTemplate,
                    ])
                    wire:key="wizard-template-grid-{{ md5($siteType) }}"
                    wire:loading.class="pointer-events-none opacity-60"
                    wire:target="selectTemplate"
                    x-data
                    x-init="$nextTick(() => requestAnimationFrame(() => window.TemplateCardPreview?.scheduleBoot?.(true)))"
                >
                    @foreach ($templates as $template)
                        @php
                            $cardEmbedUrl = route('portal.agents.website.templates.card-preview', [
                                'templateId' => $template['id'],
                                'card' => 1,
                                'type' => $siteType,
                            ]);
                        @endphp
                        <article
                            wire:key="wizard-template-{{ $template['id'] }}"
                            class="flex h-full flex-col overflow-hidden rounded-[var(--theme-card-radius,0.875rem)] border shadow-sm transition hover:-translate-y-px hover:shadow-md"
                            style="border-color: var(--theme-border-color); background: var(--theme-surface-overlay);"
                        >
                            <div
                                class="relative h-[min(70vh,720px)] w-full shrink-0 overflow-hidden border-b sm:h-[min(62vh,680px)] lg:h-[min(58vh,640px)]"
                                style="border-color: var(--theme-border-color); background: rgba(var(--theme-border-color-rgb), 0.08);"
                                data-tc-fit="page"
                            >
                                @include('appwebsitebuilder::partials.template-card-embed-preview', [
                                    'embedUrl' => $cardEmbedUrl,
                                    'priority' => true,
                                    'class' => 'absolute inset-0 h-full w-full',
                                ])
                            </div>
                            <div class="flex flex-1 flex-col gap-3 p-4 sm:p-5">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $template['name'] }}</p>
                                    @if (! empty($template['recommended']))
                                        <span
                                            class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide"
                                            style="background: rgba(var(--theme-accent-rgb), 0.14); color: var(--theme-accent);"
                                        >{{ __('Best match') }}</span>
                                    @endif
                                </div>
                                <p class="text-sm leading-relaxed" style="color: var(--theme-muted-text-color);">{{ $template['description'] }}</p>
                                <x-ui.button
                                    type="button"
                                    class="mt-auto w-full justify-center sm:w-fit"
                                    wire:click="selectTemplate({{ json_encode($template['id']) }})"
                                    wire:loading.attr="disabled"
                                    wire:target="selectTemplate"
                                    :disabled="$isCreatingFromTemplate"
                                >
                                    <span wire:loading.remove wire:target="selectTemplate">{{ __('Use this template') }}</span>
                                    <span wire:loading wire:target="selectTemplate">{{ __('Creating…') }}</span>
                                </x-ui.button>
                            </div>
                        </article>
                    @endforeach
                </div>
                @endif
            </x-ui.card>
        </div>
    @else
        <div class="mx-auto w-full max-w-3xl">
            <div class="mb-4 flex justify-start">
                <x-ui.button href="{{ route('portal.agents.website.dashboard') }}" variant="secondary" size="sm" wire:navigate>
                    <i class="fa-solid fa-arrow-left mr-2 text-[10px]"></i>
                    {{ __('Dashboard') }}
                </x-ui.button>
            </div>

            <x-ui.card class="p-6 sm:p-8">
                @include('appwebsitebuilder::partials.create-website-progress', ['step' => $step, 'buildPath' => $buildPath])

                @if ($step === 1)
                    <div class="mt-8 space-y-4">
                        <button
                            type="button"
                            wire:click="chooseAiPath"
                            wire:loading.attr="disabled"
                            wire:loading.class="pointer-events-none opacity-60"
                            wire:target="chooseAiPath"
                            class="wizard-method-option group w-full rounded-[var(--theme-card-radius,0.875rem)] border p-5 text-left sm:p-6 disabled:cursor-not-allowed"
                        >
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="space-y-2">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span
                                            class="inline-flex h-8 items-center rounded-lg px-2.5 text-[11px] font-bold uppercase tracking-wide text-white"
                                            style="background: var(--theme-accent);"
                                        >{{ __('AI') }}</span>
                                        <span
                                            class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide"
                                            style="background: rgba(var(--theme-accent-rgb), 0.14); color: var(--theme-accent);"
                                        >{{ __('Recommended') }}</span>
                                    </div>
                                    <p class="text-lg font-semibold" style="color: var(--theme-header-text-color);">
                                        {{ __('Create with your prompt') }}
                                    </p>
                                    <p class="text-sm leading-relaxed" style="color: var(--theme-muted-text-color);">
                                        {{ __('Describe your business. AI generates layout, copy, and images from your words.') }}
                                    </p>
                                </div>
                                <span
                                    class="inline-flex h-16 w-16 shrink-0 items-center justify-center rounded-xl text-xl transition duration-200 group-hover:scale-105"
                                    style="background: rgba(var(--theme-accent-rgb), 0.12); color: var(--theme-accent);"
                                >
                                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                                </span>
                            </div>
                        </button>

                        <button
                            type="button"
                            wire:click="chooseTemplatePath"
                            wire:loading.attr="disabled"
                            wire:loading.class="pointer-events-none opacity-60"
                            wire:target="chooseTemplatePath"
                            class="wizard-method-option group w-full rounded-[var(--theme-card-radius,0.875rem)] border p-5 text-left sm:p-6 disabled:cursor-not-allowed"
                        >
                            <div class="flex items-start gap-4">
                                <span
                                    class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl transition duration-200 group-hover:scale-105"
                                    style="background: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);"
                                >
                                    <i class="fa-solid fa-layer-group"></i>
                                </span>
                                <div>
                                    <p class="text-lg font-semibold" style="color: var(--theme-header-text-color);">
                                        {{ __('Start with a template') }}
                                    </p>
                                    <p class="mt-1 text-sm leading-relaxed" style="color: var(--theme-muted-text-color);">
                                        {{ __('Choose a designer layout, then pick templates for your business type.') }}
                                    </p>
                                </div>
                            </div>
                        </button>
                    </div>
                @elseif ($step === 2 && $buildPath === 'template')
                    <form
                        wire:submit="continueFromType"
                        class="mt-6 space-y-6"
                        wire:loading.class="pointer-events-none"
                        wire:target="continueFromType"
                    >
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                            <div class="min-w-0 flex-1">
                                <x-ui.input
                                    wire:model="siteType"
                                    wire:loading.attr="readonly"
                                    wire:target="continueFromType"
                                    :label="__('Website type')"
                                    placeholder="{{ __('e.g. E-commerce Store') }}"
                                    autocomplete="off"
                                />
                            </div>
                            <x-ui.button
                                type="submit"
                                class="h-11 shrink-0 justify-center px-8 sm:min-w-[8.5rem]"
                                wire:loading.attr="disabled"
                                wire:target="continueFromType"
                            >
                                <span wire:loading.remove wire:target="continueFromType">{{ __('Continue') }}</span>
                                <span wire:loading wire:target="continueFromType">{{ __('Checking…') }}</span>
                            </x-ui.button>
                        </div>
                        @error('siteType')
                            <x-ui.alert variant="warning" :description="$message" inline />
                        @enderror

                        <div>
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Examples') }}</p>
                            <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Pick one to fill the field above.') }}</p>
                            <ul class="mt-3 flex flex-wrap gap-2">
                                @foreach ($examples as $example)
                                    <li>
                                        <button
                                            type="button"
                                            wire:click="$set('siteType', {{ json_encode($example) }})"
                                            class="rounded-full border px-3.5 py-1.5 text-sm font-medium transition hover:-translate-y-px hover:shadow-sm"
                                            style="border-color: var(--theme-border-color); background-color: var(--theme-button-soft-bg); color: var(--theme-button-soft-text);"
                                        >
                                            {{ $example }}
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </form>

                    <div class="mt-6 flex justify-start border-t pt-6" style="border-color: rgba(var(--theme-border-color-rgb), 0.55);">
                        <button
                            type="button"
                            wire:click="backToMethodStep"
                            class="text-sm font-medium transition hover:opacity-80"
                            style="color: var(--theme-muted-text-color);"
                        >
                            <i class="fa-solid fa-arrow-left mr-1 text-xs"></i>
                            {{ __('Back to method') }}
                        </button>
                    </div>
                @elseif ($step === 2 && $buildPath === 'ai')
                    @error('generate')
                        <x-ui.alert class="mt-4" variant="danger" :description="$message" inline />
                    @enderror

                    @if ($isGenerating)
                        <x-ui.alert
                            class="mt-4"
                            variant="info"
                            :description="__('Generating your website with AI… This may take up to a minute.')"
                            inline
                        />
                    @endif

                    <form
                        wire:submit="generateFromUserPrompt"
                        class="mt-6 space-y-5"
                        wire:loading.class="pointer-events-none opacity-70"
                        wire:target="generateFromUserPrompt"
                    >
                        <div class="space-y-2.5">
                            <x-ui.label for="website-ai-prompt">{{ __('Your prompt') }}</x-ui.label>
                            <textarea
                                id="website-ai-prompt"
                                wire:model="userPrompt"
                                rows="8"
                                @if ($isGenerating) disabled @endif
                                placeholder="{{ __('e.g. Online fashion boutique for women. Elegant tone. Highlight new arrivals, free returns, and style guide. CTA: Shop the collection.') }}"
                                class="flex min-h-[11rem] w-full border px-4 py-3.5 text-sm leading-6 shadow-[0_1px_2px_rgba(15,23,42,0.04)] outline-none transition duration-200 placeholder:text-[var(--theme-input-placeholder)] focus:border-[var(--theme-accent)] focus:ring-4 focus:ring-[color:rgba(var(--theme-accent-rgb),0.10)] disabled:cursor-not-allowed disabled:opacity-60"
                                style="border-radius: var(--theme-input-radius, 0.75rem); border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);"
                            ></textarea>
                            @error('userPrompt')
                                <p class="text-sm font-medium" style="color: var(--theme-danger-color);">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex flex-col gap-3 border-t pt-6 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), 0.55);">
                            <button
                                type="button"
                                wire:click="backToMethodStep"
                                class="text-sm font-medium transition hover:opacity-80"
                                style="color: var(--theme-muted-text-color);"
                                @if ($isGenerating) disabled @endif
                            >
                                <i class="fa-solid fa-arrow-left mr-1 text-xs"></i>
                                {{ __('Back to method') }}
                            </button>
                            <x-ui.button
                                type="submit"
                                class="h-11 min-w-[10rem] justify-center"
                                wire:loading.attr="disabled"
                                wire:target="generateFromUserPrompt"
                            >
                                <span wire:loading.remove wire:target="generateFromUserPrompt">
                                    <i class="fa-solid fa-wand-magic-sparkles mr-2 text-xs"></i>
                                    {{ __('Generate website') }}
                                </span>
                                <span wire:loading wire:target="generateFromUserPrompt">{{ __('Generating…') }}</span>
                            </x-ui.button>
                        </div>
                    </form>
                @endif
            </x-ui.card>
        </div>
    @endif

    <style>
        .wizard-method-option {
            border-color: var(--theme-border-color);
            background: var(--theme-surface-overlay);
            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease,
                border-color 0.2s ease,
                background 0.2s ease;
        }

        .wizard-method-option:hover:not(:disabled) {
            transform: translateY(-2px);
            border-color: rgba(var(--theme-accent-rgb), 0.45);
            background: color-mix(in srgb, var(--theme-accent) 8%, var(--theme-surface-overlay));
            box-shadow: 0 4px 12px -6px rgba(var(--theme-accent-rgb), 0.25);
        }

        .wizard-method-option:active:not(:disabled) {
            transform: translateY(0);
            box-shadow: 0 2px 8px -4px rgba(var(--theme-accent-rgb), 0.2);
        }
    </style>

    @include('appwebsitebuilder::partials.template-card-preview-script')
</div>
