<?php

namespace App\Repositories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class TaskRepository
{
    /**
     * Get all tasks.
     * 
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Task::with(['user', 'creator'])->latest()->get();
    }

    /**
     * Get all tasks for a user.
     *
     * @param User $user
     * @return Collection
     */
    public function getAllForUser(User $user): Collection
    {
        return $user->tasks()->with('creator')->latest()->get();
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
        return Task::create($data);
    }

    /**
     * Update an existing task.
     *
     * @param Task $task
     * @param array $data
     * @return Task
     */
    public function update(Task $task, array $data): Task
    {
        $task->update($data);
        return $task;
    }

    /**
     * Delete a task.
     *
     * @param Task $task
     * @return bool|null
     */
    public function delete(Task $task): ?bool
    {
        return $task->delete();
    }

    /**
     * Find a task by ID.
     *
     * @param int $id
     * @return Task|null
     */
    public function findById(int $id): ?Task
    {
        return Task::with(['user', 'creator'])->find($id);
    }
} 