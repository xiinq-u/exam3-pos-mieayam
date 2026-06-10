@extends('layouts.app')

@section('title', 'Edit Menu - Mie Ayam Puput')

@section('content')
<div class="max-w-3xl mx-auto p-6">
    
    <div class="flex items-end justify-between mb-8">
        <div>
            <h1 class="text-4xl font-extrabold text-stone-900 tracking-tighter">Edit Menu</h1>
            <p class="text-stone-500 mt-2">Update informasi detail produk ke database.</p>
        </div>
        <div class="bg-stone-100 px-4 py-2 rounded-full text-xs font-bold text-stone-600 uppercase tracking-widest border border-stone-200">
            ID: {{ $product->id }}
        </div>
    </div>

    <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-3xl p-8 border border-stone-200 shadow-sm">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
            
            <div class="md:col-span-7 space-y-6">
                <div>
                    <label class="block text-[10px] font-bold text-stone-400 uppercase tracking-widest mb-2">Nama Produk</label>
                    <input type="text" name="name" class="w-full p-4 rounded-xl border-2 border-stone-100 bg-stone-50 focus:bg-white focus:border-red-500 outline-none transition-all font-bold" value="{{ old('name', $product->name) }}" required>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-stone-400 uppercase tracking-widest mb-2">Kategori</label>
                        <select name="category_id" class="w-full p-4 rounded-xl border-2 border-stone-100 bg-stone-50 focus:border-red-500 outline-none font-bold">
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}" @selected(old('category_id', $product->category_id) == $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-stone-400 uppercase tracking-widest mb-2">Harga (Rp)</label>
                        <input type="number" name="price" class="w-full p-4 rounded-xl border-2 border-stone-100 bg-stone-50 focus:border-red-500 outline-none font-bold" value="{{ old('price', $product->price) }}" required>
                    </div>
                </div>
            </div>

            <div class="md:col-span-5 space-y-6">
                <div>
                    <label class="block text-[10px] font-bold text-stone-400 uppercase tracking-widest mb-2">Foto Produk</label>
                    <div class="aspect-square bg-stone-100 rounded-2xl flex items-center justify-center relative overflow-hidden border-2 border-dashed border-stone-200">
                        @if($product->image)
                            <img id="image-preview" src="{{ asset('storage/' . $product->image) }}" class="w-full h-full object-cover">
                        @else
                            <img id="image-preview" src="" class="absolute inset-0 w-full h-full object-cover hidden">
                            <span id="preview-placeholder" class="text-stone-400 text-xs">No Image</span>
                        @endif
                        <input type="file" name="image" id="image-input" class="absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 bg-red-50 rounded-xl border border-red-100">
                    <span class="text-xs font-bold text-red-800">Aktifkan Menu</span>
                    <input type="checkbox" name="is_available" class="toggle toggle-red" @checked(old('is_available', $product->is_available))>
                </div>
            </div>
        </div>

        <div class="mt-8 pt-6 border-t border-stone-100 flex gap-4">
            <a href="{{ route('products.index') }}" class="px-8 py-3 rounded-xl font-bold text-stone-500 hover:text-stone-900 transition-colors">Batal</a>
            <button type="submit" class="flex-1 bg-red-600 text-white py-3 rounded-xl font-bold hover:bg-red-700 transition-all shadow-lg shadow-red-200">Update Data</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('image-input').addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        const preview     = document.getElementById('image-preview');
        const placeholder = document.getElementById('preview-placeholder');
        preview.src = URL.createObjectURL(file);
        preview.classList.remove('hidden');
        if (placeholder) placeholder.classList.add('hidden');
    });
</script>
@endpush