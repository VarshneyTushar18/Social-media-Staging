@php
    $contactStyle = (string) ($layout['contact'] ?? 'gradient');
    $contactHeadline = (string) data_get($contact, 'headline', '');
    $contactBody = (string) data_get($contact, 'body', '');
    $contactImage = (string) data_get($contact, 'image_url', '');

    if ($contactHeadline === '') {
        $contactHeadline = __('Get in touch');
    }

    if ($contactBody === '') {
        $contactBody = __('We would love to hear from you. Reach out anytime.');
    }
@endphp

<section id="contact" class="scroll-mt-16 px-4 py-12 sm:px-6 sm:py-16">
    @if ($contactStyle === 'light-split')
        <div class="lp-cq-split mx-auto grid max-w-5xl gap-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-lg sm:p-8">
            <div class="min-w-0 space-y-4">
                <h2 class="text-2xl font-bold text-slate-900 sm:text-3xl">{{ $contactHeadline }}</h2>
                <p class="text-sm leading-relaxed text-slate-600 sm:text-base">{{ $contactBody }}</p>
            </div>
            <div class="min-w-0 space-y-3">
                @if (filled(data_get($contact, 'email_placeholder')))
                    <div class="lp-contact-card flex items-start gap-3 rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700">
                        <i class="fa-solid fa-envelope mt-0.5 shrink-0" style="color: {{ $brand }};"></i>
                        {{ data_get($contact, 'email_placeholder') }}
                    </div>
                @endif
                @if (filled(data_get($contact, 'phone_placeholder')))
                    <div class="lp-contact-card flex items-start gap-3 rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700">
                        <i class="fa-solid fa-phone mt-0.5 shrink-0" style="color: {{ $brand }};"></i>
                        {{ data_get($contact, 'phone_placeholder') }}
                    </div>
                @endif
            </div>
        </div>
    @elseif ($contactStyle === 'minimal-bar')
        <div class="mx-auto max-w-5xl rounded-2xl bg-slate-900 px-6 py-10 text-center text-white sm:px-12">
            <h2 class="text-xl font-bold sm:text-2xl">{{ $contactHeadline }}</h2>
            <p class="mx-auto mt-2 max-w-xl text-sm text-white/75">{{ $contactBody }}</p>
            <div class="mt-6 flex flex-wrap justify-center gap-4 text-sm text-white/90">
                @if (filled(data_get($contact, 'email_placeholder')))
                    <span><i class="fa-solid fa-envelope mr-2"></i>{{ data_get($contact, 'email_placeholder') }}</span>
                @endif
                @if (filled(data_get($contact, 'phone_placeholder')))
                    <span><i class="fa-solid fa-phone mr-2"></i>{{ data_get($contact, 'phone_placeholder') }}</span>
                @endif
            </div>
        </div>
    @else
        <div class="relative mx-auto max-w-5xl overflow-hidden rounded-3xl text-white shadow-xl">
            <div class="absolute inset-0" style="background: linear-gradient(135deg, {{ $brand }}, #1e293b);"></div>
            @if ($contactImage !== '')
                <img src="{{ $contactImage }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-25" loading="lazy" onerror="this.remove()" />
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-900/50 to-slate-900/20"></div>
            <div class="relative space-y-6 px-6 py-12 text-center sm:px-12 sm:py-14">
                <h2 class="text-2xl font-bold sm:text-3xl">{{ $contactHeadline }}</h2>
                <p class="mx-auto max-w-2xl text-sm leading-relaxed text-white/85 sm:text-base">{{ $contactBody }}</p>
                <div class="lp-cq-contact-actions flex flex-col gap-3">
                    @if (filled(data_get($contact, 'email_placeholder')))
                        <span class="lp-contact-card inline-flex items-center justify-center gap-2 rounded-xl bg-white/10 px-4 py-3 text-sm backdrop-blur-sm">
                            <i class="fa-solid fa-envelope"></i>
                            {{ data_get($contact, 'email_placeholder') }}
                        </span>
                    @endif
                    @if (filled(data_get($contact, 'phone_placeholder')))
                        <span class="lp-contact-card inline-flex items-center justify-center gap-2 rounded-xl bg-white/10 px-4 py-3 text-sm backdrop-blur-sm">
                            <i class="fa-solid fa-phone"></i>
                            {{ data_get($contact, 'phone_placeholder') }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    @endif
</section>
