# Emar CRM Projesi

## 📌 Proje Tanımı
Bu proje, **Emar** şirketinin mevcut CRM (Müşteri İlişkileri Yönetimi) sisteminin arayüzünü tamamen yenilemek ve modernleştirmek amacıyla geliştirilmiştir.  
Şirket içi süreçleri optimize etmek, müşteri ve faaliyet kayıtlarını daha etkin bir şekilde yönetmek için tasarlanmıştır.  
Uygulama, kullanıcı dostu ve güncel bir arayüze sahip olup, operasyonel verimliliği artırmayı hedeflemektedir.

## 🚀 Özellikler

### 🔷 Modern ve Kullanıcı Dostu Arayüz
- Laravel Blade ve Tailwind CSS ile oluşturulmuş şık ve sezgisel arayüz.
- Yeni kurumsal kimliğe uygun tasarım.
  
### 👤 Müşteri Yönetimi
- **Müşteri Kaydı:** Yeni müşterileri sisteme ekleme (ad, soyad, iletişim vb.).
- **Listeleme & Arama:** Tüm müşterileri görüntüleme ve filtreleme.
- **Detaylar:** Müşteriye özel bilgi ekranı.
- **Güncelleme & Silme:** Mevcut kayıtları düzenleme veya silme.

### 📅 Faaliyet (Etkinlik) Yönetimi
- **Faaliyet Ekleme:** Görüşme, toplantı, telefon gibi tüm faaliyet türlerini kayıt altına alma.
- **Filtreleme:** Müşteri ya da tarih bazlı faaliyet görüntüleme.
- **Detaylar & Notlar:** Her faaliyet için özel not alanı.

### 🔐 Güvenlik
- Laravel Breeze ile kimlik doğrulama ve yetkilendirme.
- Şifreler hash’lenerek saklanır.
  
### 🛠️ Gelişmiş Özellikler
- SQL veritabanı entegrasyonu.
- Form validasyonları.
- Veri bütünlüğü ve tutarlılığı.

## 🧰 Kullanılan Teknolojiler

| Teknoloji        | Açıklama                                 |
|------------------|------------------------------------------|
| Laravel          | PHP tabanlı güçlü web framework          |
| PHP              | Sunucu taraflı programlama dili          |
| MySQL/PostgreSQL | İlişkisel veritabanı yönetimi            |
| Tailwind CSS     | Utility-first modern CSS framework       |
| Blade            | Laravel’in esnek şablon motoru           |
| Alpine.js        | Laravel Breeze ile gelen hafif JS yapısı |

### Ekran Görüntüsü
![login](https://github.com/user-attachments/assets/f6929bc0-4e56-43f8-8350-30424c76b930)


## ⚙️ Kurulum

### 1. Depoyu Klonlayın
```bash
git clone https://github.com/KULLANICI_ADINIZ/Emar-CRM.git
cd Emar-CRM
```

### 2. Composer Kurulumu
```bash

composer install
```
### 3. .env Dosyasını Oluşturun
```bash

cp .env.example .env
```
### 4. Uygulama Anahtarını Oluşturun
```bash

php artisan key:generate
```

### 5. Veritabanını Ayarlayın
```bash

.env dosyasını açıp bilgileri güncelleyin:
```
```bash

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=emar_crm
DB_USERNAME=root
DB_PASSWORD=
```

### 6. Veritabanı Tablolarını Oluşturun
```bash

php artisan migrate
```

### 7. NPM Kurulumu ve Derleme
```bash

npm install
npm run dev   # Geliştirme için
```

# veya üretim için:
# npm run build
### 8. Sunucuyu Başlatın
```bash

php artisan serve
Tarayıcıdan şu adrese gidin: http://127.0.0.1:8000
```
### 🧪 Kullanım

- Kayıt Olun: http://127.0.0.1:8000/register

- Giriş Yapın: http://127.0.0.1:8000/login

- Müşteri İşlemleri: Müşteri ekle, güncelle, sil.

- Faaliyet Yönetimi: Müşteriyle ilgili tüm etkileşimleri kaydedin.


