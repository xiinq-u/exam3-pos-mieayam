@extends('layouts.dashboard')

@section('title', 'Daftar Menu - Mie Ayam Puput')

@section('content')
<div class="max-w-7xl mx-auto space-y-10">
    
    <div class="flex justify-between items-end">
        <div>
            <h2 class="text-[10px] font-black text-red-600 tracking-[0.3em] uppercase">Mie Ayam Puput</h2>
            <h1 class="text-5xl font-black text-stone-900 tracking-tighter mt-1">Daftar Menu</h1>
        </div>
        <a href="{{ route('products.create') }}" class="bg-stone-900 hover:bg-red-600 text-white px-8 py-4 rounded-2xl font-black uppercase text-xs tracking-widest transition-all shadow-xl shadow-stone-200">
            + Tambah Menu
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($products as $p)
        <div class="bg-white border border-stone-100 p-3 rounded-[2rem] shadow-sm hover:shadow-xl hover:shadow-stone-100 transition-all duration-500 group">
            <div class="relative w-full aspect-square bg-[#FFFDF9] rounded-[1.5rem] overflow-hidden mb-4 border border-stone-100">
                @if($p->image)
                    <img src="{{ asset('storage/' . $p->image) }}" alt="{{ $p->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                @else
                    <div class="flex items-center justify-center h-full text-stone-300 font-black text-[10px] uppercase tracking-widest">No Image</div>
                @endif
                
                <div class="absolute top-4 right-4">
                    <span class="px-3 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest {{ $p->is_available ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white' }}">
                        {{ $p->is_available ? 'Ready' : 'Habis' }}
                    </span>
                </div>
            </div>

            <div class="px-2 pb-2">
                <p class="text-[9px] font-black text-stone-400 uppercase tracking-[0.2em] mb-1">{{ $p->category->name ?? 'Menu' }}</p>
                <h3 class="text-md font-black text-stone-900 mb-4">{{ $p->name }}</h3>
                
                <div class="flex justify-between items-center bg-[#FFFDF9] p-3 rounded-xl border border-stone-100">
                    <span class="font-black text-stone-900 text-sm">Rp{{ number_format($p->price, 0, ',', '.') }}</span>
                    <div class="flex gap-1">
                        <a href="{{ route('products.edit', $p) }}" class="p-2 text-stone-400 hover:text-stone-900 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </a>
                        <form action="{{ route('products.destroy', $p) }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="p-2 text-stone-400 hover:text-red-600 transition-colors" onclick="return confirm('Hapus?')">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-12 flex justify-center">
        {{ $products->links() }}
    </div>
</div>
@endsection