<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;


class TasksController extends Controller
{
     public function view_all_tasks(Request $request)
     {  
         $user = $request->user();
         $perPage = (int) $request->query('per_page', 15);
         $tasks = $user->tasks()->paginate($perPage);
         return response()->json($tasks);   
     }

     public function store(Request $request, $id)

     {
         $data=$request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'status' => 'nullable|in:pending,in_progress,completed',
            'deadline' => 'nullable|date',
            'priority' => 'nullable|in:low,medium,high',
            'assigned_to' => 'nullable|exists:users,id',
            'created_by' => 'exists:users,id',
        ]);

        $user = User::find($id);

        $task = $user->tasks()->create($data);

        return response()->json([
            'message' => 'Task created successfully',
            'task' => $task,
        ], 201);

     }


       public function view_own_tasks ($id)
{    $task = Task::find($id);
     if(!$task){
        return response()->json(['message'=>'Task not found'],404); }
        else {
        return response()->json($task,200); }
        }


        public function update(Request $request, $id)
        {
            $task = Task::find($id);
            if (!$task) {
                return response()->json(['message' => 'Task not found'], 404);
            }
    
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
            ], 200);
        }





        public function destroy_any_task($id)
        {
            $task = Task::find($id);
            if (!$task) {
                return response()->json(['message' => 'Task not found'], 404);
            }
    
            $task->delete();
    
            return response()->json([
                'message' => 'Task deleted successfully',
            ], 200);
        }




public function assignTask(Request $request, $taskId, $userId)
{
    $task = Task::findOrFail($taskId);
    $user = User::findOrFail($userId);

    $task->assigned_to = $userId;
    $task->save();

    return response()->json([
        'message' => 'Task assigned successfully',
        'task' => $task,
    ], 200);
}

public function viewTeamMemberTasks(Request $request, $userId)
{
    $manager = $request->user();
    
   
    $perPage = (int) $request->query('per_page', 15);
    $tasks = Task::where('created_by', $manager->id)
                   ->where('assigned_to', $userId)
                   ->with(['assignee:id,name,email'])
                   ->paginate($perPage);

    if ($tasks->isEmpty()) {
        return response()->json(['message' => 'Tasks not found'], 404);
    }

    $user = User::findOrFail($userId);

    return response()->json([
        'message' => 'Team member tasks retrieved successfully',
        'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
        'tasks' => $tasks,
        'total' => $tasks->total()
    ], 200);
}


public function view_task_details($id)
{
    $task = Task::with(['creator:id,name,email', 'assignee:id,name,email'])->find($id);

    if (!$task) {
        return response()->json(['message' => 'Task not found'], 404);
    }

    return response()->json([
        'task' => $task,
        'created_by' => [
            'id' => $task->creator->id,
            'name' => $task->creator->name,
            'email' => $task->creator->email,
        ],
        'assigned_to' => [
            'id' => $task->assignee->id,
            'name' => $task->assignee->name,
            'email' => $task->assignee->email,
        ],
    ], 200);
}



public function update_team_task(Request $request, $id, $userId)
{  
    $manager = $request->user();
    
    $task = Task::where('created_by', $manager->id)
                  ->where('assigned_to', $userId)
                  ->findOrFail($id);

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
    ], 200);
}

public function delete_team_task(Request $request, $userId, $id)
{
    $manager = $request->user();
    
    $task = Task::where('created_by', $manager->id)
                  ->where('assigned_to', $userId)
                  ->findOrFail($id);

    $task->delete();
    
    return response()->json([
        'message' => 'Task deleted successfully',
    ], 200);
}

public function create_team_task(Request $request, $id, $userId)
{
    $manager = $request->user();
    
    $user = User::findOrFail($id);

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

public function view_own_task_for_user(Request $request, $userId, $taskId)
{
    $user = User::findOrFail($userId);
    $task = Task::where('assigned_to', $userId)->where('id', $taskId)->first();
    

    return response()->json([
        'task' => $task,
    ], 200);
}


public function view_all_tasks_for_user(Request $request, $userId)
{
    $user = User::findOrFail($userId);
    $perPage = (int) $request->query('per_page', 15);
    $tasks = $user->tasks()->paginate($perPage);

    return response()->json([
        'tasks' => $tasks,
    ], 200);
}





public function view_task_for_user (Request $request, $userId, $taskId)
{   $user=$request->user();
    $user = User::findOrFail($userId);
    $task = Task::where('assigned_to', $userId)->where('id', $taskId)->first();
    

    return response()->json([
        'task' => $task,
    ], 200);
}


public function update_task_status (Request $request, $userId, $taskId)
{
    $user = User::findOrFail($userId);
    $task = Task::where('assigned_to', $userId)->where('id', $taskId)->first();

    if (!$task) {
        return response()->json(['message' => 'Task not found'], 404);
    }

    $data = $request->validate([
        'status' => 'required|in:pending,in_progress,completed',
    ]);

    $task->status = $data['status'];
    $task->save();

    return response()->json([
        'message' => 'Task status updated successfully',
        'task' => $task,
    ], 200);
}

public function getNumberGlobaleofTasks()
{
    $count = Task::count();
    return response()->json(['number_of_tasks' => $count], 200);



}

public function getNumberTaskforUser($userId)
{
    $count = Task::where('assigned_to', $userId)->count();
    return response()->json(['number_of_tasks_for_user' => $count], 200);
}


public function getNumberTaskTeamforUser($userId, Request $request)
{   $manager = $request->user();
    $count = Task::where('assigned_to', $userId)->where('created_by', $manager->id)->count();
    return response()->json(['number_of_team_tasks_for_user' => $count], 200);



}



public function getStatistiquetask ()
{
    $totalTasks = Task::count();
    $completedTasks = Task::where('status', 'completed')->count();
    $pendingTasks = Task::where('status', 'pending')->count();
    $inProgressTasks = Task::where('status', 'in_progress')->count();

    return response()->json([
        'total_tasks' => $totalTasks,
        'completed_tasks' => $completedTasks,
        'pending_tasks' => $pendingTasks,
        'in_progress_tasks' => $inProgressTasks,
    ], 200);





}
}
