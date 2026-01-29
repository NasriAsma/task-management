<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Audits;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuditController extends Controller
{
    public function getUserAudits(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'per_page'   => 'integer|min:1|max:100',
            'page'       => 'integer|min:1',
            'start_date' => 'date',
            'end_date'   => 'date|after_or_equal:start_date',
            'event'      => 'string|max:255',
            'sort_order' => 'in:asc,desc',
        ]);

        $query = Audits::where('user_id', $user->id);

        if (!empty($validated['start_date'])) {
            $query->whereDate('created_at', '>=', $validated['start_date']);
        }

        if (!empty($validated['end_date'])) {
            $query->whereDate('created_at', '<=', $validated['end_date']);
        }

        if (!empty($validated['event'])) {
            $query->where('event', 'LIKE', '%' . $validated['event'] . '%');
        }

        $audits = $query
            ->orderBy('created_at', $validated['sort_order'] ?? 'desc')
            ->paginate($validated['per_page'] ?? 15);

        return response()->json([
            'status' => 'success',
            'user' => $user->only(['id', 'name', 'email']),
            'data' => $audits->getCollection()->map(fn ($audit) => [
                'id' => $audit->id,
                'event' => $audit->event,
                'values' => json_decode($audit->values, true),
                'url' => $audit->url,
                'ip_address' => $audit->ip_address,
                'user_agent' => $audit->user_agent,
                'description' => $audit->description,
                'created_at' => $audit->created_at,
            ]),
            'pagination' => [
                'current_page' => $audits->currentPage(),
                'last_page' => $audits->lastPage(),
                'per_page' => $audits->perPage(),
                'total' => $audits->total(),
            ],
            'filters' => $validated,
        ]);
    }
}
