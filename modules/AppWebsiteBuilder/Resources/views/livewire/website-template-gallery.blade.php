<div
    class="space-y-6"
    x-data="{
        previewOpen: false,
        categorySlug: @entangle('activeCategorySlug').live,
        search: @entangle('searchQuery').live,
        pickCategory(slug) {
            this.categorySlug = slug;
            this.search = '';
            $wire.filterCategory(slug).then(() => {
                requestAnimationFrame(() => {
                    window.TemplateCardPreview?.scheduleBoot?.(true);
                });
            });
        },
        openPreview(id) {
            this.previewOpen = true;
            $wire.openTemplatePreview(id).then(() => $wire.loadPreviewTemplateContent());
        },
        closePreview() {
            this.previewOpen = false;
            $wire.closeTemplatePreview();
        },
    }"
>
    @include('appwebsitebuilder::partials.website-builder-session-toasts')

    @php
        $projectName = $project?->name ?? __('Project');
    @endphp

    <x-ui.sub-header
        :eyebrow="$projectName.' / '.__('Website Builder')"
        :title="__('Templates')"
        :description="__('Pick a category to browse layouts, or search across templates.')"
    >
        <x-slot:actions>
            <x-ui.button href="{{ route('portal.agents.website.sites.create') }}" wire:navigate>
                <i class="fa-solid fa-plus mr-2 text-[11px]"></i>
                {{ __('Add new website') }}
            </x-ui.button>
        </x-slot:actions>
    </x-ui.sub-header>

    <x-ui.card class="p-6 sm:p-8">
        <div class="space-y-5">
            <div>
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Category') }}</p>
                <ul class="mt-3 flex flex-wrap gap-2">
                    @foreach ($exampleFilters as $slug => $label)
                        <li wire:key="template-type-{{ $slug }}">
                            <button
                                type="button"
                                x-on:click="pickCategory(@js($slug))"
                                @class([
                                    'rounded-full border px-3.5 py-1.5 text-sm font-medium',
                                ])
                                x-bind:style="categorySlug === @js($slug)
                                    ? 'border-color: var(--theme-accent); background-color: rgba(var(--theme-accent-rgb), 0.12); color: var(--theme-accent); box-shadow: 0 1px 2px rgba(0,0,0,.06);'
                                    : 'border-color: var(--theme-border-color); background-color: var(--theme-button-soft-bg); color: var(--theme-button-soft-text);'"
                            >
                                {{ $label }}
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="min-w-0 flex-1">
                    <x-ui.input
                        wire:model.live.debounce.200ms="searchQuery"
                        :label="__('Search templates')"
                        placeholder="{{ __('e.g. Pet, boutique, restaurant…') }}"
                        autocomplete="off"
                    />
                </div>
            </div>
        </div>
    </x-ui.card>

    @if ($awaitingCategory)
        <x-ui.empty
            icon="fa-solid fa-layer-group"
            :title="__('Choose a category')"
            :description="__('Select a category above to see templates for that type. You can also use search to find a layout by keyword.')"
        />
    @elseif ($templates === [])
        <x-ui.empty
            icon="fa-solid fa-layer-group"
            :title="__('No templates match')"
            :description="__('Try another category or adjust your search.')"
        />
    @else
        <p class="text-sm" style="color: var(--theme-muted-text-color);">
            {{ __(':count templates', ['count' => count($templates)]) }}
        </p>

        @php
            $gallerySiteType = $activeCategorySlug !== '' && isset($exampleFilters[$activeCategorySlug])
                ? (string) $exampleFilters[$activeCategorySlug]
                : '';
        @endphp

        <div
            class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3"
            wire:key="template-gallery-grid-{{ $activeCategorySlug }}-{{ md5($searchQuery) }}"
        >
            @foreach ($templates as $template)
                @php
                    $cardEmbedUrl = route('portal.agents.website.templates.card-preview', array_filter([
                        'templateId' => $template['id'],
                        'card' => 1,
                        'type' => $gallerySiteType !== '' ? $gallerySiteType : null,
                    ]));
                @endphp
                <article
                    wire:key="template-card-{{ $template['id'] }}"
                    class="flex h-full flex-col overflow-hidden rounded-[var(--theme-card-radius,0.875rem)] border shadow-sm"
                    style="border-color: var(--theme-border-color); background: var(--theme-surface-overlay);"
                >
                    <div
                        class="relative aspect-[3/4] w-full shrink-0 border-b sm:aspect-[4/5]"
                        style="border-color: var(--theme-border-color); background: rgba(var(--theme-border-color-rgb), 0.08);"
                    >
                        @include('appwebsitebuilder::partials.template-card-embed-preview', [
                            'embedUrl' => $cardEmbedUrl,
                            'priority' => true,
                            'class' => 'absolute inset-0 h-full w-full',
                        ])
                    </div>

                    <div class="flex flex-col gap-3 p-4 sm:p-5">
                        <h3 class="text-base font-bold leading-snug" style="color: var(--theme-header-text-color);">
                            {{ $template['name'] }}
                        </h3>
                        <button
                            type="button"
                            x-on:click="openPreview(@js($template['id']))"
                            class="inline-flex h-9 w-fit cursor-pointer items-center justify-center gap-2 rounded-[var(--theme-button-radius,0.75rem)] border px-3.5 text-sm font-semibold shadow-sm hover:-translate-y-px"
                            style="border-color: var(--theme-border-color); color: var(--theme-header-text-color); background: var(--theme-button-soft-bg);"
                        >
                            <i class="fa-regular fa-eye text-[11px]"></i>
                            {{ __('Preview') }}
                        </button>
                    </div>
                </article>
            @endforeach
        </div>
    @endif

    @include('appwebsitebuilder::partials.template-card-preview-script')

    <template x-teleport="body">
        <div
            x-show="previewOpen"
            x-cloak
            class="fixed inset-0 z-[200] flex flex-col"
            wire:key="gallery-template-preview-shell"
        >
            <div
                class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm"
                x-on:click="closePreview()"
            ></div>

            <div class="relative flex min-h-0 flex-1 flex-col" style="background-color: var(--theme-surface-base);">
                <div
                    class="relative z-10 flex shrink-0 flex-wrap items-center justify-between gap-3 border-b px-4 py-3 sm:px-6"
                    style="border-color: var(--theme-border-color); background-color: var(--theme-surface-overlay);"
                >
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wide" style="color: var(--theme-muted-text-color);">{{ __('Template preview') }}</p>
                        <p class="truncate font-semibold" style="color: var(--theme-header-text-color);">{{ $previewTemplateName }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <x-ui.button
                            type="button"
                            variant="outline"
                            size="sm"
                            x-on:click="closePreview()"
                            wire:loading.attr="disabled"
                            wire:target="usePreviewedTemplate"
                        >
                            <i class="fa-solid fa-xmark"></i>
                            {{ __('Close') }}
                        </x-ui.button>
                        <x-ui.button
                            type="button"
                            size="sm"
                            wire:click="usePreviewedTemplate"
                            wire:loading.attr="disabled"
                            wire:target="usePreviewedTemplate"
                        >
                            <span wire:loading.remove wire:target="usePreviewedTemplate">{{ __('Use this template') }}</span>
                            <span wire:loading wire:target="usePreviewedTemplate">{{ __('Creating…') }}</span>
                        </x-ui.button>
                    </div>
                    @error('useTemplate')
                        <p class="w-full px-4 pb-2 text-sm font-medium sm:px-6" style="color: var(--theme-danger-color);">{{ $message }}</p>
                    @enderror
                </div>

                <div class="relative z-10 min-h-0 flex-1 overflow-y-auto overflow-x-hidden bg-slate-100 dark:bg-slate-950">
                    <div class="mx-auto min-h-full max-w-[1200px] bg-white shadow-xl">
                        @if (! $previewContentReady)
                            <div class="flex min-h-[50vh] flex-col items-center justify-center gap-3 p-12 text-center">
                                <i class="fa-solid fa-spinner fa-spin text-2xl" style="color: var(--theme-accent);" aria-hidden="true"></i>
                                <p class="text-sm font-medium" style="color: var(--theme-muted-text-color);">{{ __('Loading template preview…') }}</p>
                            </div>
                        @else
                            @include('appwebsitebuilder::partials.landing-site', [
                                'site' => $previewSite,
                                'content' => $previewTemplateContent,
                            ])
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
