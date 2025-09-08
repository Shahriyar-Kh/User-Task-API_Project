<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Http\Requests\StoreTaskRequest;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = auth('api')->user();

        $query = Task::query();

        if($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        if($request->filled('status')) $query->where('status', $request->status);
        if($request->filled('priority')) $query->where('priority', $request->priority);

        $tasks = $query->orderBy('due_date','asc')->paginate(15);
        return response()->json($tasks);
    }

    public function store(StoreTaskRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth('api')->id();

        $task = Task::create($data);
        return response()->json($task, 201);
    }

    public function show($id)
    {
        $task = Task::findOrFail($id);
        $this->authorizeAction($task);
        return response()->json($task);
    }

    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $this->authorizeAction($task);

        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'in:todo,in_progress,done',
            'priority' => 'in:low,medium,high',
            'due_date' => 'nullable|date|after_or_equal:today',
        ]);

        $task->update($data);
        return response()->json($task);
    }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $this->authorizeAction($task);
        $task->delete();
        return response()->json(['message'=>'deleted']);
    }

    protected function authorizeAction(Task $task)
    {
        $user = auth('api')->user();
        if($user->role === 'admin') return true;
        if($task->user_id !== $user->id) {
            abort(403, 'forbidden');
        }
        return true;
    }
}
