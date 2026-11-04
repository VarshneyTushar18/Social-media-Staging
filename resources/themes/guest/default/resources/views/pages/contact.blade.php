@php
    $options = app(\Modules\AdminSettings\Support\OptionStore::class);
    $supportEmail = trim((string) $options->get('contact_email', ''));
    $supportEmail = $supportEmail !== '' ? $supportEmail : 'support@tech2globe.tech';
    $companyWebsite = trim((string) $options->get('contact_company_website', ''));
    $companyWebsite = $companyWebsite !== '' ? $companyWebsite : 'https://social.tech2globe.tech/portal/dashboard';
@endphp

@component(theme_view('layouts.marketing', 'guest'), ['pageTitle' => $pageTitle])
    <section class="guest-marketing-shell guest-section-space mx-auto px-6 pt-10 lg:px-12 lg:pt-14">
        <div class="mx-auto max-w-3xl">
            <span class="inline-flex items-center rounded-full border border-violet-400/14 bg-violet-400/10 px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-violet-200">
                {{ __('Tech2Globe Tech support') }}
            </span>
            <h1 class="mt-5 text-[2rem] font-semibold leading-tight tracking-[-0.04em] sm:text-[2.35rem] lg:text-[2.75rem]" style="color: var(--theme-header-text-color);">
                {{ __('Support & Contact') }}
            </h1>
            <p class="mt-4 text-base leading-8" style="color: var(--theme-muted-text-color);">
                {{ __('Contact the Tech2Globe Tech team for help with account access, workspaces, publishing workflows, connected channels, or other questions about the Platform.') }}
            </p>

            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                <section class="nova-soft-surface rounded-[1.5rem] border border-white/8 p-6">
                    <h2 class="text-sm font-semibold" style="color: var(--theme-muted-text-color);">{{ __('Website') }}</h2>
                    <a class="mt-2 block break-all text-base font-semibold text-sky-300 hover:underline" href="{{ $companyWebsite }}" target="_blank" rel="noopener noreferrer">{{ $companyWebsite }}</a>
                </section>

                <section class="nova-soft-surface rounded-[1.5rem] border border-white/8 p-6">
                    <h2 class="text-sm font-semibold" style="color: var(--theme-muted-text-color);">{{ __('General support') }}</h2>
                    <a class="mt-2 block break-all text-base font-semibold text-sky-300 hover:underline" href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>
                    <p class="mt-3 text-sm leading-6" style="color: var(--theme-muted-text-color);">
                        {{ __('Account access, setup, publishing workflows, and technical issues.') }}
                    </p>
                </section>

                <section class="nova-soft-surface rounded-[1.5rem] border border-white/8 p-6">
                    <h2 class="text-sm font-semibold" style="color: var(--theme-muted-text-color);">{{ __('Privacy & data requests') }}</h2>
                    <a class="mt-2 block break-all text-base font-semibold text-sky-300 hover:underline" href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>
                    <p class="mt-3 text-sm leading-6" style="color: var(--theme-muted-text-color);">
                        {{ __('Requests to access, correct, or delete your personal information.') }}
                    </p>
                    <a class="mt-4 inline-block text-sm font-semibold text-sky-300 hover:underline" href="{{ route('guest.delete-data') }}">
                        {{ __('View data deletion instructions') }}
                    </a>
                </section>
            </div>
        </div>
    </section>
@endcomponent
