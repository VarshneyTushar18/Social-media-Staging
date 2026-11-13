@php
    $embedCardMode = (bool) ($embedCardMode ?? false);
    $brand = (string) data_get($content, 'brand_primary', '#5b4dff');
    $siteName = (string) data_get($content, 'site_name', $site->name);
    $tagline = (string) data_get($content, 'tagline', '');
    $heroBackgroundImage = (string) data_get($content, 'hero_image_url', '');
    $sections = (array) data_get($content, 'sections', []);

    $hero = collect($sections)->first(fn ($s) => strtolower((string) data_get($s, 'type')) === 'hero') ?? [];
    $heroFeatureImage = trim((string) data_get($hero, 'image_url', ''));
    if ($heroFeatureImage === ''
        || \Modules\AppWebsiteBuilder\Support\WebsiteLandingAssets::isSamePhoto($heroFeatureImage, $heroBackgroundImage)) {
        $heroFeatureImage = '';
    }
    $heroImage = $heroBackgroundImage;
    $about = collect($sections)->first(fn ($s) => strtolower((string) data_get($s, 'type')) === 'about') ?? [];
    $services = collect($sections)->first(fn ($s) => strtolower((string) data_get($s, 'type')) === 'services') ?? [];
    $highlights = collect($sections)->first(fn ($s) => strtolower((string) data_get($s, 'type')) === 'highlights') ?? [];
    $contact = collect($sections)->first(fn ($s) => strtolower((string) data_get($s, 'type')) === 'contact') ?? [];

    $imageFallbacks = \Modules\AppWebsiteBuilder\Support\WebsiteLandingAssets::landingImageFallbacks(
        $content,
        (string) data_get($content, 'site_type', $siteName),
    );
    $heroCta = (string) data_get($hero, 'cta_text', __('Get started'));

    $templateIdFromContent = (string) data_get($content, 'template_id', '');
    $isPromptSite = ($content['generation_mode'] ?? '') === 'prompt'
        || (($content['source'] ?? '') === 'ai' && filled($content['user_prompt'] ?? null));

    if (is_array($content['layout_config'] ?? null)) {
        $layout = $content['layout_config'];
    } elseif (($content['source'] ?? '') === 'ai' || ($content['generation_mode'] ?? '') === 'prompt') {
        $layout = \Modules\AppWebsiteBuilder\Support\WebsiteAiLayout::resolve(
            [],
            (string) data_get($content, 'site_type', data_get($site, 'site_type', '')),
            (string) data_get($content, 'user_prompt', ''),
        );
    } elseif ($templateIdFromContent !== '') {
        $layout = \Modules\AppWebsiteBuilder\Support\WebsiteTemplateLayouts::config($templateIdFromContent);
    } else {
        $layout = [
            'template_key' => 'classic-business',
            'skin' => 'corporate',
            'nav' => 'light',
            'hero' => 'split',
            'about' => 'split',
            'services' => 'image-cards',
            'highlights' => 'icon-cards',
            'contact' => 'gradient',
            'sections' => ['about', 'services', 'highlights', 'contact'],
        ];
    }

    if ($templateIdFromContent !== '') {
        $layout = array_merge(
            \Modules\AppWebsiteBuilder\Support\WebsiteTemplateLayouts::config($templateIdFromContent),
            $layout,
        );
    } elseif (empty($layout['skin'] ?? null) && $isPromptSite) {
        $layout['skin'] = \Modules\AppWebsiteBuilder\Support\WebsiteAiLayout::inferSkin(
            (string) data_get($content, 'user_prompt', ''),
            (string) data_get($content, 'site_type', $site->name ?? ''),
        );
    } elseif (empty($layout['skin'] ?? null)) {
        $layout['skin'] = 'modern';
    }

    $templateKey = (string) ($layout['template_key'] ?? $templateIdFromContent ?: 'default');
    $sectionOrder = (array) ($layout['sections'] ?? ['about', 'services', 'highlights', 'contact']);

    $theme = is_array($content['theme'] ?? null) ? $content['theme'] : [];
    $themeCssVars = $isPromptSite && $theme !== []
        ? \Modules\AppWebsiteBuilder\Support\WebsiteAiTheme::cssVariables($theme, $brand)
        : ['--lp-brand' => $brand];
    $themeClasses = $isPromptSite && $theme !== []
        ? \Modules\AppWebsiteBuilder\Support\WebsiteAiTheme::bodyClasses($theme)
        : [];

    if ($isPromptSite && $theme === []) {
        $theme = \Modules\AppWebsiteBuilder\Support\WebsiteAiTheme::resolve(
            [],
            (string) data_get($content, 'user_prompt', ''),
            $brand,
        );
        $themeCssVars = \Modules\AppWebsiteBuilder\Support\WebsiteAiTheme::cssVariables($theme, $brand);
        $themeClasses = \Modules\AppWebsiteBuilder\Support\WebsiteAiTheme::bodyClasses($theme);
    }

    $visualSkin = preg_replace('/[^a-z0-9-]/', '', strtolower((string) ($layout['skin'] ?? 'modern')));
    if ($visualSkin === '') {
        $visualSkin = 'modern';
    }

    $landingClasses = trim(
        'lp-landing min-h-full text-slate-900 lp-tpl-'
        .str_replace(['_', '.'], '-', $templateKey)
        .' lp-skin-'.$visualSkin
        .' '.implode(' ', $themeClasses)
        .($embedCardMode ? ' lp-embed-card' : '')
    );
    if (! in_array('lp-surface-dark', $themeClasses, true) && ! in_array('lp-surface-warm', $themeClasses, true) && ! in_array('lp-surface-cool', $themeClasses, true)) {
        $landingClasses .= ' bg-white';
    }

    $navLinks = [
        ['href' => '#home', 'label' => __('Home')],
        ['href' => '#about', 'label' => __('About')],
        ['href' => '#services', 'label' => __('Services')],
        ['href' => '#content', 'label' => __('Content')],
        ['href' => '#contact', 'label' => __('Contact')],
    ];
