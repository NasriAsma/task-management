<?php

namespace App\Http\Middleware;

use App\Models\Audit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class LogActivity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
      $response = $next($request);

      $route = $request->route();
      $routeNameOrPath = $route ? ($route->getName() ?? $route->uri() ?? $request->path()) : $request->path();

      $payload = [
        'type' => 'http_request',
        'action' => null,
        'message' => 'Global HTTP Request',
        'method' => $request->method(),
        'url' => $request->fullUrl(),
        'route' => $routeNameOrPath,
        'ip' => $request->ip(),
        'user_id' => $request->user() ? $request->user()->id : null,
        'user_agent' => $request->userAgent(),
        'parameters' => $request->except(['password', 'password_confirmation']),
        'status' => $response->getStatusCode(),
      ];

      Log::info('Global HTTP Request', $payload);

      try {
        Audit::create([
          'event' => strtoupper($request->method()),
          'user_id' => $payload['user_id'],
          'values' => $payload,
          'url' => $request->fullUrl(),
          'ip_address' => $request->ip(),
          'user_agent' => $request->userAgent(),
          'description' => 'Global HTTP Request',
        ]);
      } catch (Throwable $e) {
        Log::warning('Audit database save failed', [
          'error' => $e->getMessage(),
        ]);
      }

      return $response;

    }
}