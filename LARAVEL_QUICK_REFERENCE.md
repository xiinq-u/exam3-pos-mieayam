# Laravel Quick Reference — POS Mie Ayam

Acuan singkat cara kerja Laravel untuk dipakai selama development project POS ini. Semua contoh memakai entitas project: `Category`, `Product`, `Order`, `OrderItem`. Untuk Laravel 12+.

> Tip: jalankan `php artisan list` untuk melihat semua perintah, dan `php artisan help <perintah>` untuk detail.

---

## 1. Artisan & Alur Dasar

Laravel memproses request dengan alur sederhana:

```
Browser → routes/web.php → Controller → Model (Eloquent) → View (Blade) → Browser
```

Perintah Artisan yang paling sering dipakai:

```bash
php artisan serve              # jalankan server dev di http://127.0.0.1:8000
php artisan make:model Product -mcr   # model + migration + controller + resource
php artisan make:controller PosController
php artisan make:migration create_products_table
php artisan make:request StoreProductRequest   # form request validasi
php artisan make:seeder ProductSeeder

php artisan migrate            # jalankan migration
php artisan migrate:fresh --seed   # reset DB lalu seed (hati-hati: hapus data)
php artisan db:seed            # jalankan seeder

php artisan route:list         # lihat semua route
php artisan storage:link       # symlink storage agar gambar bisa diakses publik
```

Flag `-mcr` saat membuat model = sekaligus **m**igration, **c**ontroller, dan **r**esource controller. Sangat menghemat waktu.

---

## 2. Migration

Migration mendefinisikan struktur tabel. File ada di `database/migrations/`.

```php
// database/migrations/xxxx_create_products_table.php
public function up(): void
{
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->foreignId('category_id')->constrained()->cascadeOnDelete();
        $table->string('name');
        $table->decimal('price', 10, 2);
        $table->string('image')->nullable();
        $table->boolean('is_available')->default(true);
        $table->softDeletes();   // menambah kolom deleted_at (soft delete)
        $table->timestamps();    // created_at & updated_at
    });
}
```

Contoh foreign key yang **tidak merusak histori** (dipakai di `order_items`):

```php
$table->foreignId('product_id')->nullable()
      ->constrained()->nullOnDelete();   // jika produk dihapus, set null
```

Tipe kolom umum: `string`, `text`, `integer`, `boolean`, `decimal('price', 10, 2)`, `enum('payment_method', ['cash','qris','transfer'])`, `timestamp`.

---

## 3. Model & Eloquent Relationship

Model mewakili satu tabel. File ada di `app/Models/`.

```php
// app/Models/Product.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;   // mengaktifkan soft delete

    protected $fillable = ['category_id', 'name', 'price', 'image', 'is_available'];

    protected $casts = [
        'price' => 'decimal:2',
        'is_available' => 'boolean',
    ];

    // Satu produk milik satu kategori
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
```

```php
// app/Models/Category.php — satu kategori punya banyak produk
public function products()
{
    return $this->hasMany(Product::class);
}
```

```php
// app/Models/Order.php
public function items()
{
    return $this->hasMany(OrderItem::class);
}

public function user()
{
    return $this->belongsTo(User::class);
}
```

Mengakses relasi:

```php
$product->category->name;        // nama kategori dari sebuah produk
$category->products;             // koleksi produk dalam kategori
$order->items;                   // item-item dalam order
```

> **Eager loading** untuk hindari query berlebih (N+1):
> `Product::with('category')->get();`

---

## 4. Routing

File: `routes/web.php`.

```php
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PosController;

// Route biasa
Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');

// Route dengan parameter (model binding otomatis)
Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

// Resource route → otomatis bikin index/create/store/show/edit/update/destroy
Route::resource('products', ProductController::class);
Route::resource('categories', CategoryController::class);

// Lindungi sekelompok route dengan login
Route::middleware('auth')->group(function () {
    Route::resource('products', ProductController::class);
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    // ...route lain yang butuh login
});
```

Lihat semua route + nama-nya: `php artisan route:list`.

---

## 5. Controller

Controller berisi logika menangani request. File: `app/Http/Controllers/`.

