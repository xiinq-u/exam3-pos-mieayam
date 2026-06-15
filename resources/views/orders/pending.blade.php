@extends('layouts.app')

@section('title', 'Riwayat Pesanan - Mie Ayam Puput')

@section('content')
{{-- Halaman antrean: menampilkan pesanan yang sudah dibuat tetapi belum selesai dibayar. --}}
<div class="max-w-[1500px] mx-auto">
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-4xl font-extrabold text-stone-900 tracking-tighter">Pesanan</h1>
            <p class="text-stone-400 text-sm font-medium mt-1">Pesanan yang belum diselesaikan</p>
        </div>
        <a href="{{ route('cashier.index') }}" class="w-fit px-5 py-2.5 bg-stone-900 text-white rounded-xl font-bold text-center hover:bg-red-600 transition-all">
            Kembali ke Kasir
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-6 text-sm">{{ session('success') }}</div>
    @endif

    @if($orders->isEmpty())
    <div class="max-w-xl mx-auto bg-[#FFFDF9] p-16 rounded-xl border border-stone-200 text-center shadow-sm">
        <div class="text-4xl mb-4 opacity-50">📜</div>
        <p class="text-stone-400 font-bold text-xs uppercase tracking-[0.2em]">Tidak ada antrean pesanan</p>
        <a href="{{ route('cashier.index') }}" class="mt-8 inline-block px-8 py-3 bg-red-600 text-white rounded-lg font-black uppercase text-[10px] tracking-widest hover:bg-red-700 transition-all">Kembali ke Kasir</a>
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 items-start">
        @foreach($orders as $order)
            <div class="bg-[#FFFDF9] p-6 shadow-sm border-t-4 border-red-600 relative min-h-full">
                <div class="text-center mb-6">
                    <h2 class="text-xl font-black uppercase tracking-tighter text-stone-900">Mie Ayam Puput</h2>
                    <p class="text-[9px] font-bold text-stone-400 uppercase mt-1">{{ $order->created_at->format('d M Y • H:i') }}</p>
                </div>

                <div class="border-b-2 border-dashed border-stone-200 mb-4 pb-4 text-stone-700">
                    <div class="flex justify-between text-xs font-bold mb-1">
                        <span>No. Order</span>
                        <span class="text-stone-900">{{ $order->order_number }}</span>
                    </div>
                    <div class="flex justify-between text-xs font-bold mb-1">
                        <span>Pembeli</span>
                        <span class="text-stone-900">{{ $order->customer_name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between text-xs font-bold">
                        <span>Tipe</span>
                        <span class="uppercase text-stone-900">{{ str_replace('_', ' ', $order->order_type) }}</span>
                    </div>
                </div>

                <div class="space-y-3 mb-6 text-stone-700">
                    @foreach($order->items as $item)
                        <div class="flex justify-between text-xs">
                            <span class="font-bold">{{ $item->quantity }} x {{ $item->product_name }}</span>
                            <span class="font-mono text-red-600 font-bold">Rp{{ number_format($item->price * $item->quantity, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="border-t-2 border-dashed border-stone-200 pt-4 mb-6">
                    <div class="flex justify-between items-center text-stone-900">
                        <span class="font-black text-sm uppercase">Total</span>
                        <span class="text-xl font-black text-red-600">Rp{{ number_format($order->total, 0, ',', '.') }}</span>
                    </div>
                </div>

                <a href="{{ route('orders.show', $order) }}" class="block w-full text-center py-3 bg-red-600 text-white font-black uppercase text-[10px] tracking-widest hover:bg-red-700 transition-all">
                    Selesaikan Pembayaran
                </a>
                
                <div class="absolute -bottom-2 left-0 w-full h-4" style="background: radial-gradient(circle, transparent 50%, #FFFDF9 50%) 0 0/15px 15px repeat-x;"></div>
            </div>
        @endforeach
    </div>

        <div class="mt-8 flex justify-center">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
