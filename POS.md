# Rencana Pengembangan POS Mie Ayam

## 1. Ketentuan Utama Proyek

- **Frontend:** React JS
- **Backend:** Laravel REST API
- **Database:** MySQL
- **Autentikasi:** Laravel Sanctum
- **Target pengguna:** Satu usaha mie ayam/satu lokasi
- **Role pengguna:** Owner, Kasir, dan Dapur

## 2. Fitur yang Sudah Tersedia

- Login dan logout pada website Laravel Blade
- Dashboard penjualan sederhana
- Manajemen produk dan kategori
- Kasir, keranjang, dan checkout
- Pesanan makan di tempat dan bungkus
- Pembayaran tunai dan QRIS
- Perhitungan uang kembalian
- Antrean pesanan
- Laporan penjualan dan pendapatan
- Laravel Sanctum untuk autentikasi API
- Role `owner`, `cashier`, dan `kitchen`
- API bahan baku dan penyesuaian stok
- Peringatan stok minimum
- Relasi produk dengan bahan baku/resep
- Status pesanan `pending`, `processed`, `ready`, dan `completed`
- Riwayat perubahan status pesanan
- Pengurangan bahan berdasarkan pesanan

## 3. Fitur Wajib yang Masih Kurang

### 3.1 Frontend React JS

- [x] Instal React JS dan React DOM
- [x] Instal React Router
- [x] Konfigurasi Axios
- [x] Halaman login React
- [x] Dashboard owner
- [x] Halaman kasir
- [x] Keranjang dan checkout
- [x] Halaman antrean dapur
- [x] Manajemen produk dan kategori
- [x] Manajemen bahan baku
- [x] Manajemen pengguna
- [x] Halaman laporan
- [x] Proteksi halaman berdasarkan login dan role

> Frontend React tersedia di `/react`; beberapa alur Blade lama masih dipertahankan untuk kompatibilitas.

### 3.2 REST API Laravel

- [x] API produk
- [x] API kategori
- [x] API transaksi kasir
- [x] API checkout dan pembayaran
- [x] API resep produk dan bahan baku
- [x] API dashboard
- [x] API laporan
- [x] API pengeluaran
- [x] API pengguna dan role
- [x] API shift kasir
- [x] Format respons dan validasi API yang konsisten

### 3.3 Akun dan Hak Akses

- [x] Akun owner/admin tersedia
- [x] Akun kasir bawaan untuk pengujian
- [x] Akun dapur bawaan untuk pengujian
- [x] Owner dapat membuat akun pegawai
- [x] Owner dapat mengganti role pegawai
- [x] Owner dapat menonaktifkan akun
- [x] Fitur ganti dan reset password
- [x] Pembatasan setiap endpoint berdasarkan role

## 4. Penyempurnaan Stok Bahan Baku

- [x] Edit data bahan baku
- [x] Nonaktifkan atau soft delete bahan baku
- [x] Tampilan untuk mengatur resep menu
- [x] Riwayat stok masuk, keluar, rusak, dan koreksi
- [x] Filter riwayat berdasarkan bahan dan tanggal
- [x] Catatan bahan rusak atau kedaluwarsa
- [x] Harga beli bahan
- [x] Perhitungan nilai persediaan
- [x] Laporan stok
- [x] Pencegahan stok minus
- [x] Pencegahan pengurangan stok lebih dari satu kali
- [x] Database transaction saat mengubah stok

## 5. Modul Keuangan

- [x] Pencatatan pemasukan di luar transaksi
- [x] Pencatatan pengeluaran
- [x] Kategori pengeluaran
- [x] Harga pokok bahan/menu
- [x] Perhitungan laba kotor
- [x] Perhitungan laba bersih
- [x] Laporan laba-rugi
- [x] Filter harian, mingguan, bulanan, dan rentang tanggal
- [x] Pembatalan atau koreksi data dengan alasan
- [x] Export PDF
- [x] Export Excel

Data transaksi dan keuangan tidak boleh dihapus permanen. Gunakan pembatalan, arsip, koreksi, atau soft delete agar riwayat tetap dapat diperiksa.

## 6. Penyempurnaan Pesanan

