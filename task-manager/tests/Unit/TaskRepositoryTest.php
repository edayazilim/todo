<?php

namespace Tests\Unit;

use App\Models\Task;
use App\Models\User;
use App\Repositories\TaskRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TaskRepositoryTest extends TestCase
{
    use RefreshDatabase;
    
    protected TaskRepository $repository;
    protected User $admin;
    protected User $user;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->repository = new TaskRepository();
        
        // Kullanıcılar oluştur
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user']);
    }
    
    #[Test]
    public function repository_tum_gorevleri_getirebilir()
    {
        // Görevler oluştur
        Task::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'created_by' => $this->admin->id
        ]);
        
        $tasks = $this->repository->getAll();
        
        $this->assertCount(5, $tasks);
        $this->assertInstanceOf(Task::class, $tasks->first());
    }
    
    #[Test]
    public function repository_kullanicinin_gorevlerini_getirebilir()
    {
        // İlk kullanıcı için görevler
        Task::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'created_by' => $this->admin->id
        ]);
        
        // İkinci (farklı) kullanıcı için görevler
        $anotherUser = User::factory()->create();
        Task::factory()->count(2)->create([
            'user_id' => $anotherUser->id,
            'created_by' => $this->admin->id
        ]);
        
        $tasks = $this->repository->getAllForUser($this->user);
        
        $this->assertCount(3, $tasks);
        foreach ($tasks as $task) {
            $this->assertEquals($this->user->id, $task->user_id);
        }
    }
    
    #[Test]
    public function repository_yeni_gorev_olusturabilir()
    {
        $taskData = [
            'title' => 'Test Görevi',
            'description' => 'Bu bir test görevidir',
            'status' => 'pending',
            'user_id' => $this->user->id,
            'created_by' => $this->admin->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(7)->format('Y-m-d'),
        ];
        
        $task = $this->repository->create($taskData, $this->admin);
        
        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals('Test Görevi', $task->title);
        $this->assertEquals($this->user->id, $task->user_id);
        $this->assertEquals($this->admin->id, $task->created_by);
        $this->assertEquals(now()->format('Y-m-d'), $task->start_date->format('Y-m-d'));
        $this->assertEquals(now()->addDays(7)->format('Y-m-d'), $task->end_date->format('Y-m-d'));
    }
    
    #[Test]
    public function repository_gorevi_guncelleyebilir()
    {
        $task = Task::factory()->create([
            'title' => 'Eski Başlık',
            'description' => 'Eski Açıklama',
            'status' => 'pending',
            'user_id' => $this->user->id,
            'created_by' => $this->user->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
        ]);
        
        $updateData = [
            'title' => 'Güncellenmiş Başlık',
            'description' => 'Güncellenmiş Açıklama',
            'status' => 'in_progress',
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addDays(10)->format('Y-m-d'),
        ];
        
        $updatedTask = $this->repository->update($task, $updateData);
        
        $this->assertEquals('Güncellenmiş Başlık', $updatedTask->title);
        $this->assertEquals('Güncellenmiş Açıklama', $updatedTask->description);
        $this->assertEquals('in_progress', $updatedTask->status);
        $this->assertEquals(now()->addDay()->format('Y-m-d'), $updatedTask->start_date->format('Y-m-d'));
        $this->assertEquals(now()->addDays(10)->format('Y-m-d'), $updatedTask->end_date->format('Y-m-d'));
    }
    
    #[Test]
    public function repository_gorevi_silebilir()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'created_by' => $this->user->id
        ]);
        
        $result = $this->repository->delete($task);
        
        $this->assertTrue($result);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
    
    #[Test]
    public function repository_id_ile_gorev_bulabilir()
    {
        $task = Task::factory()->create([
            'title' => 'Aranacak Görev',
            'user_id' => $this->user->id,
            'created_by' => $this->admin->id
        ]);
        
        $foundTask = $this->repository->findById($task->id);
        
        $this->assertInstanceOf(Task::class, $foundTask);
        $this->assertEquals($task->id, $foundTask->id);
        $this->assertEquals('Aranacak Görev', $foundTask->title);
    }
    
    #[Test]
    public function repository_olmayan_id_ile_gorev_bulamaz()
    {
        $nonExistentId = 9999;
        
        $foundTask = $this->repository->findById($nonExistentId);
        
        $this->assertNull($foundTask);
    }
}
