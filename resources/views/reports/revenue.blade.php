@extends('layouts.app')

@section('title', 'Daily Revenue')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between pb-4 border-b-2 border-stone-200 border-dashed">
        <div>
            <h1 class="text-2xl font-black text-stone-800 uppercase tracking-tighter">Catatan Harian</h1>
            <p class="text-stone-500 text-xs font-bold uppercase tracking-widest mt-1">Rekap Jualan Hari Ini</p>
        </div>
        <a href="{{ route('reports.sales') }}" class="px-4 py-2 bg-stone-200 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-stone-300">Kembali</a>
    </div>

    <div class="bg-[#F7F3E9] p-8 rounded-3xl border-2 border-stone-200 shadow-inner">
        <div class="grid grid-cols-2 gap-8">
            <div class="space-y-1">
                <p class="text-[10px] font-black uppercase text-stone-400">Total Masuk</p>
                <p class="text-3xl font-black text-stone-900 tracking-tight">Rp{{ number_format($daily->sum('revenue'), 0, ',', '.') }}</p>
            </div>
            <div class="space-y-1">
                <p class="text-[10px] font-black uppercase text-stone-400">Total Pesanan</p>
                <p class="text-3xl font-black text-stone-900 tracking-tight">{{ $daily->sum('orders_count') }}</p>
            </div>
        </div>
    </div>

    <div class="relative space-y-4">
        <div class="absolute left-6 top-2 bottom-2 w-0.5 bg-stone-200"></div>

        @forelse($daily as $row)
        <div class="relative flex items-center gap-6 group">
            <div class="w-4 h-4 rounded-full bg-stone-300 border-4 border-[#FFFDF9] z-10 group-hover:bg-red-500 transition-colors"></div>
            
            <div class="flex-1 bg-white p-5 rounded-2xl border border-stone-200 shadow-sm flex items-center justify-between hover:border-red-200 transition-all">
                <div class="flex flex-col">
                    <span class="text-[10px] font-black uppercase text-stone-400">{{ \Carbon\Carbon::parse($row->day)->translatedFormat('M, Y') }}</span>
                    <span class="text-sm font-black text-stone-800">{{ \Carbon\Carbon::parse($row->day)->translatedFormat('l, d') }}</span>
                </div>
                <div class="text-right">
                    <p class="font-black text-stone-900">Rp{{ number_format($row->revenue, 0, ',', '.') }}</p>
                    <p class="text-[9px] font-bold text-stone-400 uppercase tracking-widest">{{ $row->orders_count }} Pesanan</p>
                </div>
            </div>
        </div>
        @empty
        <div class="p-10 text-center text-stone-400 font-bold border-2 border-dashed border-stone-200 rounded-2xl">
            Belum ada catatan jualan.
        </div>
        @endforelse
    </div>
</div>
@endsection