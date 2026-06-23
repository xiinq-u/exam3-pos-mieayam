@extends('layouts.app')

@section('title', 'Detail Pesanan - '.$order->order_number)

@push('styles')
    <style>
        @media print {
            @page {
                margin: 0;
                size: 58mm auto;
            }

            html,
            body {
                width: 58mm !important;
                min-width: 58mm !important;
                max-width: 58mm !important;
                min-height: 0 !important;
                height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: visible !important;
                background: white !important;
                color: #000 !important;
                font-family: "Courier New", Courier, monospace !important;
                font-size: 11px !important;
                line-height: 1.45 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            header,
            footer,
            nav,
            aside,
            .btn,
            .navbar,
            .drawer-side,
            .drawer-content > :not(main),
            main > div > aside,
            .no-print {
                display: none !important;
            }

            .drawer,
            .drawer-content,
            main,
            main > div,
            main > div > section,
                .max-w-3xl,
                .mx-auto {
                display: block !important;
                width: 58mm !important;
                max-width: 58mm !important;
                min-height: 0 !important;
                height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: visible !important;
                background: white !important;
            }

            main > div {
                grid-template-columns: 1fr !important;
                gap: 0 !important;
            }

            .receipt-print-area {
                display: block !important;
                width: 44mm !important;
                max-width: 44mm !important;
                margin: 0 !important;
                padding: 3mm 1.5mm 5mm !important;
                background: white !important;
                color: #000 !important;
                border: 0 !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                page-break-after: avoid !important;
                break-after: avoid-page !important;
            }

            .receipt-print-area * {
                color: #000 !important;
                background: transparent !important;
                box-shadow: none !important;
                text-shadow: none !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .receipt-title {
                font-size: 15px !important;
                line-height: 1.35 !important;
                font-weight: 800 !important;
                text-align: center !important;
                letter-spacing: 0 !important;
            }

            .receipt-subtitle {
                font-size: 9.5px !important;
                line-height: 1.45 !important;
                text-align: center !important;
            }

            .row {
                display: grid !important;
                grid-template-columns: 15mm minmax(0, 1fr) !important;
                column-gap: 1.5mm !important;
                width: 100% !important;
                margin: 4px 0 !important;
                font-size: 10px !important;
                line-height: 1.45 !important;
            }

            .row > span {
                display: inline-block !important;
                max-width: none !important;
                overflow-wrap: anywhere !important;
            }

            .row > span:first-child {
                font-weight: 700 !important;
            }

            .row > span:last-child {
                text-align: left !important;
                font-weight: 800 !important;
                max-width: none !important;
            }

            .item-name {
                max-width: 44mm !important;
                overflow-wrap: anywhere !important;
                font-size: 12.5px !important;
                font-weight: 800 !important;
                line-height: 1.35 !important;
                margin-bottom: 2px !important;
            }

            .item-row {
                display: block !important;
                font-size: 11.5px !important;
                margin: 8px 0 !important;
            }

            .item-row > div,
            .item-row > span {
                display: block !important;
                max-width: 44mm !important;
                text-align: left !important;
            }

            .item-row > span {
                font-size: 12px !important;
                margin-top: 3px !important;
                font-weight: 900 !important;
            }

            .row p {
                margin: 0 !important;
                line-height: 1.35 !important;
                font-weight: 700 !important;
            }

            hr,
            .receipt-divider {
                display: block !important;
                height: 1px !important;
                border: 0 !important;
                border-top: 1px dashed #000 !important;
                margin: 9px 0 !important;
                visibility: visible !important;
            }

            .total-row {
                display: grid !important;
                grid-template-columns: 14mm minmax(0, 1fr) !important;
                font-size: 11px !important;
                font-weight: 900 !important;
                border-top: 1px solid #000 !important;
                padding-top: 7px !important;
                margin-top: 7px !important;
            }

            .total-row > span:last-child {
                text-align: left !important;
            }
        }
    </style>
@endpush

@section('content')
{{-- Detail pesanan: menampilkan isi pembelian, total, pembayaran, dan kembalian. --}}
<div class="max-w-3xl mx-auto">
    <div class="mb-8 no-print">
        <div class="mb-4 flex flex-wrap gap-3">
            <a href="{{ $order->status === 'completed' ? route('reports.sales') : route('orders.pending') }}" class="text-red-600 font-bold hover:text-red-700 text-sm flex items-center gap-1">
                &larr; {{ $order->status === 'completed' ? 'Kembali ke Riwayat' : 'Kembali ke Antrean' }}
            </a>
            <a href="{{ route('cashier.index') }}" class="text-stone-500 font-bold hover:text-stone-900 text-sm flex items-center gap-1">
                Kembali ke Kasir
            </a>
        </div>
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

    @if(session('print_receipt'))
        <div id="print-choice" class="no-print mb-6 rounded-3xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-black text-emerald-800 uppercase tracking-widest">Pembayaran Berhasil</p>
                    <p class="text-xs text-emerald-700 mt-1">Mau cetak struk sekarang?</p>
                </div>
                <div class="grid grid-cols-2 gap-2 sm:flex">
                    <button type="button" onclick="window.print()" class="rounded-xl bg-emerald-600 px-5 py-3 text-xs font-black uppercase tracking-widest text-white hover:bg-emerald-700 transition-all">
                        Cetak
                    </button>
                    <a href="{{ route('cashier.index', ['order_completed' => 1]) }}" class="rounded-xl bg-white px-5 py-3 text-center text-xs font-black uppercase tracking-widest text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition-all">
                        Tidak Dulu
                    </a>
                </div>
            </div>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success mb-6 text-sm no-print">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-error mb-6 text-sm no-print">{{ session('error') }}</div>
    @endif

    <div class="receipt-print-area relative overflow-hidden bg-white rounded-xl border border-stone-200 p-5 shadow-xl">
        <div class="text-center">
            <h2 class="receipt-title text-xl font-black uppercase tracking-tight text-stone-950">Mie Ayam Puput</h2>
            <p class="receipt-subtitle mt-1 text-[10px] font-bold uppercase tracking-widest text-stone-500">Struk Pembayaran</p>
            <p class="receipt-subtitle mt-2 text-[10px] text-stone-500">Jl. Hj Manshur Rawa Mulya</p>
        </div>

        <hr class="receipt-divider">

        <div class="space-y-1.5 text-xs">
            <div class="row flex justify-between gap-4">
                <span class="text-stone-500">No. Order:</span>
                <span class="font-bold text-right text-stone-900">{{ $order->order_number }}</span>
            </div>
            <div class="row flex justify-between gap-4">
                <span class="text-stone-500">Waktu:</span>
                <span class="font-bold text-right text-stone-900">{{ $order->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="row flex justify-between gap-4">
                <span class="text-stone-500">Pembeli:</span>
                <span class="font-bold text-right text-stone-900">{{ $order->customer_name ?? '-' }}</span>
            </div>
            <div class="row flex justify-between gap-4">
                <span class="text-stone-500">Kasir:</span>
                <span class="font-bold text-right text-stone-900">{{ $order->user->name ?? 'Guest' }}</span>
            </div>
            <div class="row flex justify-between gap-4">
                <span class="text-stone-500">Tipe:</span>
                <span class="font-bold text-right uppercase text-stone-900">{{ str_replace('_', ' ', $order->order_type) }}</span>
            </div>
            <div class="row flex justify-between gap-4">
                <span class="text-stone-500">Metode:</span>
                <span class="font-bold text-right uppercase text-stone-900">{{ $order->payment_method }}</span>
            </div>
        </div>

        <hr class="receipt-divider">

        <div class="space-y-3">
            <div class="row flex justify-between text-[10px] font-black uppercase tracking-widest text-stone-500">
                <span>Menu</span>
                <span>Subtotal</span>
            </div>

            @foreach($order->items as $item)
                <div class="row item-row flex justify-between gap-4 text-xs">
                    <div class="min-w-0">
                        <p class="item-name font-bold text-stone-900">{{ $item->product_name }}</p>
                        <p class="text-xs text-stone-500">
                            {{ $item->quantity }} x Rp{{ number_format($item->price, 0, ',', '.') }}
                        </p>
                    </div>
                    <span class="shrink-0 font-black text-stone-900">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </div>
            @endforeach
        </div>

        <hr class="receipt-divider">

        <div class="space-y-2">
            <div class="row total-row flex justify-between items-center">
                <span class="font-black uppercase text-stone-500 text-xs">Total:</span>
                <span class="text-xl font-black text-stone-950">Rp{{ number_format($order->total, 0, ',', '.') }}</span>
            </div>

            @if($order->status === 'completed')
                <div class="row flex justify-between items-center text-xs">
                    <span class="font-bold text-stone-500">Bayar:</span>
                    <span class="font-black text-stone-900">Rp{{ number_format($order->paid_amount, 0, ',', '.') }}</span>
                </div>
                <div class="row flex justify-between items-center text-xs">
                    <span class="font-bold text-stone-500">Kembali:</span>
                    <span class="font-black text-stone-900">Rp{{ number_format($order->change_amount, 0, ',', '.') }}</span>
                </div>
            @endif
        </div>

        @if($order->status === 'pending')
            <form action="{{ route('orders.complete', $order) }}" method="POST" class="no-print space-y-6 border-t border-stone-200 p-5">
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
        @elseif(! session('print_receipt'))
            <button type="button" onclick="window.print()" class="no-print w-full py-4 bg-stone-900 text-white font-black uppercase text-sm tracking-[0.2em] rounded-2xl hover:bg-red-600 active:scale-[0.98] transition-all">
                Cetak Struk
            </button>
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
