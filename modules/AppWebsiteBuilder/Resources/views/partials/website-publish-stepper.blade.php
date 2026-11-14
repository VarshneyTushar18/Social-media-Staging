@props(['current' => 1, 'compact' => false])

@php
    $steps = [
        1 => __('Find domain'),
        2 => __('Confirm'),
        3 => __('Purchase'),
        4 => __('Connect DNS'),
    ];
    $total = count($steps);
    $currentLabel = $steps[$current] ?? '';
@endphp

<div class="w-full space-y-2.5">
    <div
        @class([
            'flex w-full items-center gap-2 text-xs font-semibold',
            'justify-between' => ! $compact,
            'justify-start' => $compact,
        ])
        style="color: var(--theme-muted-text-color);"
    >
        <span>{{ __('Step :n of :total', ['n' => $current, 'total' => $total]) }}</span>
        @unless ($compact)
            <span class="truncate text-right" style="color: var(--theme-header-text-color);">{{ $currentLabel }}</span>
        @endunless
    </div>

    @unless ($compact)
        <nav class="flex w-full flex-wrap items-center justify-between gap-1 sm:justify-center sm:gap-2" aria-label="{{ __('Publish progress') }}">
            @foreach ($steps as $number => $label)
                @php
                    $isCurrent = $number === $current;
                    $isComplete = $number < $current;
                @endphp
                <div
                    class="flex min-w-0 flex-1 items-center justify-center gap-1 rounded-full px-1.5 py-1 text-[10px] font-semibold transition sm:flex-none sm:gap-1.5 sm:px-2.5 sm:text-xs"
                    @if ($isCurrent)
                        style="background-color: rgba(var(--theme-accent-rgb), 0.12); color: var(--theme-accent); border: 1px solid rgba(var(--theme-accent-rgb), 0.22);"
                    @elseif ($isComplete)
                        style="color: var(--theme-header-text-color);"
                    @else
                        style="color: var(--theme-muted-text-color);"
                    @endif
                    title="{{ $label }}"
                >
                    <span
                        class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[9px] font-bold sm:h-6 sm:w-6 sm:text-[10px]"
                        @if ($isCurrent || $isComplete)
                            style="background-color: var(--theme-accent); color: #fff;"
                        @else
                            style="background-color: rgba(var(--theme-border-color-rgb), 0.2); color: var(--theme-muted-text-color);"
                        @endif
                    >@if ($isComplete)<i class="fa-solid fa-check text-[8px]"></i>@else{{ $number }}@endif</span>
                    <span class="hidden truncate sm:inline lg:max-w-none">{{ $label }}</span>
                </div>
            @endforeach
        </nav>
    @endunless

    <div
        class="h-1 w-full overflow-hidden rounded-full"
        style="background-color: rgba(var(--theme-border-color-rgb), 0.45);"
        role="progressbar"
        aria-valuenow="{{ (int) round(($current / $total) * 100) }}"
        aria-valuemin="0"
        aria-valuemax="100"
    >
        <div
            class="h-full rounded-full transition-all duration-300"
            style="width: {{ (int) round(($current / $total) * 100) }}%; background-color: var(--theme-accent);"
        ></div>
    </div>
</div>
