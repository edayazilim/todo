<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TaskApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    
    protected User $admin;
    protected User $user;
    protected Task $adminTask;
    protected Task $userTask;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Admin ve normal kullanıcı oluştur
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user']);
        
        // Admin tarafından oluşturulan ve kullanıcıya atanan görev
        $this->adminTask = Task::factory()->create([
            'title' => 'Admin tarafından oluşturulan görev',
            'user_id' => $this->user->id,
            'created_by' => $this->admin->id,
            'start_date' => now()->subDays(5)->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
        ]);
        
        // Kullanıcının kendi oluşturduğu görev
        $this->userTask = Task::factory()->create([
            'title' => 'Kullanıcı tarafından oluşturulan görev',
            'user_id' => $this->user->id,
            'created_by' => $this->user->id,
            'start_date' => now()->subDays(2)->format('Y-m-d'),
            'end_date' => now()->addDays(10)->format('Y-m-d'),
        ]);
    }
    
    #[Test]
    public function kullanici_kendine_ait_gorevleri_listeleyebilir()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/tasks');
        
        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['title' => $this->adminTask->title])
            ->assertJsonFragment(['title' => $this->userTask->title]);
    }
    
    #[Test]
    public function admin_tum_gorevleri_gorebilir()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/tasks');
        
        $response->assertStatus(200)
            ->assertJsonFragment(['title' => $this->adminTask->title])
            ->assertJsonFragment(['title' => $this->userTask->title]);
    }
    
    #[Test]
    public function kullanici_yeni_gorev_olusturabilir()
    {
        $taskData = [
            'title' => 'Yeni test görevi',
            'description' => 'Bu bir test görevidir',
            'status' => 'pending',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(7)->format('Y-m-d'),
        ];
        
        $response = $this->actingAs($this->user)
            ->postJson('/api/tasks', $taskData);
        
        $response->assertStatus(201)
            ->assertJsonFragment(['title' => $taskData['title']]);
        
        $this->assertDatabaseHas('tasks', [
            'title' => $taskData['title'],
            'user_id' => $this->user->id,
            'created_by' => $this->user->id,
        ]);
    }
    
    #[Test]
    public function kullanici_gecersiz_tarih_ile_gorev_olusturamaz()
    {
        $taskData = [
            'title' => 'Geçersiz tarihli görev',
            'description' => 'Bu bir test görevidir',
            'status' => 'pending',
            'start_date' => now()->addDays(10)->format('Y-m-d'), // Bitiş tarihinden sonraki bir tarih
            'end_date' => now()->addDays(5)->format('Y-m-d'),
        ];
        
        $response = $this->actingAs($this->user)
            ->postJson('/api/tasks', $taskData);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_date']);
    }
    
    #[Test]
    public function kullanici_sadece_baslangic_tarihi_girince_hata_alir()
    {
        $taskData = [
            'title' => 'Eksik tarihli görev',
            'description' => 'Bu bir test görevidir',
            'status' => 'pending',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => null,
        ];
        
        $response = $this->actingAs($this->user)
            ->postJson('/api/tasks', $taskData);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['dates']);
    }
    
    #[Test]
    public function kullanici_kendi_gorevini_guncelleyebilir()
    {
        $updateData = [
            'title' => 'Güncellenmiş görev başlığı',
            'description' => 'Güncellenmiş açıklama',
            'status' => 'in_progress',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(15)->format('Y-m-d'),
        ];
        
        $response = $this->actingAs($this->user)
            ->putJson("/api/tasks/{$this->userTask->id}", $updateData);
        
        $response->assertStatus(200)
            ->assertJsonFragment(['title' => $updateData['title']])
            ->assertJsonFragment(['description' => $updateData['description']]);
    }
    
    #[Test]
    public function kullanici_admin_gorevinin_sadece_durumunu_degistirebilir()
    {
        $updateData = [
            'status' => 'completed'
        ];
        
        $response = $this->actingAs($this->user)
            ->putJson("/api/tasks/{$this->adminTask->id}", $updateData);
        
        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'completed'])
            ->assertJsonFragment(['title' => $this->adminTask->title]);
    }
    
    #[Test]
    public function admin_herhangi_bir_gorevi_tamamen_guncelleyebilir()
    {
        $updateData = [
            'title' => 'Admin tarafından güncellenen başlık',
            'description' => 'Admin tarafından güncellenen açıklama',
            'status' => 'in_progress',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(30)->format('Y-m-d'),
        ];
        
        $response = $this->actingAs($this->admin)
            ->putJson("/api/tasks/{$this->userTask->id}", $updateData);
        
        $response->assertStatus(200)
            ->assertJsonFragment(['title' => $updateData['title']])
            ->assertJsonFragment(['description' => $updateData['description']])
            ->assertJsonFragment(['status' => $updateData['status']]);
    }
    
    #[Test]
    public function kullanici_kendi_gorevini_silebilir()
    {
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/tasks/{$this->userTask->id}");
        
        $response->assertStatus(200);
        $this->assertDatabaseMissing('tasks', ['id' => $this->userTask->id]);
    }
    
    #[Test]
    public function kullanici_admin_gorevini_silemez()
    {
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/tasks/{$this->adminTask->id}");
        
        $response->assertStatus(403);
        $this->assertDatabaseHas('tasks', ['id' => $this->adminTask->id]);
    }
    
    #[Test]
    public function admin_herhangi_bir_gorevi_silebilir()
    {
        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/tasks/{$this->userTask->id}");
        
        $response->assertStatus(200);
        $this->assertDatabaseMissing('tasks', ['id' => $this->userTask->id]);
    }
}
