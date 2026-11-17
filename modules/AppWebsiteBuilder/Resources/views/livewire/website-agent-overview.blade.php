@php
    $projectName = $project?->name ?? __('Project');
@endphp

<div class="min-w-0 max-w-full space-y-8 lg:space-y-10">
    @include('appwebsitebuilder::partials.website-builder-session-toasts')

    <x-ui.sub-header
        :eyebrow="$projectName.' / '.__('Website Builder')"
        :title="__('Website Builder overview')"
    />

    @include(theme_view('partials.dashboard-main-grid', 'app'), ['dashboardItems' => $dashboardItems])
</div>