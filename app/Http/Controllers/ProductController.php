<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Menampilkan semua menu yang sudah dibuat.
     */
    public function index()
    {
        $products = Product::with('category')->latest()->paginate(15);

        return view('products.index', compact('products'));
    }

    /**
     * Menampilkan form untuk menambah menu baru.
     */
    public function create()
    {
        $categories = Category::all();

        return view('products.create', compact('categories'));
    }

    /**
     * Menyimpan menu baru ke database.
     * Jika ada gambar, file gambar disimpan ke storage publik.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'is_available' => 'nullable|boolean',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $data['is_available'] = $request->has('is_available');

        Product::create($data);

        return redirect()->route('products.index')->with('success', 'Product created.');
    }

    /**
     * Menampilkan detail satu menu.
     */
    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    /**
     * Menampilkan form edit untuk mengubah data menu.
     */
    public function edit(Product $product)
    {
        $categories = Category::all();

        return view('products.edit', compact('product', 'categories'));
    }

    /**
     * Menyimpan perubahan menu yang diedit.
     * Jika gambar baru dipilih, gambar lama akan diganti di data produk.
     */
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'is_available' => 'nullable|boolean',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $data['is_available'] = $request->has('is_available');

        $product->update($data);

        return redirect()->route('products.index')->with('success', 'Product updated.');
    }

    /**
     * Menghapus menu dari daftar.
     * Karena model memakai soft delete, datanya disembunyikan dulu dan tidak langsung hilang permanen.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted.');
    }
}
