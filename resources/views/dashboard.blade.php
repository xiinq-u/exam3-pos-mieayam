@extends('layouts.dashboard')

@section('title', 'Dashboard - Mie Ayam Puput')

@section('content')
{{-- Dashboard admin: ringkasan penjualan, transaksi, antrean, dan grafik pendapatan. --}}
<div class="absolute inset-0 opacity-20 pointer-events-none bg-[radial-gradient(#e4d5b7_1.5px,transparent_1.5px)] [background-size:24px_24px]"></div>

<div class="space-y-8 relative z-10 select-none px-2 py-4">
    
    <div class="grid gap-6 md:grid-cols-3">
        <div class="bg-stone-800 p-2.5 pb-4 rounded-2xl shadow-xl border-2 border-stone-700 relative group transform hover:-translate-y-1 transition-all duration-300">
            <div class="absolute -top-3 left-1/4 w-1 h-3 bg-amber-800 rounded-full"></div>
            <div class="absolute -top-3 right-1/4 w-1 h-3 bg-amber-800 rounded-full"></div>
            <div class="bg-[#FFFDF9] rounded-xl p-4 border border-amber-100 flex flex-col justify-between h-full">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-mono font-bold text-stone-400 uppercase tracking-wider">01. Total Penjualan</h3>
                    <span class="p-1.5 bg-red-50 rounded-lg text-red-600"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg></span>
                </div>
                <div class="mt-4">
                    <p class="text-2xl font-black text-stone-800 tracking-tight">Rp {{ number_format($totalSales, 0, ',', '.') }}</p>
                    <span class="text-[10px] font-mono text-emerald-600 font-bold bg-emerald-50 px-1.5 py-0.5 rounded mt-1 inline-block">Hari Ini: Rp {{ number_format($todaySales, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <div class="bg-stone-800 p-2.5 pb-4 rounded-2xl shadow-xl border-2 border-stone-700 relative group transform hover:-translate-y-1 transition-all duration-300">
            <div class="absolute -top-3 left-1/4 w-1 h-3 bg-amber-800 rounded-full"></div>
            <div class="absolute -top-3 right-1/4 w-1 h-3 bg-amber-800 rounded-full"></div>
            <div class="bg-[#FFFDF9] rounded-xl p-4 border border-amber-100 flex flex-col justify-between h-full">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-mono font-bold text-stone-400 uppercase tracking-wider">02. Transaksi Selesai</h3>
                    <span class="p-1.5 bg-amber-50 rounded-lg text-amber-700"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg></span>
                </div>
                <div class="mt-4">
                    <p class="text-2xl font-black text-stone-800 tracking-tight">{{ number_format($completedOrdersCount, 0, ',', '.') }} <span class="text-xs font-normal text-stone-400">Nota</span></p>
                    <span class="text-[10px] font-mono text-stone-400 tracking-wide mt-1 inline-block">Hari Ini: {{ number_format($todayOrdersCount, 0, ',', '.') }} Nota</span>
                </div>
            </div>
        </div>

        <div class="bg-stone-800 p-2.5 pb-4 rounded-2xl shadow-xl border-2 border-stone-700 relative group transform hover:-translate-y-1 transition-all duration-300">
            <div class="absolute -top-3 left-1/4 w-1 h-3 bg-amber-800 rounded-full"></div>
            <div class="absolute -top-3 right-1/4 w-1 h-3 bg-amber-800 rounded-full"></div>
            <div class="bg-[#FFFDF9] rounded-xl p-4 border border-amber-100 flex flex-col justify-between h-full">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-mono font-bold text-stone-400 uppercase tracking-wider">03. Antrean Dapur</h3>
                    <span class="p-1.5 bg-red-100 rounded-lg text-red-600 animate-pulse"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></span>
                </div>
                <div class="mt-4">
                    <p class="text-2xl font-black text-red-600 tracking-tight">{{ number_format($pendingOrdersCount, 0, ',', '.') }} <span class="text-xs font-normal text-stone-400">Nota</span></p>
                    <span class="text-[10px] font-mono text-red-500 font-bold bg-red-50 px-1.5 py-0.5 rounded mt-1 inline-block">{{ $availableProductsCount }} Menu Tersedia</span>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-stone-100 shadow-sm relative">
        <div class="mb-6">
            <h3 class="text-sm font-bold text-stone-800 uppercase tracking-widest">Tren Penjualan Mingguan</h3>
        </div>
        <div class="h-[300px] w-full">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    <div class="w-full bg-[#FFFDF9] rounded-2xl p-6 md:p-8 shadow-xl border border-stone-200 relative overflow-hidden">
        <div class="absolute top-0 inset-x-0 h-1.5 bg-gradient-to-r from-red-500 via-red-600 to-amber-500"></div>
        <div class="absolute -bottom-8 -right-8 text-stone-100 font-black text-9xl tracking-tighter opacity-40 pointer-events-none transform -rotate-12">MIE</div>
        <div class="relative z-10">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-dashed border-stone-200 pb-4 mb-6">
                <div>
                    <span class="text-[9px] font-mono tracking-widest text-red-500 font-bold uppercase block mb-1">Sesi Kasir Aktif</span>
                    <h2 class="text-2xl font-black text-stone-800 tracking-tight">Selamat Datang Kembali, Petugas!</h2>
                </div>
                <div class="text-left sm:text-right font-mono text-[10px] text-stone-400 mt-2 sm:mt-0">
                    <div>NO. LAPORAN: #RP-{{ date('Ymd') }}</div>
                    <div>STATUS MIKRO: <span class="text-emerald-600 font-bold">TERKONEKSI</span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('salesChart').getContext('2d');
    
    // Gradient Warna Mie (Warna Kaldu/Emas)
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(217, 119, 6, 0.3)'); // Amber
    gradient.addColorStop(1, 'rgba(217, 119, 6, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'Penjualan',
                data: @json($chartData),
                borderColor: '#d97706', // Warna Mie Ayam
                backgroundColor: gradient,
                fill: true,
                tension: 0.4,
                borderWidth: 4,
                // MENGUBAH TITIK JADI "BAKSO"
                pointStyle: 'circle',
                pointRadius: 8, 
                pointBackgroundColor: '#f5f5f4', // Warna Bakso (Stone-100)
                pointBorderColor: '#a8a29e',      // Bayangan Bakso
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        // Kustomisasi teks tooltip biar ala warung
                        label: function(context) {
                            return ' Penjualan: Rp ' + context.parsed.y.toLocaleString('id-ID');
                        }
                    }
                }
            },
            scales: {
                y: { beginAtZero: true, grid: { color: '#fef3c7' } }, // Grid warna kaldu
                x: { grid: { display: false } }
            }
        }
    });
</script>
@endsection
