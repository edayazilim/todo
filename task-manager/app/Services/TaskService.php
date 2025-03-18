<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Repositories\TaskRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class TaskService
{
    protected TaskRepository $taskRepository;

    /**
     * Constructor.
     *
     * @param TaskRepository $taskRepository
     */
    public function __construct(TaskRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    /**
     * Get all tasks for a user.
     *
     * @param User $user
     * @return Collection
     */
    public function getAllForUser(User $user): Collection
    {
        if ($user->isAdmin()) {
            return $this->taskRepository->getAll();
        }
        return $this->taskRepository->getAllForUser($user);
    }

    /**
     * Create a new task.
     *
     * @param array $data
     * @param User $user
     * @return Task
     */
    public function create(array $data, User $user): Task
    {
        
        $user_id = isset($data['user_id']) && $user->isAdmin() ? $data['user_id'] : $user->id;
        $task_data = array_merge($data, [
            'created_by' => $user->id,
            'user_id' => $user_id
        ]);
        
        return $this->taskRepository->create($task_data, $user);
    }

    /**
     * Update a task if the user is authorized.
     *
     * @param Task $task
     * @param array $data
     * @param User $user
     * @return Task
     * @throws \Exception
     */
    public function update(Task $task, array $data, User $user): Task
    {
       
        if (!$user->isAdmin() && $task->created_by !== null && $task->created_by !== $user->id) {
            
            $nonStatusFields = array_diff(array_keys($data), ['status']);
            if (count($nonStatusFields) > 0) {
                $restrictedFields = ['title', 'description', 'start_date', 'end_date'];
                $attemptedFields = array_intersect($nonStatusFields, $restrictedFields);
                
                if (count($attemptedFields) > 0) {
                    throw new \Exception('Admin tarafından oluşturulan görevlerin sadece durumu güncellenebilir. Şu alanları güncelleyemezsiniz: ' . implode(', ', $attemptedFields));
                }
            }
            
            return $this->taskRepository->update($task, ['status' => $data['status'] ?? $task->status]);
        }
        
        return $this->taskRepository->update($task, $data);
    }

    /**
     * Delete a task if the user is authorized.
     *
     * @param Task $task
     * @param User $user
     * @return bool|null
     * @throws \Exception
     */
    public function delete(Task $task, User $user): ?bool
    {        
        
        if (!$user->isAdmin() && $task->created_by !== null && $task->created_by !== $user->id) {
            throw new \Exception('Admin tarafından oluşturulan görevler silinemez.');
        }
        
        return $this->taskRepository->delete($task);
    }

    /**
     * Find a task by ID.
     *
     * @param int $id
     * @return Task|null
     */
    public function findById(int $id): ?Task
    {
        return $this->taskRepository->findById($id);
    }
} 