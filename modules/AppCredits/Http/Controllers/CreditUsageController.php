<?php

namespace Modules\AppCredits\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\AppCredits\Models\CreditUsageLog;
use Modules\AppCredits\Models\CreditTopupLedger;
use Modules\AppCredits\Support\CreditSettings;
use Modules\AppCredits\Support\CreditTopupService;
use Modules\AppCredits\Support\PortalCreditAccess;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

class CreditUsageController extends Controller
{
    public function index(Request $request, CreditTopupService $topups, CreditSettings $settings): View
    {
        [$user, $team, $planOwner] = $this->context();

        $agentFilter = $request->string('agent')->toString();
        $agentFilter = $agentFilter === '' ? 'all' : $agentFilter;

        $agentOptions = PortalCreditAccess::agentFilterOptions($user);

        if (! array_key_exists($agentFilter, $agentOptions)) {
            $agentFilter = 'all';
        }

        $prefixes = PortalCreditAccess::actionPrefixesForFilter($agentFilter, $user);

        $query = CreditUsageLog::query()
            ->where('user_id', $planOwner?->id);

        if ($prefixes !== []) {
            $query->where(function ($builder) use ($prefixes): void {
                foreach ($prefixes as $index => $prefix) {
                    if ($index === 0) {
                        $builder->where('action_key', 'like', $prefix);
                    } else {
                        $builder->orWhere('action_key', 'like', $prefix);
                    }
                }
            });
        } else {
            $query->whereRaw('1 = 0');
        }

        $query
            ->when($request->string('action')->toString() !== '', fn ($builder) => $builder->where('action_key', $request->string('action')->toString()));

        $summaryQuery = clone $query;
        $entriesCount = (clone $summaryQuery)->count();
        $creditsUsed = (int) (clone $summaryQuery)->sum('amount');
        $actionsCount = (clone $summaryQuery)->distinct('action_key')->count('action_key');
        $topupLedger = CreditTopupLedger::query()
            ->where('user_id', $planOwner?->id)
            ->latest('id')
            ->limit(12)
            ->get();

        $canBuyMoreCredits = $planOwner
            && $user
            && (int) $planOwner->id === (int) $user->id
            && $topups->canUserBuyTopup($planOwner);

        $hasAiStudioPortal = TeamWorkspaceAccess::teamHasModule($team, 'publishing')
            && (! $planOwner?->plan || $planOwner->hasPlanFeature('ai_studio'));

        return view('appcredits::portal.index', [
            'logs' => $query->latest('id')->paginate(25)->withQueryString(),
            'entriesCount' => $entriesCount,
            'creditsUsed' => $creditsUsed,
            'actionsCount' => $actionsCount,
            'creditSummary' => function_exists('credit_summary') ? credit_summary($planOwner) : ['remaining' => null, 'unlimited' => true],
            'actionOptions' => PortalCreditAccess::actionOptionsForPrefixes($prefixes),
            'agentFilter' => $agentFilter,
            'agentOptions' => $agentOptions,
            'workspaceOwner' => $planOwner,
            'canBuyMoreCredits' => $canBuyMoreCredits,
            'creditPacks' => $canBuyMoreCredits ? $topups->availablePacksFor($planOwner) : collect(),
            'creditTopupRemaining' => $topups->remaining($planOwner),
            'creditSettings' => $settings,
            'topupLedger' => $topupLedger,
            'showAiStudioShortcuts' => $hasAiStudioPortal,
        ]);
    }

    protected function context(): array
    {
        $user = auth()->user();
        $team = TeamWorkspaceAccess::activeTeam($user);
        $planOwner = $team?->owner ?: $user;

        abort_unless(PortalCreditAccess::userCanViewPortalCredits($user), 404);

        return [$user, $team, $planOwner];
    }
}
