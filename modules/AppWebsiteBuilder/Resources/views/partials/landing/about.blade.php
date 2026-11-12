@php
    $aboutStyle = (string) ($layout['about'] ?? 'split');
    $imageFirst = $aboutStyle === 'split-reverse';
@endphp

<section id="about" class="scroll-mt-16 border-b border-slate-100 bg-slate-50">
    @if ($aboutStyle === 'stack')
        <div class="px-4 py-12 sm:px-6 sm:py-16">
            @if (filled(data_get($about, 'image_url')))
                <img src="{{ data_get($about, 'image_url') }}" alt="{{ data_get($about, 'image_alt', $siteName) }}" class="mx-auto mb-8 aspect-[21/9] max-w-5xl w-full rounded-2xl object-cover shadow-lg" loading="lazy" referrerpolicy="no-referrer" decoding="async" onerror="this.onerror=null;this.src=@js($imageFallbacks['about'] ?? '');" />
            @endif
            <div class="mx-auto max-w-3xl space-y-4 text-center">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500">{{ __('About') }}</p>
                <h2 class="text-2xl font-bold text-slate-900 sm:text-3xl">{{ data_get($about, 'headline', __('About us')) }}</h2>
                <p class="text-sm leading-relaxed text-slate-600 sm:text-base whitespace-pre-line">{{ data_get($about, 'body', '') }}</p>
            </div>
        </div>
    @else
        <div @class([
            'lp-grid-2 grid gap-8 px-4 py-12 sm:px-6 sm:py-16 lg:gap-12',
            'max-w-5xl mx-auto' => true,
        ])>
            @if ($imageFirst && filled(data_get($about, 'image_url')))
                <img src="{{ data_get($about, 'image_url') }}" alt="{{ data_get($about, 'image_alt', $siteName) }}" class="aspect-[4/3] w-full rounded-2xl object-cover shadow-lg" loading="lazy" referrerpolicy="no-referrer" decoding="async" onerror="this.onerror=null;this.src=@js($imageFallbacks['about'] ?? '');" />
            @endif
            <div class="space-y-4">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500">{{ __('About') }}</p>
                <h2 class="text-2xl font-bold text-slate-900 sm:text-3xl">{{ data_get($about, 'headline', __('About us')) }}</h2>
                <p class="text-sm leading-relaxed text-slate-600 sm:text-base whitespace-pre-line">{{ data_get($about, 'body', '') }}</p>
            </div>
            @if (! $imageFirst && filled(data_get($about, 'image_url')))
                <img src="{{ data_get($about, 'image_url') }}" alt="{{ data_get($about, 'image_alt', $siteName) }}" class="aspect-[4/3] w-full rounded-2xl object-cover shadow-lg" loading="lazy" referrerpolicy="no-referrer" decoding="async" onerror="this.onerror=null;this.src=@js($imageFallbacks['about'] ?? '');" />
            @endif
        </div>
    @endif
</section>
