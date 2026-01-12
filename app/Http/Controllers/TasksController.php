<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TasksController extends Controller
{
     public function index(request $request)
     {  
         $user = $request->user();
         $tasks = $user->tasks; 
         return response()->json($tasks);   
     }

     public function store(request $request)
     {   $data=$request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'status' => ['pending', 'in_progress', 'completed'],
            'deadline' => 'nullable|date',
            'priority' => ['low', 'medium', 'high'],
            'assigned_to' => 'nullable|exists:users,id',
            'created_by' => 'exists:users,id',
        ]);

        $user = $request->user();

        $task = $user->tasks()->create($data);

        return response()->json([
            'message' => 'Task created successfully',
            'task' => $task,
        ], 201);

     }
       public function show($id)
{    $task=Task::find($id);
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

        public function destroy($id)
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
        
}