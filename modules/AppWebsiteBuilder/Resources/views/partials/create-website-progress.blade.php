@props(['step' => 1, 'buildPath' => ''])

@php
    $isTemplatePath = $buildPath === 'template';
    $isPromptPath = $buildPath === 'ai';

    $steps = [
        1 => __('Method'),
    ];

    if ($isTemplatePath) {
        $steps[2] = __('Type');
        $steps[3] = __('Template');
    } elseif ($isPromptPath) {
        $steps[2] = __('Prompt');
    } else {
        $steps[2] = __('Next');
    }

    $total = count($steps);
    $current = max(1, min($step, $total));
    $percent = (int) round(($current / $total) * 100);
@endphp

<div class="mb-6 flex w-full justify-center px-2">
    <div class="inline-flex w-fit max-w-full flex-col items-stretch gap-3">
    <nav class="flex flex-wrap items-center justify-center gap-1.5 sm:gap-2" aria-label="{{ __('Create website progress') }}">
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
                <span class="max-w-[5.5rem] truncate sm:max-w-none">{{ $label }}</span>
            </div>
            @if (! $loop->last)
                <span
                    class="h-0.5 w-6 shrink-0 rounded-full sm:w-8"
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
        class="h-1 w-full min-w-[12rem] overflow-hidden rounded-full sm:min-w-[14rem]"
        style="background-color: rgba(var(--theme-border-color-rgb), 0.45);"
        role="progressbar"
        aria-valuenow="{{ $percent }}"
        aria-valuemin="0"
        aria-valuemax="100"
        aria-label="{{ __('Progress') }}"
    >
        <div
            class="h-full rounded-full transition-all duration-300"
            style="width: {{ $percent }}%; background-color: var(--theme-accent);"
        ></div>
    </div>
    </div>
</div>