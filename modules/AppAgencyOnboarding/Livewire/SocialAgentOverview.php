<?php

namespace Modules\AppAgencyOnboarding\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;
use Modules\AppPublishing\Models\PublishingPost;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

#[Title('Social Media overview')]
class SocialAgentOverview extends Component
{
    public function mount(): void
    {
        $onboarding = app(AgencyOnboarding::class);
        $user = auth()->user();

        abort_unless($user && $onboarding->isComplete($user), 403);
        abort_unless(in_array('social', $onboarding->enabledAgentsForProject($user), true), 403);
    }

  public function render(): View
    {
        $onboarding = app(AgencyOnboarding::class);
        $user = auth()->user();
        $project = $onboarding->activeProject($user);

        if ($project && $project->active_agent !== 'social') {
            $project->forceFill(['active_agent' => 'social'])->save();
        }

        $postsQuery = PublishingPost::query()->ownedBy((int) $user?->id);
        $scheduledCount = (clone $postsQuery)
            ->where('status', PublishingPost::STATUS_SCHEDULED)
            ->where('time_post', '>=', now()->timestamp)
            ->where('time_post', '<=', now()->addDays(7)->endOfDay()->timestamp)
            ->count();

        $recentPosts = (clone $postsQuery)
            ->orderByDesc('created')
            ->limit(5)
            ->get(['id', 'data', 'status', 'created', 'time_post']);

        return view('appagencyonboarding::livewire.social-agent-overview', [
            'project' => $project,
            'metrics' => [
                'followers' => '48.2k',
                'followers_delta' => '+3.1%',
                'engagement' => '4.7%',
                'engagement_delta' => '+0.6 pts',
                'scheduled' => (string) max($scheduledCount, $recentPosts->where('status', 'scheduled')->count()),
                'reach' => '612k',
                'reach_delta' => '+12%',
            ],
            'recentPosts' => $recentPosts,
            'team' => TeamWorkspaceAccess::activeTeam($user),
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('Social Media overview'),
            'shellArea' => 'user',
            'hideSidebar' => false,
        ]);
    }
}
