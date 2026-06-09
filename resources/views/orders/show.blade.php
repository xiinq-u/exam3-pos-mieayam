@extends('layouts.app')

@section('title', 'Detail Pesanan - '.$order->order_number)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-8">
        <a href="{{ $order->status === 'completed' ? route('reports.sales') : route('orders.pending') }}" class="text-red-600 font-bold hover:text-red-700 text-sm flex items-center gap-1 mb-4">
            &larr; {{ $order->status === 'completed' ? 'Kembali ke Riwayat' : 'Kembali ke Antrean' }}
        </a>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-3xl font-extrabold text-stone-900">{{ $order->order_number }}</h1>
                <p class="text-stone-400 text-sm mt-1">{{ $order->created_at->format('d M Y H:i') }}</p>
            </div>
            <span class="w-fit px-3 py-1 rounded-full text-xs font-black uppercase tracking-widest {{ $order->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                {{ $order->status }}
            </span>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-error mb-6 text-sm">{{ session('error') }}</div>
    @endif

    <div class="relative bg-[#FFFDF9] rounded-[2rem] border border-stone-100 p-6 sm:p-8 shadow-xl">
        <div class="grid gap-4 sm:grid-cols-3 mb-8">
            <div class="rounded-2xl bg-stone-100 p-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-stone-400">Kasir</p>
                <p class="font-bold text-stone-900 mt-1">{{ $order->user->name ?? 'Guest' }}</p>
            </div>
            <div class="rounded-2xl bg-stone-100 p-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-stone-400">Tipe Pesanan</p>
                <p class="font-bold text-stone-900 mt-1">{{ ucfirst(str_replace('_', ' ', $order->order_type)) }}</p>
            </div>
            <div class="rounded-2xl bg-stone-100 p-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-stone-400">Metode</p>
                <p class="font-bold text-stone-900 mt-1">{{ strtoupper($order->payment_method) }}</p>
            </div>
        </div>

        <div class="bg-stone-900/5 rounded-2xl p-5 sm:p-6 mb-8 space-y-4">
            <div class="flex justify-between text-[10px] font-black uppercase tracking-widest text-stone-400">
                <span>Item Dibeli</span>
                <span>Subtotal</span>
            </div>

            @foreach($order->items as $item)
                <div class="flex justify-between gap-4 text-sm">
                    <div>
                        <p class="font-bold text-stone-900">{{ $item->product_name }}</p>
                        <p class="text-xs text-stone-500">
                            {{ $item->quantity }} x Rp{{ number_format($item->price, 0, ',', '.') }}
                        </p>
                    </div>
                    <span class="shrink-0 font-black text-stone-900">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </div>
            @endforeach

            <div class="border-t-2 border-stone-900/10 pt-4 space-y-3">
                <div class="flex justify-between items-center">
                    <span class="font-black uppercase text-stone-400 text-xs">Total</span>
                    <span class="text-2xl sm:text-3xl font-black text-stone-900 tracking-tighter">Rp{{ number_format($order->total, 0, ',', '.') }}</span>
                </div>

                @if($order->status === 'completed')
                    <div class="flex justify-between items-center text-sm">
                        <span class="font-bold text-stone-500">Uang Dibayar</span>
                        <span class="font-black text-stone-900">Rp{{ number_format($order->paid_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="font-bold text-stone-500">Kembalian</span>
                        <span class="font-black text-emerald-700">Rp{{ number_format($order->change_amount, 0, ',', '.') }}</span>
                    </div>
                @endif
            </div>
        </div>

        @if($order->status === 'pending')
            <form action="{{ route('orders.complete', $order) }}" method="POST" class="space-y-6">
                @csrf
                <div class="flex bg-stone-200 p-1 rounded-2xl">
                    <input type="radio" name="payment_method" value="cash" id="cash" class="peer/cash sr-only" checked>
                    <label for="cash" class="flex-1 text-center py-3 rounded-xl cursor-pointer font-black text-xs uppercase tracking-widest text-stone-500 peer-checked/cash:bg-white peer-checked/cash:text-red-600 peer-checked/cash:shadow-sm transition-all">Tunai</label>

                    <input type="radio" name="payment_method" value="qris" id="qris" class="peer/qris sr-only">
                    <label for="qris" class="flex-1 text-center py-3 rounded-xl cursor-pointer font-black text-xs uppercase tracking-widest text-stone-500 peer-checked/qris:bg-white peer-checked/qris:text-red-600 peer-checked/qris:shadow-sm transition-all">QRIS</label>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-stone-400 tracking-widest px-2">Uang yang Dibayarkan</label>
                    <input type="number" name="paid_amount" id="paid_amount"
                        class="w-full px-6 py-4 bg-white border-2 border-stone-200 rounded-2xl font-black text-xl text-stone-900 focus:border-red-500 outline-none transition-all"
                        value="{{ $order->total }}" min="{{ $order->total }}" required>
                </div>

                <div class="flex justify-between items-center px-6 py-4 bg-red-600 rounded-2xl text-white">
                    <span class="font-black uppercase text-[10px] tracking-widest opacity-70">Kembalian</span>
                    <span class="text-2xl font-black" id="change-amount">Rp0</span>
                </div>

                <button type="submit" class="w-full py-4 bg-stone-900 text-white font-black uppercase text-sm tracking-[0.2em] rounded-2xl hover:bg-red-600 active:scale-[0.98] transition-all">
                    Bayar Sekarang
                </button>
            </form>
        @endif
    </div>
</div>

@if($order->status === 'pending')
    <script>
        const paidAmountInput = document.querySelector('input[name="paid_amount"]');
        const changeAmountDisplay = document.getElementById('change-amount');
        const total = {{ $order->total }};

        function updateChange() {
            const paid = parseFloat(paidAmountInput.value) || 0;
            const change = Math.max(0, paid - total);
            changeAmountDisplay.textContent = 'Rp' + change.toLocaleString('id-ID', { maximumFractionDigits: 0 });
        }

        paidAmountInput.addEventListener('input', updateChange);
        updateChange();
    </script>
@endif
@endsection
