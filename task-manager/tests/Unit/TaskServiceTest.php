<?php

namespace Tests\Unit;

use App\Models\Task;
use App\Models\User;
use App\Repositories\TaskRepository;
use App\Services\TaskService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TaskServiceTest extends TestCase
{
    use DatabaseTransactions;
    
    protected TaskService $service;
    protected MockInterface $mockRepository;
    protected User $admin;
    protected User $user;
    protected Task $adminTask;
    protected Task $userTask;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock TaskRepository
        $this->mockRepository = Mockery::mock(TaskRepository::class);
        $this->service = new TaskService($this->mockRepository);
        
        // Kullanıcıları oluştur - veritabanına kaydetmeden sadece model örnekleri oluştur
        $this->admin = $this->partialMock(User::class, function ($mock) {
            $mock->shouldReceive('isAdmin')->andReturn(true);
        });
        $this->admin->id = 1;
        $this->admin->name = 'Admin';
        $this->admin->email = 'admin@example.com';
        $this->admin->role = 'admin';
        
        $this->user = $this->partialMock(User::class, function ($mock) {
            $mock->shouldReceive('isAdmin')->andReturn(false);
        });
        $this->user->id = 2;
        $this->user->name = 'User';
        $this->user->email = 'user@example.com';
        $this->user->role = 'user';
        
        // Görevleri oluştur
        $this->adminTask = new Task();
        $this->adminTask->id = 1;
        $this->adminTask->title = 'Admin tarafından oluşturulan görev';
        $this->adminTask->user_id = $this->user->id;
        $this->adminTask->created_by = $this->admin->id;
        $this->adminTask->status = 'pending';
        $this->adminTask->start_date = now()->subDays(5)->format('Y-m-d');
        $this->adminTask->end_date = now()->addDays(5)->format('Y-m-d');
        
        $this->userTask = new Task();
        $this->userTask->id = 2;
        $this->userTask->title = 'Kullanıcı tarafından oluşturulan görev';
        $this->userTask->user_id = $this->user->id;
        $this->userTask->created_by = $this->user->id;
        $this->userTask->status = 'pending';
        $this->userTask->start_date = now()->subDays(2)->format('Y-m-d');
        $this->userTask->end_date = now()->addDays(10)->format('Y-m-d');
    }
    
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    
    #[Test]
    public function service_admin_icin_tum_gorevleri_getirir()
    {
        $tasks = new EloquentCollection([$this->adminTask, $this->userTask]);
        
        $this->mockRepository->shouldReceive('getAll')
            ->once()
            ->andReturn($tasks);
            
        $result = $this->service->getAllForUser($this->admin);
        
        $this->assertEquals($tasks, $result);
    }
    
    #[Test]
    public function service_normal_kullanici_icin_kendi_gorevlerini_getirir()
    {
        $tasks = new EloquentCollection([$this->adminTask, $this->userTask]);
        
        $this->mockRepository->shouldReceive('getAllForUser')
            ->once()
            ->with($this->user)
            ->andReturn($tasks);
            
        $result = $this->service->getAllForUser($this->user);
        
        $this->assertEquals($tasks, $result);
    }
    
    #[Test]
    public function service_gorev_olusturur()
    {
        $taskData = [
            'title' => 'Yeni görev',
            'description' => 'Görev açıklaması',
            'status' => 'pending',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(7)->format('Y-m-d'),
        ];
        
        $expectedData = array_merge($taskData, [
            'created_by' => $this->user->id,
            'user_id' => $this->user->id
        ]);
        
        $task = new Task();
        $task->title = 'Yeni görev';
        $task->fill($expectedData);
        
        $this->mockRepository->shouldReceive('create')
            ->once()
            ->with($expectedData, $this->user)
            ->andReturn($task);
            
        $result = $this->service->create($taskData, $this->user);
        
        $this->assertInstanceOf(Task::class, $result);
        $this->assertEquals('Yeni görev', $result->title);
    }
    
    #[Test]
    public function admin_baska_kullaniciya_gorev_atayabilir()
    {
        $taskData = [
            'title' => 'Admin tarafından atanan görev',
            'description' => 'Görev açıklaması',
            'status' => 'pending',
            'user_id' => $this->user->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(7)->format('Y-m-d'),
        ];
        
        $expectedData = array_merge($taskData, [
            'created_by' => $this->admin->id,
        ]);
        
        $task = new Task();
        $task->title = 'Admin tarafından atanan görev';
        $task->user_id = $this->user->id;
        $task->created_by = $this->admin->id;
        
        $this->mockRepository->shouldReceive('create')
            ->once()
            ->with($expectedData, $this->admin)
            ->andReturn($task);
            
        $result = $this->service->create($taskData, $this->admin);
        
        $this->assertInstanceOf(Task::class, $result);
        $this->assertEquals($this->user->id, $result->user_id);
        $this->assertEquals($this->admin->id, $result->created_by);
    }
    
    #[Test]
    public function service_kullanicinin_kendi_gorevini_gunceller()
    {
        $updateData = [
            'title' => 'Güncellenmiş başlık',
            'description' => 'Güncellenmiş açıklama',
            'status' => 'in_progress',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(7)->format('Y-m-d'),
        ];
        
        $this->mockRepository->shouldReceive('update')
            ->once()
            ->with(Mockery::on(function($task) {
                return $task->id === $this->userTask->id;
            }), $updateData)
            ->andReturn($this->userTask);
            
        $result = $this->service->update($this->userTask, $updateData, $this->user);
        
        $this->assertEquals($this->userTask, $result);
    }
    
    #[Test]
    public function service_kullanicinin_admin_gorevinin_sadece_durumunu_gunceller()
    {
        // Sadece status içeren update data
        $updateData = [
            'status' => 'completed',
        ];
        
        $expectedUpdate = ['status' => 'completed'];
        
        $this->mockRepository->shouldReceive('update')
            ->once()
            ->with(Mockery::on(function($task) {
                return $task->id === $this->adminTask->id;
            }), $expectedUpdate)
            ->andReturn($this->adminTask);
            
        $result = $this->service->update($this->adminTask, $updateData, $this->user);
        
        $this->assertEquals($this->adminTask, $result);
    }
    
    #[Test]
    public function service_admin_herhangi_bir_gorevi_tam_olarak_gunceller()
    {
        $updateData = [
            'title' => 'Admin tarafından güncellenmiş başlık',
            'description' => 'Admin tarafından güncellenmiş açıklama',
            'status' => 'in_progress',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(15)->format('Y-m-d'),
        ];
        
        $this->mockRepository->shouldReceive('update')
            ->once()
            ->with(Mockery::on(function($task) {
                return $task->id === $this->userTask->id;
            }), $updateData)
            ->andReturn($this->userTask);
            
        $result = $this->service->update($this->userTask, $updateData, $this->admin);
        
        $this->assertEquals($this->userTask, $result);
    }
    
    #[Test]
    public function kullanici_admin_gorevini_guncellerken_title_guncelleyince_hata_alir()
    {
        $updateData = [
            'title' => 'Bu başlık değişmeyecek',
            'status' => 'completed',
        ];
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Admin tarafından oluşturulan görevlerin sadece durumu/');
        
        // TaskService'te hata atmadan önce repository'ye erişilmeyecek
        // Bu nedenle expectation tanımlamıyoruz
        
        $this->service->update($this->adminTask, $updateData, $this->user);
    }
    
    #[Test]
    public function kullanici_admin_gorevini_guncellerken_baslangic_tarihini_degistirince_hata_alir()
    {
        $updateData = [
            'status' => 'completed',
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
        ];
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Admin tarafından oluşturulan görevlerin sadece durumu/');
        
        // TaskService'te hata atmadan önce repository'ye erişilmeyecek
        // Bu nedenle expectation tanımlamıyoruz
        
        $this->service->update($this->adminTask, $updateData, $this->user);
    }
}
