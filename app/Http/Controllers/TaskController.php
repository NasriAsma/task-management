<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;

class TaskController extends Controller
{
    private const PER_PAGE_DEFAULT = 15;

    /**
     * Get all tasks for authenticated user
     */
    public function viewAllTasks(Request $request)
    {
        $user = $request->user();
        $this->authorize('viewAny', Task::class);

        $perPage = (int) $request->query('per_page', self::PER_PAGE_DEFAULT);
        $tasks = $user->tasks()->paginate($perPage);

        return response()->json($tasks);
    }

    /**
     * Create a new task for a user
     */
    public function store(Request $request, $userId)
    {
        $this->authorize('create', Task::class);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'status' => 'nullable|in:pending,in_progress,completed',
            'deadline' => 'nullable|date',
            'priority' => 'nullable|in:low,medium,high',
            'assigned_to' => 'nullable|exists:users,id',
            'created_by' => 'exists:users,id',
        ]);

        $user = User::findOrFail($userId);
        $task = $user->tasks()->create($data);

        return response()->json([
            'message' => 'Task created successfully',
            'task' => $task,
        ], 201);
    }

    /**
     * Get a single task by ID
     */
    public function viewOwnTask($id)
    {
        $task = Task::findOrFail($id);
        $this->authorize('view', $task);

        return response()->json($task);
    }

    /**
     * Update a task
     */
    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $this->authorize('update', $task);

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'due_date' => 'sometimes|nullable|date',
            'status' => 'sometimes|in:pending,in_progress,completed',
            'deadline' => 'sometimes|nullable|date',
            'priority' => 'sometimes|in:low,medium,high',
            'assigned_to' => 'sometimes|nullable|exists:users,id',
        ]);

        $task->update($data);

        return response()->json([
            'message' => 'Task updated successfully',
            'task' => $task,
        ]);
    }

    /**
     * Delete a task
     */
    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully']);
    }

    /**
     * Assign task to a user
     */
    public function assignTask(Request $request, $taskId, $userId)
    {
        $task = Task::findOrFail($taskId);
        User::findOrFail($userId);

        $task->assigned_to = $userId;
        $task->save();

        return response()->json([
            'message' => 'Task assigned successfully',
            'task' => $task,
        ]);
    }

    /**
     * Get tasks for a team member created by manager
     */
    public function viewTeamMemberTasks(Request $request, $userId)
    {
        $manager = $request->user();
        $perPage = (int) $request->query('per_page', self::PER_PAGE_DEFAULT);

        $tasks = Task::where('created_by', $manager->id)
                      ->where('assigned_to', $userId)
                      ->with(['assignee:id,name,email'])
                      ->paginate($perPage);

        abort_if($tasks->isEmpty(), 404, 'Tasks not found');

        $user = User::findOrFail($userId);

        return response()->json([
            'message' => 'Team member tasks retrieved successfully',
            'user' => $user->only(['id', 'name', 'email']),
            'tasks' => $tasks,
            'total' => $tasks->total(),
        ]);
    }

    /**
     * Get task details with creator and assignee info
     */
    public function viewTaskDetails($id)
    {
        $task = Task::with(['creator:id,name,email', 'assignee:id,name,email'])
                     ->findOrFail($id);

        $this->authorize('view', $task);

        return response()->json([
            'task' => $task,
            'created_by' => $task->creator->only(['id', 'name', 'email']),
            'assigned_to' => $task->assignee->only(['id', 'name', 'email']),
        ]);
    }

    /**
     * Update a task for team member
     */
    public function updateTeamTask(Request $request, $taskId, $userId)
    {
        $manager = $request->user();

        $task = Task::where('created_by', $manager->id)
                     ->where('assigned_to', $userId)
                     ->findOrFail($taskId);

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'due_date' => 'sometimes|nullable|date',
            'status' => 'sometimes|in:pending,in_progress,completed',
            'deadline' => 'sometimes|nullable|date',
            'priority' => 'sometimes|in:low,medium,high',
            'assigned_to' => 'sometimes|nullable|exists:users,id',
        ]);

        $task->update($data);

        return response()->json([
            'message' => 'Task updated successfully',
            'task' => $task,
        ]);
    }

    /**
     * Delete a team task
     */
    public function deleteTeamTask(Request $request, $userId, $taskId)
    {
        $manager = $request->user();

        $task = Task::where('created_by', $manager->id)
                     ->where('assigned_to', $userId)
                     ->findOrFail($taskId);

        $this->authorize('delete', $task);
        $task->delete();

        return response()->json(['message' => 'Task deleted successfully']);
    }

    /**
     * Create a task for team member
     */
    public function createTeamTask(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'status' => 'nullable|in:pending,in_progress,completed',
            'deadline' => 'nullable|date',
            'priority' => 'nullable|in:low,medium,high',
            'assigned_to' => 'nullable|exists:users,id',
            'created_by' => 'exists:users,id',
        ]);

        $task = $user->tasksAssigned()->create($data);

        return response()->json([
            'message' => 'Task created successfully',
            'task' => $task,
        ], 201);
    }

    /**
     * Get specific task for a user
     */
    public function viewTaskForUser(Request $request, $userId, $taskId)
    {
        User::findOrFail($userId);
        $task = Task::where('assigned_to', $userId)
                     ->where('id', $taskId)
                     ->firstOrFail();

        return response()->json(['task' => $task]);
    }

    /**
     * Get all tasks for a specific user
     */
    public function viewAllTasksForUser(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $perPage = (int) $request->query('per_page', self::PER_PAGE_DEFAULT);
        $tasks = $user->tasks()->paginate($perPage);

        return response()->json(['tasks' => $tasks]);
    }

    /**
     * Update task status for a user
     */
    public function updateTaskStatus(Request $request, $userId, $taskId)
    {
        User::findOrFail($userId);
        $this->authorize('updateStatus', Task::class);

        $task = Task::where('assigned_to', $userId)
                     ->where('id', $taskId)
                     ->firstOrFail();

        $data = $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $task->update(['status' => $data['status']]);

        return response()->json([
            'message' => 'Task status updated successfully',
            'task' => $task,
        ]);
    }

    /**
     * Get total number of all tasks
     */
    public function getTaskCount()
    {
        return response()->json(['number_of_tasks' => Task::count()]);
    }

    /**
     * Get number of tasks assigned to a user
     */
    public function getUserTaskCount($userId)
    {
        return response()->json([
            'number_of_tasks_for_user' => Task::where('assigned_to', $userId)->count(),
        ]);
    }

    /**
     * Get number of team tasks assigned to a user
     */
    public function getTeamTaskCount($userId, Request $request)
    {
        $manager = $request->user();
        $count = Task::where('assigned_to', $userId)
                      ->where('created_by', $manager->id)
                      ->count();

        return response()->json(['number_of_team_tasks_for_user' => $count]);
    }

    /**
     * Get task statistics
     */
    public function getTaskStatistics()
    {
        return response()->json([
            'total_tasks' => Task::count(),
            'completed_tasks' => Task::where('status', 'completed')->count(),
            'pending_tasks' => Task::where('status', 'pending')->count(),
            'in_progress_tasks' => Task::where('status', 'in_progress')->count(),
        ]);
    }
}
