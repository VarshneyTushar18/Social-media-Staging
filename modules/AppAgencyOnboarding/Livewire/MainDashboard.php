<?php

namespace Modules\AppAgencyOnboarding\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AdminPaymentHistory\Models\PaymentHistory;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;
use Modules\AppAgencyOnboarding\Support\AgentCatalog;

#[Title('Main dashboard overview')]
class MainDashboard extends Component
{
    public function mount(): void
    {
        $onboarding = app(AgencyOnboarding::class);
        $user = auth()->user();

        abort_unless($user && $onboarding->isComplete($user), 403);
    }

    public function render(): View
    {
        $onboarding = app(AgencyOnboarding::class);
        $user = auth()->user();
        $project = $onboarding->activeProject($user);
        $enabledAgents = $onboarding->enabledAgentsForProject($user);
        $plan = $user?->plan;

        $agentCards = collect(AgentCatalog::all())
            ->map(function (array $agent, string $key) use ($enabledAgents, $onboarding, $user): array {
                $isOn = in_array($key, $enabledAgents, true);
                $routeName = (string) ($agent['route'] ?? '');
                $routeParams = (array) ($agent['route_params'] ?? []);
                $canOpen = $isOn && $routeName !== '' && Route::has($routeName);
                $openUrl = $canOpen
                    ? ($routeParams === [] ? route($routeName) : route($routeName, $routeParams))
                    : null;

                return [
                    'key' => $key,
                    'label' => (string) ($agent['label'] ?? $key),
                    'short' => (string) ($agent['short'] ?? '?'),
                    'description' => (string) ($agent['description'] ?? ''),
                    'color' => (string) ($agent['color'] ?? 'var(--theme-accent)'),
                    'is_on' => $isOn,
                    'open_url' => $openUrl,
                    'allowed_by_plan' => $onboarding->canUseAgent($user, $key),
                ];
            })
            ->values()
            ->all();

        $projectsCount = $onboarding->projectsForUser($user)->count();
        $activeAgentsCount = count($enabledAgents);
        $totalAgents = count(AgentCatalog::all());

        $planPrice = $plan?->price;
        $planInterval = match ((int) ($plan?->type ?? 1)) {
            2 => __('year'),
            3 => __('lifetime'),
            default => __('month'),
        };

        $recentInvoices = class_exists(PaymentHistory::class)
            ? PaymentHistory::query()
                ->where('uid', $user?->id)
                ->orderByDesc('created')
                ->limit(5)
                ->get()
            : collect();

        return view('appagencyonboarding::livewire.main-dashboard', [
            'project' => $project,
            'user' => $user,
            'plan' => $plan,
            'agentCards' => $agentCards,
            'metrics' => [
                'plan_name' => $plan?->name ?: __('No plan'),
                'plan_price' => is_numeric($planPrice) ? (($plan?->currency_symbol ?: '$').number_format((float) $planPrice, 0).' '.__('per :interval', ['interval' => $planInterval])) : null,
                'agents_label' => __(':active of :total', ['active' => $activeAgentsCount, 'total' => $totalAgents]),
                'agents_hint' => __('Across this project'),
                'projects_count' => (string) $projectsCount,
                'projects_hint' => $project?->name
                    ? __('Active: :name', ['name' => $project->name])
                    : __('No active project'),
                'expiry' => (($user?->isInPlanTrial() ? $user?->trialEndsAt() : $user?->plan_expires_at)?->format('Y-m-d')) ?: __('Unlimited'),
            ],
            'recentInvoices' => $recentInvoices,
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('Main dashboard overview'),
            'shellArea' => 'user',
        ]);
    }
}
