<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>


# 💬 Laravel + Reverb Gerçek Zamanlı Mesajlaşma Sistemi

Bu proje, Laravel 12 ve Laravel Reverb kullanılarak geliştirilmiş gerçek zamanlı bir WebSocket mesajlaşma sistemidir. Kullanıcılar arasında anlık özel mesajlaşmayı destekler. Yayın (broadcast) sistemi olarak Laravel Reverb kullanılır.

---

## ⚙️ Gereksinimler

- PHP >= 8.2
- Composer
- Laravel 12
- Node.js >= 18
- Redis (Reverb için zorunlu)
- MySQL (veya desteklenen diğer veritabanları)

---

## 🚀 Kurulum Adımları

### 1. Projeyi klonlayın

```bash
git clone https://github.com/aydinyagizz/laravelReverbMessagingApp.git
cd laravelReverbMessagingApp
````

### 2. PHP bağımlılıklarını yükleyin

```bash
composer install
````
### 3. Node.js bağımlılıklarını yükleyin
```bash
npm install
````

### 4. Ortam dosyasını oluşturun
```bash
cp .env.example .env
php artisan key:generate
````
### 5. Veritabanı ayarlarını yapın
.env dosyasında aşağıdaki alanları kendi ortamınıza göre güncelleyin:
```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=reverb_chat
DB_USERNAME=root
DB_PASSWORD=
````
### 6. Veritabanı tablolarını oluşturun
```bash
php artisan migrate db:seed
````
### 📡 Reverb Yayın Sistemi Ayarları
.env dosyanıza aşağıdaki Reverb yapılandırmasını ekleyin:
```bash
BROADCAST_DRIVER=reverb

REVERB_APP_ID=app-id
REVERB_APP_KEY=app-key
REVERB_APP_SECRET=app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"

````

### 🧑‍💻 Uygulamayı Başlatma
Tüm servisleri aşağıdaki komutlarla başlatın:

Laravel HTTP sunucusu
```bash
php artisan serve
php artisan reverb:start
php artisan queue:work
npm run dev
````
### 🔐 Kimlik Doğrulama ve Kullanıcılar

Laravel'in standart kullanıcı kimlik doğrulama sistemi kullanılmaktadır. Kayıt ve giriş işlemleri /register ve /login sayfalarından yapılabilir.

## Kullanıcı 1 => admin@gmail.com : 123456
## Kullanıcı 2 => user@gmail.com : 123456


Komut Açıklamaları
- php artisan serve => Laravel sunucusunu başlatır
- php artisan reverb:start => Reverb WebSocket sunucusunu başlatır
- php artisan queue:work =>Kuyruktaki yayın olaylarını işler
- npm run dev => Vite ile canlı frontend derlemesi

