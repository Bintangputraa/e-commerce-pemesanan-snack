# Cemil.in

Platform pemesanan camilan online yang membantu pelanggan menjelajahi menu,
menyimpan favorit, mengelola keranjang, checkout, dan membayar pesanan melalui
Midtrans. Aplikasi ini juga menyediakan dashboard admin untuk mengelola produk,
pesanan, dan pengguna.

## Fitur

- Katalog camilan dengan pencarian dan filter kategori.
- Registrasi, login, verifikasi email, dan pengaturan profil.
- Keranjang belanja dan daftar favorit.
- Checkout dengan alamat pengiriman, tanggal pesan, catatan item, dan voucher
  `CEMIL10` untuk diskon 10%.
- Pembayaran Midtrans Snap dan pembaruan status melalui webhook.
- Riwayat pesanan dan pemantauan pesanan yang sedang diproses.
- Dashboard admin untuk CRUD item, melihat pesanan, serta mengelola pengguna.
- REST API untuk users, items, favorites, carts, orders, notifications, dan
  order details.

## Teknologi

- PHP 8.3 dan Laravel 13
- Inertia.js 3 dan Vue 3 dengan TypeScript
- Tailwind CSS 4 dan Vite
- SQLite (default) atau database lain yang didukung Laravel
- Midtrans Snap untuk pembayaran
- Pest, PHPStan, dan Laravel Pint untuk pengujian serta kualitas kode

## Persyaratan

Pastikan perangkat sudah memiliki:

- PHP >= 8.3
- Composer
- Node.js dan npm
- SQLite atau MySQL

## Instalasi Lokal

1. Clone repository dan masuk ke folder proyek:

   ```bash
   git clone <URL_REPOSITORY>
   cd ecommerce-pemesanan-snack-app
   ```

2. Install dependency PHP dan JavaScript:

   ```bash
   composer install
   npm install
   ```

3. Buat file environment dan application key:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   Pada Windows PowerShell, gunakan:

   ```powershell
   Copy-Item .env.example .env
   php artisan key:generate
   ```

4. Untuk menggunakan SQLite sebagai database default, buat file database:

   ```powershell
   New-Item database\database.sqlite -ItemType File
   ```

   Jika file tersebut sudah ada, langkah ini dapat dilewati. Alternatifnya,
   ubah `DB_*` di `.env` sesuai konfigurasi MySQL atau database pilihan.

5. Jalankan migration dan seeder:

   ```bash
   php artisan migrate --seed
   ```

6. Konfigurasikan pembayaran Midtrans pada `.env`:

   ```dotenv
   MIDTRANS_SERVER_KEY=your-server-key
   MIDTRANS_CLIENT_KEY=your-client-key
   MIDTRANS_IS_PRODUCTION=false
   MIDTRANS_IS_SANITIZED=true
   MIDTRANS_IS_3DS=true
   ```

   Gunakan key Sandbox untuk pengembangan. Atur URL webhook di dashboard
   Midtrans ke:

   ```text
   https://domain-anda.com/api/midtrans/notification
   ```

7. Build asset frontend:

   ```bash
   npm run build
   ```

8. Jalankan aplikasi:

   ```bash
   php artisan serve
   ```

   Pada terminal lain, jalankan Vite saat mengembangkan frontend:

   ```bash
   npm run dev
   ```

   Buka `http://localhost:8000`.

> Alternatif cepat: setelah `.env` siap, `composer run setup` menjalankan
> instalasi dependency, migration, instalasi npm, dan build frontend.

## Role Pengguna

Semua pengguna baru memiliki role `customer`. Role admin harus diberikan
secara manual melalui database, misalnya menggunakan Tinker:

```bash
php artisan tinker
```

```php
$user = App\Models\User::where('email', 'admin@example.com')->first();
$user->update(['role' => 'admin']);
```

Pengguna admin dapat mengakses `/admin`, sedangkan pelanggan menggunakan
halaman katalog, checkout, dan riwayat pesanan.

## Perintah Pengembangan

```bash
# Menjalankan development server Laravel
composer run dev

# Build frontend untuk production
npm run build

# Memeriksa format dan kualitas frontend
npm run check

# Memeriksa tipe TypeScript
npm run types:check

# Menjalankan test dan pemeriksaan PHP
php artisan test

# Menjalankan test lengkap yang dikonfigurasi proyek
composer run test
```

## Endpoint API untuk Kotlin

Semua endpoint berikut menggunakan prefix `/api`. Endpoint selain login,
registrasi, katalog item, dan webhook membutuhkan header:

```text
Authorization: Bearer <token>
```

Token didapat dari `auth/register` atau `auth/login` dan berlaku selama 30 hari.

| Method | Endpoint | Keterangan |
| --- | --- | --- |
| POST | `/auth/register` | Registrasi pelanggan dan mendapatkan token |
| POST | `/auth/login` | Login pelanggan dan mendapatkan token |
| GET | `/auth/me` | Profil pengguna yang sedang login |
| POST | `/auth/logout` | Menghapus token aktif |
| POST | `/checkout` | Membuat pesanan dan mendapatkan Snap token |
| POST | `/orders/{order}/pay` | Membuat ulang pembayaran |
| GET | `/orders/{order}/payment-status` | Memeriksa status pembayaran |
| POST | `/midtrans/notification` | Webhook status pembayaran (tanpa token) |

Resource `items` (katalog), `carts`, `favorites`, `orders`,
`notifications`, dan `order-details` tersedia untuk aplikasi Kotlin dengan
token pengguna. Perubahan katalog dan resource `users` hanya dapat dilakukan
oleh admin.

Route web tetap terpisah di `routes/web.php` dan menggunakan session Fortify.

Resource API:

| Method | Endpoint | Keterangan |
| --- | --- | --- |
| GET | `/items`, `/items/{item}` | Katalog camilan |
| GET/POST/PUT/DELETE | `/carts` | Keranjang milik pengguna |
| GET/POST/PUT/DELETE | `/favorites` | Favorit milik pengguna |
| GET/POST/PUT/DELETE | `/orders` | Pesanan milik pengguna |
| GET/POST/PUT/DELETE | `/notifications` | Notifikasi milik pengguna |
| GET/POST | `/order-details` | Detail pesanan milik pengguna |

## Struktur Direktori

```text
app/
  Http/Controllers/       Controller web dan API
  Models/                 Model User, Item, Cart, Order, dan lainnya
  Services/               Integrasi layanan pembayaran
database/
  migrations/             Struktur database
  seeders/                Data awal
resources/js/
  pages/                  Halaman Inertia/Vue
  components/             Komponen UI
routes/
  web.php                 Route halaman web
  api.php                 Route REST API
tests/                    Feature dan unit test
```

## Lisensi

Proyek ini menggunakan lisensi MIT.