@endphp

@include('appwebsitebuilder::partials.landing.responsive-styles')
@include('appwebsitebuilder::partials.landing.template-skin-styles')
@if ($isPromptSite && $theme !== [])
    @include('appwebsitebuilder::partials.landing.prompt-theme-styles')
@endif

<div
    class="{{ $landingClasses }}"
    style="{{ collect($themeCssVars)->map(fn ($v, $k) => $k.': '.$v)->implode('; ') }}"
    @unless ($embedCardMode)
        x-data="{ mobileNav: false }"
        x-on:keydown.escape.window="mobileNav = false"
    @endunless
>
    @include('appwebsitebuilder::partials.landing.nav')

    @include('appwebsitebuilder::partials.landing.hero')

    @foreach ($sectionOrder as $sectionKey)
        @switch ($sectionKey)
            @case('about')
                @include('appwebsitebuilder::partials.landing.about')
                @break
            @case('services')
                @include('appwebsitebuilder::partials.landing.services')
                @break
            @case('highlights')
                @include('appwebsitebuilder::partials.landing.highlights')
                @break
            @case('contact')
                @include('appwebsitebuilder::partials.landing.contact')
                @break
        @endswitch
    @endforeach

    <footer class="border-t border-slate-200 bg-slate-900 px-4 py-10 text-white sm:px-6">
        <div class="mx-auto max-w-5xl">
            <div class="lp-footer-main flex flex-col gap-8">
                <div class="min-w-0 space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-sm font-bold text-white" style="background: {{ $brand }};">{{ strtoupper(substr($siteName, 0, 1)) }}</span>
                        <span class="text-lg font-bold">{{ $siteName }}</span>
                    </div>
                    <p class="max-w-xs text-sm text-white/70">{{ $tagline }}</p>
                </div>
                <div class="lp-footer-links grid min-w-0 gap-6 text-sm">
                    <div>
                        <p class="font-semibold text-white/90">{{ __('Explore') }}</p>
                        <ul class="mt-2 space-y-1.5 text-white/65">
                            @foreach ($navLinks as $link)
                                <li><a href="{{ $link['href'] }}" class="hover:text-white">{{ $link['label'] }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                    <div>
                        <p class="font-semibold text-white/90">{{ __('Connect') }}</p>
                        <ul class="mt-2 flex gap-3 text-white/80">
                            <li><a href="#" class="hover:text-white" aria-label="Facebook"><i class="fa-brands fa-facebook"></i></a></li>
                            <li><a href="#" class="hover:text-white" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a></li>
                            <li><a href="#" class="hover:text-white" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <p class="mt-8 border-t border-white/10 pt-6 text-center text-xs text-white/50">
                © {{ date('Y') }} {{ $siteName }}
                @if (empty($publicMode))
                    · {{ __('AI draft preview — not published') }}
                @endif
            </p>
        </div>
    </footer>
</div>
