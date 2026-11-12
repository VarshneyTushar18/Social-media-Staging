<style>
    .lp-landing .lp-cta {
        border-radius: 0.75rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .lp-landing .lp-cta:hover {
        transform: translateY(-1px);
        box-shadow: 0 12px 28px -8px color-mix(in srgb, var(--lp-brand) 55%, transparent);
    }

    .lp-landing .lp-service-card {
        transition: transform 0.22s ease, box-shadow 0.22s ease;
    }

    /* —— Corporate (business / MAXA-style) —— */
    .lp-landing.lp-skin-corporate #home .lp-hero-overlay {
        background: linear-gradient(
            105deg,
            rgba(15, 23, 42, 0.94) 0%,
            color-mix(in srgb, var(--lp-brand) 55%, rgba(15, 23, 42, 0.85)) 55%,
            rgba(15, 23, 42, 0.35) 100%
        );
    }

    .lp-landing.lp-skin-corporate #home h1 {
        letter-spacing: -0.02em;
        text-transform: uppercase;
        font-weight: 800;
    }

    .lp-landing.lp-skin-corporate .lp-cta {
        border-radius: 0.5rem;
        padding-left: 1.75rem;
        padding-right: 1.75rem;
    }

    .lp-landing.lp-skin-corporate #services .lp-service-card,
    .lp-landing.lp-skin-corporate #content .rounded-2xl {
        border-top: 3px solid var(--lp-brand);
        box-shadow: 0 18px 40px -24px rgba(15, 23, 42, 0.35);
    }

    /* —— Marketplace (Foodwagon-style warm hero) —— */
    .lp-landing.lp-skin-marketplace #home .lp-hero-overlay {
        background: linear-gradient(
            155deg,
            rgba(251, 191, 36, 0.9) 0%,
            rgba(249, 115, 22, 0.82) 38%,
            color-mix(in srgb, var(--lp-brand) 70%, rgba(15, 23, 42, 0.6)) 100%
        );
    }

    .lp-landing.lp-skin-marketplace #home h1 {
        font-weight: 800;
        line-height: 1.08;
    }

    .lp-landing.lp-skin-marketplace .lp-hero-overlap-img,
    .lp-landing.lp-skin-marketplace .lp-hero-aside img {
        border-radius: 50%;
        max-width: 22rem;
        margin-left: auto;
        margin-right: auto;
        border-width: 6px;
        border-color: rgba(255, 255, 255, 0.85);
    }

    .lp-landing.lp-skin-marketplace #services .lp-service-card {
        border-radius: 1.25rem;
        overflow: hidden;
        box-shadow: 0 16px 32px -20px rgba(249, 115, 22, 0.35);
    }

    /* —— SaaS dark hero (Verity-style) —— */
    .lp-landing.lp-skin-saas #home {
        background: #030712;
    }

    .lp-landing.lp-skin-saas #home .lp-hero-overlay {
        background:
            radial-gradient(ellipse 80% 60% at 70% 100%, color-mix(in srgb, var(--lp-brand) 45%, transparent), transparent 70%),
            linear-gradient(180deg, rgba(3, 7, 18, 0.35) 0%, rgba(3, 7, 18, 0.92) 100%);
    }

    .lp-landing.lp-skin-saas #home h1 {
        font-weight: 700;
        letter-spacing: -0.035em;
    }

    .lp-landing.lp-skin-saas .lp-cta {
        border-radius: 9999px;
        box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.12), 0 20px 40px -16px color-mix(in srgb, var(--lp-brand) 65%, transparent);
    }

    .lp-landing.lp-skin-saas .lp-site-nav--dark,
    .lp-landing.lp-skin-saas .lp-site-nav--minimal {
        border-color: rgba(255, 255, 255, 0.08);
    }

    /* —— Glass (Corda-style) —— */
    .lp-landing.lp-skin-glass #home > img {
        filter: blur(1px) saturate(1.08);
        transform: scale(1.04);
    }

    .lp-landing.lp-skin-glass #home .lp-hero-overlay {
        background: rgba(15, 23, 42, 0.48);
        backdrop-filter: blur(6px);
    }

    .lp-landing.lp-skin-glass .lp-site-nav {
        margin: 0.75rem 1rem 0;
        border-radius: 9999px;
        border: 1px solid rgba(255, 255, 255, 0.22);
        background: rgba(255, 255, 255, 0.14) !important;
        backdrop-filter: blur(14px);
    }

    .lp-landing.lp-skin-glass:not(.lp-surface-dark) .lp-site-nav {
        border-color: rgba(15, 23, 42, 0.1);
        background: rgba(255, 255, 255, 0.72) !important;
    }

    .lp-landing.lp-skin-glass #home .lp-hero-panel {
        margin-top: 1.5rem;
        padding: 1.25rem 1.5rem;
        border-radius: 1.25rem;
        border: 1px solid rgba(255, 255, 255, 0.22);
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(12px);
    }

    /* —— Luxury (boutique / editorial) —— */
    .lp-landing.lp-skin-luxury #home .lp-hero-overlay {
        background: linear-gradient(180deg, rgba(15, 23, 42, 0.55) 0%, rgba(15, 23, 42, 0.88) 100%);
    }

    .lp-landing.lp-skin-luxury #home h1 {
        font-weight: 300;
        letter-spacing: 0.04em;
        text-transform: none;
    }

    .lp-landing.lp-skin-luxury .lp-cta {
        border-radius: 0;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        font-size: 0.7rem;
        padding: 0.9rem 2rem;
    }

    .lp-landing.lp-skin-luxury .lp-hero-overlap-media > div,
    .lp-landing.lp-skin-luxury .lp-hero-aside {
        border-radius: 0.25rem;
    }

    /* —— Studio (agency service cards) —— */
    .lp-landing.lp-skin-studio #services {
        background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
    }

    .lp-landing.lp-skin-studio #services .lp-section-eyebrow {
        text-align: center;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.2em;
        text-transform: uppercase;
        color: var(--lp-brand);
    }

    .lp-landing.lp-skin-studio #services .lp-service-card {
        border-radius: 1rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 22px 44px -28px rgba(15, 23, 42, 0.28);
    }

    .lp-landing.lp-skin-studio #services .lp-service-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 28px 48px -22px color-mix(in srgb, var(--lp-brand) 35%, rgba(15, 23, 42, 0.2));
    }

    /* —— Pulse (bold dark marketing) —— */
    .lp-landing.lp-skin-pulse #home {
        background: #0a0a0a;
    }

    .lp-landing.lp-skin-pulse #home .lp-hero-overlay {
        background:
            radial-gradient(ellipse 90% 70% at 50% 120%, color-mix(in srgb, var(--lp-brand) 55%, #ff4d35), transparent 65%),
            linear-gradient(180deg, rgba(10, 10, 10, 0.4) 0%, rgba(10, 10, 10, 0.95) 100%);
    }

    .lp-landing.lp-skin-pulse #home h1 em,
    .lp-landing.lp-skin-pulse #home h1 .lp-hero-accent {
        font-style: italic;
        color: color-mix(in srgb, var(--lp-brand) 80%, #fff);
    }

    .lp-landing.lp-skin-pulse .lp-cta {
        border-radius: 9999px;
        font-weight: 700;
    }

    .lp-landing.lp-skin-pulse .lp-site-nav--dark {
        background: rgba(10, 10, 10, 0.92) !important;
    }

    @container lp (min-width: 48rem) {
        .lp-landing.lp-skin-marketplace #home .lp-hero-overlap {
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr);
            align-items: center;
            gap: 2rem;
        }

        .lp-landing.lp-skin-corporate #home .lp-grid-2 {
            align-items: center;
        }
    }
</style>
