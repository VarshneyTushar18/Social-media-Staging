@props([
    'label' => null,
    'help' => null,
    'error' => null,
    'name' => null,
    'value' => null,
    'previewColor' => '#0f766e',
    'dialogTitle' => null,
    'dialogDescription' => null,
    'placeholder' => 'fa-solid fa-hashtag',
    'icons' => [],
    'styleOptions' => [],
])

@php
    $defaultIcons = [
        'fa-solid fa-hashtag',
        'fa-solid fa-tag',
        'fa-solid fa-tags',
        'fa-solid fa-bookmark',
        'fa-solid fa-thumbtack',
        'fa-solid fa-thumbs-up',
        'fa-solid fa-thumbs-down',
        'fa-solid fa-flag',
        'fa-solid fa-flag-pennant',
        'fa-solid fa-certificate',
        'fa-solid fa-award',
        'fa-solid fa-link',
        'fa-solid fa-paperclip',
        'fa-solid fa-chain',
        'fa-solid fa-anchor',
        'fa-solid fa-folder',
        'fa-solid fa-folder-open',
        'fa-solid fa-folder-tree',
        'fa-solid fa-folder-plus',
        'fa-solid fa-file-lines',
        'fa-solid fa-files',
        'fa-solid fa-file-circle-plus',
        'fa-solid fa-newspaper',
        'fa-solid fa-book-open',
        'fa-solid fa-book',
        'fa-solid fa-book-atlas',
        'fa-solid fa-bookmark-slash',
        'fa-solid fa-feather',
        'fa-solid fa-pen',
        'fa-solid fa-pen-nib',
        'fa-solid fa-pen-to-square',
        'fa-solid fa-pencil',
        'fa-solid fa-signature',
        'fa-solid fa-quote-left',
        'fa-solid fa-quote-right',
        'fa-solid fa-paragraph',
        'fa-solid fa-align-left',
        'fa-solid fa-bolt',
        'fa-solid fa-sparkles',
        'fa-solid fa-wand-magic-sparkles',
        'fa-solid fa-star',
        'fa-solid fa-stars',
        'fa-solid fa-certificate',
        'fa-solid fa-badge-percent',
        'fa-solid fa-coins',
        'fa-solid fa-fire',
        'fa-solid fa-sun',
        'fa-solid fa-moon',
        'fa-solid fa-cloud',
        'fa-solid fa-rainbow',
        'fa-solid fa-snowflake',
        'fa-solid fa-bolt-lightning',
        'fa-solid fa-heart',
        'fa-solid fa-heart-circle-check',
        'fa-solid fa-face-smile',
        'fa-solid fa-face-laugh',
        'fa-solid fa-face-surprise',
        'fa-solid fa-lightbulb',
        'fa-solid fa-rocket',
        'fa-solid fa-meteor',
        'fa-solid fa-plane',
        'fa-solid fa-paper-plane',
        'fa-solid fa-bullhorn',
        'fa-solid fa-megaphone',
        'fa-solid fa-bell',
        'fa-solid fa-bells',
        'fa-solid fa-bell-ring',
        'fa-solid fa-bell-on',
        'fa-solid fa-globe',
        'fa-solid fa-earth-asia',
        'fa-solid fa-compass',
        'fa-solid fa-location-dot',
        'fa-solid fa-map',
        'fa-solid fa-map-pin',
        'fa-solid fa-signs-post',
        'fa-solid fa-route',
        'fa-solid fa-road',
        'fa-solid fa-shapes',
        'fa-solid fa-layer-group',
        'fa-solid fa-grid-2',
        'fa-solid fa-grid-2-plus',
        'fa-solid fa-grid',
        'fa-solid fa-sitemap',
        'fa-solid fa-cubes',
        'fa-solid fa-cube',
        'fa-solid fa-objects-column',
        'fa-solid fa-object-group',
        'fa-solid fa-palette',
        'fa-solid fa-brush',
        'fa-solid fa-swatchbook',
        'fa-solid fa-droplet',
        'fa-solid fa-fill-drip',
        'fa-solid fa-highlighter',
        'fa-solid fa-pen-paintbrush',
        'fa-solid fa-image',
        'fa-solid fa-images',
        'fa-solid fa-camera',
        'fa-solid fa-camera-retro',
        'fa-solid fa-clapperboard',
        'fa-solid fa-film',
        'fa-solid fa-photo-film',
        'fa-solid fa-video',
        'fa-solid fa-video-plus',
        'fa-solid fa-photo-film',
        'fa-solid fa-microphone',
        'fa-solid fa-headphones',
        'fa-solid fa-music-note',
        'fa-solid fa-music',
        'fa-solid fa-podcast',
        'fa-solid fa-radio',
        'fa-solid fa-speaker',
        'fa-solid fa-chart-column',
        'fa-solid fa-chart-line',
        'fa-solid fa-chart-pie',
        'fa-solid fa-diagram-project',
        'fa-solid fa-chart-simple',
        'fa-solid fa-gauge-high',
        'fa-solid fa-signal-stream',
        'fa-solid fa-arrow-trend-up',
        'fa-solid fa-arrow-trend-down',
        'fa-solid fa-chart-scatter',
        'fa-solid fa-chart-line',
        'fa-solid fa-trophy',
        'fa-solid fa-medal',
        'fa-solid fa-crown',
        'fa-solid fa-gem',
        'fa-solid fa-diamond',
        'fa-solid fa-trophy-star',
        'fa-solid fa-ranking-star',
        'fa-solid fa-shield-check',
        'fa-solid fa-circle-check',
        'fa-solid fa-circle-info',
        'fa-solid fa-circle-question',
        'fa-solid fa-circle-exclamation',
        'fa-solid fa-lock',
        'fa-solid fa-key',
        'fa-solid fa-fingerprint',
        'fa-solid fa-shield-keyhole',
        'fa-solid fa-user',
        'fa-solid fa-users',
        'fa-solid fa-user-pen',
        'fa-solid fa-user-group',
        'fa-solid fa-user-gear',
        'fa-solid fa-address-card',
        'fa-solid fa-id-badge',
        'fa-solid fa-user-tie',
        'fa-solid fa-user-doctor',
        'fa-solid fa-user-graduate',
        'fa-solid fa-briefcase',
        'fa-solid fa-building',
        'fa-solid fa-buildings',
        'fa-solid fa-store',
        'fa-solid fa-cart-shopping',
        'fa-solid fa-bag-shopping',
        'fa-solid fa-basket-shopping',
        'fa-solid fa-shop',
        'fa-solid fa-shop-lock',
        'fa-solid fa-cash-register',
        'fa-solid fa-credit-card',
        'fa-solid fa-wallet',
        'fa-solid fa-coins',
        'fa-solid fa-gift',
        'fa-solid fa-ticket',
        'fa-solid fa-ticket-simple',
        'fa-solid fa-leaf',
        'fa-solid fa-seedling',
        'fa-solid fa-tree',
        'fa-solid fa-flower',
        'fa-solid fa-sprout',
        'fa-solid fa-mountain-sun',
        'fa-solid fa-mug-hot',
        'fa-solid fa-burger',
        'fa-solid fa-pizza-slice',
        'fa-solid fa-ice-cream',
        'fa-solid fa-cookie',
        'fa-solid fa-cake-candles',
        'fa-solid fa-utensils',
        'fa-solid fa-coffee-beans',
        'fa-solid fa-bowl-food',
        'fa-solid fa-salad',
        'fa-solid fa-wine-glass',
        'fa-solid fa-glass-water',
        'fa-solid fa-laptop',
        'fa-solid fa-desktop',
        'fa-solid fa-mobile-screen',
        'fa-solid fa-tablet-screen',
        'fa-solid fa-keyboard',
        'fa-solid fa-mouse',
        'fa-solid fa-server',
        'fa-solid fa-database',
        'fa-solid fa-code',
        'fa-solid fa-terminal',
        'fa-solid fa-brackets-curly',
        'fa-solid fa-window',
        'fa-solid fa-microchip',
        'fa-solid fa-robot',
        'fa-solid fa-cpu',
        'fa-solid fa-bug',
        'fa-solid fa-shield-halved',
        'fa-solid fa-plug',
        'fa-solid fa-wifi',
        'fa-solid fa-satellite-dish',
        'fa-solid fa-cloud-arrow-up',
        'fa-solid fa-cloud-arrow-down',
        'fa-solid fa-envelope',
        'fa-solid fa-envelopes',
        'fa-solid fa-inbox',
        'fa-solid fa-paper-plane',
        'fa-solid fa-message',
        'fa-solid fa-messages',
        'fa-solid fa-comment',
        'fa-solid fa-comments',
        'fa-solid fa-comment-dots',
        'fa-solid fa-comment-lines',
        'fa-solid fa-phone',
        'fa-solid fa-phone-volume',
        'fa-solid fa-calendar',
        'fa-solid fa-calendar-days',
        'fa-solid fa-clock',
        'fa-solid fa-hourglass-half',
        'fa-solid fa-stopwatch',
        'fa-solid fa-timer',
        'fa-solid fa-alarm-clock',
        'fa-solid fa-list',
        'fa-solid fa-list-tree',
        'fa-solid fa-check-double',
        'fa-solid fa-bars-progress',
        'fa-solid fa-sliders',
        'fa-solid fa-gear',
        'fa-solid fa-gears',
        'fa-solid fa-wrench',
        'fa-solid fa-screwdriver-wrench',
        'fa-solid fa-toolbox',
        'fa-solid fa-search',
        'fa-solid fa-magnifying-glass',
        'fa-solid fa-binoculars',
        'fa-solid fa-eye',
        'fa-solid fa-eye-slash',
        'fa-solid fa-trash',
        'fa-solid fa-trash-can',
        'fa-solid fa-box-archive',
        'fa-solid fa-download',
        'fa-solid fa-upload',
        'fa-solid fa-arrows-rotate',
        'fa-solid fa-share-nodes',
        'fa-solid fa-square-plus',
        'fa-solid fa-circle-plus',
        'fa-solid fa-plus',
        'fa-solid fa-minus',
        'fa-solid fa-xmark',
        'fa-solid fa-arrow-up-right-from-square',
        'fa-solid fa-up-right-and-down-left-from-center',
    ];

    $defaultStyleOptions = [
        ['value' => 'fa-solid', 'label' => 'Solid'],
        ['value' => 'fa-regular', 'label' => 'Regular'],
        ['value' => 'fa-brands', 'label' => 'Brands'],
    ];

    $normalizedIcons = collect(count($icons) ? $icons : $defaultIcons)
        ->map(fn ($icon) => trim((string) $icon))
        ->filter()
        ->unique()
        ->values();

    $normalizedStyleOptions = collect(count($styleOptions) ? $styleOptions : $defaultStyleOptions)
        ->map(function ($option) {
            if (is_string($option)) {
                return ['value' => $option, 'label' => str_replace('fa-', '', $option)];
            }

            return [
                'value' => (string) ($option['value'] ?? 'fa-solid'),
                'label' => (string) ($option['label'] ?? str_replace('fa-', '', (string) ($option['value'] ?? 'fa-solid'))),
            ];
        })
        ->filter(fn ($option) => $option['value'] !== '')
        ->unique('value')
        ->values();

    $specialFamilyIcons = [
        'fa-brands' => [
            'fa-solid fa-github',
            'fa-solid fa-facebook',
            'fa-solid fa-instagram',
            'fa-solid fa-linkedin',
            'fa-solid fa-x-twitter',
            'fa-solid fa-youtube',
            'fa-solid fa-tiktok',
            'fa-solid fa-discord',
            'fa-solid fa-telegram',
            'fa-solid fa-whatsapp',
            'fa-solid fa-pinterest',
            'fa-solid fa-dribbble',
        ],
        'fa-graphite fa-thin' => [
            'fa-solid fa-hashtag',
            'fa-solid fa-tag',
            'fa-solid fa-bookmark',
            'fa-solid fa-link',
            'fa-solid fa-folder',
            'fa-solid fa-file-lines',
            'fa-solid fa-book',
            'fa-solid fa-pen',
            'fa-solid fa-star',
            'fa-solid fa-heart',
            'fa-solid fa-bell',
            'fa-solid fa-globe',
            'fa-solid fa-shapes',
            'fa-solid fa-image',
            'fa-solid fa-chart-column',
            'fa-solid fa-user',
            'fa-solid fa-briefcase',
            'fa-solid fa-leaf',
            'fa-solid fa-calendar',
            'fa-solid fa-gear',
            'fa-solid fa-search',
            'fa-solid fa-trash',
            'fa-solid fa-download',
            'fa-solid fa-plus',
            'fa-solid fa-minus',
            'fa-solid fa-xmark',
        ],
        'fa-jelly fa-regular' => [
            'fa-solid fa-face-smile',
            'fa-solid fa-face-laugh',
            'fa-solid fa-heart',
            'fa-solid fa-star',
            'fa-solid fa-sparkles',
            'fa-solid fa-bell',
            'fa-solid fa-gift',
            'fa-solid fa-ice-cream',
            'fa-solid fa-cookie',
            'fa-solid fa-cake-candles',
            'fa-solid fa-mug-hot',
            'fa-solid fa-burger',
            'fa-solid fa-pizza-slice',
            'fa-solid fa-leaf',
            'fa-solid fa-flower',
            'fa-solid fa-rainbow',
            'fa-solid fa-cloud',
            'fa-solid fa-rainbow',
        ],
        'fa-jelly-fill fa-regular' => [
            'fa-solid fa-heart',
            'fa-solid fa-star',
            'fa-solid fa-bell',
            'fa-solid fa-gift',
            'fa-solid fa-cookie',
            'fa-solid fa-cake-candles',
            'fa-solid fa-mug-hot',
            'fa-solid fa-burger',
            'fa-solid fa-pizza-slice',
            'fa-solid fa-flower',
            'fa-solid fa-cloud',
            'fa-solid fa-face-smile',
            'fa-solid fa-face-laugh',
        ],
        'fa-jelly-duo fa-regular' => [
            'fa-solid fa-heart',
            'fa-solid fa-star',
            'fa-solid fa-bell',
            'fa-solid fa-gift',
            'fa-solid fa-cookie',
            'fa-solid fa-cake-candles',
            'fa-solid fa-mug-hot',
            'fa-solid fa-burger',
            'fa-solid fa-pizza-slice',
            'fa-solid fa-flower',
            'fa-solid fa-cloud',
            'fa-solid fa-face-smile',
            'fa-solid fa-face-laugh',
        ],
        'fa-chisel fa-regular' => [
            'fa-solid fa-star',
            'fa-solid fa-crown',
            'fa-solid fa-gem',
            'fa-solid fa-medal',
            'fa-solid fa-trophy',
            'fa-solid fa-shield-check',
            'fa-solid fa-key',
            'fa-solid fa-lock',
            'fa-solid fa-book',
            'fa-solid fa-feather',
            'fa-solid fa-pen-nib',
            'fa-solid fa-fire',
            'fa-solid fa-shield-check',
            'fa-solid fa-medal',
        ],
        'fa-etch fa-solid' => [
            'fa-solid fa-hashtag',
            'fa-solid fa-tag',
            'fa-solid fa-bookmark',
            'fa-solid fa-link',
            'fa-solid fa-folder',
            'fa-solid fa-file-lines',
            'fa-solid fa-book',
            'fa-solid fa-pen',
            'fa-solid fa-star',
            'fa-solid fa-heart',
            'fa-solid fa-bell',
            'fa-solid fa-globe',
            'fa-solid fa-gear',
            'fa-solid fa-shapes',
        ],
        'fa-notdog fa-solid' => [
            'fa-solid fa-face-smile',
            'fa-solid fa-heart',
            'fa-solid fa-star',
            'fa-solid fa-bell',
            'fa-solid fa-gift',
            'fa-solid fa-burger',
            'fa-solid fa-pizza-slice',
            'fa-solid fa-cookie',
            'fa-solid fa-cake-candles',
            'fa-solid fa-mug-hot',
        ],
        'fa-notdog-duo fa-solid' => [
            'fa-solid fa-face-smile',
            'fa-solid fa-heart',
            'fa-solid fa-star',
            'fa-solid fa-bell',
            'fa-solid fa-gift',
            'fa-solid fa-burger',
            'fa-solid fa-pizza-slice',
            'fa-solid fa-cookie',
            'fa-solid fa-cake-candles',
            'fa-solid fa-mug-hot',
        ],
        'fa-thumbprint fa-solid' => [
            'fa-solid fa-fingerprint',
            'fa-solid fa-shield-keyhole',
            'fa-solid fa-lock',
            'fa-solid fa-key',
            'fa-solid fa-user',
            'fa-solid fa-user-group',
            'fa-solid fa-circle-check',
            'fa-solid fa-circle-info',
            'fa-solid fa-circle-question',
            'fa-solid fa-eye',
            'fa-solid fa-eye-slash',
            'fa-solid fa-certificate',
            'fa-solid fa-user',
            'fa-solid fa-user-group',
        ],
        'fa-utility fa-semibold' => [
            'fa-solid fa-gear',
            'fa-solid fa-sliders',
            'fa-solid fa-circle-check',
            'fa-solid fa-circle-info',
            'fa-solid fa-circle-exclamation',
            'fa-solid fa-search',
            'fa-solid fa-plus',
        ],
        'fa-utility-fill fa-semibold' => [
            'fa-solid fa-gear',
            'fa-solid fa-sliders',
            'fa-solid fa-circle-check',
            'fa-solid fa-circle-info',
            'fa-solid fa-circle-exclamation',
            'fa-solid fa-search',
            'fa-solid fa-plus',
        ],
        'fa-utility-duo fa-semibold' => [
            'fa-solid fa-gear',
            'fa-solid fa-sliders',
            'fa-solid fa-circle-check',
            'fa-solid fa-circle-info',
            'fa-solid fa-circle-exclamation',
            'fa-solid fa-search',
            'fa-solid fa-plus',
        ],
        'fa-whiteboard fa-semibold' => [
            'fa-solid fa-lightbulb',
            'fa-solid fa-bullhorn',
            'fa-solid fa-megaphone',
            'fa-solid fa-bookmark',
            'fa-solid fa-thumbtack',
            'fa-solid fa-pen',
            'fa-solid fa-pen-to-square',
            'fa-solid fa-pencil',
            'fa-solid fa-shapes',
            'fa-solid fa-grid-2',
            'fa-solid fa-layer-group',
            'fa-solid fa-star',
            'fa-solid fa-note-sticky',
            'fa-solid fa-thumbtack',
        ],
        'fa-slab fa-regular' => [
            'fa-solid fa-book',
            'fa-solid fa-book-open',
            'fa-solid fa-book-atlas',
            'fa-solid fa-newspaper',
            'fa-solid fa-file-lines',
            'fa-solid fa-folder',
            'fa-solid fa-tag',
            'fa-solid fa-bookmark',
            'fa-solid fa-feather',
            'fa-solid fa-pen-nib',
        ],
        'fa-slab-press fa-regular' => [
            'fa-solid fa-book',
            'fa-solid fa-book-open',
            'fa-solid fa-book-atlas',
            'fa-solid fa-newspaper',
            'fa-solid fa-file-lines',
            'fa-solid fa-folder',
            'fa-solid fa-tag',
            'fa-solid fa-bookmark',
            'fa-solid fa-feather',
            'fa-solid fa-pen-nib',
        ],
    ];

    $initialValue = old($name ?? '', $value ?: $placeholder);

    $allAttributes = $attributes->getAttributes();

    $modelAttributeKeys = array_values(array_filter(
        array_keys($allAttributes),
        fn (string $key): bool => str_starts_with($key, 'wire:model')
    ));

    $keyAttribute = $attributes->get('wire:key');

    $modelAttributes = new \Illuminate\View\ComponentAttributeBag(
        \Illuminate\Support\Arr::only($allAttributes, $modelAttributeKeys)
    );

    $inputAttributes = new \Illuminate\View\ComponentAttributeBag(
        \Illuminate\Support\Arr::except($allAttributes, array_merge(['class', 'wire:key'], $modelAttributeKeys))
    );
