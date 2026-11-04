<?php

namespace Modules\AppPublishing\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\AppTeams\Support\TeamWorkspaceAccess;
use Symfony\Component\HttpFoundation\Response;

class EnsurePortalPublishingTeamContext
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->routeIs('portal.publishing.*')) {
            TeamWorkspaceAccess::ensurePublishingTeamSession($request->user());
        }

        return $next($request);
    }
}
