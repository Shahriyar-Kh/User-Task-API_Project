<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    // 🔹 List tasks
    public function index(Request $request)
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return response()->json(['error' => 'Authentication required'], 401);
            }

            $this->authorize('viewAny', Task::class);

            // ✅ Always load both assignee (user) and creator (admin)
            $query = Task::with(['user', 'creator']);

            // Admin sees all; user sees only own tasks
            if ($user->role !== 'admin') {
                $query->where('user_id', $user->id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }

            $tasks = $query->orderBy('due_date', 'asc')->get();

            return response()->json($tasks);
        } catch (\Exception $e) {
            Log::error('Task index error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error while fetching tasks'], 500);
        }
    }

    // 🔹 Create task (admin only via policy)
    public function store(Request $request)
    {
        try {
            $this->authorize('create', Task::class);

            $validated = $request->validate([
                'title'       => 'required|string|max:255',
                'description' => 'nullable|string',
                'user_id'     => 'required|exists:users,id', // assigned user
                'priority'    => 'in:low,medium,high',
                'due_date'    => 'nullable|date',
            ]);

            // ❌ Prevent assigning tasks to admins
            $assignee = User::findOrFail($validated['user_id']);
            if ($assignee->role === 'admin') {
                return response()->json(['error' => 'Cannot assign tasks to an admin'], 422);
            }

            $adminUser = auth('api')->user();
            
            $task = Task::create([
                'title'       => $validated['title'],
                'description' => $validated['description'] ?? null,
                'status'      => 'todo',
                'priority'    => $validated['priority'] ?? 'medium',
                'due_date'    => $validated['due_date'] ?? null,
                'user_id'     => $validated['user_id'],
                'created_by'  => $adminUser->id, // admin who created it
            ]);

            // Load relationships for response
            $task->load(['user', 'creator']);

            return response()->json([
                'message' => 'Task created successfully',
                'task'    => $task,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Task store error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error while creating task'], 500);
        }
    }

    // 🔹 Show single task
    public function show($id)
    {
        try {
            $task = Task::with(['user', 'creator'])->findOrFail($id);

            $this->authorize('view', $task);

            return response()->json($task);
        } catch (\Exception $e) {
            Log::error('Task show error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error while fetching task'], 500);
        }
    }

    // 🔹 Update task
    public function update(Request $request, $id)
    {
        try {
            $task = Task::findOrFail($id);

            $this->authorize('update', $task);

            $user = auth('api')->user();

            if ($user->role === 'admin') {
                // ✅ Admin can update everything
                $validated = $request->validate([
                    'title'       => 'sometimes|string|max:255',
                    'description' => 'nullable|string',
                    'status'      => 'in:todo,in_progress,done',
                    'priority'    => 'in:low,medium,high',
                    'due_date'    => 'nullable|date',
                    'user_id'     => 'sometimes|exists:users,id',
                ]);

                // ❌ Prevent reassigning to an admin
                if (isset($validated['user_id'])) {
                    $assignee = User::findOrFail($validated['user_id']);
                    if ($assignee->role === 'admin') {
                        return response()->json(['error' => 'Cannot assign tasks to an admin'], 422);
                    }
                }

                $task->update($validated);
            } else {
                // ✅ User: only update own task (status + description)
                $validated = $request->validate([
                    'status'      => 'in:todo,in_progress,done',
                    'description' => 'nullable|string',
                ]);

                $task->update($validated);
            }

            // Load relationships for response
            $task->load(['user', 'creator']);

            return response()->json([
                'message' => 'Task updated successfully',
                'task'    => $task,
            ]);
        } catch (\Exception $e) {
            Log::error('Task update error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error while updating task'], 500);
        }
    }

    // 🔹 Delete task (admin only via policy)
    public function destroy($id)
    {
        try {
            $task = Task::findOrFail($id);

            $this->authorize('delete', $task);

            $task->delete();

            return response()->json(['message' => 'Task deleted']);
        } catch (\Exception $e) {
            Log::error('Task destroy error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error while deleting task'], 500);
        }
    }
}