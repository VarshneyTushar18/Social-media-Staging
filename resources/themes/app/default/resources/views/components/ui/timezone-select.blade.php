@props([
    'label' => null,
    'help' => null,
    'error' => null,
    'name' => 'timezone',
    'value' => null,
    'placeholder' => null,
])

@php
    $selected = (string) old($name, $value ?? '');
    $options = timezone_select_options();
    $placeholderText = $placeholder ?: __('Select timezone');
    $match = collect($options)->firstWhere('value', $selected);
    $initialLabel = is_array($match) ? (string) ($match['label'] ?? $placeholderText) : $placeholderText;

    $wireProperty = null;
    foreach ($attributes->getAttributes() as $attributeKey => $attributeValue) {
        if (str_starts_with((string) $attributeKey, 'wire:model')) {
            $wireProperty = (string) $attributeValue;
            break;
        }
    }

    $wrapperAttributes = $attributes
        ->whereDoesntStartWith('wire:model')
        ->only('class');
@endphp

<div {{ $wrapperAttributes->class('space-y-2.5') }}>
    @if ($label)
        <x-ui.label>{{ $label }}</x-ui.label>
    @endif

    <div
        wire:ignore.self
        class="relative"
        x-data="{
            open: false,
            value: @js($selected),
            label: @js($initialLabel),
            placeholder: @js($placeholderText),
            options: @js($options),
            wireProperty: @js($wireProperty),
            search: '',
            filtered() {
                const query = String(this.search || '').trim().toLowerCase();

                if (query === '') {
                    return this.options;
                }

                return this.options.filter((option) => {
                    return String(option.label || '').toLowerCase().includes(query)
                        || String(option.value || '').toLowerCase().includes(query);
                });
            },
            syncHidden() {
                if (! this.$refs.hiddenInput) {
                    return;
                }

                this.$refs.hiddenInput.value = this.value;
                this.$refs.hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                this.$refs.hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
            },
            syncLivewire() {
                if (! this.wireProperty || typeof this.$wire === 'undefined' || typeof this.$wire.set !== 'function') {
                    return;
                }

                this.$wire.set(this.wireProperty, this.value);
            },
            choose(option) {
                this.value = option.value;
                this.label = option.label;
                this.search = '';
                this.open = false;
                this.syncHidden();
                this.syncLivewire();
            },
            clearChoice() {
                this.value = '';
                this.label = this.placeholder;
                this.search = '';
                this.open = false;
                this.syncHidden();
                this.syncLivewire();
            },
            toggle() {
                this.open = !this.open;

                if (! this.open) {
                    this.search = '';

                    return;
                }

                this.search = '';
                this.$nextTick(() => {
                    this.$refs.searchInput?.focus();
                    this.scrollToValue(this.value);
                });
            },
            close() {
                this.open = false;
                this.search = '';
            },
            scrollToValue(value) {
                if (! value || ! this.$refs.list) {
                    return;
                }

                this.$refs.list.querySelector('[data-value=\'' + CSS.escape(value) + '\']')?.scrollIntoView({ block: 'nearest' });
            },
        }"
        x-on:click.outside="close()"
    >
        @if ($name)
            <input x-ref="hiddenInput" type="hidden" name="{{ $name }}" value="{{ $selected }}">
        @endif

        <button
            type="button"
            class="flex h-11 w-full appearance-none items-center border px-4 pr-14 text-left text-sm font-medium shadow-[0_1px_2px_rgba(15,23,42,0.04)] outline-none transition duration-200 focus:border-[var(--theme-accent)] focus:ring-4 focus:ring-[color:rgba(var(--theme-accent-rgb),0.10)]"
            style="border-radius: var(--theme-input-radius, 0.65rem); border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);"
            x-bind:aria-expanded="open"
            x-on:click="toggle()"
        >
            <span class="block truncate" x-bind:class="value === '' ? 'opacity-60' : ''" x-text="label"></span>
        </button>

        <span class="pointer-events-none absolute inset-y-0 right-4 inline-flex items-center" style="color: var(--theme-muted-text-color);">
            <i class="fa-solid fa-chevron-down text-xs"></i>
        </span>

        <div
            x-cloak
            x-show="open"
            class="absolute left-0 right-0 bottom-[calc(100%+0.35rem)] z-[90] overflow-hidden border shadow-[0_-16px_40px_-24px_rgba(15,23,42,0.35)]"
            style="border-radius: var(--theme-input-radius, 0.65rem); border-color: var(--theme-border-color); background-color: var(--theme-input-surface);"
        >
            <div class="border-b p-2" style="border-color: var(--theme-border-color);">
                <input
                    x-ref="searchInput"
                    x-model="search"
                    type="search"
                    autocomplete="off"
                    class="h-9 w-full border px-3 text-sm font-medium outline-none transition duration-200 focus:border-[var(--theme-accent)] focus:ring-4 focus:ring-[color:rgba(var(--theme-accent-rgb),0.10)]"
                    style="border-radius: var(--theme-input-radius, 0.65rem); border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);"
                    placeholder="{{ __('Search timezone...') }}"
                    x-on:keydown.stop
                    x-on:click.stop
                >
            </div>

            <div x-ref="list" class="max-h-72 overflow-y-auto py-1">
                <button
                    type="button"
                    class="flex w-full px-4 py-2 text-left text-sm font-medium transition hover:bg-slate-900/5 dark:hover:bg-white/5"
                    style="color: var(--theme-muted-text-color);"
                    x-on:click="clearChoice()"
                >
                    {{ $placeholderText }}
                </button>
                <template x-for="option in filtered()" :key="option.value">
                    <button
                        type="button"
                        class="flex w-full px-4 py-2 text-left text-sm font-medium transition hover:bg-slate-900/5 dark:hover:bg-white/5"
                        x-bind:data-value="option.value"
                        x-bind:style="value === option.value ? 'color: var(--theme-accent);' : 'color: var(--theme-input-text);'"
                        x-on:click="choose(option)"
                        x-text="option.label"
                    ></button>
                </template>
                <p
                    x-show="filtered().length === 0"
                    class="px-4 py-4 text-center text-sm"
                    style="color: var(--theme-muted-text-color);"
                >
                    {{ __('No matching timezone.') }}
                </p>
            </div>
        </div>
    </div>

    @if ($error)
        <p class="text-sm font-medium" style="color: var(--theme-danger-color);">{{ $error }}</p>
    @elseif ($help)
        <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ $help }}</p>
    @endif
</div>
