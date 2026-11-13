@php
    $widthClasses = [
        'compact' => 'lg:col-span-4',
        'half' => 'lg:col-span-6',
        'wide' => 'lg:col-span-8',
        'full' => 'lg:col-span-12',
    ];
@endphp

@if (($dashboardItems ?? []) === [])
    <x-ui.empty
        :title="__('No dashboard items registered')"
        :description="__('Widgets for this section are not available yet.')"
    />
@else
    <div class="min-w-0 max-w-full grid gap-x-5 gap-y-8 lg:gap-y-10 lg:grid-cols-12">
        @foreach ($dashboardItems as $item)
            <section
                wire:key="dashboard-item-{{ $item['id'] }}"
                class="relative min-w-0 max-w-full {{ $widthClasses[$item['width'] ?? 'half'] ?? $widthClasses['half'] }}"
            >
                {!! $item['content'] !!}
            </section>
        @endforeach
    </div>
@endif
