<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tüm kullanıcılar için görevler oluşturalım
        $users = User::all();
        $admin = User::where('role', 'admin')->first();
        
        $statuses = ['pending', 'in_progress', 'completed'];
        
        // Bugünün tarihi
        $today = Carbon::today();
        
        foreach ($users as $user) {
            // Her kullanıcı için 5 görev oluştur - kendi oluşturduğu görevler
            for ($i = 1; $i <= 5; $i++) {
                // Statüsü rastgele belirle
                $status = $statuses[array_rand($statuses)];
                
                // Rastgele tarihler oluştur
                $startDate = $this->getRandomStartDate($status, $today);
                $endDate = $startDate ? $this->getRandomEndDate($startDate, $status, $today) : null;
                
                Task::create([
                    'title' => "Görev #{$i} - {$user->name}",
                    'description' => "Bu {$user->name} kullanıcısına ait örnek bir {$status} görevdir. Görev detayı burada yer alır.",
                    'status' => $status,
                    'user_id' => $user->id,
                    'created_by' => $user->id, // Kendi oluşturduğu
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]);
            }
            
            // Her kullanıcıya durum bazlı görevler ekleyelim
            // Bekleyen görev - Gelecekte başlayacak
            $startDate = $today->copy()->addDays(rand(1, 10));
            $endDate = $startDate->copy()->addDays(rand(5, 20));
            
            Task::create([
                'title' => "Bekleyen Görev - {$user->name}",
                'description' => "Bu görev henüz başlanmamış durumda ve beklemede.",
                'status' => 'pending',
                'user_id' => $user->id,
                'created_by' => $user->id, // Kendi oluşturduğu
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);
            
            // Devam eden görev - Başlamış ama bitmemiş
            $startDate = $today->copy()->subDays(rand(1, 10));
            $endDate = $today->copy()->addDays(rand(1, 15));
            
            Task::create([
                'title' => "Devam Eden Görev - {$user->name}",
                'description' => "Bu görev şu anda devam etmektedir.",
                'status' => 'in_progress',
                'user_id' => $user->id,
                'created_by' => $user->id, // Kendi oluşturduğu
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);
            
            // Tamamlanmış görev - Başlamış ve bitmiş
            $startDate = $today->copy()->subDays(rand(15, 30));
            $endDate = $today->copy()->subDays(rand(1, 14));
            
            Task::create([
                'title' => "Tamamlanmış Görev - {$user->name}",
                'description' => "Bu görev tamamlanmış durumda.",
                'status' => 'completed',
                'user_id' => $user->id,
                'created_by' => $user->id, // Kendi oluşturduğu
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);
            
            // Kullanıcı admin değilse, admin tarafından atanmış görevler ekle
            if ($user->role !== 'admin' && $admin) {
                // Admin tarafından ataması yapılan görevler
                for ($i = 1; $i <= 3; $i++) {
                    $status = $statuses[array_rand($statuses)];
                    
                    // Özel tarihler oluştur - süresi yaklaşan ve geçmiş görevler
                    $startDate = null;
                    $endDate = null;
                    
                    // 1. görev süresi dolmuş
                    if ($i === 1 && $status !== 'completed') {
                        $startDate = $today->copy()->subDays(rand(20, 30));
                        $endDate = $today->copy()->subDays(rand(1, 5));
                    }
                    // 2. görev süresi yaklaşıyor (1-5 gün kaldı)
                    else if ($i === 2 && $status !== 'completed') {
                        $startDate = $today->copy()->subDays(rand(10, 15));
                        $endDate = $today->copy()->addDays(rand(1, 5));
                    }
                    // 3. görev normal
                    else {
                        $startDate = $this->getRandomStartDate($status, $today);
                        $endDate = $startDate ? $this->getRandomEndDate($startDate, $status, $today) : null;
                    }
                    
                    Task::create([
                        'title' => "Admin Tarafından Atanmış Görev #{$i} - {$user->name}",
                        'description' => "Bu görev admin tarafından {$user->name} kullanıcısına atanmıştır. Durum: {$status}",
                        'status' => $status,
                        'user_id' => $user->id,
                        'created_by' => $admin->id, // Admin tarafından oluşturuldu
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                    ]);
                }
            }
        }
    }
    
    /**
     * Görev durumuna göre rastgele başlangıç tarihi oluştur
     */
    private function getRandomStartDate(string $status, Carbon $today): ?string
    {
        // %20 ihtimalle tarihi null bırak
        if (rand(1, 100) <= 20) {
            return null;
        }
        
        switch ($status) {
            case 'pending':
                // Gelecekte başlayacak
                return $today->copy()->addDays(rand(1, 15))->format('Y-m-d');
                
            case 'in_progress':
                // Başlamış ama bitmemiş
                return $today->copy()->subDays(rand(1, 10))->format('Y-m-d');
                
            case 'completed':
                // Çoktan tamamlanmış
                return $today->copy()->subDays(rand(10, 30))->format('Y-m-d');
                
            default:
                return null;
        }
    }
    
    /**
     * Başlangıç tarihine ve görev durumuna göre bitiş tarihi oluştur
     */
    private function getRandomEndDate(?string $startDateString, string $status, Carbon $today): ?string
    {
        if (!$startDateString) {
            return null;
        }
        
        $startDate = Carbon::parse($startDateString);
        
        switch ($status) {
            case 'pending':
                // Gelecekte bitecek, başlangıç tarihinden sonra
                return $startDate->copy()->addDays(rand(5, 30))->format('Y-m-d');
                
            case 'in_progress':
                // Gelecekte bitecek
                return $today->copy()->addDays(rand(1, 20))->format('Y-m-d');
                
            case 'completed':
                // Başlangıçtan sonra, bugünden önce
                $endDate = $startDate->copy()->addDays(rand(1, 15));
                if ($endDate->gt($today)) {
                    $endDate = $today->copy()->subDays(1);
                }
                return $endDate->format('Y-m-d');
                
            default:
                return null;
        }
    }
}
