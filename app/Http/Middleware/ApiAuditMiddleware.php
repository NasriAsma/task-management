<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Models\routes as RouteModel;
use App\Models\audits as AuditInteraction;

class ApiAuditMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */

    private const EXCLUDED_PATHS = [
        'api/voip/call-stats',
        'api/voip/trafic-stats',
        'api/voip/trafic-interco-stats',
        'api/voip/operators-consumption',
        'api/voip/hangupcause',
        'api/tickets/metrics',
    ];

    private const SENSITIVE_REQUEST_KEYS = [
        'password',
        'password_confirmation',
        'token',
    ];

    private const SENSITIVE_RESPONSE_KEYS = [
        'access_token',
        'token',
        'refresh_token',
    ];





public function handle(Request $request, Closure $next)
{    
    $response = $next($request);
    if (!$this->shouldAudit($request)) {
        return $response;
    }

    try {
        $this->storeAudit($request, $response);
    } catch (\Throwable $e) {
        Log::error('Audit failed', ['error' => $e->getMessage()]);
    }

    return $response;
}

    


// verifier si la requette doit etre auditer
private function shouldAudit(Request $request): bool
{   
    return str_starts_with($request->path(), 'api/') 
           && $request->route()
           && !in_array($request->path(), self::EXCLUDED_PATHS, true);
}


private function filterArray(array &$data, array $keysToFilter): void
{  // CE FONCTION A POUR BUT DE FILTRER LES DONNES SENSIBLES DANS UN TABLEAU
   // comme LES MOTS DE PASSE, LES TOKENS, ETC.
    foreach ($data as $key => &$value) {
        if (in_array($key, $keysToFilter, true)) {
            $value = '[FILTERED]';
        } elseif (is_array($value)) {
            $this->filterArray($value, $keysToFilter);
        }
    }
}


private function resolveEventName(Request $request): string
{   // CE FONCTION A POUR Donne un nom lisible à la route pour l’audit
    $routePath = '/' . $request->route()->uri();

    return RouteModel::where('path', $routePath)
        ->value('name') ?? $routePath;
}


    


    // 3   enregistre audit dans le bd
    private function storeAudit(Request $request, $response): void
    {
        $user = Auth::user();
        $event = $this->resolveEventName($request);

        $requestPayload = $request->all();
        $this->filterArray($requestPayload, self::SENSITIVE_REQUEST_KEYS);

        $responsePayload = [];
        if ($response instanceof JsonResponse && $response->isSuccessful()) {
            $responsePayload = json_decode($response->getContent(), true) ?? [];
            $this->filterArray($responsePayload, self::SENSITIVE_RESPONSE_KEYS);
        }

        AuditInteraction::create([
            'event' => $event,
            'user_id' => $user?->id,
            'values' => json_encode([
                'method' => $request->method(),
                'route' => $request->path(),
                'status' => $response->status(),
                'request_payload' => $requestPayload,
                'response_payload' => $responsePayload,
            ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'description' => 'API call audit',
        ]);
    }
}
