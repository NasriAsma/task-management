<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    private const PER_PAGE = 15;

    // --- SECTION : MES TÂCHES ---

    public function index(Request $request)
    {
        $tasks = $request->user()->tasksAssigned()->paginate(self::PER_PAGE);
        return response()->json($tasks);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Task::class);

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date'    => 'nullable|date',
            'priority'    => 'nullable|in:low,medium,high',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        // Sécurité : On force le créateur à être l'utilisateur connecté
        $data['created_by'] = $request->user()->id;
        $task = Task::create($data);

        return response()->json(['message' => 'Task created', 'task' => $task], 201);
    }

    public function show($id)
    {
        $task = Task::with(['creator', 'assignee'])->findOrFail($id);
        $this->authorize('view', $task);

        return response()->json($task);
    }

    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $this->authorize('update', $task);

        $data = $request->validate([
            'title'    => 'sometimes|string|max:255',
            'priority' => 'sometimes|in:low,medium,high',
            'status'   => 'sometimes|in:pending,in_progress,completed',
        ]);

        $task->update($data);
        return response()->json(['message' => 'Task updated', 'task' => $task]);
    }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $this->authorize('delete', $task);

        $task->delete();
        return response()->json(['message' => 'Task deleted']);
    }

    // --- SECTION : ÉQUIPE (MANAGER) ---

    public function viewTeamMemberTasks(Request $request, $userId)
    {
        // On récupère les tâches créées par le manager pour cet utilisateur
        $tasks = Task::where('created_by', $request->user()->id)
                     ->where('assigned_to', $userId)
                     ->paginate(self::PER_PAGE);

        return response()->json($tasks);
    }

    public function assignTask(Request $request, $taskId, $userId)
    {
        $task = Task::findOrFail($taskId);
        $this->authorize('update', $task); // Seul le créateur peut réassigner

        $task->update(['assigned_to' => $userId]);
        return response()->json(['message' => 'Task assigned']);
    }

    // --- SECTION : STATUTS ET STATISTIQUES ---

    public function updateStatus(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $this->authorize('updateStatus', $task); // Assigné OU Créateur autorisé

        $data = $request->validate(['status' => 'required|in:pending,in_progress,completed']);
        $task->update(['status' => $data['status']]);

        return response()->json(['message' => 'Status updated']);
    }

    public function getTaskStatistics()
    {
        // On vérifie si l'utilisateur a le droit de voir les stats globales
        $this->authorize('viewGlobalStats', Task::class);

        return response()->json([
            'total_tasks' => Task::count(),
            'completed'   => Task::where('status', 'completed')->count(),
            'pending'     => Task::where('status', 'pending')->count(),
        ]);
    }

    public function getUserTaskCount($userId, Request $request)
    {
        // Un utilisateur ne peut voir que son propre compteur (ou un admin)
        if ($request->user()->id != $userId && $request->user()->role !== 'admin') {
            abort(403);
        }

        $count = Task::where('assigned_to', $userId)->count();
        return response()->json(['count' => $count]);
    }
}