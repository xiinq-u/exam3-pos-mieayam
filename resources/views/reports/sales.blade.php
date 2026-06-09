@extends('layouts.app')

@section('title', 'Sales History')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-stone-900 uppercase tracking-tighter">Buku Besar</h1>
            <p class="text-[10px] font-bold text-stone-400 uppercase tracking-widest mt-1">Laporan rekap penjualan</p>
        </div>
        <a href="{{ route('reports.revenue') }}" class="btn btn-sm bg-stone-900 text-white hover:bg-red-600 border-none rounded-lg uppercase tracking-widest text-[10px]">Laporan</a>
    </div>

    <div class="bg-white rounded-2xl border border-stone-200 shadow-sm overflow-x-auto">
        <table class="w-full min-w-[700px] text-left border-collapse">
            <thead>
                <tr class="bg-stone-50 border-b border-stone-100 text-[10px] font-black text-stone-400 uppercase tracking-widest">
                    <th class="p-5">Waktu</th>
                    <th class="p-5">Order ID</th>
                    <th class="p-5">Kasir</th>
                    <th class="p-5">Metode</th>
                    <th class="p-5 text-right">Total</th>
                    <th class="p-5 text-center">Status</th>
                    <th class="p-5 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse($orders as $order)
                <tr class="hover:bg-stone-50 transition-colors group relative text-sm">
                    <td class="p-5 font-mono text-xs text-stone-500 whitespace-nowrap">{{ $order->created_at->format('d/m/y H:i') }}</td>
                    <td class="p-5 font-bold text-stone-900">
                        <a href="{{ route('orders.show', $order) }}" class="relative z-10 text-red-600 hover:text-red-700 hover:underline">
                            {{ $order->order_number }}
                        </a>
                    </td>
                    <td class="p-5">{{ $order->user->name ?? 'Guest' }}</td>
                    <td class="p-5">
                        <span class="px-2 py-1 bg-stone-100 rounded text-[10px] font-bold uppercase whitespace-nowrap">{{ $order->payment_method }}</span>
                    </td>
                    <td class="p-5 text-right font-mono font-bold text-stone-900 whitespace-nowrap">
                        Rp{{ number_format($order->total, 0, ',', '.') }}
                    </td>
                    <td class="p-5 text-center">
                        <span class="px-2 py-1 rounded-full border border-emerald-100 bg-emerald-50 text-emerald-700 font-black text-[9px] uppercase whitespace-nowrap">
                            {{ $order->status }}
                        </span>
                    </td>
                    <td class="p-5 text-center">
                        <a href="{{ route('orders.show', $order) }}" class="relative z-10 btn btn-xs btn-ghost text-[10px] font-black uppercase tracking-widest">
                            Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-stone-400 py-12 text-sm font-bold">Belum ada transaksi tercatat.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex justify-center">
        {{ $orders->links() }}
    </div>
</div>
@endsection