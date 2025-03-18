# İş Yönetim Sistemi

Bu proje, kullanıcıların görevleri oluşturabildiği, takip edebildiği ve yönetebildiği kapsamlı bir iş yönetim sistemidir. Sistem, Laravel tabanlı bir backend API ve Vue.js tabanlı modern bir frontend arayüzünden oluşmaktadır.

## Proje Yapısı

Proje iki ana bileşenden oluşur:

- **[Backend API (Laravel)](/task-manager/README.md)**: REST API ve iş mantığı.
- **[Frontend Uygulaması (Vue.js)](/frontend/README.md)**: Kullanıcı arayüzü.

## Özellikler

### Görev Yönetimi
- Görev oluşturma, düzenleme, görüntüleme ve silme
- Başlangıç ve bitiş tarihleri belirleme
- Görevlerin durumlarını takip etme (Bekliyor, Devam Ediyor, Tamamlandı)
- Son teslim tarihi yaklaşan görevler için görsel uyarılar

### Kullanıcı Yönetimi
- Kullanıcı kaydı ve kimlik doğrulama
- Admin ve normal kullanıcı rolleri
- Rol bazlı erişim kontrolü

### Arayüz
- Duyarlı (responsive) ve modern tasarım
- Kolay kullanım için sezgisel arayüz
- Görevlerin durumlarını görsel olarak izleme

## Teknolojiler

### Backend
- Laravel 12
- MySQL/PostgreSQL
- Laravel Sanctum (API Kimlik Doğrulama)
- Repository-Service Pattern

### Frontend
- Vue.js 3
- TypeScript
- Tailwind CSS
- Pinia (State Yönetimi)
- Vue Router
- Axios (HTTP İstekleri)

## Kurulum

### Ön Gereksinimler
- Composer
- Node.js ve NPM/Yarn
- MySQL/PostgreSQL veritabanı

### Backend Kurulumu

1. Repoyu klonlayın:
```
git clone https://github.com/edayazilim/todo.git
cd todo
```

2. Backend bağımlılıklarını yükleyin:
```
cd task-manager
composer install
```

3. `.env` dosyasını oluşturun:
```
cp .env.example .env
```

4. Uygulama anahtarını oluşturun:
```
php artisan key:generate
```

5. Veritabanı ayarlarını `.env` dosyasında yapılandırın ve tabloları oluşturun:
```
php artisan migrate --seed
```

6. API sunucusunu başlatın:
```
php artisan serve
```

### Frontend Kurulumu

1. Yeni bir terminal açın ve frontend dizinine gidin:
```
cd frontend
```

2. Frontend bağımlılıklarını yükleyin:
```
npm install
# veya
yarn install
```

3. `.env` dosyasını oluşturun ve backend API URL'sini yapılandırın:
```
echo "VITE_API_URL=http://localhost:8000/api" > .env
```

4. Geliştirme sunucusunu başlatın:
```
npm run dev
# veya
yarn dev
```

5. Tarayıcınızda şu adresi açın: http://localhost:5173

## Kullanım

### Demo Hesapları

Seed işlemi sonrası aşağıdaki hesaplarla giriş yapabilirsiniz:

**Admin Hesabı:**
- E-posta: admin@example.com
- Şifre: password

**Kullanıcı Hesabı:**
- E-posta: user@example.com
- Şifre: password

### Temel Kullanım

1. Giriş yapın veya yeni bir hesap oluşturun
2. Görev listesini görüntüleyin
3. Yeni görev ekleyin (başlık, açıklama, tarihler)
4. Görev durumlarını güncelleyin
5. Görevleri düzenleyin veya silin

## Test

Projeyi test etmek için aşağıdaki komutları kullanabilirsiniz:

### Backend Testleri
```
cd task-manager
php artisan test
```

### Frontend Testleri
```
cd frontend
npm run test
# veya
yarn test
```

## Katkıda Bulunma

Katkıda bulunmak isteyenler için:

1. Projeyi fork edin
2. Feature branch oluşturun (`git checkout -b feature/amazing-feature`)
3. Değişikliklerinizi commit edin (`git commit -m 'Add some amazing feature'`)
4. Branch'inizi push edin (`git push origin feature/amazing-feature`)
5. Pull Request gönderin

## Lisans

Bu proje [MIT Lisansı](LICENSE) altında lisanslanmıştır.

## İletişim

Email: edayazilim@gmail.com

