@props([
    'previewUrl' => '',
    'previewFallback' => '',
    'templateName' => '',
])

<div {{ $attributes->merge(['class' => 'relative h-full w-full overflow-hidden bg-white']) }}>
    @if ($previewUrl !== '')
        <img
            src="{{ $previewUrl }}"
            alt="{{ $templateName !== '' ? $templateName : __('Template preview') }}"
            class="h-full w-full object-cover object-top"
            loading="lazy"
            referrerpolicy="no-referrer"
            decoding="async"
            onerror="if (!this.dataset.fb) { this.dataset.fb='1'; this.src=@js($previewFallback !== '' ? $previewFallback : $previewUrl); }"
        />
    @else
        <div class="flex h-full w-full items-center justify-center bg-slate-100 text-slate-400" aria-hidden="true">
            <i class="fa-solid fa-image text-2xl"></i>
        </div>
    @endif
</div>
