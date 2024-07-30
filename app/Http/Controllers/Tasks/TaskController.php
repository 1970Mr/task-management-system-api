<?php

namespace App\Http\Controllers\Tasks;

use App\Events\TaskCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tasks\AssignUsersRequest;
use App\Http\Requests\Tasks\TaskStoreRequest;
use App\Http\Requests\Tasks\TaskUpdateRequest;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class TaskController extends Controller
{
    public function index(): JsonResponse
    {
        $tasks = Task::with('users')->latest()->get();
        return Response::json($tasks);
    }

    public function store(TaskStoreRequest $request): JsonResponse
    {
        $task = Auth::user()->tasks()->create($request->validated());
        event(new TaskCreated($task));
        return Response::json($task, 201);
    }

    public function update(TaskUpdateRequest $request, Task $task): JsonResponse
    {
        $task->update($request->all());
        return Response::json($task);
    }

    public function destroy(Task $task): JsonResponse
    {
        $task->delete();
        return Response::json(['message' => 'Task deleted successfully']);
    }

    public function userTasks(): JsonResponse
    {
        $user = Auth::user();
        $tasks = $user->assignedTasks()->with('users')->latest()->get();
        return Response::json($tasks);
    }

    public function assignUsersToTask(AssignUsersRequest $request, Task $task): JsonResponse
    {
        $task->users()->sync($request->user_ids);
        return Response::json([
            'message' => 'Users assigned to task successfully',
            'task' => $task->load('users')
        ]);
    }
}
