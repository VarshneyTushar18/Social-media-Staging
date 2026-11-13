@php
    $servicesStyle = (string) ($layout['services'] ?? 'image-cards');
@endphp

<section id="services" class="scroll-mt-16 px-4 py-12 sm:px-6 sm:py-16">
    <div class="mx-auto max-w-5xl">
        <p class="lp-section-eyebrow text-xs font-bold uppercase tracking-widest text-slate-500 @if($servicesStyle !== 'menu-rows') text-center @endif">
            {{ __('Services we provide') }}
        </p>
        <h2 class="text-2xl font-bold text-slate-900 sm:text-3xl @if($servicesStyle !== 'menu-rows') text-center @endif">
            {{ data_get($services, 'headline', __('Our services')) }}
        </h2>

        @if ($servicesStyle === 'feature-list')
            <ul class="mx-auto mt-8 max-w-3xl divide-y divide-slate-200 rounded-2xl border border-slate-200 bg-white lp-service-card">
                @foreach ((array) data_get($services, 'items', []) as $index => $item)
                    @php $icon = (string) data_get($item, 'icon', 'star'); @endphp
                    <li class="flex gap-4 p-5 sm:p-6 lp-service-card">
                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-bold text-white" style="background: {{ $brand }};">{{ $index + 1 }}</span>
                        <div>
                            <h3 class="font-semibold text-slate-900">{{ data_get($item, 'title', '') }}</h3>
                            <p class="mt-1 text-sm text-slate-600">{{ data_get($item, 'description', '') }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        @elseif ($servicesStyle === 'menu-rows')
            <ul class="mx-auto mt-8 max-w-2xl space-y-0 divide-y divide-dashed divide-slate-300">
                @foreach ((array) data_get($services, 'items', []) as $item)
                    <li class="lp-service-card flex flex-wrap items-baseline justify-between gap-2 py-4">
                        <h3 class="text-lg font-semibold text-slate-900">{{ data_get($item, 'title', '') }}</h3>
                        <p class="text-sm text-slate-600">{{ data_get($item, 'description', '') }}</p>
                    </li>
                @endforeach
            </ul>
        @elseif ($servicesStyle === 'product-grid')
            <ul class="lp-grid-2 mt-8 grid gap-6">
                @foreach ((array) data_get($services, 'items', []) as $item)
                    @php $itemImage = (string) data_get($item, 'image_url', ''); @endphp
                    <li class="lp-service-card overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-md">
                        @if ($itemImage !== '')
                            <img src="{{ $itemImage }}" alt="" class="h-48 w-full object-cover" loading="lazy" referrerpolicy="no-referrer" decoding="async" onerror="this.onerror=null;this.src=@js($imageFallbacks['service'] ?? '');" />
                        @endif
                        <div class="p-5">
                            <h3 class="text-lg font-bold text-slate-900">{{ data_get($item, 'title', '') }}</h3>
                            <p class="mt-2 text-sm text-slate-600">{{ data_get($item, 'description', '') }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        @elseif ($servicesStyle === 'icon-row')
            <ul class="mt-8 space-y-3">
                @foreach ((array) data_get($services, 'items', []) as $item)
                    @php $icon = (string) data_get($item, 'icon', 'star'); @endphp
                    <li class="lp-service-card flex items-start gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-white" style="background: {{ $brand }};">
                            <i class="fa-solid fa-{{ $icon }}"></i>
                        </span>
                        <div>
                            <h3 class="font-semibold text-slate-900">{{ data_get($item, 'title', '') }}</h3>
                            <p class="text-sm text-slate-600">{{ data_get($item, 'description', '') }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <ul class="lp-grid-3 mx-auto mt-8 grid gap-5">
                @foreach ((array) data_get($services, 'items', []) as $item)
                    @php
                        $icon = (string) data_get($item, 'icon', 'star');
                        $itemImage = (string) data_get($item, 'image_url', '');
                    @endphp
                    <li class="lp-service-card overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        @if ($itemImage !== '')
                            <img src="{{ $itemImage }}" alt="" class="h-36 w-full object-cover sm:h-40" loading="lazy" referrerpolicy="no-referrer" decoding="async" onerror="this.onerror=null;this.src=@js($imageFallbacks['service'] ?? '');" />
                        @endif
                        <div class="space-y-2 p-4 sm:p-5">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-sm text-white" style="background: {{ $brand }};">
                                <i class="fa-solid fa-{{ $icon }}"></i>
                            </span>
                            <h3 class="text-base font-semibold text-slate-900 sm:text-lg">{{ data_get($item, 'title', '') ?: __('Service') }}</h3>
                            <p class="text-sm leading-relaxed text-slate-600">{{ data_get($item, 'description', '') }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>
