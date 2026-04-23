<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\TaskRequest;
use App\Http\Requests\TaskStatusRequest;

class TaskController extends Controller
{
    private const PER_PAGE = 15;

    private const SAFE_TASK_COLUMNS = [
        'id',
        'title',
        'description',
        'status',
        'deadline',
        'priority',
        'assigned_to',
        'created_by',
        'created_at',
        'updated_at',
    ];

    private function safeTasksQuery()
    {
        return Task::query()
            ->select(self::SAFE_TASK_COLUMNS)
            ->with([
                'creator:id,name,email',
                'assignee:id,name,email',
            ]);
    }

    private function formatTask(Task $task): array
    {
        return [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status,
            'deadline' => $task->deadline,
            'priority' => $task->priority,
            'assigned_to' => $task->assigned_to,
            'created_by' => $task->created_by,
            'created_at' => optional($task->created_at)->toJSON(),
            'updated_at' => optional($task->updated_at)->toJSON(),
            'creator' => $task->creator ? [
                'id' => $task->creator->id,
                'name' => $task->creator->name,
                'email' => $task->creator->email,
            ] : null,
            'assignee' => $task->assignee ? [
                'id' => $task->assignee->id,
                'name' => $task->assignee->name,
                'email' => $task->assignee->email,
            ] : null,
        ];
    }

    // --- SECTION : MES TÂCHES ---

    public function index(Request $request)
    {
        $this->logRequest($request, __FUNCTION__);
        $query = $this->safeTasksQuery();

        if ($request->user()->hasRole('employee')) {
            $query->where('assigned_to', $request->user()->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function ($subQuery) use ($search) {
                if (is_numeric($search)) {
                    $subQuery->orWhere('id', (int) $search);
                }

                $subQuery
                    ->orWhere('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $perPage = (int) $request->query('per_page', self::PER_PAGE);
        $tasks = $query->paginate($perPage);

        $data = $tasks->getCollection()->map(function (Task $task) {
            return $this->formatTask($task);
        })->all();

        return response()->json([
            'current_page' => $tasks->currentPage(),
            'data' => $data,
            'first_page_url' => $tasks->url(1),
            'from' => $tasks->firstItem(),
            'last_page' => $tasks->lastPage(),
            'last_page_url' => $tasks->url($tasks->lastPage()),
            'links' => [],
            'next_page_url' => $tasks->nextPageUrl(),
            'path' => $tasks->path(),
            'per_page' => $tasks->perPage(),
            'prev_page_url' => $tasks->previousPageUrl(),
            'to' => $tasks->lastItem(),
            'total' => $tasks->total(),
        ]);
    }

    public function store(TaskRequest $request) 
{
    $this->logRequest($request, __FUNCTION__);
    $this->authorize('create', Task::class);

    $data = $request->validated(); // <-- Récupère les données déjà validées
    $data['created_by'] = $request->user()->id;
    
    $task = Task::create($data);
    return response()->json(['message' => 'Task created', 'task' => $task], 201);
}
    public function show(Request $request, $id)
    {
        $this->logRequest($request, __FUNCTION__);
        $task = Task::with(['creator', 'assignee'])->findOrFail($id);
        $this->authorize('view', $task);

        return response()->json($task);
    }

    public function update(TaskRequest $request, $id)
    {
        $this->logRequest($request, __FUNCTION__);
        $task = Task::findOrFail($id);
        $this->authorize('update', $task);

        $data = $request->validated();
       

        $task->update($data);
        return response()->json(['message' => 'Task updated', 'task' => $task]);
    }

    public function destroy(Request $request, $id)
    {
        $this->logRequest($request, __FUNCTION__);
        $task = Task::findOrFail($id);
        $this->authorize('delete', $task);

        $task->delete();
        return response()->json(['message' => 'Task deleted']);
    }

    // --- SECTION : ÉQUIPE (MANAGER) ---

    public function viewTeamMemberTasks(Request $request, $userId)
    {
        $this->logRequest($request, __FUNCTION__);
        // On récupère les tâches créées par le manager pour cet utilisateur
        $tasks = Task::where('created_by', $request->user()->id)
                     ->where('assigned_to', $userId)
                     ->paginate(self::PER_PAGE);

        return response()->json($tasks);
    }

    public function assignTask(Request $request, $taskId, $userId)
    {
        $this->logRequest($request, __FUNCTION__);
        $task = Task::findOrFail($taskId);
        $this->authorize('update', $task); // Seul le créateur peut réassigner

        $task->update(['assigned_to' => $userId]);
        return response()->json(['message' => 'Task assigned']);
    }

    // --- SECTION : STATUTS ET STATISTIQUES ---

    public function updateStatus(TaskStatusRequest $request, $id)
    {
        $this->logRequest($request, __FUNCTION__);
        $task = Task::findOrFail($id);
        $this->authorize('updateStatus', $task); // Assigné OU Créateur autorisé

        $data = $request->validated();
        $task->update(['status' => $data['status']]);

        return response()->json(['message' => 'Status updated']);
    }

    public function getTaskStatistics(Request $request)
    {
        $this->logRequest($request, __FUNCTION__);
        // On vérifie si l'utilisateur a le droit de voir les stats globales
        $this->authorize('viewGlobalStats', Task::class);

        return response()->json([
            'total_tasks' => Task::count(),
            'completed'   => Task::where('status', 'completed')->count(),
            'pending'     => Task::where('status', 'pending')->count(),
        ]);
    }

    public function getTaskCount(Request $request)
    {
        $this->logRequest($request, __FUNCTION__);
        $this->authorize('viewGlobalStats', Task::class);

        return response()->json([
            'total_tasks' => Task::count(),
        ]);
    }

    public function getUserTaskCount(Request $request, $userId)
    {
        $this->logRequest($request, __FUNCTION__);
        // Un utilisateur ne peut voir que son propre compteur (ou un admin)
        if ($request->user()->id != $userId && !$request->user()->hasRole('admin')) {
            abort(403);
        }

        $count = Task::where('assigned_to', $userId)->count();
        return response()->json(['count' => $count]);
    }
}