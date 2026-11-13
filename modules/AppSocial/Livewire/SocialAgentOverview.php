<?php

namespace Modules\AppSocial\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;

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

        return view('appsocial::livewire.social-agent-overview', [
            'project' => $project,
            'dashboardItems' => $this->arrangeSocialDashboardItems(
                user_dashboard_items($user, 'main', 'social-overview')
            ),
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('Social Media overview'),
            'shellArea' => 'user',
            'hideSidebar' => false,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function arrangeSocialDashboardItems(array $items): array
    {
        $priority = [
            'app-publishing.summary' => 0,
            'app-channels.summary' => 1,
            'app-ai-studio.summary' => 2,
        ];

        return collect($items)
            ->values()
            ->sort(function (array $a, array $b) use ($priority): int {
                $rankA = $priority[$a['id'] ?? ''] ?? 100;
                $rankB = $priority[$b['id'] ?? ''] ?? 100;

                if ($rankA === $rankB) {
                    return ($a['order'] ?? 100) <=> ($b['order'] ?? 100);
                }

                return $rankA <=> $rankB;
            })
            ->values()
            ->all();
    }
}
