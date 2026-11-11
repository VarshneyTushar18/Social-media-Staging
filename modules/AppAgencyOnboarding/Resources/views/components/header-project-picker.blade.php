@props([
    'context' => null,
])

@php
    $project = $context['project'] ?? null;
    $projects = collect($context['projects'] ?? []);
    $canSwitchRoute = \Illuminate\Support\Facades\Route::has('portal.projects.switch');
    $canAdd = \Illuminate\Support\Facades\Route::has('portal.projects.create');
    $canSwitch = $canSwitchRoute && $projects->count() > 1;
@endphp

@if ($project)
    <div class="relative shrink-0" x-data="{ open: false }" x-on:keydown.escape.window="open = false">
        <button
            type="button"
            class="flex h-11 w-[12.25rem] min-w-0 items-center gap-2.5 rounded-[0.75rem] border border-slate-200/90 px-3 shadow-[0_12px_24px_-20px_rgba(15,23,42,0.28)] transition hover:border-slate-300 hover:bg-slate-50 sm:w-[14.5rem] dark:border-slate-800 dark:hover:border-slate-700 dark:hover:bg-slate-900"
            style="background-color: rgba(var(--theme-header-surface-rgb), 0.9); border-color: var(--theme-shell-border-color);"
            x-bind:class="open ? 'border-slate-300 bg-slate-50 dark:border-slate-600 dark:bg-slate-900' : ''"
            x-on:click="open = !open"
            aria-haspopup="listbox"
            x-bind:aria-expanded="open"
        >
            <span
                class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-[0.65rem] text-[11px] font-bold text-white"
                style="background: var(--theme-accent);"
            >{{ $project->initialLetter() }}</span>
            <span class="min-w-0 flex-1 text-left">
                <span class="block text-[10px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Project') }}</span>
                <span class="block truncate text-sm font-semibold" style="color: var(--theme-header-text-color);" title="{{ $project->name }}">{{ $project->name }}</span>
            </span>
            <i class="fa-solid fa-chevron-down shrink-0 text-[12px] transition-transform duration-150" style="color: var(--theme-muted-text-color);" x-bind:class="open ? '-rotate-180' : ''"></i>
        </button>

        <div
            x-cloak
            x-show="open"
            x-transition:enter="transition ease-out duration-160"
            x-transition:enter-start="opacity-0 translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-120"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-1"
            x-on:click.outside="open = false"
            class="absolute left-0 top-[calc(100%+0.75rem)] z-[130] w-72 max-w-[calc(100vw-2rem)] overflow-hidden rounded-[0.9rem] border border-slate-200/90 bg-white p-2 shadow-[0_28px_80px_-28px_rgba(15,23,42,0.32)] dark:border-slate-700 dark:bg-slate-900"
            style="border-color: var(--theme-shell-border-color);"
            role="listbox"
        >
            <div class="px-3 pb-2 pt-1">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">{{ __('Your projects') }}</p>
            </div>

            @foreach ($projects as $candidate)
                @php
                    $isCurrent = (int) $candidate->id === (int) $project->id;
                    $rowClass = $isCurrent
                        ? 'bg-slate-950 text-white shadow-[0_12px_24px_-18px_rgba(15,23,42,0.4)] dark:bg-white dark:text-slate-950'
                        : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-800/90 dark:hover:text-white';
                @endphp

                @if ($canSwitch && ! $isCurrent)
                    <form action="{{ route('portal.projects.switch') }}" method="post">
                        @csrf
                        <input type="hidden" name="project_id" value="{{ $candidate->id }}">
                        <button
                            type="submit"
                            class="{{ $rowClass }} mb-0.5 flex w-full items-center justify-between rounded-[0.7rem] px-3 py-2.5 text-left text-sm font-medium transition last:mb-0"
                            x-on:click="open = false"
                        >
                            <span class="flex min-w-0 items-center gap-2.5">
                                <span
                                    class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-[0.65rem] text-[10px] font-bold text-white"
                                    style="background: var(--theme-accent);"
                                >{{ $candidate->initialLetter() }}</span>
                                <span class="min-w-0 truncate">{{ $candidate->name }}</span>
                            </span>
                        </button>
                    </form>
                @else
                    <div
                        class="{{ $rowClass }} mb-0.5 flex w-full items-center rounded-[0.7rem] px-3 py-2.5 text-sm font-medium last:mb-0"
                        @if ($isCurrent) aria-current="true" @endif
                    >
                        <span class="flex min-w-0 items-center gap-2.5">
                            <span
                                class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-[0.65rem] text-[10px] font-bold text-white"
                                style="background: var(--theme-accent);"
                            >{{ $candidate->initialLetter() }}</span>
                            <span class="min-w-0 truncate">{{ $candidate->name }}</span>
                        </span>
                    </div>
                @endif
            @endforeach

            @if ($canAdd)
                <div class="mt-1 border-t pt-1" style="border-color: var(--theme-shell-border-color);">
                    <a
                        href="{{ route('portal.projects.create') }}"
                        class="flex w-full items-center gap-2.5 rounded-[0.7rem] px-3 py-2.5 text-sm font-semibold transition hover:bg-slate-50 dark:hover:bg-slate-800/90"
                        style="color: var(--theme-header-text-color);"
                        wire:navigate
                        x-on:click="open = false"
                    >
                        <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-[0.65rem] border border-dashed" style="border-color: rgba(var(--theme-border-color-rgb), 0.75); color: var(--theme-muted-text-color);">
                            <i class="fa-solid fa-plus text-[11px]"></i>
                        </span>
                        {{ __('Add project') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
@endif
