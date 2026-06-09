# Development Plan — POS Mie Ayam

Aplikasi Point of Sale (POS) sederhana untuk warung Mie Ayam. Dibuat sebagai bagian dari ujian, dengan fokus pada fungsionalitas inti yang bersih dan tidak overcomplicated.

---

## 1. Ringkasan Project

Kasir memilih menu, menambahkannya ke keranjang, memproses pembayaran (dengan keterangan metode bayar), menampilkan/mencetak struk, lalu owner dapat melihat histori transaksi dan laporan penjualan harian.

**Prinsip desain:**

- Alur sesederhana mungkin, cukup untuk demonstrasi ujian.
- Tanpa manajemen stok dan tanpa sistem role yang kompleks.
- Metode pembayaran hanya sebagai *keterangan* (tunai / QRIS / transfer) — tidak ada integrasi pembayaran nyata.

---

## 2. Tech Stack

| Komponen | Teknologi |
|----------|-----------|
| Framework | Laravel 12+ |
| Bahasa | PHP 8.2+ |
| Template | Blade |
| CSS | Tailwind CSS 4 |
| Komponen UI | daisyUI 5 |
| Build tool | Vite |
| Interaktivitas POS | JavaScript vanilla (tanpa frontend framework) |
| Database | MySQL (atau SQLite untuk kemudahan) |
| Auth | Laravel Breeze / starter kit (login sederhana) |

**Catatan arsitektur:** Halaman POS (pemilihan menu & keranjang dinamis) dikerjakan dengan JavaScript vanilla — state keranjang disimpan di sisi klien, dirender ulang tanpa reload halaman, lalu dikirim ke server saat checkout. Tidak menggunakan Vue/React/Livewire untuk fitur POS.

---

## 3. Fitur MVP

### 3.1 Autentikasi
Login sederhana untuk kasir. Satu jenis user, tanpa pembedaan role. Halaman aplikasi hanya bisa diakses setelah login.

### 3.2 Master Data Menu
CRUD menu lengkap: nama, harga, kategori, gambar (opsional), dan status tersedia/tidak tersedia. Dilengkapi kategori menu sederhana (contoh: Mie Ayam, Minuman, Topping).

### 3.3 Halaman POS (inti)
- Grid menu yang dapat diklik untuk ditambahkan ke keranjang.
- Filter menu per kategori.
- Panel keranjang yang update real-time via JavaScript: tambah qty, kurangi qty, hapus item, dan total dihitung otomatis.
- Menu dengan status tidak tersedia tidak bisa ditambahkan.

### 3.4 Checkout & Pembayaran
- Pilihan metode bayar: **Tunai / QRIS / Transfer** (sebagai keterangan saja).
- Input nominal uang dibayar dengan perhitungan kembalian otomatis (relevan untuk tunai).
- Simpan transaksi secara atomik (order + item) dalam satu DB transaction.
- Generate nomor order unik.

### 3.5 Struk
Tampilan struk yang dapat di-print: nomor order, daftar item & qty, total, metode bayar, nominal bayar, kembalian, tanggal/waktu, dan nama kasir.

### 3.6 Histori Transaksi
Daftar semua transaksi dengan badge metode bayar (warna berbeda untuk Tunai / QRIS / Transfer). Tiap baris dapat diklik untuk melihat detail / struk.

### 3.7 Laporan Harian
- Total omzet hari ini dan jumlah transaksi.
- Rincian omzet per metode bayar (total Tunai vs QRIS vs Transfer).
- Daftar transaksi hari ini.
- Menu terlaris (agregasi qty terjual).

---

## 4. Desain Database

Lima tabel inti (di luar tabel bawaan Laravel seperti `password_reset_tokens`, dll).

### 4.1 `users` (bawaan Laravel)

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| name | string | Nama kasir |
| email | string unique | Login |
| password | string | |
| timestamps | | |

### 4.2 `categories`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| name | string | Nama kategori |
| timestamps | | |

