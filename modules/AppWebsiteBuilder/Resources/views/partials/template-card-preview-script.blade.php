@once
    <script>
        (function () {
            const FRAME_W = 1280;
            const DEFAULT_H = 3200;
            const VIEW_MARGIN = 200;
            const MAX_CONCURRENT = 3;
            let bootTimer = null;
            let activeLoads = 0;
            const loadQueue = [];

            function hostNearViewport(host) {
                const rect = host.getBoundingClientRect();

                return rect.bottom > -VIEW_MARGIN && rect.top < window.innerHeight + VIEW_MARGIN;
            }

            function layoutHost(host, layoutAttempts) {
                const stage = host.querySelector('[data-tc-stage]');
                const iframe = host.querySelector('[data-tc-iframe]');
                if (!stage || !iframe) {
                    return;
                }

                const attempts = layoutAttempts ?? 0;
                const frameH = Math.max(1200, parseInt(iframe.getAttribute('height') || String(DEFAULT_H), 10));
                const w = host.clientWidth;
                const h = host.clientHeight;

                if (w < 2 || h < 2) {
                    if (attempts < 40) {
                        requestAnimationFrame(() => layoutHost(host, attempts + 1));
                    }

                    return;
                }

                const fitPage = host.getAttribute('data-tc-fit') === 'page'
                    || host.closest('[data-tc-fit="page"]');
                const scale = fitPage
                    ? Math.min(w / FRAME_W, h / frameH)
                    : w / FRAME_W;
                stage.style.width = FRAME_W + 'px';
                stage.style.height = frameH + 'px';
                stage.style.transform = 'translateX(-50%) scale(' + scale + ')';
                stage.style.transformOrigin = 'top center';
                iframe.style.width = FRAME_W + 'px';
                iframe.style.height = frameH + 'px';
            }

            function releaseLoadSlot() {
                activeLoads = Math.max(0, activeLoads - 1);
                pumpQueue();
            }

            function pumpQueue() {
                while (activeLoads < MAX_CONCURRENT && loadQueue.length > 0) {
                    const host = loadQueue.shift();
                    if (!host || !host.isConnected) {
                        continue;
                    }

                    if (startHostLoad(host)) {
                        activeLoads++;
                    }
                }
            }

            function enqueueHost(host) {
                if (host.dataset.tcQueued === '1' || host.dataset.tcLoading === '1') {
                    return;
                }

                host.dataset.tcQueued = '1';

                const priority = host.getAttribute('data-tc-priority') === '1';
                if (priority) {
                    loadQueue.unshift(host);
                } else {
                    loadQueue.push(host);
                }

                pumpQueue();
            }

            function startHostLoad(host) {
                const iframe = host.querySelector('[data-tc-iframe]');
                const url = host.getAttribute('data-tc-embed');

                if (!iframe || !url || iframe.getAttribute('src')) {
                    delete host.dataset.tcQueued;

                    return false;
                }

                host.dataset.tcLoading = '1';
                delete host.dataset.tcQueued;

                bindIframeLoad(host);

                iframe.addEventListener(
                    'load',
                    () => releaseLoadSlot(),
                    { once: true },
                );
                iframe.addEventListener(
                    'error',
                    () => releaseLoadSlot(),
                    { once: true },
                );

                iframe.setAttribute('src', url);

                return true;
            }

            function onIframeLoad(iframe) {
                const host = iframe.closest('[data-tc-preview]');
                if (!host) {
                    return;
                }

                host.dataset.tcLoaded = '1';
                delete host.dataset.tcLoading;

                try {
                    const root = iframe.contentDocument?.getElementById('template-card-embed-root');
                    if (root) {
                        const measured = Math.max(1200, root.offsetHeight + 16);
                        iframe.setAttribute('height', String(measured));
                    }
                } catch (e) {
                    /* same-origin only */
                }

                layoutHost(host);
            }

            function bindIframeLoad(host) {
                const iframe = host.querySelector('[data-tc-iframe]');
                if (!iframe || iframe.dataset.tcLoadBound === '1') {
                    return;
                }

                iframe.dataset.tcLoadBound = '1';

                const handleLoad = () => onIframeLoad(iframe);
                iframe.addEventListener('load', handleLoad, { once: true });
            }

            function queueHost(host) {
                if (host.dataset.tcLoaded === '1') {
                    return;
                }

                const iframe = host.querySelector('[data-tc-iframe]');
                if (iframe?.getAttribute('src')) {
                    return;
                }

                if (host.getAttribute('data-tc-priority') === '1' || hostNearViewport(host)) {
                    enqueueHost(host);

                    return;
                }

                if (!('IntersectionObserver' in window)) {
                    enqueueHost(host);

                    return;
                }

                if (host.dataset.tcObserved === '1') {
                    return;
                }

                host.dataset.tcObserved = '1';

                const observer = new IntersectionObserver(
                    (entries) => {
                        entries.forEach((entry) => {
                            if (!entry.isIntersecting) {
                                return;
                            }

                            observer.disconnect();
                            delete host.dataset.tcObserved;
                            enqueueHost(host);
                        });
                    },
                    { rootMargin: VIEW_MARGIN + 'px', threshold: 0.01 },
                );

                observer.observe(host);
            }

            function bindHost(host) {
                layoutHost(host);

                if (host.dataset.tcBound === '1') {
                    queueHost(host);

                    return;
                }

                host.dataset.tcBound = '1';
                host.setAttribute('data-tc-bound', '1');

                if (typeof ResizeObserver !== 'undefined') {
                    new ResizeObserver(() => layoutHost(host)).observe(host);
                }

                queueHost(host);
            }

            function resetRuntime() {
                activeLoads = 0;
                loadQueue.length = 0;
            }

            function init(root) {
                const scope = root && root.querySelectorAll ? root : document;
                scope.querySelectorAll('[data-tc-preview]').forEach(bindHost);
            }

            function scheduleBoot(resetBound) {
                if (resetBound) {
                    resetRuntime();
                    document.querySelectorAll('[data-tc-preview][data-tc-bound]').forEach((host) => {
                        delete host.dataset.tcBound;
                        delete host.dataset.tcLoaded;
                        delete host.dataset.tcLoading;
                        delete host.dataset.tcQueued;
                        delete host.dataset.tcObserved;
                        const iframe = host.querySelector('[data-tc-iframe]');
                        if (iframe) {
                            delete iframe.dataset.tcLoadBound;
                            iframe.removeAttribute('src');
                        }
                    });
                }

                if (bootTimer) {
                    clearTimeout(bootTimer);
                }

                bootTimer = setTimeout(() => {
                    requestAnimationFrame(() => {
                        requestAnimationFrame(() => init(document));
                    });
                }, 32);
            }

            if (window.TemplateCardPreview) {
                window.TemplateCardPreview.scheduleBoot = scheduleBoot;
                window.TemplateCardPreview.boot = () => scheduleBoot(true);
                scheduleBoot(true);

                return;
            }

            window.TemplateCardPreview = {
                boot: () => scheduleBoot(true),
                scheduleBoot,
                init,
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => scheduleBoot(true));
            } else {
                scheduleBoot(true);
            }

            document.addEventListener('livewire:navigated', () => scheduleBoot(true));

            document.addEventListener('livewire:init', () => {
                Livewire.hook('morph.updated', () => scheduleBoot(true));
            });
        })();
    </script>
@endonce
