# İş Yönetim Sistemi - Frontend

Bu proje, İş Yönetim Sistemi API'si için Vue.js ile geliştirilmiş modern bir kullanıcı arayüzüdür. Kullanıcıların görevleri yönetmesini, takip etmesini ve organize etmesini sağlar.

## Özellikler

- **Kullanıcı Arayüzü**
  - Modern ve duyarlı (responsive) tasarım
  - Kolay kullanılabilir görev yönetim paneli
  - Sürükle-bırak görev sıralaması
  - Görevlerin durumlarını (Bekliyor, Devam Ediyor, Tamamlandı) görsel olarak izleme

- **Görev Yönetimi**
  - Görevleri oluşturma, görüntüleme, güncelleme ve silme
  - Başlangıç ve bitiş tarihi belirleme
  - Son teslim tarihi yaklaşan görevler için renkli uyarılar
    - Kırmızı: Süresi geçmiş görevler
    - Turuncu: 3 gün veya daha az kalan görevler
    - Mavi: 4-5 gün içinde süresi dolacak görevler

- **Rol Tabanlı Erişim**
  - Admin ve normal kullanıcı görünümleri
  - Admin kullanıcılar için ekstra yönetim özellikleri
  - Kullanıcı rolüne göre özelleştirilmiş arayüz

## Teknolojiler

- Vue.js 3
- TypeScript
- Tailwind CSS
- Pinia (State Yönetimi)
- Vue Router
- Axios (HTTP İstekleri)

## Mimari Yapı

- **Komponentler**: Yeniden kullanılabilir UI komponentleri
- **Sayfalar**: Uygulama sayfaları
- **Store**: Pinia ile durum yönetimi
- **Servisler**: API çağrıları ve harici hizmetler
- **Yardımcılar**: Yardımcı fonksiyonlar ve araçlar

## Kurulum

1. Repoyu klonlayın (backend reposunu zaten klonladıysanız bu adımı atlayabilirsiniz):
```
git clone https://github.com/edayazilim/todo.git
cd todo/frontend
```

2. Bağımlılıkları yükleyin:
```
npm install
# veya
yarn install
```

3. Geliştirme sunucusunu başlatın:
```
npm run dev
# veya
yarn dev
```

4. Backend API URL'sini yapılandırmak için `.env` dosyasını düzenleyin:
```
VITE_API_URL=http://localhost:8000/api
```

## Arayüz Bileşenleri

### TaskList
Görevlerin listesini gösteren ve kategorilere ayıran komponent. Görevleri durumlarına göre (Bekleyen, Devam Eden, Tamamlanan) gruplandırır.

### TaskItem
Her bir görev öğesini görselleştiren komponent. Görevin durumunu, başlık, açıklama, tarih ve süre bilgilerini içerir. Son teslim tarihine yaklaşan görevler için uyarı gösterir:
- 3 günden az kalan görevler için turuncu uyarı
- 4-5 gün kalan görevler için mavi uyarı
- Süresi geçmiş görevler için kırmızı uyarı

### TaskForm
Görev oluşturma ve düzenleme formu. Başlangıç ve bitiş tarihlerini, başlık, açıklama ve durumu belirlemek için alanlar içerir.

## Entegrasyon

Frontend, backend API ile tam entegre çalışır. Kullanıcılar uygulamaya giriş yaptıktan sonra görevleri oluşturabilir, düzenleyebilir ve silebilirler. Admin kullanıcılar tüm görevleri görebilir ve yönetebilirken, normal kullanıcılar sadece kendi görevlerini görebilir.

## Canlı Demo

```
https://is-yonetim-sistemi.example.com
```

## Test

```
npm run test
# veya
yarn test
```

## Derleme

Projeyi production için derlemek:

```
npm run build
# veya
yarn build
```

Derlenen dosyalar `dist` dizininde oluşturulacaktır.

## Katkıda Bulunma

1. Projeyi fork edin
2. Feature branch oluşturun (`git checkout -b feature/amazing-feature`)
3. Değişikliklerinizi commit edin (`git commit -m 'Add some amazing feature'`)
4. Branch'inizi push edin (`git push origin feature/amazing-feature`)
5. Pull Request gönderin

## Lisans

Bu proje [MIT Lisansı](LICENSE) altında lisanslanmıştır.

## Testler

Projenin testlerini çalıştırmak için:

```bash
npm run test       # Testleri bir kez çalıştırır
npm run test:watch # Testleri izleme modunda çalıştırır
```

### Test Yapısı

Proje testleri şu şekilde organize edilmiştir:

- `src/tests/components/`: Bileşen testleri
- `src/tests/views/`: Sayfa testleri
- `src/tests/store/`: Pinia store testleri

Testler Vitest ve Vue Test Utils kullanılarak yazılmıştır.

#### Bileşen Testleri

Bileşen testleri şunları test eder:

- Doğru şekilde render ediliyor mu?
- Props doğru şekilde işleniyor mu?
- Olaylar doğru şekilde tetikleniyor mu?
- Computed özellikler doğru çalışıyor mu?

#### Store Testleri

Store testleri şunları test eder:

- Getters doğru değerleri döndürüyor mu?
- Actions doğru şekilde çalışıyor mu?
- State doğru şekilde güncelleniyor mu?

#### Sayfa Testleri

Sayfa testleri şunları test eder:

- Sayfa doğru şekilde render ediliyor mu?
- Kullanıcı etkileşimleri doğru işleniyor mu?
- Yönlendirmeler doğru çalışıyor mu?

## Otomatik Test Kodu Üretme

Yeni bir bileşen veya özellik için test kodu üretmek istiyorsanız şu adımları izleyin:

1. Bileşen veya özelliği önce geliştirin
2. Test klasöründe uygun yerde yeni bir `.spec.ts` dosyası oluşturun
3. Yukarıdaki örneklere benzer şekilde testleri yazın

## Test Kapsamı

Projedeki mevcut test kapsamı:

- **Bileşenler**:
  - TaskItem
  - TaskForm
  
- **Store**:
  - useAuthStore
  
- **Sayfalar**:
  - Login