```php
// app/Http/Controllers/PosController.php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function index()
    {
        $categories = Category::with('products')->get();
        $products = Product::where('is_available', true)->get();

        // kirim data ke view
        return view('pos.index', compact('categories', 'products'));
    }
}
```

**Resource controller** otomatis punya 7 method standar:

```php
class ProductController extends Controller
{
    public function index()   { /* daftar */ }
    public function create()  { /* form tambah */ }
    public function store(Request $request)  { /* simpan */ }
    public function show(Product $product)   { /* detail */ }
    public function edit(Product $product)   { /* form edit */ }
    public function update(Request $request, Product $product) { /* update */ }
    public function destroy(Product $product) { /* hapus */ }
}
```

`Product $product` di parameter = **route model binding**: Laravel otomatis cari produk berdasarkan id di URL.

---

## 6. Validasi (Form Request)

Pisahkan aturan validasi ke class sendiri agar controller rapi.

```bash
php artisan make:request StoreProductRequest
```

```php
// app/Http/Requests/StoreProductRequest.php
public function authorize(): bool { return true; }

public function rules(): array
{
    return [
        'category_id' => ['required', 'exists:categories,id'],
        'name'        => ['required', 'string', 'max:255'],
        'price'       => ['required', 'numeric', 'min:0'],
        'image'       => ['nullable', 'image', 'max:2048'], // maks 2MB
        'is_available'=> ['boolean'],
    ];
}
```

Pakai di controller — data yang lolos validasi langsung tersedia:

```php
public function store(StoreProductRequest $request)
{
    Product::create($request->validated());
    return redirect()->route('products.index')->with('success', 'Menu ditambahkan.');
}
```

Validasi inline (tanpa Form Request) juga bisa:

```php
$data = $request->validate([
    'paid_amount' => ['required', 'numeric', 'min:0'],
    'payment_method' => ['required', 'in:cash,qris,transfer'],
]);
```

---

## 7. Blade Template

File view: `resources/views/`. Ekstensi `.blade.php`.

**Layout induk** (`layouts/app.blade.php`):

```blade
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="utf-8">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="navbar bg-base-100">POS Mie Ayam</div>
    <main class="p-4">
        @yield('content')
    </main>
</body>
</html>
```

**View anak** memakai layout:

```blade
@extends('layouts.app')

@section('content')
    <h1 class="text-2xl font-bold">Daftar Menu</h1>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-3 gap-4">
        @foreach ($products as $product)
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h2 class="card-title">{{ $product->name }}</h2>
                    <p>Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                </div>
            </div>
        @endforeach
    </div>
@endsection
```

Sintaks Blade penting:

- `{{ $var }}` — tampilkan data (otomatis di-escape, aman dari XSS).
- `@if / @elseif / @else / @endif`, `@foreach / @endforeach`, `@forelse / @empty / @endforelse`.
- `@csrf` — wajib di setiap form POST/PUT/DELETE.
- `@method('PUT')` — untuk form update/delete.

**Form contoh:**

```blade
<form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="text" name="name" class="input input-bordered" value="{{ old('name') }}">
    @error('name') <span class="text-error">{{ $message }}</span> @enderror
    <button class="btn btn-primary">Simpan</button>
</form>
```

---

## 8. Eloquent CRUD & Query

```php
// CREATE
Product::create(['name' => 'Mie Ayam', 'price' => 15000, 'category_id' => 1]);

// READ
Product::all();                              // semua
Product::find(1);                            // berdasarkan id
Product::where('is_available', true)->get(); // dengan kondisi
Product::where('name', 'like', '%ayam%')->first();

// UPDATE
$product = Product::find(1);
$product->update(['price' => 16000]);

// DELETE
$product->delete();   // dengan SoftDeletes → set deleted_at (tidak benar-benar hilang)
```

**Query untuk laporan** (agregasi):

