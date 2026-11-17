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
    $percent = (int) round(($current / $total) * 100);
@endphp

@if ($compact)
    <div class="mx-auto w-full max-w-2xl space-y-3 text-center">
        <nav class="flex flex-wrap items-center justify-center gap-1.5 sm:gap-2" aria-label="{{ __('Publish progress') }}">
            @foreach ($steps as $number => $label)
                @php
                    $isCurrent = $number === $current;
                    $isComplete = $number < $current;
                @endphp
                <div
                    class="flex items-center gap-1.5 rounded-full px-2 py-1 text-[11px] font-semibold transition sm:px-3 sm:py-1.5 sm:text-xs"
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
                        class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-[10px] font-bold sm:h-7 sm:w-7 sm:text-xs"
                        @if ($isCurrent || $isComplete)
                            style="background-color: var(--theme-accent); color: #fff;"
                        @else
                            style="background-color: rgba(var(--theme-border-color-rgb), 0.2); color: var(--theme-muted-text-color);"
                        @endif
                    >@if ($isComplete)<i class="fa-solid fa-check text-[8px]"></i>@else{{ $number }}@endif</span>
                    <span class="hidden max-w-[4.5rem] truncate sm:inline md:max-w-none">{{ $label }}</span>
                </div>
                @if (! $loop->last)
                    <span
                        class="hidden h-0.5 w-6 shrink-0 rounded-full sm:block sm:w-8"
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

        <div
            class="mx-auto h-1 w-full max-w-xl overflow-hidden rounded-full"
            style="background-color: rgba(var(--theme-border-color-rgb), 0.45);"
            role="progressbar"
            aria-valuenow="{{ $percent }}"
            aria-valuemin="0"
            aria-valuemax="100"
        >
            <div
                class="h-full rounded-full transition-all duration-300"
                style="width: {{ $percent }}%; background-color: var(--theme-accent);"
            ></div>
        </div>
    </div>
@else
    <div class="w-full space-y-2.5">
        <div
            class="flex w-full items-center justify-between gap-2 text-xs font-semibold"
            style="color: var(--theme-muted-text-color);"
        >
            <span>{{ __('Step :n of :total', ['n' => $current, 'total' => $total]) }}</span>
            <span class="truncate text-right" style="color: var(--theme-header-text-color);">{{ $currentLabel }}</span>
        </div>

        <nav class="flex w-full flex-wrap items-center justify-center gap-1 sm:gap-2" aria-label="{{ __('Publish progress') }}">
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

        <div
            class="h-1 w-full overflow-hidden rounded-full"
            style="background-color: rgba(var(--theme-border-color-rgb), 0.45);"
            role="progressbar"
            aria-valuenow="{{ $percent }}"
            aria-valuemin="0"
            aria-valuemax="100"
        >
            <div
                class="h-full rounded-full transition-all duration-300"
                style="width: {{ $percent }}%; background-color: var(--theme-accent);"
            ></div>
        </div>
    </div>
@endif