- [x] Nomor antrean otomatis
- [x] Catatan pesanan, misalnya "tanpa sawi"
- [x] Edit pesanan sebelum diproses
- [x] Pembatalan pesanan dan alasannya
- [x] Refund
- [x] Cetak ulang struk
- [x] Pencegahan perubahan pesanan yang sudah selesai
- [x] Riwayat perubahan pesanan
- [x] Pisahkan status dapur dengan status pembayaran

Status yang disarankan:

```text
order_status: pending, processed, ready, completed, cancelled
payment_status: unpaid, paid, refunded
```

## 7. Shift Kasir

- [x] Buka shift
- [x] Saldo awal kas
- [x] Nama kasir yang bertugas
- [x] Pemasukan dan pengeluaran kas
- [x] Tutup shift
- [x] Uang yang seharusnya tersedia
- [x] Uang aktual
- [x] Perhitungan selisih kas
- [x] Riwayat shift

## 8. Keamanan dan Kesiapan Online

- [x] Konfigurasi CORS untuk frontend React
- [x] Pembatasan percobaan login
- [x] Masa berlaku token
- [x] Logout dari seluruh perangkat
- [x] Audit log aktivitas pengguna
- [ ] HTTPS di server produksi
- [x] Backup database MySQL
- [ ] Penyimpanan `.env` secara aman
- [x] Validasi upload gambar
- [x] Penanganan error API yang aman
- [x] Jangan masukkan `.env`, `.git`, `vendor`, dan `node_modules` ke ZIP distribusi

## 9. Masalah Teknis yang Harus Diperbaiki

### Pengurangan stok ganda

Permintaan perubahan status `completed` masih dapat dikirim kembali ketika pesanan sudah berstatus `completed`. Hal ini dapat membuat bahan baku berkurang dua kali.

Perbaikan:

- Tolak perubahan jika status baru sama dengan status sekarang.
- Simpan penanda bahwa bahan pesanan sudah dikurangi.
- Gunakan database transaction dan row locking.

### Perubahan stok tidak atomik

Jika salah satu bahan tidak mencukupi, sebagian bahan berpotensi sudah berkurang sebelum proses gagal.

Perbaikan:

- Periksa seluruh kebutuhan bahan terlebih dahulu.
- Jalankan perubahan status, pengurangan stok, dan pencatatan pergerakan dalam satu database transaction.

### Frontend dan backend belum sepenuhnya terpisah

Sebagian transaksi kasir masih memakai Laravel Blade, session, dan route web. Semua proses React nantinya harus menggunakan API yang dilindungi Laravel Sanctum.

## 10. Urutan Pengerjaan

1. Membuat frontend React dan routing.
2. Menyelesaikan autentikasi Sanctum dan role.
3. Membuat API produk, kategori, kasir, checkout, dan pembayaran.
4. Menghubungkan seluruh halaman React dengan Laravel API.
5. Menyelesaikan resep serta stok otomatis.
6. Memperbaiki keamanan transaksi stok.
7. Menambahkan akun owner, kasir, dan dapur.
8. Membuat modul pengeluaran dan laba-rugi.
9. Menambahkan pembatalan, refund, dan audit log.
10. Membuat shift kasir dan laporan lengkap.
11. Menambahkan pengujian frontend dan backend.
12. Deploy React, Laravel, dan MySQL secara online.

## 11. Struktur Sistem yang Disarankan

```text
React JS
   |
   | REST API / JSON
   v
Laravel 12 + Sanctum
   |
   v
MySQL
```

React bertugas menampilkan antarmuka. Laravel menangani autentikasi, validasi, hak akses, proses transaksi, stok, dan laporan. MySQL menyimpan seluruh data aplikasi.

## 12. Target Akhir

Proyek dinyatakan memenuhi ketentuan utama apabila:

- [x] Seluruh antarmuka utama sudah menggunakan React JS.
- [x] React berkomunikasi dengan Laravel melalui REST API.
- [x] Data disimpan di MySQL.
- [x] Login menggunakan Laravel Sanctum.
- [x] Hak akses owner, kasir, dan dapur berfungsi.
- [x] Transaksi, stok, dan keuangan tercatat dengan aman.
- [x] Pengujian utama berhasil dijalankan.
- [ ] Aplikasi dapat digunakan secara online.(ini nanti saja)
