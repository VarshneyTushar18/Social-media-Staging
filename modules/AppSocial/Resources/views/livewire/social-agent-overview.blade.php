@php
    use Illuminate\Support\Facades\Route;

    $projectName = $project?->name ?? __('Project');
@endphp

<div class="min-w-0 max-w-full space-y-8 lg:space-y-10">
    <x-ui.sub-header
        :eyebrow="$projectName.' / '.__('Social Media')"
        :title="__('Social Media overview')"
        :description="__('Publishing, channels, and AI Studio at a glance.')"
    >
        <x-slot:actions>
            @if (Route::has('portal.dashboard'))
                <x-ui.button :href="route('portal.dashboard')" variant="outline" wire:navigate>
                    <i class="fa-solid fa-house"></i>
                    {{ __('Workspace dashboard') }}
                </x-ui.button>
            @endif
            @if (Route::has('portal.publishing.calendar'))
                <x-ui.button :href="route('portal.publishing.calendar')" wire:navigate>
                    {{ __('Open calendar') }}
                </x-ui.button>
            @endif
        </x-slot:actions>
    </x-ui.sub-header>

    @include(theme_view('partials.dashboard-main-grid', 'app'), ['dashboardItems' => $dashboardItems])
</div>
