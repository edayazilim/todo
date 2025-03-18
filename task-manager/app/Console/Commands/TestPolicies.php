<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Gate;

class TestPolicies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-policies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Task policies with different user roles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Task Policies...');
        
        // 1. Admin kullanıcı oluştur
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $this->error('Admin kullanıcı bulunamadı. Lütfen bir admin kullanıcı oluşturun.');
            return 1;
        }
        
        // 2. Normal kullanıcı oluştur
        $user = User::where('role', 'user')->first();
        if (!$user) {
            $this->error('Normal kullanıcı bulunamadı. Lütfen bir normal kullanıcı oluşturun.');
            return 1;
        }
        
        // 3. Bir admin görevi oluştur
        $adminTask = Task::where('created_by', $admin->id)->first();
        if (!$adminTask) {
            $this->warn('Admin tarafından oluşturulmuş bir görev bulunamadı. Yeni bir görev oluşturuluyor...');
            $adminTask = Task::create([
                'title' => 'Admin Görevi',
                'description' => 'Bu görev admin tarafından oluşturuldu',
                'status' => 'pending',
                'user_id' => $user->id,
                'created_by' => $admin->id
            ]);
            $this->info("Admin görevi oluşturuldu: #{$adminTask->id}");
        } else {
            // Admin görevinin normal kullanıcıya atandığından emin olalım
            if ($adminTask->user_id !== $user->id) {
                $adminTask->user_id = $user->id;
                $adminTask->save();
                $this->info("Admin görevi kullanıcıya atandı: #{$adminTask->id}");
            }
        }
        
        // 4. Kullanıcı görevi oluştur
        $userTask = Task::where('created_by', $user->id)->first();
        if (!$userTask) {
            $this->warn('Kullanıcı tarafından oluşturulmuş bir görev bulunamadı. Yeni bir görev oluşturuluyor...');
            $userTask = Task::create([
                'title' => 'Kullanıcı Görevi',
                'description' => 'Bu görev kullanıcı tarafından oluşturuldu',
                'status' => 'pending',
                'user_id' => $user->id,
                'created_by' => $user->id
            ]);
            $this->info("Kullanıcı görevi oluşturuldu: #{$userTask->id}");
        } else {
            // Kullanıcı görevinin kendisine atandığından emin olalım
            if ($userTask->user_id !== $user->id) {
                $userTask->user_id = $user->id;
                $userTask->save();
                $this->info("Kullanıcı görevi kendisine atandı: #{$userTask->id}");
            }
        }
        
        // Görev detaylarını listele
        $this->info('Admin görevi detayları:');
        $this->info("ID: {$adminTask->id}, Oluşturan: {$adminTask->created_by}, Atanan: {$adminTask->user_id}");
        
        $this->info('Kullanıcı görevi detayları:');
        $this->info("ID: {$userTask->id}, Oluşturan: {$userTask->created_by}, Atanan: {$userTask->user_id}");
        
        // 5. Policy testleri
        $this->testPolicies($admin, $user, $adminTask, $userTask);
        
        return 0;
    }
    
    /**
     * Tüm politikaları test et
     */
    private function testPolicies(User $admin, User $user, Task $adminTask, Task $userTask)
    {
        $this->info('------------- ADMİN YETKİLERİ -------------');
        $this->testPolicy($admin, 'viewAny', Task::class);
        $this->testPolicy($admin, 'view', $adminTask);
        $this->testPolicy($admin, 'view', $userTask);
        $this->testPolicy($admin, 'create', Task::class);
        $this->testPolicy($admin, 'update', $adminTask);
        $this->testPolicy($admin, 'update', $userTask);
        $this->testPolicy($admin, 'updateAllFields', $adminTask);
        $this->testPolicy($admin, 'updateAllFields', $userTask);
        $this->testPolicy($admin, 'updateStatus', $adminTask);
        $this->testPolicy($admin, 'updateStatus', $userTask);
        $this->testPolicy($admin, 'delete', $adminTask);
        $this->testPolicy($admin, 'delete', $userTask);
        
        $this->info('------------- KULLANICI YETKİLERİ -------------');
        $this->testPolicy($user, 'viewAny', Task::class);
        $this->testPolicy($user, 'view', $adminTask);
        $this->testPolicy($user, 'view', $userTask);
        $this->testPolicy($user, 'create', Task::class);
        $this->testPolicy($user, 'update', $adminTask);
        $this->testPolicy($user, 'update', $userTask);
        $this->testPolicy($user, 'updateAllFields', $adminTask);
        $this->testPolicy($user, 'updateAllFields', $userTask);
        $this->testPolicy($user, 'updateStatus', $adminTask);
        $this->testPolicy($user, 'updateStatus', $userTask);
        $this->testPolicy($user, 'delete', $adminTask);
        $this->testPolicy($user, 'delete', $userTask);
    }
    
    /**
     * Tek bir policy'i test et
     */
    private function testPolicy(User $user, string $ability, $argument)
    {
        $result = Gate::forUser($user)->allows($ability, $argument);
        $taskId = $argument instanceof Task ? "#{$argument->id}" : '';
        $userName = $user->role === 'admin' ? 'Admin' : 'Kullanıcı';
        
        if ($result) {
            $this->info("✓ {$userName} '{$ability}' yetkisine sahip {$taskId}");
        } else {
            $this->error("✗ {$userName} '{$ability}' yetkisine sahip değil {$taskId}");
            
            // Hata durumunda görev ve kullanıcı bilgilerini yazdır
            if ($argument instanceof Task) {
                $this->error("    Görev Detayları - user_id: {$argument->user_id}, created_by: {$argument->created_by}");
                $this->error("    Kullanıcı ID: {$user->id}");
            }
        }
    }
}
