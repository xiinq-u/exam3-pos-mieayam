@extends('layouts.app')

@section('title', 'Tambah Menu Baru - Mie Ayam Puput')

@section('content')
<div class="max-w-3xl mx-auto p-6">
    
    <div class="mb-8">
        <h1 class="text-4xl font-extrabold text-stone-900 tracking-tighter">Tambah Menu</h1>
        <p class="text-stone-500 mt-2">Input data produk baru untuk sistem Mie Ayam Puput.</p>
    </div>

    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-3xl p-8 border border-stone-200 shadow-sm">
        @csrf
        
        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-xl border border-red-100 text-sm font-bold">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
            
            <div class="md:col-span-7 space-y-6">
                <div>
                    <label class="block text-[10px] font-bold text-stone-400 uppercase tracking-widest mb-2">Nama Produk</label>
                    <input type="text" name="name" class="w-full p-4 rounded-xl border-2 border-stone-100 bg-stone-50 focus:bg-white focus:border-red-500 outline-none transition-all font-bold" value="{{ old('name') }}" placeholder="Contoh: Mie Ayam Bakso" required>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-stone-400 uppercase tracking-widest mb-2">Kategori</label>
                        <select name="category_id" class="w-full p-4 rounded-xl border-2 border-stone-100 bg-stone-50 focus:border-red-500 outline-none font-bold">
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}" @selected(old('category_id') == $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-stone-400 uppercase tracking-widest mb-2">Harga (Rp)</label>
                        <input type="number" name="price" class="w-full p-4 rounded-xl border-2 border-stone-100 bg-stone-50 focus:border-red-500 outline-none font-bold" value="{{ old('price', 0) }}" required>
                    </div>
                </div>
            </div>

            <div class="md:col-span-5 space-y-6">
                <div>
                    <label class="block text-[10px] font-bold text-stone-400 uppercase tracking-widest mb-2">Foto Produk</label>
                    <div class="aspect-square bg-stone-100 rounded-2xl flex flex-col items-center justify-center relative overflow-hidden border-2 border-dashed border-stone-200 hover:border-red-400 transition-colors">
                        <img id="image-preview" src="" class="absolute inset-0 w-full h-full object-cover hidden">
                        <span id="preview-placeholder" class="text-stone-400 text-xs text-center p-4">Klik untuk upload foto</span>
                        <input type="file" name="image" id="image-input" class="absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 bg-red-50 rounded-xl border border-red-100">
                    <span class="text-xs font-bold text-red-800">Tersedia untuk dijual</span>
                    <input type="checkbox" name="is_available" class="toggle toggle-red" @checked(old('is_available', true))>
                </div>
            </div>
        </div>

        <div class="mt-8 pt-6 border-t border-stone-100 flex gap-4">
            <a href="{{ route('products.index') }}" class="px-8 py-3 rounded-xl font-bold text-stone-500 hover:text-stone-900 transition-colors">Batal</a>
            <button type="submit" class="flex-1 bg-red-600 text-white py-3 rounded-xl font-bold hover:bg-red-700 transition-all shadow-lg shadow-red-200">Simpan Produk</button>
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
        placeholder.classList.add('hidden');
    });
</script>
@endpush