### 4.3 `products` (menu)

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| category_id | bigint FK → categories | |
| name | string | Nama menu |
| price | decimal(10,2) | Harga |
| image | string nullable | Path gambar |
| is_available | boolean default true | Status tersedia |
| deleted_at | timestamp nullable | **Soft delete** (SoftDeletes) |
| timestamps | | |

### 4.4 `orders`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| order_number | string unique | Nomor order (mis. `INV-20260608-0001`) |
| user_id | bigint FK → users | Kasir yang melayani |
| total | decimal(10,2) | Total transaksi |
| paid_amount | decimal(10,2) | Nominal uang dibayar |
| change_amount | decimal(10,2) | Kembalian |
| payment_method | enum('cash','qris','transfer') | Keterangan metode bayar |
| timestamps | | Termasuk `created_at` sebagai waktu transaksi |

### 4.5 `order_items`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| order_id | bigint FK → orders | |
| product_id | bigint FK → products, nullable | Referensi menu |
| product_name | string | **Snapshot** nama saat transaksi |
| price | decimal(10,2) | **Snapshot** harga saat transaksi |
| quantity | integer | Jumlah |
| subtotal | decimal(10,2) | price × quantity |
| timestamps | | |

**Catatan penting:** `order_items` menyimpan *snapshot* nama dan harga produk pada saat transaksi terjadi. Dengan begitu, laporan & struk lama tetap akurat meskipun menu di-master data nanti diubah harganya atau dihapus.

Tabel `products` menggunakan **soft delete** (`SoftDeletes` + kolom `deleted_at`): menu yang "dihapus" tidak benar-benar hilang dari database, hanya ditandai. Ini menjaga integritas histori transaksi (baris `products` tetap ada sehingga relasi & referensi lama aman) dan memungkinkan menu dipulihkan bila perlu. `product_id` pada `order_items` tetap dibuat nullable sebagai pengaman tambahan.

### 4.6 Relasi

```
users (1) ───< (N) orders (1) ───< (N) order_items (N) >─── (1) products (N) >─── (1) categories
```

- `categories` 1 — N `products`
- `products` 1 — N `order_items`
- `orders` 1 — N `order_items`
- `users` 1 — N `orders`

---

## 5. Struktur Route, Controller & View

### Routes (`routes/web.php`, dilindungi middleware `auth`)

| Method | URI | Controller@method | Keterangan |
|--------|-----|-------------------|------------|
| GET | `/` | DashboardController@index | Redirect ke POS / ringkasan |
| GET | `/categories` ... | CategoryController (resource) | CRUD kategori |
| GET | `/products` ... | ProductController (resource) | CRUD menu |
| GET | `/pos` | PosController@index | Halaman kasir |
| POST | `/pos/checkout` | PosController@checkout | Simpan transaksi |
| GET | `/orders` | OrderController@index | Histori transaksi |
| GET | `/orders/{order}` | OrderController@show | Detail / struk |
| GET | `/orders/{order}/receipt` | OrderController@receipt | Struk printable |
| GET | `/reports/daily` | ReportController@daily | Laporan harian |

### Controllers

- `CategoryController` — resource CRUD kategori.
- `ProductController` — resource CRUD menu + upload gambar.
- `PosController` — `index` (tampilkan menu + kategori), `checkout` (validasi & simpan order dalam DB transaction).
- `OrderController` — `index` (list + filter), `show`, `receipt`.
- `ReportController` — `daily` (query agregasi omzet, per metode bayar, menu terlaris).

### Views (Blade + daisyUI)

```
resources/views/
├── layouts/app.blade.php          # layout utama + navbar daisyUI
├── auth/                          # bawaan Breeze
├── categories/                    # index, create, edit
├── products/                      # index, create, edit
├── pos/index.blade.php            # halaman kasir (grid menu + keranjang)
├── orders/
│   ├── index.blade.php            # histori transaksi + badge metode bayar
│   ├── show.blade.php             # detail transaksi
│   └── receipt.blade.php          # struk printable
└── reports/daily.blade.php        # laporan harian
```