```php
// Total omzet hari ini
$omzet = Order::whereDate('created_at', today())->sum('total');

// Jumlah transaksi hari ini
$jumlah = Order::whereDate('created_at', today())->count();

// Rincian omzet per metode bayar
$perMetode = Order::whereDate('created_at', today())
    ->selectRaw('payment_method, SUM(total) as total')
    ->groupBy('payment_method')
    ->get();

// Menu terlaris (total qty terjual)
$terlaris = OrderItem::selectRaw('product_name, SUM(quantity) as qty')
    ->groupBy('product_name')
    ->orderByDesc('qty')
    ->limit(5)
    ->get();
```

---

## 9. DB Transaction (untuk Checkout)

Saat menyimpan order beserta item-itemnya, bungkus dengan transaction agar **semua tersimpan atau semua batal** — tidak ada data setengah jadi.

```php
use Illuminate\Support\Facades\DB;

public function checkout(Request $request)
{
    $data = $request->validate([
        'items' => ['required', 'array', 'min:1'],
        'items.*.product_id' => ['required', 'exists:products,id'],
        'items.*.quantity'   => ['required', 'integer', 'min:1'],
        'paid_amount'        => ['required', 'numeric', 'min:0'],
        'payment_method'     => ['required', 'in:cash,qris,transfer'],
    ]);

    $order = DB::transaction(function () use ($data) {
        $total = 0;
        $order = Order::create([
            'order_number'   => 'INV-' . now()->format('Ymd') . '-' . str_pad(
                                  Order::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT),
            'user_id'        => auth()->id(),
            'total'          => 0, // diisi setelah hitung
            'paid_amount'    => $data['paid_amount'],
            'change_amount'  => 0,
            'payment_method' => $data['payment_method'],
        ]);

        foreach ($data['items'] as $item) {
            $product = Product::find($item['product_id']);
            $subtotal = $product->price * $item['quantity'];
            $total += $subtotal;

            $order->items()->create([
                'product_id'   => $product->id,
                'product_name' => $product->name,   // snapshot
                'price'        => $product->price,  // snapshot
                'quantity'     => $item['quantity'],
                'subtotal'     => $subtotal,
            ]);
        }

        $order->update([
            'total'         => $total,
            'change_amount' => $data['paid_amount'] - $total,
        ]);

        return $order;
    });

    return redirect()->route('orders.receipt', $order);
}
```

---

## 10. Upload File (Gambar Menu)

```php
// di controller store/update
if ($request->hasFile('image')) {
    $path = $request->file('image')->store('products', 'public');
    $data['image'] = $path;   // simpan path ke kolom image
}
```

Jalankan sekali: `php artisan storage:link`. Lalu tampilkan di Blade:

```blade
<img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
```

---

## 11. Auth & Middleware

```php
// Proteksi route — hanya bisa diakses setelah login
Route::middleware('auth')->group(function () {
    // route di sini butuh login
});
```

```php
auth()->check();      // true jika sudah login
auth()->user();       // object user yang login
auth()->id();         // id user yang login (dipakai di order: cashier)
```

Di Blade:

```blade
@auth
    Halo, {{ auth()->user()->name }}
@endauth
```

Login sederhana bisa langsung pakai **Laravel Breeze** (`composer require laravel/breeze --dev` lalu `php artisan breeze:install blade`).

---

## 12. Vite + Tailwind 4 + daisyUI 5

`resources/css/app.css`:

```css
@import "tailwindcss";
@plugin "daisyui";
```

Build aset:

```bash
npm install
npm run dev      # mode development (watch)
npm run build    # build untuk produksi
```

Panggil aset di layout Blade: `@vite(['resources/css/app.css', 'resources/js/app.js'])`.

**Komponen daisyUI yang berguna untuk POS:**

```html
<!-- Tombol -->
<button class="btn btn-primary">Bayar</button>

<!-- Badge metode bayar -->
<span class="badge badge-success">Tunai</span>
<span class="badge badge-info">QRIS</span>
<span class="badge badge-warning">Transfer</span>

<!-- Card menu -->
<div class="card bg-base-100 shadow-md">
  <div class="card-body">...</div>
</div>

<!-- Stat untuk laporan -->
<div class="stats shadow">
  <div class="stat">
    <div class="stat-title">Omzet Hari Ini</div>
    <div class="stat-value">Rp 1.250.000</div>
  </div>
</div>

<!-- Tabel histori -->
<table class="table">
  <thead><tr><th>No Order</th><th>Total</th><th>Metode</th></tr></thead>
  <tbody>...</tbody>
</table>
```

