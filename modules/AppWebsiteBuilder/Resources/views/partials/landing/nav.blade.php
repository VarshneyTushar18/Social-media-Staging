@php
    $navStyle = (string) ($layout['nav'] ?? 'light');
    $navIsDark = $navStyle === 'dark';
    $navIsMinimal = $navStyle === 'minimal';
@endphp

<nav @class([
    'lp-site-nav relative sticky top-0 z-30 backdrop-blur-md',
    'lp-site-nav--dark' => $navIsDark,
    'lp-site-nav--minimal' => $navIsMinimal,
    'border-b border-slate-200/90 bg-white/95' => ! $navIsDark && ! $navIsMinimal,
    'border-b border-slate-800 bg-slate-950/95 text-white' => $navIsDark,
    'border-b border-transparent bg-white/80' => $navIsMinimal,
])>
    <div class="flex h-14 items-center justify-between gap-3 px-4 sm:h-16 sm:px-6">
        <a href="#home" class="flex min-w-0 items-center gap-2.5" x-on:click="mobileNav = false">
            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-sm font-bold text-white" style="background: {{ $brand }};">
                {{ strtoupper(substr($siteName, 0, 1)) }}
            </span>
            <span @class([
                'truncate text-sm font-bold sm:text-base',
                'text-slate-900' => ! $navIsDark,
                'text-white' => $navIsDark,
            ])>{{ $siteName }}</span>
        </a>

        <div class="lp-nav-desktop hidden items-center gap-6 md:flex">
            @foreach ($navLinks as $link)
                <a href="{{ $link['href'] }}" @class([
                    'text-sm font-medium transition',
                    'text-slate-600 hover:text-slate-900' => ! $navIsDark,
                    'text-slate-300 hover:text-white' => $navIsDark,
                ])>{{ $link['label'] }}</a>
            @endforeach
            <a href="#contact" class="inline-flex rounded-full px-4 py-2 text-xs font-semibold text-white shadow-sm" style="background: {{ $brand }};">
                {{ $heroCta }}
            </a>
        </div>

        <button
            type="button"
            @class([
                'lp-nav-toggle inline-flex h-10 w-10 items-center justify-center rounded-xl border md:hidden',
                'border-slate-200 text-slate-700' => ! $navIsDark,
                'border-slate-700 text-white' => $navIsDark,
            ])
            x-on:click="mobileNav = !mobileNav"
            aria-label="{{ __('Menu') }}"
        >
            <i class="fa-solid" x-bind:class="mobileNav ? 'fa-xmark' : 'fa-bars'"></i>
        </button>
    </div>

    <div
        class="lp-nav-mobile-panel absolute inset-x-0 top-full z-40 max-h-[min(70vh,22rem)] overflow-y-auto border-t border-slate-200 bg-white px-4 py-3 shadow-lg"
        x-show="mobileNav"
        x-cloak
        x-on:click.outside="mobileNav = false"
    >
        <div class="space-y-1">
            @foreach ($navLinks as $link)
                <a href="{{ $link['href'] }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50" x-on:click="mobileNav = false">
                    {{ $link['label'] }}
                </a>
            @endforeach
            <a
                href="#contact"
                class="mt-2 block rounded-lg px-3 py-2.5 text-center text-sm font-semibold text-white"
                style="background: {{ $brand }};"
                x-on:click="mobileNav = false"
            >
                {{ $heroCta }}
            </a>
        </div>
    </div>

    <div
        class="lp-nav-mobile-backdrop absolute inset-x-0 top-full z-[35] h-[200vh] bg-slate-900/35"
        x-show="mobileNav"
        x-cloak
        x-on:click="mobileNav = false"
        aria-hidden="true"
    ></div>
</nav>
