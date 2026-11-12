<style>
    .lp-landing {
        width: 100%;
        max-width: 100%;
        overflow-x: clip;
        container-type: inline-size;
        container-name: lp;
    }

    .lp-landing .lp-grid-2,
    .lp-landing .lp-cq-split {
        grid-template-columns: minmax(0, 1fr);
    }

    .lp-landing .lp-grid-3,
    .lp-landing .lp-highlight-grid,
    .lp-landing .lp-grid-4 {
        grid-template-columns: minmax(0, 1fr);
    }

    .lp-landing .lp-cq-contact-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .lp-landing .lp-footer-main {
        flex-direction: column;
        align-items: stretch;
    }

    .lp-landing .lp-footer-links {
        grid-template-columns: minmax(0, 1fr);
    }

    .lp-landing .lp-contact-card {
        min-width: 0;
        word-break: break-word;
    }

    @container lp (min-width: 28rem) {
        .lp-landing .lp-footer-links {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @container lp (min-width: 36rem) {
        .lp-landing .lp-grid-3,
        .lp-landing .lp-highlight-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .lp-landing .lp-cq-contact-actions {
            flex-direction: row;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
        }
    }

    @container lp (min-width: 40rem) {
        .lp-landing .lp-footer-main {
            flex-direction: row;
            align-items: flex-start;
            justify-content: space-between;
        }
    }

    @container lp (min-width: 48rem) {
        .lp-landing .lp-grid-2,
        .lp-landing .lp-cq-split {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            align-items: center;
        }

        .lp-landing .lp-grid-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .lp-landing .lp-grid-4 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @container lp (min-width: 64rem) {
        .lp-landing .lp-grid-4 {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .lp-landing .lp-hero-overlap-img {
            aspect-ratio: 21 / 9;
        }

        .lp-landing .lp-hero-overlap-media {
            margin-bottom: -2.5rem;
        }

        .lp-landing .lp-hero-overlap-copy {
            position: relative;
            z-index: 1;
            padding-top: 3.5rem;
        }
    }

    @container lp (max-width: 47.99rem) {
        .lp-landing .lp-hero-overlap-media {
            margin-bottom: 0;
        }

        .lp-landing .lp-hero-overlap-copy {
            padding-top: 0;
        }
    }

    .lp-landing #home {
        overflow: hidden;
    }

    .lp-landing .lp-hero-overlap-copy p {
        text-wrap: pretty;
    }
</style>
