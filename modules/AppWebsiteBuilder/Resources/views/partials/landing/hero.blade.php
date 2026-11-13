@php
    $heroStyle = (string) ($layout['hero'] ?? 'split');
    $heroSkin = preg_replace('/[^a-z0-9-]/', '', strtolower((string) ($visualSkin ?? ($layout['skin'] ?? 'modern'))));
    $heroUsesDefaultOverlay = $heroSkin === '' || $heroSkin === 'modern';
@endphp

<section id="home" class="relative overflow-hidden bg-slate-900 text-white">
    @if ($heroBackgroundImage !== '')
        <img src="{{ $heroBackgroundImage }}" alt="{{ data_get($hero, 'image_alt', $siteName) }}" class="absolute inset-0 h-full w-full object-cover" loading="eager" decoding="async" referrerpolicy="no-referrer" onerror="this.onerror=null;this.src=@js($imageFallbacks['hero'] ?? '');" />
        <div @class([
            'lp-hero-overlay absolute inset-0',
            'lp-hero-overlay--banner' => $heroStyle === 'banner',
            'lp-hero-overlay--default' => $heroStyle !== 'banner',
            'bg-gradient-to-b from-slate-950/85 via-slate-900/70 to-slate-900/50' => $heroUsesDefaultOverlay && $heroStyle !== 'banner',
            'bg-gradient-to-r from-slate-950/90 via-slate-900/60 to-slate-900/20' => $heroUsesDefaultOverlay && $heroStyle === 'banner',
        ])></div>
    @else
        <div class="absolute inset-0" style="background: linear-gradient(145deg, {{ $brand }}, #0f172a);"></div>
    @endif

    @if ($heroStyle === 'banner')
        <div class="relative px-4 py-16 sm:px-6 sm:py-20 lg:py-24">
            <div class="mx-auto max-w-4xl space-y-4">
                @if ($tagline !== '')
                    <p class="text-sm font-semibold uppercase tracking-widest text-white/75">{{ $tagline }}</p>
                @endif
                <h1 class="text-3xl font-bold leading-tight sm:text-5xl lg:text-6xl">
                    {{ data_get($hero, 'headline', __('Welcome to :name', ['name' => $siteName])) }}
                </h1>
                @if (filled(data_get($hero, 'subheadline')))
                    <p class="max-w-2xl text-base leading-relaxed text-white/85">{{ data_get($hero, 'subheadline') }}</p>
                @endif
                @if (filled(data_get($hero, 'cta_text')))
                    <a href="#contact" class="lp-cta inline-flex px-8 py-3.5 text-sm font-semibold text-white shadow-lg" style="background: {{ $brand }};">
                        {{ data_get($hero, 'cta_text') }}
                    </a>
                @endif
            </div>
        </div>
    @elseif ($heroStyle === 'center')
        <div class="relative px-4 py-14 sm:px-6 sm:py-20 lg:py-28">
            <div class="mx-auto max-w-3xl space-y-5 text-center">
                @if ($tagline !== '')
                    <p class="text-sm font-medium text-white/75">{{ $tagline }}</p>
                @endif
                <h1 class="text-3xl font-bold leading-tight sm:text-4xl lg:text-5xl">
                    {{ data_get($hero, 'headline', __('Welcome to :name', ['name' => $siteName])) }}
                </h1>
                @if (filled(data_get($hero, 'subheadline')))
                    <p class="mx-auto max-w-2xl text-sm leading-relaxed text-white/85 sm:text-base">{{ data_get($hero, 'subheadline') }}</p>
                @endif
                @if (filled(data_get($hero, 'cta_text')))
                    <a href="#contact" class="lp-cta inline-flex px-6 py-3 text-sm font-semibold text-white shadow-lg" style="background: {{ $brand }};">
                        {{ data_get($hero, 'cta_text') }}
                    </a>
                @endif
            </div>
        </div>
    @elseif ($heroStyle === 'overlap')
        <div class="relative px-4 py-14 sm:px-6 sm:py-16">
            <div class="lp-hero-overlap mx-auto max-w-5xl space-y-6">
                <div class="max-w-xl min-w-0 space-y-4">
                    @if ($tagline !== '')
                        <p class="text-sm font-medium text-white/80">{{ $tagline }}</p>
                    @endif
                    <h1 class="text-3xl font-black leading-tight sm:text-5xl">
                        {{ data_get($hero, 'headline', __('Welcome to :name', ['name' => $siteName])) }}
                    </h1>
                </div>
                @if ($heroFeatureImage !== '')
                    <div class="lp-hero-overlap-media min-w-0">
                        <div class="overflow-hidden rounded-2xl border border-white/25 shadow-2xl sm:rounded-3xl sm:border-4 sm:border-white/30">
                            <img src="{{ $heroFeatureImage }}" alt="" class="lp-hero-overlap-img aspect-[4/3] w-full object-cover" loading="eager" decoding="async" referrerpolicy="no-referrer" onerror="this.onerror=null;this.src=@js($imageFallbacks['feature'] ?? '');" />
                        </div>
                    </div>
                @endif
                <div class="lp-hero-overlap-copy min-w-0 space-y-4">
                    @if (filled(data_get($hero, 'subheadline')))
                        <p class="max-w-2xl text-sm leading-relaxed text-white/90 sm:text-base">{{ data_get($hero, 'subheadline') }}</p>
                    @endif
                    @if (filled(data_get($hero, 'cta_text')))
                        <a href="#contact" class="lp-cta inline-flex px-6 py-3 text-sm font-semibold text-white shadow-lg" style="background: {{ $brand }};">
                            {{ data_get($hero, 'cta_text') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="relative px-4 py-14 sm:px-6 sm:py-20 lg:py-24">
            <div class="lp-grid-2 mx-auto grid max-w-5xl gap-8">
                <div class="space-y-5">
                    @if ($tagline !== '')
                        <p class="text-sm font-medium text-white/75">{{ $tagline }}</p>
                    @endif
                    <h1 class="text-3xl font-bold leading-tight sm:text-4xl lg:text-5xl">
                        {{ data_get($hero, 'headline', __('Welcome to :name', ['name' => $siteName])) }}
                    </h1>
                    @if (filled(data_get($hero, 'subheadline')))
                        <p class="max-w-xl text-sm leading-relaxed text-white/85 sm:text-base">{{ data_get($hero, 'subheadline') }}</p>
                    @endif
                    @if (filled(data_get($hero, 'cta_text')))
                        <a href="#contact" class="lp-cta inline-flex px-6 py-3 text-sm font-semibold text-white shadow-lg" style="background: {{ $brand }};">
                            {{ data_get($hero, 'cta_text') }}
                        </a>
                    @endif
                </div>
                @if ($heroFeatureImage !== '')
                    <div class="lp-hero-aside overflow-hidden rounded-2xl border border-white/20 shadow-2xl">
                        <img src="{{ $heroFeatureImage }}" alt="" class="aspect-[4/3] w-full object-cover lg:aspect-square" loading="eager" decoding="async" referrerpolicy="no-referrer" onerror="this.onerror=null;this.src=@js($imageFallbacks['feature'] ?? '');" />
                    </div>
                @endif
            </div>
        </div>
    @endif
</section>
