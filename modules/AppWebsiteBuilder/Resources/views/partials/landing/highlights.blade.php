@php
    $highlightsStyle = (string) ($layout['highlights'] ?? 'icon-cards');
    $highlightsHeadline = (string) data_get($highlights, 'headline', '');
    $highlightsSubheadline = (string) data_get($highlights, 'subheadline', '');

    if ($highlightsHeadline === '') {
        $highlightsHeadline = __('Why choose :name', ['name' => $siteName]);
    }

    if ($highlightsSubheadline === '') {
        $highlightsSubheadline = __('Everything you need to grow online.');
    }
@endphp

<section id="content" class="scroll-mt-16 border-y border-slate-100 bg-slate-50 px-4 py-12 sm:px-6 sm:py-16">
    <div class="mx-auto max-w-5xl">
        @if ($highlightsStyle !== 'stat-row')
            <div class="text-center">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500">{{ __('Highlights') }}</p>
                <h2 class="mt-2 text-2xl font-bold text-slate-900 sm:text-3xl">{{ $highlightsHeadline }}</h2>
                <p class="mx-auto mt-3 max-w-2xl text-sm leading-relaxed text-slate-600 sm:text-base">{{ $highlightsSubheadline }}</p>
            </div>
        @endif

        @if ($highlightsStyle === 'stat-row')
            <div class="lp-grid-4 grid gap-4">
                @foreach ((array) data_get($highlights, 'items', []) as $index => $item)
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm">
                        <p class="text-3xl font-black" style="color: {{ $brand }};">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</p>
                        <h3 class="mt-2 text-sm font-bold text-slate-900">{{ data_get($item, 'title', '') }}</h3>
                        <p class="mt-1 text-xs text-slate-600">{{ data_get($item, 'description', '') }}</p>
                    </div>
                @endforeach
            </div>
        @elseif ($highlightsStyle === 'list-check')
            <ul class="mx-auto mt-8 max-w-2xl space-y-3">
                @foreach ((array) data_get($highlights, 'items', []) as $item)
                    <li class="flex items-start gap-3 rounded-xl bg-white p-4 shadow-sm">
                        <i class="fa-solid fa-circle-check mt-0.5 text-lg" style="color: {{ $brand }};"></i>
                        <div>
                            <h3 class="font-semibold text-slate-900">{{ data_get($item, 'title', '') }}</h3>
                            <p class="text-sm text-slate-600">{{ data_get($item, 'description', '') }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <ul class="lp-grid-2 lp-highlight-grid mx-auto mt-10 grid gap-4 sm:gap-5">
                @foreach ((array) data_get($highlights, 'items', []) as $item)
                    @php
                        $icon = (string) data_get($item, 'icon', 'circle-check');
                        $itemTitle = (string) data_get($item, 'title', '');
                        $itemDescription = (string) data_get($item, 'description', '');
                    @endphp
                    <li class="flex h-full items-start gap-4 rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm sm:p-5">
                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-base text-white shadow-sm" style="background: {{ $brand }};">
                            <i class="fa-solid fa-{{ $icon }}"></i>
                        </span>
                        <div class="min-w-0 flex-1 space-y-1.5">
                            <h3 class="text-base font-semibold leading-snug text-slate-900 sm:text-lg">{{ $itemTitle }}</h3>
                            <p class="text-sm leading-relaxed text-slate-600">{{ $itemDescription }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>
