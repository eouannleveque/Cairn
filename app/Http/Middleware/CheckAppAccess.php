<?php

namespace App\Http\Middleware;

use App\Support\Modules\ModuleManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAppAccess
{
    public function __construct(protected ModuleManager $modules) {}

    /**
     * Usage dans les routes de module: ->middleware('app.access:weed-count')
     */
    public function handle(Request $request, Closure $next, string $slug): Response
    {
        $user = $request->user();

        abort_unless($user, 401);
        abort_unless($this->modules->userHasAccess($user, $slug), 403, "Vous n'avez pas acces a cette application.");

        return $next($request);
    }
}
