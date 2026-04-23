<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Journalise une action spécifique depuis un contrôleur enfant.
     *
     * @param Request $request
     * @param string $action
     */

    protected function logRequest(Request $request, string $action)
{
    $route = $request->route();
    $routeNameOrPath = $route ? ($route->getName() ?? $route->uri() ?? $request->path()) : $request->path();

    Log::info("Action Controller : {$action}", [
        'type'       => 'controller_action',
        'action'     => $action,
        'message'    => "Action Controller : {$action}",
        'method'     => $request->method(),
        'url'        => $request->path(),
        'route'      => $routeNameOrPath,
        'ip'         => $request->ip(),
        'user_id'    => $request->user() ? $request->user()->id : null,
        'user_agent' => $request->userAgent(),
        'parameters' => $request->except(['password', 'password_confirmation']),
        'status'     => null,
    ]);
}
}