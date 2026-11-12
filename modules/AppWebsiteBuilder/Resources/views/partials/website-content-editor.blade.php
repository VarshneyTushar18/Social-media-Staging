<form wire:submit="saveContent" class="space-y-6">
    <div class="space-y-4">
        <p class="text-xs font-bold uppercase tracking-[0.2em]" style="color: var(--theme-accent);">{{ __('Brand') }}</p>
        <x-ui.input wire:model.live.debounce.400ms="siteName" :label="__('Site name')" />
        <x-ui.input wire:model.live.debounce.400ms="tagline" :label="__('Tagline')" />
        <x-ui.input wire:model.live.debounce.400ms="brandPrimary" type="text" :label="__('Brand color (hex)')" placeholder="#5b4dff" />
    </div>

    <div class="space-y-4 border-t pt-6" style="border-color: rgba(var(--theme-border-color-rgb), 0.5);">
        <p class="text-xs font-bold uppercase tracking-[0.2em]" style="color: var(--theme-accent);">{{ __('Hero') }}</p>
        <x-ui.input wire:model.live.debounce.400ms="heroHeadline" :label="__('Headline')" />
        <x-ui.textarea wire:model.live.debounce.400ms="heroSubheadline" :label="__('Subheadline')" rows="3" />
        <x-ui.input wire:model.live.debounce.400ms="heroCtaText" :label="__('Button text')" />
        <x-ui.input-file-picker
            wire:model.live="heroImageUrl"
            context="portal"
            pickerType="image"
            valueField="url"
            :label="__('Hero image URL')"
            :dialogTitle="__('Choose an image')"
            :dialogDescription="__('Upload to your file library or pick an existing image. The URL is stored on your site.')"
            :buttonLabel="__('Upload')"
            :placeholder="__('Image URL or choose from files')"
        />
    </div>

    <div class="space-y-4 border-t pt-6" style="border-color: rgba(var(--theme-border-color-rgb), 0.5);">
        <p class="text-xs font-bold uppercase tracking-[0.2em]" style="color: var(--theme-accent);">{{ __('About') }}</p>
        <x-ui.input wire:model.live.debounce.400ms="aboutHeadline" :label="__('Headline')" />
        <x-ui.textarea wire:model.live.debounce.400ms="aboutBody" :label="__('Body')" rows="6" />
        <x-ui.input-file-picker
            wire:model.live="aboutImageUrl"
            context="portal"
            pickerType="image"
            valueField="url"
            :label="__('About image URL')"
            :dialogTitle="__('Choose an image')"
            :dialogDescription="__('Upload to your file library or pick an existing image. The URL is stored on your site.')"
            :buttonLabel="__('Upload')"
            :placeholder="__('Image URL or choose from files')"
        />
    </div>

    <div class="space-y-4 border-t pt-6" style="border-color: rgba(var(--theme-border-color-rgb), 0.5);">
        <p class="text-xs font-bold uppercase tracking-[0.2em]" style="color: var(--theme-accent);">{{ __('Services') }}</p>
        <x-ui.input wire:model.live.debounce.400ms="servicesHeadline" :label="__('Section headline')" />
        @foreach ($serviceItems as $index => $item)
            <div class="space-y-3 rounded-xl border p-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.65);">
                <p class="text-xs font-semibold" style="color: var(--theme-muted-text-color);">{{ __('Service :n', ['n' => $index + 1]) }}</p>
                <x-ui.input wire:model.live.debounce.400ms="serviceItems.{{ $index }}.title" :label="__('Title')" />
                <x-ui.textarea wire:model.live.debounce.400ms="serviceItems.{{ $index }}.description" :label="__('Description')" rows="3" />
                <x-ui.input-file-picker
                    wire:model.live="serviceItems.{{ $index }}.image_url"
                    context="portal"
                    pickerType="image"
                    valueField="url"
                    :label="__('Image URL')"
                    :dialogTitle="__('Choose an image')"
                    :dialogDescription="__('Upload to your file library or pick an existing image. The URL is stored on your site.')"
                    :buttonLabel="__('Upload')"
                    :placeholder="__('Image URL or choose from files')"
                />
            </div>
        @endforeach
    </div>

    <div class="space-y-4 border-t pt-6" style="border-color: rgba(var(--theme-border-color-rgb), 0.5);">
        <p class="text-xs font-bold uppercase tracking-[0.2em]" style="color: var(--theme-accent);">{{ __('Content highlights') }}</p>
        <x-ui.input wire:model.live.debounce.400ms="highlightsHeadline" :label="__('Section headline')" />
        <x-ui.input wire:model.live.debounce.400ms="highlightsSubheadline" :label="__('Subheadline')" />
        @foreach ($highlightItems as $index => $item)
            <div class="space-y-3 rounded-xl border p-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.65);">
                <p class="text-xs font-semibold" style="color: var(--theme-muted-text-color);">{{ __('Highlight :n', ['n' => $index + 1]) }}</p>
                <x-ui.input wire:model.live.debounce.400ms="highlightItems.{{ $index }}.title" :label="__('Title')" />
                <x-ui.textarea wire:model.live.debounce.400ms="highlightItems.{{ $index }}.description" :label="__('Description')" rows="3" />
                <x-ui.input-file-picker
                    wire:model.live="highlightItems.{{ $index }}.image_url"
                    context="portal"
                    pickerType="image"
                    valueField="url"
                    :label="__('Image URL')"
                    :dialogTitle="__('Choose an image')"
                    :dialogDescription="__('Upload to your file library or pick an existing image. The URL is stored on your site.')"
                    :buttonLabel="__('Upload')"
                    :placeholder="__('Image URL or choose from files')"
                />
            </div>
        @endforeach
    </div>

    <div class="space-y-4 border-t pt-6" style="border-color: rgba(var(--theme-border-color-rgb), 0.5);">
        <p class="text-xs font-bold uppercase tracking-[0.2em]" style="color: var(--theme-accent);">{{ __('Contact') }}</p>
        <x-ui.input wire:model.live.debounce.400ms="contactHeadline" :label="__('Headline')" />
        <x-ui.textarea wire:model.live.debounce.400ms="contactBody" :label="__('Body')" rows="4" />
        <x-ui.input wire:model.live.debounce.400ms="contactEmail" :label="__('Email')" />
        <x-ui.input wire:model.live.debounce.400ms="contactPhone" :label="__('Phone')" />
        <x-ui.input-file-picker
            wire:model.live="contactImageUrl"
            context="portal"
            pickerType="image"
            valueField="url"
            :label="__('Contact image URL')"
            :dialogTitle="__('Choose an image')"
            :dialogDescription="__('Upload to your file library or pick an existing image. The URL is stored on your site.')"
            :buttonLabel="__('Upload')"
            :placeholder="__('Image URL or choose from files')"
        />
    </div>

    <div class="sticky bottom-0 flex justify-end border-t pt-4 backdrop-blur-sm" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background: color-mix(in srgb, var(--theme-surface-base) 88%, transparent);">
        <x-ui.button type="submit" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="saveContent">{{ __('Save changes') }}</span>
            <span wire:loading wire:target="saveContent">{{ __('Saving…') }}</span>
        </x-ui.button>
    </div>
</form>
