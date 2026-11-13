<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ current_locale_direction() }}">
<head>
    @include(theme_view('partials.head', 'app'), ['title' => $title ?? null])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>
    <style>
        [x-cloak]{display:none!important}
        .preview-device-frame[data-device="mobile"] .lp-nav-desktop { display: none !important; }
        .preview-device-frame[data-device="mobile"] .lp-nav-toggle { display: inline-flex !important; }
        .preview-device-frame[data-device="laptop"] .lp-nav-mobile-panel,
        .preview-device-frame[data-device="tablet"] .lp-nav-mobile-panel,
        .preview-device-frame[data-device="laptop"] .lp-nav-mobile-backdrop,
        .preview-device-frame[data-device="tablet"] .lp-nav-mobile-backdrop { display: none !important; }
        .preview-device-frame[data-device="mobile"] .lp-grid-2,
        .preview-device-frame[data-device="mobile"] .lp-grid-3,
        .preview-device-frame[data-device="mobile"] .lp-grid-4,
        .preview-device-frame[data-device="mobile"] .lp-highlight-grid,
        .preview-device-frame[data-device="mobile"] .lp-cq-split { grid-template-columns: minmax(0, 1fr) !important; }
        .preview-device-frame[data-device="mobile"] .lp-footer-main { flex-direction: column !important; align-items: stretch !important; }
        .preview-device-frame[data-device="mobile"] .lp-cq-contact-actions { flex-direction: column !important; align-items: stretch !important; }
        .preview-device-frame[data-device="mobile"] .lp-hero-aside { display: none !important; }
        .preview-device-frame[data-device="mobile"] .lp-hero-overlap-media,
        .preview-device-frame[data-device="tablet"] .lp-hero-overlap-media {
            margin-bottom: 0 !important;
        }
        .preview-device-frame[data-device="mobile"] .lp-hero-overlap-copy,
        .preview-device-frame[data-device="tablet"] .lp-hero-overlap-copy {
            padding-top: 0 !important;
        }
        .preview-device-frame[data-device="tablet"] .lp-grid-3 { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
        .preview-device-frame[data-device="laptop"] .lp-nav-toggle { display: none !important; }
        .preview-device-frame[data-device="laptop"] .lp-nav-desktop { display: flex !important; }
        .preview-device-frame[data-device="tablet"] .lp-nav-toggle { display: none !important; }
        .preview-device-frame[data-device="tablet"] .lp-nav-desktop { display: flex !important; }
    </style>
</head>
<body class="antialiased bg-white text-slate-900">
    {{ $slot }}
</body>
</html>
