<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class TaskController extends Controller
{
    protected TaskService $taskService;

    /**
     * Constructor.
     *
     * @param TaskService $taskService
     */
    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Display a listing of the tasks for the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Task::class);
        
        $tasks = $this->taskService->getAllForUser($request->user());

        return response()->json([
            'data' => $tasks
        ]);
    }

    /**
     * Store a newly created task in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Task::class);
        
        $validationRules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'sometimes|in:pending,in_progress,completed',
            'start_date' => 'nullable|date|date_format:Y-m-d',
            'end_date' => 'nullable|date|date_format:Y-m-d|after_or_equal:start_date',
        ];
        
        $requestData = $request->all();
        if (
            (isset($requestData['start_date']) && !empty($requestData['start_date']) && (!isset($requestData['end_date']) || empty($requestData['end_date']))) ||
            (isset($requestData['end_date']) && !empty($requestData['end_date']) && (!isset($requestData['start_date']) || empty($requestData['start_date'])))
        ) {
            throw ValidationException::withMessages([
                'dates' => ['Başlangıç ve bitiş tarihlerinin ikisi de girilmelidir.']
            ]);
        }
        
        if ($request->user()->isAdmin()) {
            $validationRules['user_id'] = 'sometimes|exists:users,id';
        }
        
        $data = $request->validate($validationRules);

        $task = $this->taskService->create($data, $request->user());

        return response()->json([
            'message' => 'Task created successfully',
            'data' => $task
        ], 201);
    }

    /**
     * Display the specified task.
     *
     * @param Task $task
     * @return JsonResponse
     */
    public function show(Task $task): JsonResponse
    {
        $this->authorize('view', $task);
        
        return response()->json([
            'data' => $task
        ]);
    }

    /**
     * Update the specified task in storage.
     *
     * @param Request $request
     * @param Task $task
     * @return JsonResponse
     */
    public function update(Request $request, Task $task): JsonResponse
    {
        $validationRules = [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'status' => 'sometimes|in:pending,in_progress,completed',
            'start_date' => 'nullable|date|date_format:Y-m-d',
            'end_date' => 'nullable|date|date_format:Y-m-d|after_or_equal:start_date',
        ];
        
        if ($request->user()->isAdmin()) {
            $validationRules['user_id'] = 'sometimes|exists:users,id';
        }
        
        $data = $request->validate($validationRules);
        
        if (isset($data['start_date']) || isset($data['end_date'])) {
            
            $newStartDate = $data['start_date'] ?? null;
            $newEndDate = $data['end_date'] ?? null;
            
            $currentStartDate = $task->start_date;
            $currentEndDate = $task->end_date;
            
            $finalStartDate = $newStartDate ?? $currentStartDate;
            $finalEndDate = $newEndDate ?? $currentEndDate;
            
            if (
                ($finalStartDate && !$finalEndDate) || 
                (!$finalStartDate && $finalEndDate)
            ) {
                return response()->json([
                    'message' => 'Başlangıç ve bitiş tarihlerinin ikisi de girilmelidir veya ikisi de boş olmalıdır.',
                    'errors' => [
                        'dates' => ['Başlangıç ve bitiş tarihlerinin ikisi de girilmelidir veya ikisi de boş olmalıdır.']
                    ]
                ], 422);
            }
            
            if (array_key_exists('start_date', $data) && $data['start_date'] === null) {
                $data['end_date'] = null;
            }
            
            if (array_key_exists('end_date', $data) && $data['end_date'] === null) {
                $data['start_date'] = null;
            }
        }

        try {
            $isStatusUpdate = count($data) === 1 && isset($data['status']);
            
            if (!$isStatusUpdate) {
                $this->authorize('updateAllFields', $task);
            } else {
                $this->authorize('updateStatus', $task);
            }
            
            $task = $this->taskService->update($task, $data, $request->user());

            return response()->json([
                'message' => 'Task updated successfully',
                'data' => $task
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 403);
        }
    }

    /**
     * Remove the specified task from storage.
     *
     * @param Task $task
     * @return JsonResponse
     */
    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);
        
        try {
            $this->taskService->delete($task, auth()->user());

            return response()->json([
                'message' => 'Task deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 403);
        }
    }
    
    /**
     * Get all users (only accessible to admin).
     *
     * @return JsonResponse
     */
    public function getAllUsers(): JsonResponse
    {
        if (!Gate::allows('viewAllUsers', User::class)) {
            return response()->json([
                'message' => 'Unauthorized action'
            ], 403);
        }
        
        $users = User::select('id', 'name', 'email', 'role')->get();
        
        return response()->json([
            'data' => $users
        ]);
    }
}