---

## 13. JavaScript Vanilla untuk POS (Keranjang)

Pola sederhana: simpan keranjang sebagai array di JS, render ulang setiap ada perubahan. Letakkan di `resources/js/pos.js`.

```js
let cart = [];   // [{ id, name, price, qty }]

function addToCart(id, name, price) {
    const item = cart.find(i => i.id === id);
    if (item) {
        item.qty++;
    } else {
        cart.push({ id, name, price, qty: 1 });
    }
    renderCart();
}

function changeQty(id, delta) {
    const item = cart.find(i => i.id === id);
    if (!item) return;
    item.qty += delta;
    if (item.qty <= 0) cart = cart.filter(i => i.id !== id);
    renderCart();
}

function renderCart() {
    const container = document.getElementById('cart-items');
    container.innerHTML = '';
    let total = 0;

    cart.forEach(item => {
        const subtotal = item.price * item.qty;
        total += subtotal;
        container.innerHTML += `
            <div class="flex justify-between items-center">
                <span>${item.name} x ${item.qty}</span>
                <span>Rp ${subtotal.toLocaleString('id-ID')}</span>
                <button onclick="changeQty(${item.id}, -1)" class="btn btn-xs">-</button>
                <button onclick="changeQty(${item.id}, 1)" class="btn btn-xs">+</button>
            </div>`;
    });

    document.getElementById('cart-total').textContent =
        'Rp ' + total.toLocaleString('id-ID');
    return total;
}

// Hitung kembalian saat input uang dibayar berubah
function hitungKembalian() {
    const total = cart.reduce((s, i) => s + i.price * i.qty, 0);
    const bayar = Number(document.getElementById('paid').value) || 0;
    document.getElementById('change').textContent =
        'Rp ' + Math.max(0, bayar - total).toLocaleString('id-ID');
}
```

Saat checkout, kirim `cart` ke server. Cara sederhana: isi `<input type="hidden">` dengan `JSON.stringify(cart)` lalu submit form, atau gunakan `fetch()` dengan header CSRF.

```js
// contoh kirim via fetch
fetch('/pos/checkout', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
    },
    body: JSON.stringify({ items: cart, paid_amount: bayar, payment_method: metode }),
}).then(r => r.json()).then(res => window.location = res.redirect);
```

> Tambahkan `<meta name="csrf-token" content="{{ csrf_token() }}">` di `<head>` agar token tersedia untuk fetch.

---

## 14. Helper Format Rupiah

Cara cepat di Blade: `Rp {{ number_format($product->price, 0, ',', '.') }}`.

Agar bisa dipakai di mana saja, buat helper. Tambahkan fungsi di `app/helpers.php`:

```php
if (! function_exists('rupiah')) {
    function rupiah($angka) {
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }
}
```

Daftarkan di `composer.json` (bagian `autoload`) lalu jalankan `composer dump-autoload`:

```json
"autoload": {
    "files": ["app/helpers.php"]
}
```

Pakai: `{{ rupiah($order->total) }}` → `Rp 15.000`.

---

## 15. Soft Delete — Ringkasan Cepat

Sudah diaktifkan di model `Product` (lihat bagian 3). Perilakunya:

```php
$product->delete();          // set deleted_at, baris tetap di DB
Product::all();              // OTOMATIS sembunyikan yang sudah dihapus
Product::withTrashed()->get();   // termasuk yang dihapus
Product::onlyTrashed()->get();   // hanya yang dihapus
$product->restore();         // pulihkan kembali
$product->forceDelete();     // hapus permanen (hati-hati)
```

Manfaat untuk POS: menu yang dihapus tidak merusak histori transaksi, dan bisa dipulihkan jika terhapus tidak sengaja.

---

## Referensi Resmi

- Dokumentasi Laravel: https://laravel.com/docs
- Eloquent: https://laravel.com/docs/eloquent
- Blade: https://laravel.com/docs/blade
- daisyUI: https://daisyui.com/components
- Tailwind CSS: https://tailwindcss.com/docs
