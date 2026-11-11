@props([
    'context' => null,
])

@php
    $project = $context['project'] ?? null;
    $projects = collect($context['projects'] ?? []);
    $hasMany = $projects->count() > 1;
    $canSwitch = $hasMany && \Illuminate\Support\Facades\Route::has('portal.projects.switch');
    $canAdd = \Illuminate\Support\Facades\Route::has('portal.projects.create');
@endphp

@if ($project)
    <div
        class="border-b px-3 pb-3"
        style="border-color: var(--theme-border-color);"
        x-data="{ open: false }"
        @click.outside="open = false"
    >
        <p
            class="mb-2 px-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500"
            x-cloak
            x-show="sidebarContentVisible"
        >{{ __('Project') }}</p>

        <div
            class="flex items-center gap-2"
            x-bind:class="sidebarContentVisible ? '' : 'justify-center'"
        >
            <span
                class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-[11px] font-bold text-white"
                style="background: var(--theme-accent);"
                title="{{ $project->name }}"
            >{{ $project->initialLetter() }}</span>

            <div class="min-w-0 flex-1" x-cloak x-show="sidebarContentVisible">
                @if ($canSwitch)
                    <button
                        type="button"
                        class="flex w-full min-w-0 items-center gap-2 rounded-lg border px-2.5 py-2 text-left transition hover:bg-black/[0.03] dark:hover:bg-white/[0.04]"
                        style="border-color: rgba(var(--theme-border-color-rgb), 0.65);"
                        x-on:click="open = ! open"
                    >
                        <span class="min-w-0 flex-1 truncate text-[13px] font-semibold" style="color: var(--theme-header-text-color);">{{ $project->name }}</span>
                        <i class="fa-solid fa-chevron-down shrink-0 text-[9px]" style="color: var(--theme-muted-text-color);" x-bind:class="open ? 'rotate-180' : ''"></i>
                    </button>
                @else
                    <div class="min-w-0 px-0.5">
                        <p class="truncate text-[13px] font-semibold" style="color: var(--theme-header-text-color);">{{ $project->name }}</p>
                    </div>
                @endif
            </div>
        </div>

        @if ($canSwitch)
            <div
                x-cloak
                x-show="open && sidebarContentVisible"
                x-transition
                class="mt-2 max-h-40 space-y-0.5 overflow-y-auto rounded-lg border p-1"
                style="border-color: rgba(var(--theme-border-color-rgb), 0.6); background: var(--theme-sidebar-bg);"
            >
                @foreach ($projects as $candidate)
                    <form action="{{ route('portal.projects.switch') }}" method="post">
                        @csrf
                        <input type="hidden" name="project_id" value="{{ $candidate->id }}">
                        <button
                            type="submit"
                            class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-left text-[12px] transition hover:bg-black/[0.04] dark:hover:bg-white/[0.05] {{ (int) $candidate->id === (int) $project->id ? 'font-semibold' : '' }}"
                            style="color: var(--theme-sidebar-text-color);"
                        >
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded text-[9px] font-bold text-white" style="background: var(--theme-accent);">{{ $candidate->initialLetter() }}</span>
                            <span class="truncate">{{ $candidate->name }}</span>
                        </button>
                    </form>
                @endforeach
            </div>
        @endif

        @if ($canAdd)
            <a
                href="{{ route('portal.projects.create') }}"
                class="mt-2 flex w-full items-center justify-center gap-1.5 rounded-lg border border-dashed px-2 py-2 text-[11px] font-semibold transition hover:bg-black/[0.03] dark:hover:bg-white/[0.04]"
                style="border-color: rgba(var(--theme-border-color-rgb), 0.75); color: var(--theme-muted-text-color);"
                x-cloak
                x-show="sidebarContentVisible"
            >
                <i class="fa-solid fa-plus text-[10px]"></i>
                {{ __('Add project') }}
            </a>
        @endif
    </div>
@endif
