@php
    $faqStyle = (string) ($layout['faq'] ?? 'accordion');
    $faqHeadline = (string) data_get($faq, 'headline', '');
    $faqSubheadline = (string) data_get($faq, 'subheadline', '');

    if ($faqHeadline === '') {
        $faqHeadline = __('Frequently asked questions');
    }

    if ($faqSubheadline === '') {
        $faqSubheadline = __('Quick answers about how we work.');
    }

    $faqItems = (array) data_get($faq, 'items', []);
@endphp

<section id="faq" class="scroll-mt-16 border-b border-slate-100 bg-white px-4 py-12 sm:px-6 sm:py-16">
    <div class="mx-auto max-w-3xl">
        <div class="text-center">
            <p class="text-xs font-bold uppercase tracking-widest text-slate-500">{{ __('FAQ') }}</p>
            <h2 class="mt-2 text-2xl font-bold text-slate-900 sm:text-3xl">{{ $faqHeadline }}</h2>
            <p class="mx-auto mt-3 max-w-2xl text-sm leading-relaxed text-slate-600 sm:text-base">{{ $faqSubheadline }}</p>
        </div>

        @if ($faqStyle === 'simple')
            <dl class="mt-10 space-y-8">
                @foreach ($faqItems as $item)
                    <div>
                        <dt class="text-base font-semibold text-slate-900">{{ data_get($item, 'question', '') }}</dt>
                        <dd class="mt-2 text-sm leading-relaxed text-slate-600">{{ data_get($item, 'answer', '') }}</dd>
                    </div>
                @endforeach
            </dl>
        @else
            <h3 class="sr-only">{{ __('Questions and answers') }}</h3>
            <div class="mt-10 space-y-3">
                @foreach ($faqItems as $index => $item)
                    <details @class([
                        'group rounded-2xl border border-slate-200 bg-slate-50/80 p-4 shadow-sm open:bg-white sm:p-5',
                    ])>
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-left font-semibold text-slate-900 marker:content-none [&::-webkit-details-marker]:hidden">
                            <span class="text-sm sm:text-base">{{ data_get($item, 'question', '') }}</span>
                            <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white text-slate-500 shadow-sm transition group-open:rotate-180" style="color: {{ $brand }};">
                                <i class="fa-solid fa-chevron-down text-xs"></i>
                            </span>
                        </summary>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">{{ data_get($item, 'answer', '') }}</p>
                    </details>
                @endforeach
            </div>
        @endif
    </div>
</section>