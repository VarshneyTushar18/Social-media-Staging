<?php

namespace Modules\AppAgencyOnboarding\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;

class StoreOnboardingPlanController extends Controller
{
    public function __invoke(Request $request, AgencyOnboarding $onboarding): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user, 401);

        $action = (string) $request->input('action', 'select');

        try {
            if ($action === 'continue') {
                if (! $user->plan_id) {
                    return redirect()
                        ->route('portal.onboarding.plan')
                        ->withErrors(['plan' => __('Select a plan to continue.')]);
                }

                $onboarding->completeOnboardingPlanSelection($user, (int) $user->plan_id, false);
            } else {
                $validated = $request->validate([
                    'plan_id' => ['required', 'integer'],
                    'require_free' => ['nullable', 'boolean'],
                ]);

                $requireFree = filter_var($validated['require_free'] ?? false, FILTER_VALIDATE_BOOLEAN);

                $onboarding->completeOnboardingPlanSelection(
                    $user,
                    (int) $validated['plan_id'],
                    $requireFree,
                );
            }
        } catch (ModelNotFoundException) {
            return redirect()
                ->route('portal.onboarding.plan')
                ->withErrors(['plan' => __('That plan is not available. Pick another plan or refresh the page.')]);
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->route('portal.onboarding.plan')
                ->withErrors(['plan' => __('Could not activate this plan. Try again or contact support.')]);
        }

        $request->session()->save();

        return redirect()->route('portal.onboarding.project');
    }
}
