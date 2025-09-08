<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TaskController extends Controller
{
    use AuthorizesRequests;

    // List tasks
    public function index(Request $request)
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return response()->json(['error' => 'Authentication required'], 401);
            }

            // Load both assignee (user) and creator (admin)
            $query = Task::with(['user', 'creator']);

            // Admin sees all; user sees only own tasks
            if ($user->role !== 'admin') {
                $query->where('user_id', $user->id);
            }

            // Apply filters if provided
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }

            $tasks = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $tasks,
                'message' => 'Tasks retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Task index error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Server error while fetching tasks',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Create task (admin only)
    public function store(Request $request)
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return response()->json(['error' => 'Authentication required'], 401);
            }

            // Only admins can create tasks
            if ($user->role !== 'admin') {
                return response()->json(['error' => 'Only admins can create tasks'], 403);
            }

            $validated = $request->validate([
                'title'       => 'required|string|max:255',
                'description' => 'nullable|string',
                'user_id'     => 'required|exists:users,id',
                'priority'    => 'nullable|in:low,medium,high',
                'due_date'    => 'nullable|date',
            ]);

            // Prevent assigning tasks to admins
            $assignee = User::findOrFail($validated['user_id']);
            if ($assignee->role === 'admin') {
                return response()->json(['error' => 'Cannot assign tasks to an admin'], 422);
            }

            $task = Task::create([
                'title'       => $validated['title'],
                'description' => $validated['description'] ?? null,
                'status'      => 'todo',
                'priority'    => $validated['priority'] ?? 'medium',
                'due_date'    => $validated['due_date'] ?? null,
                'user_id'     => $validated['user_id'],
                'created_by'  => $user->id,
            ]);

            // Load relationships for response
            $task->load(['user', 'creator']);

            return response()->json([
                'success' => true,
                'message' => 'Task created successfully',
                'task'    => $task,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Task store error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return response()->json([
                'error' => 'Server error while creating task',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Show single task
    public function show($id)
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return response()->json(['error' => 'Authentication required'], 401);
            }

            $task = Task::with(['user', 'creator'])->findOrFail($id);

            // Check if user can view this task
            if ($user->role !== 'admin' && $user->id !== $task->user_id) {
                return response()->json(['error' => 'Unauthorized to view this task'], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $task
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Task not found'], 404);
        } catch (\Exception $e) {
            Log::error('Task show error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Server error while fetching task',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Update task
    public function update(Request $request, $id)
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return response()->json(['error' => 'Authentication required'], 401);
            }

            $task = Task::findOrFail($id);

            // Check permissions
            if ($user->role !== 'admin' && $user->id !== $task->user_id) {
                return response()->json(['error' => 'Unauthorized to update this task'], 403);
            }

            if ($user->role === 'admin') {
                // Admin can update everything
                $validated = $request->validate([
                    'title'       => 'sometimes|string|max:255',
                    'description' => 'nullable|string',
                    'status'      => 'sometimes|in:todo,in_progress,done',
                    'priority'    => 'sometimes|in:low,medium,high',
                    'due_date'    => 'nullable|date',
                    'user_id'     => 'sometimes|exists:users,id',
                ]);

                // Prevent reassigning to an admin
                if (isset($validated['user_id'])) {
                    $assignee = User::findOrFail($validated['user_id']);
                    if ($assignee->role === 'admin') {
                        return response()->json(['error' => 'Cannot assign tasks to an admin'], 422);
                    }
                }

                $task->update($validated);
            } else {
                // User can only update status and description
                $validated = $request->validate([
                    'status'      => 'sometimes|in:todo,in_progress,done',
                    'description' => 'nullable|string',
                ]);

                $task->update($validated);
            }

            // Load relationships for response
            $task->load(['user', 'creator']);

            return response()->json([
                'success' => true,
                'message' => 'Task updated successfully',
                'task'    => $task,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Task not found'], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Task update error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return response()->json([
                'error' => 'Server error while updating task',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Delete task (admin only)
    public function destroy($id)
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return response()->json(['error' => 'Authentication required'], 401);
            }

            // Only admins can delete tasks
            if ($user->role !== 'admin') {
                return response()->json(['error' => 'Only admins can delete tasks'], 403);
            }

            $task = Task::findOrFail($id);
            $task->delete();

            return response()->json([
                'success' => true,
                'message' => 'Task deleted successfully'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Task not found'], 404);
        } catch (\Exception $e) {
            Log::error('Task destroy error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Server error while deleting task',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}