@endphp

<x-ui.field :label="$label" :help="$help" :error="$error" {{ $attributes->only('class') }}>
    <div
        @if ($keyAttribute) wire:key="{{ $keyAttribute }}" @endif
        x-data="{
            search: '',
            icon: @js($initialValue),
            editing: false,
            icons: @js($normalizedIcons->all()),
            styleOptions: @js($normalizedStyleOptions->all()),
            specialFamilyIcons: @js($specialFamilyIcons),
            iconStyle: 'fa-solid',
            iconName: 'fa-hashtag',
            init() {
                this.parseIcon(this.icon || '{{ $placeholder }}');

                // x-ref targets are only registered once this scope's children are
                // initialised, so the live value has to be picked up afterwards.
                setTimeout(() => {
                    const source = this.$refs.input;

                    if (!source) {
                        return;
                    }

                    const current = source.value || source.getAttribute('value') || '';

                    if (current && current !== this.icon) {
                        this.parseIcon(current);
                    }

                    this.syncInput(false);
                    this.observeSource();
                }, 0);
            },
            observeSource() {
                const source = this.$refs.input;

                if (!source) {
                    return;
                }

                this.sourceObserver?.disconnect();
                this.sourceObserver = new MutationObserver(() => {
                    const next = source.getAttribute('value') || '';

                    // While the user is typing the server value is always one
                    // keystroke behind, so it must never overwrite the input.
                    if (!next || next === this.icon || this.editing) {
                        return;
                    }

                    this.parseIcon(next);
                    source.value = this.resolvedIcon();
                });

                this.sourceObserver.observe(source, { attributes: true, attributeFilter: ['value'] });
            },
            choose(value) {
                const nextName = this.shortLabel(value) || this.iconName || 'fa-hashtag';

                if (nextName === this.iconName) {
                    this.syncInput(true);
                    return;
                }

                this.iconName = nextName;
                this.applyIcon(`${this.iconStyle} ${nextName}`.trim());
                this.syncInput(true);
            },
            applyIcon(value) {
                this.icon = value;

                // Alpine never writes a model value into a focused input, so the
                // field is updated directly to keep it in sync with the picker.
                if (this.$refs.visible) {
                    this.$refs.visible.value = value;
                }
            },
            parseIcon(value) {
                const text = (value || '').toString();
                const raw = text.trim();
                const parts = raw.split(/\s+/).filter(Boolean);
                const styleMatch = this.styleOptions
                    .slice()
                    .sort((a, b) => b.value.split(' ').length - a.value.split(' ').length)
                    .find((option) => {
                        const styleParts = option.value.split(' ');
                        return styleParts.every((stylePart) => parts.includes(stylePart));
                    });
                const styleParts = styleMatch ? styleMatch.value.split(' ') : [];
                const iconPart = parts.find((part) => part.startsWith('fa-') && !styleParts.includes(part));

                this.iconStyle = styleMatch ? styleMatch.value : (this.iconStyle || 'fa-solid');
                this.iconName = iconPart || this.iconName || 'fa-hashtag';

                // `icon` always mirrors the exact text of the field (trimming it
                // would swallow a space the user just typed), so typing is never
                // overwritten by the normalised value.
                this.icon = text;
            },
            resolvedIcon() {
                if (! (this.icon || '').trim()) {
                    return '';
                }

                return `${this.iconStyle} ${this.iconName}`.trim();
            },
            syncInput(shouldDispatch = true) {
                const nextIcon = `${this.iconStyle} ${this.iconName}`.trim();

                if (!this.$refs.input) {
                    return;
                }

                this.$refs.input.value = nextIcon;

                if (!shouldDispatch) {
                    return;
                }

                this.$refs.input.dispatchEvent(new InputEvent('input', { bubbles: true }));
                this.$refs.input.dispatchEvent(new Event('change', { bubbles: true }));
            },
            setStyle(value) {
                if (this.iconStyle === value) {
                    return;
                }

                this.iconStyle = value;
                this.applyIcon(`${value} ${this.iconName}`.trim());
                this.syncInput(true);
            },
            filteredIcons() {
                let source = this.specialFamilyIcons[this.iconStyle] || this.icons;
                if (this.search.trim() === '') return source;
                const keyword = this.search.trim().toLowerCase();
                return source.filter((icon) => {
                    const full = icon.toLowerCase();
                    const short = this.shortLabel(icon).toLowerCase();
                    return full.includes(keyword) || short.includes(keyword);
                });
            },
            shortLabel(value) {
                return value
                    .split(/\s+/)
                    .filter((part) => part.startsWith('fa-') && !this.styleOptions.some((option) => option.value.split(' ').includes(part)))
                    .join(' ');
            },
            iconClass(value) {
                return `${this.iconStyle} ${this.shortLabel(value)}`.trim();
            },
        }"
        class="space-y-3"
    >
        <input
            x-ref="input"
            type="hidden"
            @if ($name) name="{{ $name }}" @endif
            value="{{ $initialValue }}"
            {{ $inputAttributes }}
            {{ $modelAttributes }}
        >

        <div class="flex items-center gap-3">
            <div class="flex h-11 w-14 shrink-0 items-center justify-center rounded-[0.95rem] border shadow-[0_1px_2px_rgba(15,23,42,0.04)]" style="border-color: var(--theme-border-color); background-color: color-mix(in srgb, var(--theme-surface-base) 98%, transparent); color: {{ $previewColor }};">
                <i x-bind:class="resolvedIcon() || '{{ $placeholder }}'" class="text-lg"></i>
            </div>
            <x-ui.input
                x-ref="visible"
                x-model="icon"
                x-on:focus="editing = true"
                x-on:blur="editing = false; applyIcon(resolvedIcon())"
                x-on:input="parseIcon($event.target.value); syncInput()"
                class="flex-1"
            />
        </div>

        <x-ui.modal
            :title="$dialogTitle ?: __('Choose an icon')"
            :description="$dialogDescription ?: __('Select a Font Awesome icon or search by keyword.')"
            width="xl"
            dismissible
        >
            <x-slot:trigger>
                <x-ui.button type="button" variant="outline" class="w-full justify-center">
                    <i class="fa-solid fa-icons"></i>
                    {{ __('Browse Font Awesome icons') }}
                </x-ui.button>
            </x-slot:trigger>

            <div class="space-y-4">
                <div class="flex flex-wrap gap-2">
                    <template x-for="option in styleOptions" :key="option.value">
                        <button
                            type="button"
                            x-on:click="setStyle(option.value)"
                            class="rounded-full border px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.14em] transition"
                            :style="iconStyle === option.value
                                ? 'border-color: rgba(var(--theme-accent-rgb), 0.28); background-color: rgba(var(--theme-accent-rgb), 0.08); color: var(--theme-accent);'
                                : 'border-color: var(--theme-border-color); background-color: color-mix(in srgb, var(--theme-surface-base) 98%, transparent); color: var(--theme-muted-text-color);'"
                            x-text="option.label"
                        ></button>
                    </template>
                </div>

                <x-ui.icon-input
                    x-model="search"
                    icon="fa-solid fa-magnifying-glass"
                    :placeholder="__('Search icons...')"
                />

                <div class="grid grid-cols-3 gap-3 sm:grid-cols-4 lg:grid-cols-6">
                    <template x-for="presetIcon in filteredIcons()" :key="presetIcon">
                        <button
                            type="button"
                            x-on:click="choose(presetIcon); open = false"
                            class="group flex flex-col items-center justify-center gap-2 rounded-[1rem] border px-3 py-4 text-center transition hover:-translate-y-0.5"
                            :style="iconName === shortLabel(presetIcon)
                                ? 'border-color: rgba(var(--theme-accent-rgb), 0.28); background-color: rgba(var(--theme-accent-rgb), 0.08);'
                                : 'border-color: var(--theme-border-color); background-color: color-mix(in srgb, var(--theme-surface-base) 97%, transparent);'"
                        >
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl" style="background-color: rgba(var(--theme-accent-rgb), 0.10); color: {{ $previewColor }};">
                                <i :class="iconClass(presetIcon)" class="text-base"></i>
                            </span>
                            <span class="text-[11px] font-medium leading-4" style="color: var(--theme-muted-text-color);" x-text="shortLabel(presetIcon)"></span>
                        </button>
                    </template>
                </div>

                <p x-show="filteredIcons().length === 0" class="text-sm" style="color: var(--theme-muted-text-color);">
                    {{ __('No icons match your search.') }}
                </p>
            </div>
        </x-ui.modal>
    </div>
</x-ui.field>
