# POS Mie Ayam

Aplikasi Point of Sale (POS) sederhana untuk usaha mie ayam dengan fitur kasir, manajemen produk, dan laporan penjualan.

## Fitur utama

- Kasir untuk menambah item ke keranjang dan checkout
- Order pending dan payment completion dengan tunai atau QRIS
- Detail order dan struk pembayaran
- Manajemen produk (tambah, edit, hapus, gambar produk)
- Laporan penjualan dan pendapatan harian
- Login admin dan akses terbatas

## Persyaratan

- PHP 8.2+
- Composer
- Node.js 18+
- NPM
- Database MySQL/SQLite untuk lokal development

## Setup cepat

1. Salin file environment:
   ```bash
   cp .env.example .env
   ```
2. Atur koneksi database di `.env`.
3. Install dependency PHP dan Node:
   ```bash
   composer install
   npm install
   ```
4. Jalankan migrasi dan seeder:
   ```bash
   php artisan migrate --seed
   ```
5. Jalankan aplikasi:
   ```bash
   composer run dev
   ```
   atau jalankan secara terpisah:
   ```bash
   php artisan serve
   npm run dev
   ```

## Login default

Setelah menjalankan seeder, akun admin biasanya tersedia:

- Email: admin@example.com
- Password: password

## Catatan keamanan

- Jangan memasukkan file `.env` dan folder `.git` ke dalam ZIP aplikasi untuk distribusi.
- Pastikan `public/storage` dan `storage/app/public` sudah di-link jika gambar produk dipakai.

## Menjalankan test

```bash
php artisan test
```

## Build asset frontend

```bash
npm run build
```
