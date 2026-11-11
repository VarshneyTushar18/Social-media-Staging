<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;
use Symfony\Component\HttpFoundation\Response;

class EnsureAgencyOnboarding
{
    public function __construct(
        protected AgencyOnboarding $onboarding,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $this->onboarding->requestExemptFromOnboarding($request)) {
            return $next($request);
        }

        if (! $this->onboarding->shouldEnforce($user)) {
            return $next($request);
        }

        if ($this->onboarding->isComplete($user)) {
            return $next($request);
        }

        $step = $this->onboarding->currentStep($user);
        $targetRoute = $this->onboarding->routeForStep($step);

        if ($request->routeIs($targetRoute)) {
            return $next($request);
        }

        return redirect()->route($targetRoute);
    }
}
