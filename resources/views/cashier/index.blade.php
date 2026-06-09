@extends('layouts.app')

@section('title', 'Kasir - Mie Ayam Puput')

@section('content')
<div class="max-w-[1500px] mx-auto p-8 bg-[#FAFAFA] min-h-screen">
    
    <div class="flex justify-between items-end mb-10">
        <div>
            <h1 class="text-4xl font-extrabold text-stone-900 tracking-tighter">Kasir</h1>
            <p class="text-stone-400 text-sm font-medium mt-1">Mie Ayam Puput • {{ date('d F Y') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('products.index') }}" class="px-5 py-2.5 bg-white border border-stone-200 rounded-xl text-stone-600 font-bold hover:border-stone-400 transition-all">Kelola Menu</a>
            <a href="{{ route('reports.sales') }}" class="px-5 py-2.5 bg-red-600 text-white rounded-xl font-bold shadow-lg shadow-red-200 hover:bg-red-700 transition-all">Riwayat</a>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-[1fr_420px] gap-8">
        
        <section class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
    @foreach($products as $product)
    <div class="bg-white p-5 rounded-[2rem] border border-stone-100 shadow-sm hover:shadow-xl hover:shadow-stone-100 transition-all duration-500 group">
        <div class="w-full aspect-square bg-[#FFFDF9] rounded-[1.5rem] overflow-hidden mb-4 border border-stone-100 relative">
            @if($product->image)
                <img src="{{ asset('storage/' . $product->image) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
            @else
                <div class="w-full h-full flex items-center justify-center text-stone-300 text-[10px] uppercase font-black tracking-widest">No Image</div>
            @endif
        </div>
        
        <h2 class="font-bold text-stone-900 text-sm mb-1 truncate">{{ $product->name }}</h2>
        <p class="text-red-600 font-black text-lg mb-4">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
        
        <form action="{{ route('cashier.add') }}" method="POST" class="flex gap-2">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="number" name="quantity" value="1" min="1" class="w-16 bg-[#FFFDF9] border border-stone-100 rounded-xl text-center font-bold text-sm focus:ring-2 focus:ring-red-100 outline-none transition-all" />
            <button class="flex-1 bg-stone-900 text-white text-[10px] font-black tracking-widest rounded-xl hover:bg-red-600 transition-all uppercase">Tambah</button>
        </form>
    </div>
    @endforeach
</section>

        <aside>
            <div class="bg-white p-6 border-x border-t border-stone-200 shadow-xl relative font-sans text-stone-800">
    <div class="absolute -bottom-[8px] left-0 w-full h-[10px] z-10" 
         style="background: linear-gradient(135deg, white 50%, transparent 50%) 0 0/15px 15px, linear-gradient(225deg, white 50%, transparent 50%) 0 0/15px 15px; background-repeat: repeat-x;">
    </div>

    <div class="text-center mb-6 border-b-2 border-stone-900 pb-4">
        <h2 class="text-2xl font-black uppercase tracking-tight text-stone-900">Mie Ayam Puput</h2>
        <div class="flex justify-between mt-4 text-[10px] font-bold text-stone-500 uppercase">
            <span>{{ date('d/m/Y') }}</span>
            <span>{{ date('H:i') }}</span>
        </div>
    </div>

    @if($items->isEmpty())
        <div class="py-10 text-center text-stone-400 text-xs italic">- Keranjang Kosong -</div>
    @else
        <div class="space-y-4 mb-6">
            @foreach($items as $id => $item)
                <div class="flex justify-between items-center text-sm">
                    <div class="flex flex-col">
                        <span class="font-bold text-stone-900">{{ $item['product']->name }}</span>
                        <span class="text-[10px] text-stone-500">{{ $item['quantity'] }} x {{ number_format($item['product']->price, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center gap-3 font-bold">
                        <span>{{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                        <form action="{{ route('cashier.remove', $item['product']->id) }}" method="POST">
                            @csrf
                            <button class="text-stone-300 hover:text-red-500">✕</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="border-t-2 border-stone-900 pt-4">
            <div class="flex justify-between items-center mb-6">
                <span class="font-black text-sm uppercase">Total</span>
                <span class="text-2xl font-black text-stone-900">Rp{{ number_format($total, 0, ',', '.') }}</span>
            </div>

            <form action="{{ route('cashier.checkout') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <select name="order_type" class="w-full bg-stone-100 border-none p-3 text-xs font-bold uppercase rounded-lg">
                        <option value="dine_in">Dine In</option>
                        <option value="take_away">Take Away</option>
                    </select>
                </div>
                
                <button type="submit" class="w-full bg-stone-900 text-white font-black py-4 uppercase tracking-widest hover:bg-red-600 transition-all text-xs rounded-xl shadow-lg">
                    Buat Pesanan
                </button>
            </form>
        </div>
    @endif
</div>
        </aside>
    </div>
</div>
@endsection