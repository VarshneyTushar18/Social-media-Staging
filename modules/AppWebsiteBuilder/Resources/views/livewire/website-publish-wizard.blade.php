<div class="{{ $embedded ? 'w-full min-w-0 max-w-full overflow-x-hidden space-y-4' : 'mx-auto max-w-3xl space-y-6' }}">
    @unless ($embedded)
        @include('appwebsitebuilder::partials.website-builder-session-toasts')
    @endunless

    @include('appwebsitebuilder::partials.website-publish-domain-flow', [
        'embedded' => $embedded,
        'publishStepNumber' => $publishStepNumber,
    ])
</div>
