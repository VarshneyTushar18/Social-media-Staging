<div class="space-y-6">
    @php
        $projectName = $project?->name ?? __('Project');
        $website = $project?->website;
    @endphp

    <x-ui.sub-header
        :eyebrow="$projectName.' / '.__('Social Media')"
        :title="__('Social Media overview')"
        :description="__('Monitor performance, scheduled content, and agent activity for this project.')"
    >
        <x-slot:actions>
            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold" style="background: rgba(var(--theme-success-color-rgb), 0.12); color: var(--theme-success-color);">
                <span class="h-2 w-2 rounded-full bg-current"></span>
                {{ __('Agent running') }}
            </span>
            @if (Route::has('portal.publishing.calendar'))
                <x-ui.button href="{{ route('portal.publishing.calendar') }}" wire:navigate>
                    {{ __('Run agent now') }}
                </x-ui.button>
            @endif
        </x-slot:actions>
    </x-ui.sub-header>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['label' => __('Followers'), 'value' => $metrics['followers'], 'delta' => $metrics['followers_delta'].' '.__('this month')],
            ['label' => __('Engagement rate'), 'value' => $metrics['engagement'], 'delta' => $metrics['engagement_delta']],
            ['label' => __('Scheduled posts'), 'value' => $metrics['scheduled'], 'delta' => __('Next 7 days')],
            ['label' => __('Reach (30 days)'), 'value' => $metrics['reach'], 'delta' => $metrics['reach_delta']],
        ] as $card)
            <x-ui.card class="space-y-2 p-5">
                <p class="text-xs font-semibold uppercase tracking-wide" style="color: var(--theme-muted-text-color);">{{ $card['label'] }}</p>
                <p class="text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ $card['value'] }}</p>
                <p class="text-sm" style="color: var(--theme-success-color);">{{ $card['delta'] }}</p>
            </x-ui.card>
        @endforeach
    </div>

    <div class="grid gap-5 xl:grid-cols-[1.1fr_0.9fr]">
        <x-ui.card class="p-5">
            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Reach') }}</p>
            <p class="text-xs" style="color: var(--theme-muted-text-color);">{{ __('Last 14 days') }}</p>
            <div class="mt-6 flex h-40 items-end gap-2">
                @foreach (range(1, 14) as $bar)
                    <div class="flex-1 rounded-t-md" style="height: {{ rand(30, 95) }}%; background: {{ $bar === 14 ? 'var(--theme-accent)' : 'rgba(var(--theme-accent-rgb), 0.25)' }};"></div>
                @endforeach
            </div>
        </x-ui.card>

        <x-ui.card class="space-y-4 p-5">
            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Agent activity') }}</p>
            <ul class="space-y-3 text-sm" style="color: var(--theme-muted-text-color);">
                <li class="flex gap-2"><span class="text-xs text-zinc-400">17:40</span> {{ __('Drafted 3 captions for Instagram Stories') }}</li>
                <li class="flex gap-2"><span class="text-xs text-zinc-400">16:05</span> {{ __('Replied to 14 comments on the launch post') }}</li>
                <li class="flex gap-2"><span class="text-xs text-zinc-400">12:22</span> {{ __('Flagged a LinkedIn reply for review') }}</li>
            </ul>
        </x-ui.card>
    </div>

    <x-ui.card class="overflow-hidden p-0">
        <div class="flex items-center justify-between border-b px-5 py-4" style="border-color: var(--theme-border-color);">
            <div>
                <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ __('Upcoming posts') }}</p>
                <p class="text-xs" style="color: var(--theme-muted-text-color);">{{ __('Scheduled content for this workspace') }}</p>
            </div>
            @if (Route::has('portal.publishing.calendar'))
                <x-ui.button href="{{ route('portal.publishing.calendar') }}" variant="outline" size="sm" wire:navigate>{{ __('View all') }}</x-ui.button>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase" style="color: var(--theme-muted-text-color);">
                    <tr>
                        <th class="px-5 py-3">{{ __('Post') }}</th>
                        <th class="px-5 py-3">{{ __('Channel') }}</th>
                        <th class="px-5 py-3">{{ __('When') }}</th>
                        <th class="px-5 py-3">{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentPosts as $post)
                        @php
                            $caption = data_get($post->data, 'caption', __('Untitled post'));
                            $statusLabel = match ((int) $post->status) {
                                \Modules\AppPublishing\Models\PublishingPost::STATUS_SCHEDULED => __('Scheduled'),
                                \Modules\AppPublishing\Models\PublishingPost::STATUS_PUBLISHED => __('Published'),
                                \Modules\AppPublishing\Models\PublishingPost::STATUS_DRAFT => __('Draft'),
                                default => __('Pending'),
                            };
                        @endphp
                        <tr class="border-t" style="border-color: var(--theme-border-color);">
                            <td class="px-5 py-3 font-medium" style="color: var(--theme-header-text-color);">{{ Str::limit($caption, 48) }}</td>
                            <td class="px-5 py-3" style="color: var(--theme-muted-text-color);">{{ __('Social') }}</td>
                            <td class="px-5 py-3" style="color: var(--theme-muted-text-color);">
                                {{ $post->time_post ? \Carbon\Carbon::createFromTimestamp($post->time_post)->format('M j, g:ia') : '—' }}
                            </td>
                            <td class="px-5 py-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-semibold" style="background: rgba(var(--theme-accent-rgb), 0.12); color: var(--theme-accent);">{{ $statusLabel }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-sm" style="color: var(--theme-muted-text-color);">
                                {{ __('No posts yet. Open the publishing calendar to schedule content.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</div>
