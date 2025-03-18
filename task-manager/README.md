# İş Yönetim Sistemi API

Bu proje, kullanıcıların görevler oluşturabileceği, görüntüleyebileceği, güncelleyebileceği ve silebileceği bir iş yönetim sistemi API'sidir. Sistem, Laravel 10 framework'ü kullanılarak oluşturulmuştur.

## Özellikler

- **Kullanıcı Yönetimi**
  - Kayıt ve giriş işlemleri
  - JWT tabanlı kimlik doğrulama
  - Admin ve normal kullanıcı rolleri

- **Görev Yönetimi**
  - Görev oluşturma, görüntüleme, güncelleme ve silme işlemleri
  - Başlangıç ve bitiş tarihleri belirleme
  - Görevlerin durumlarını takip etme (Bekliyor, Devam Ediyor, Tamamlandı)
  - Son teslim tarihi yaklaşan görevler için uyarılar

- **İzinler ve Kısıtlamalar**
  - Admin kullanıcılar tüm görevleri görebilir ve yönetebilir
  - Normal kullanıcılar sadece kendi görevlerini görebilir
  - Normal kullanıcılar admin tarafından oluşturulan görevlerin sadece durumunu değiştirebilir
  - Kapsamlı politikalar ve izin yönetimi

## Teknolojiler

- Laravel 12
- MySQL/PostgreSQL
- Laravel Sanctum (API Kimlik Doğrulama)
- Repository-Service Pattern

## Veritabanı Yapısı

### Tasks Tablosu
- `id`: Otomatik artan birincil anahtar
- `title`: Görev başlığı
- `description`: Görev açıklaması
- `user_id`: Görevin atandığı kullanıcı
- `created_by`: Görevi oluşturan kullanıcı (admin veya kendi)
- `status`: Görevin durumu (pending, in_progress, completed)
- `start_date`: Görevin başlangıç tarihi
- `end_date`: Görevin bitiş tarihi
- `created_at`: Oluşturulma zamanı
- `updated_at`: Güncelleme zamanı

### Users Tablosu
- `id`: Otomatik artan birincil anahtar
- `name`: Kullanıcı adı
- `email`: E-posta adresi
- `password`: Şifrelenmiş parola
- `role`: Kullanıcı rolü (admin, user)
- Standart Laravel zaman damgaları

## Kurulum

1. Repoyu klonlayın:
```
git clone https://github.com/edayazilim/todo.git
cd todo/task-manager
```

2. Bağımlılıkları yükleyin:
```
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

5. Veritabanı ayarlarını `.env` dosyasında yapılandırın:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_manager
DB_USERNAME=root
DB_PASSWORD=
```

6. Veritabanı tablolarını oluşturun:
```
php artisan migrate
```

7. (İsteğe bağlı) Örnek verileri ekleyin:
```
php artisan db:seed
```

8. Geliştirme sunucusunu başlatın:
```
php artisan serve
```

## API Rotaları

### Kimlik Doğrulama
- `POST /api/register` - Yeni kullanıcı kaydı
- `POST /api/login` - Kullanıcı girişi
- `POST /api/logout` - Çıkış yapma

### Görevler
- `GET /api/tasks` - Tüm görevleri listele (Admin: Tüm görevler, Kullanıcı: Kendi görevleri)
- `POST /api/tasks` - Yeni görev oluştur
- `GET /api/tasks/{id}` - Belirli bir görevi görüntüle
- `PUT /api/tasks/{id}` - Görevi güncelle
- `DELETE /api/tasks/{id}` - Görevi sil

### Kullanıcılar
- `GET /api/users` - Tüm kullanıcıları listele (Sadece admin)

## Mimari Yapı

Proje, aşağıdaki mimari yapıyı takip eder:

- **Controller**: HTTP isteklerini işler ve ilgili servislere yönlendirir
- **Service**: İş mantığını içerir
- **Repository**: Veritabanı işlemlerini gerçekleştirir
- **Model**: Veritabanı tablolarını temsil eder
- **Policy**: Yetkilendirme kurallarını tanımlar

Bu yaklaşım, kodun daha iyi organize edilmesine, test edilebilirliğinin artmasına ve bakımının kolaylaşmasına yardımcı olur.

## Test

Uygulamayı test etmek için:

```
php artisan test
```

## Lisans

Bu proje [MIT Lisansı](LICENSE) altında lisanslanmıştır.
