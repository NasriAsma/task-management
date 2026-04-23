<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Audit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->logRequest($request, __FUNCTION__);

        return $this->listAudits($request);
    }

    public function userAudits(Request $request, int $userId): JsonResponse
    {
        $this->logRequest($request, __FUNCTION__);

        $authUser = $request->user();
        $canViewOtherUsers = $authUser && ($authUser->hasRole('admin') || $authUser->hasPermission('view_audits'));

        if ((int) $authUser->id !== $userId && !$canViewOtherUsers) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden - You can only view your own audits.',
            ], 403);
        }

        return $this->listAudits($request, $userId);
    }

    private function listAudits(Request $request, ?int $forcedUserId = null): JsonResponse
    {

        $validated = $request->validate([
            'per_page' => 'integer|min:1|max:100',
            'page' => 'integer|min:1',
            'sort_order' => 'in:asc,desc',
            'start_date' => 'date',
            'end_date' => 'date|after_or_equal:start_date',
            'method' => 'string|max:16',
            'action' => 'string|max:255',
            'user_id' => 'integer|min:1',
            'search' => 'string|max:255',
        ]);

        if ($forcedUserId !== null) {
            $validated['user_id'] = $forcedUserId;
        }

        $query = $this->buildAuditQuery($validated);

        $sortOrder = $validated['sort_order'] ?? 'desc';
        $perPage = (int) ($validated['per_page'] ?? 15);
        $page = (int) ($validated['page'] ?? 1);

        $audits = $query
            ->orderBy('created_at', $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);

        $data = collect($audits->items())
            ->map(fn (Audit $audit) => $this->transformAudit($audit))
            ->values();

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'pagination' => [
                'current_page' => $audits->currentPage(),
                'last_page' => $audits->lastPage(),
                'per_page' => $perPage,
                'total' => $audits->total(),
            ],
            'filters' => $validated,
        ]);
    }

    private function buildAuditQuery(array $filters): Builder
    {
        $query = Audit::query()->with('user:id,name,email');

        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', Carbon::parse($filters['start_date'])->toDateString());
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', Carbon::parse($filters['end_date'])->toDateString());
        }

        if (!empty($filters['method'])) {
            $query->where('event', strtoupper((string) $filters['method']));
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', (int) $filters['user_id']);
        }

        if (!empty($filters['action'])) {
            $action = (string) $filters['action'];
            $query->where(function (Builder $builder) use ($action) {
                $builder
                    ->where('description', 'like', '%' . $action . '%')
                    ->orWhere('values', 'like', '%"action":"' . $action . '"%');
            });
        }

        if (!empty($filters['search'])) {
            $search = (string) $filters['search'];
            $query->where(function (Builder $builder) use ($search) {
                $builder
                    ->where('event', 'like', '%' . $search . '%')
                    ->orWhere('url', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('values', 'like', '%' . $search . '%')
                    ->orWhere('user_id', 'like', '%' . $search . '%');
            });
        }

        return $query;
    }

    private function transformAudit(Audit $audit): array
    {
        $values = is_array($audit->values) ? $audit->values : [];

        return [
            'id' => $audit->id,
            'type' => $values['type'] ?? 'http_request',
            'action' => $values['action'] ?? null,
            'message' => $values['message'] ?? $audit->description,
            'method' => $values['method'] ?? $audit->event,
            'url' => $values['url'] ?? $audit->url,
            'route' => $values['route'] ?? null,
            'status' => $values['status'] ?? null,
            'ip' => $values['ip'] ?? $audit->ip_address,
            'user_id' => $audit->user_id,
            'user' => $audit->user,
            'user_agent' => $values['user_agent'] ?? $audit->user_agent,
            'parameters' => $values['parameters'] ?? [],
            'created_at' => optional($audit->created_at)->toISOString(),
        ];
    }
}
