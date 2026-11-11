@props(['current' => 1])

@php
    $steps = [
        1 => __('Plan'),
        2 => __('Project'),
        3 => __('Setup'),
    ];
@endphp

<nav class="mb-8 flex flex-wrap items-center justify-center gap-2 sm:gap-3" aria-label="{{ __('Onboarding progress') }}">
    @foreach ($steps as $number => $label)
        @php
            $isCurrent = $number === $current;
            $isComplete = $number < $current;
        @endphp
        <div
            class="flex items-center gap-2 rounded-full px-3 py-2 text-sm font-semibold transition sm:px-4"
            @if ($isCurrent)
                style="background-color: rgba(var(--theme-accent-rgb), 0.12); color: var(--theme-accent); border: 1px solid rgba(var(--theme-accent-rgb), 0.22);"
            @elseif ($isComplete)
                style="color: var(--theme-header-text-color);"
            @else
                style="color: var(--theme-muted-text-color);"
            @endif
        >
            <span
                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold"
                @if ($isCurrent || $isComplete)
                    style="background-color: var(--theme-accent); color: #fff;"
                @else
                    style="background-color: rgba(var(--theme-border-color-rgb), 0.2); color: var(--theme-muted-text-color);"
                @endif
            >{{ $number }}</span>
            <span class="hidden sm:inline">{{ $label }}</span>
        </div>
        @if (! $loop->last)
            <span
                class="hidden h-0.5 w-8 rounded-full sm:block sm:w-12"
                @if ($number < $current)
                    style="background-color: var(--theme-accent);"
                @else
                    style="background-color: rgba(var(--theme-border-color-rgb), 0.5);"
                @endif
                aria-hidden="true"
            ></span>
        @endif
    @endforeach
</nav>
