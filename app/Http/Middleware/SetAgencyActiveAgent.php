<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\AppAgencyOnboarding\Support\AgencyOnboarding;
use Symfony\Component\HttpFoundation\Response;

class SetAgencyActiveAgent
{
    public function __construct(
        protected AgencyOnboarding $onboarding,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->routeIs('portal.*')) {
            $user = $request->user();

            if ($user->isSuperAdmin()) {
                $this->onboarding->ensureSuperAdminWorkspace($user);
            }

            $this->onboarding->syncActiveAgentFromRequest($user);
        }

        return $next($request);
    }
}