### JavaScript

```
resources/js/
└── pos.js     # state keranjang, render item, hitung total & kembalian, submit checkout
```

---

## 6. Plan Per Fase

### Fase 0 — Setup Project (½ hari)
- [ ] `composer create-project laravel/laravel` (Laravel 12+).
- [ ] Konfigurasi `.env` & koneksi database.
- [ ] Pasang Tailwind CSS 4 + daisyUI 5 via Vite, verifikasi build.
- [ ] Install auth starter (Breeze) untuk login kasir.
- [ ] Buat layout dasar `layouts/app.blade.php` + navbar daisyUI.

### Fase 1 — Master Data (1 hari)
- [ ] Migration + model `Category` dan `Product` (beserta relasi & casts). Tambahkan `$table->softDeletes()` & trait `SoftDeletes` pada `Product`.
- [ ] Seeder kategori + contoh menu Mie Ayam.
- [ ] CRUD `CategoryController` + view.
- [ ] CRUD `ProductController` + view + upload gambar + toggle `is_available`.
- [ ] Validasi form (Form Request).

### Fase 2 — Halaman POS (1–2 hari)
- [ ] `PosController@index`: kirim daftar menu (tersedia) & kategori.
- [ ] Layout grid menu + panel keranjang (daisyUI).
- [ ] `pos.js`: tambah ke keranjang, ubah qty, hapus, hitung subtotal/total.
- [ ] Filter menu per kategori (JS).
- [ ] Handle menu tidak tersedia.

### Fase 3 — Checkout & Struk (1 hari)
- [ ] Form pembayaran: pilihan metode bayar (Tunai/QRIS/Transfer) + input uang dibayar.
- [ ] Perhitungan kembalian otomatis (JS).
- [ ] `PosController@checkout`: validasi, simpan `order` + `order_items` dalam DB transaction, generate `order_number`.
- [ ] Halaman struk printable (`receipt.blade.php`) + tombol print.

### Fase 4 — Histori & Laporan (1–1½ hari)
- [ ] `OrderController@index`: daftar transaksi + badge metode bayar (warna berbeda).
- [ ] `OrderController@show`: detail transaksi.
- [ ] `ReportController@daily`: omzet hari ini, jumlah transaksi, rincian per metode bayar, menu terlaris.
- [ ] Tampilkan laporan dengan ringkasan daisyUI (stat cards).

### Fase 5 — Polish & Uji (½ hari)
- [ ] Validasi & edge case (keranjang kosong, uang bayar kurang dari total untuk tunai).
- [ ] Rapikan styling daisyUI & responsivitas.
- [ ] Uji alur end-to-end: login → tambah menu → POS → checkout → struk → histori → laporan.
- [ ] Seed data demo untuk presentasi ujian.

**Total estimasi: ~5–7 hari kerja.**

---

## 7. Catatan Implementasi

- **Snapshot harga:** selalu salin `name` & `price` ke `order_items` saat checkout, jangan hanya menyimpan `product_id`.
- **DB transaction:** bungkus penyimpanan order + items dengan `DB::transaction()` agar konsisten.
- **Nomor order:** format `INV-{YYYYMMDD}-{urutan harian}` untuk keterbacaan.
- **Format Rupiah:** buat helper untuk format mata uang (mis. `Rp 15.000`).
- **Badge metode bayar:** gunakan kelas daisyUI — contoh `badge-success` (Tunai), `badge-info` (QRIS), `badge-warning` (Transfer).
- **Soft delete menu:** model `Product` memakai trait `SoftDeletes`. Query menu di POS & master data otomatis mengecualikan yang sudah dihapus; gunakan `withTrashed()` / `restore()` bila perlu menampilkan atau memulihkan.
- **Keamanan minimal:** seluruh route aplikasi dilindungi middleware `auth`; validasi semua input.
