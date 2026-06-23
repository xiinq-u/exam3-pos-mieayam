@if (! request()->routeIs('orders.show'))
{{-- Footer bawah: informasi kecil aplikasi yang tampil di bagian paling bawah halaman. --}}
<footer class="w-full max-w-6xl mx-auto px-6 mb-8 relative z-10 select-none">
    <div class="w-full bg-[#FFFDF9] border-2 border-dashed border-stone-300/80 rounded-2xl p-5 text-center shadow-lg relative overflow-hidden">
        
        <div class="absolute left-3 top-1/2 -translate-y-1/2 flex flex-col space-y-1.5 opacity-40">
            <div class="w-2 h-2 rounded-full bg-stone-300"></div>
            <div class="w-2 h-2 rounded-full bg-stone-300"></div>
        </div>
        <div class="absolute right-3 top-1/2 -translate-y-1/2 flex flex-col space-y-1.5 opacity-40">
            <div class="w-2 h-2 rounded-full bg-stone-300"></div>
            <div class="w-2 h-2 rounded-full bg-stone-300"></div>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 px-4">
            <div class="text-left">
                <span class="block text-[9px] font-mono tracking-widest text-red-500 font-bold uppercase mb-0.5">Sistem Kasir v2.0</span>
                <p class="text-xs font-bold text-stone-700 tracking-tight">
                    © {{ date('Y') }} {{ config('app.name', 'Mie Ayam Puput') }}. All rights reserved.
                </p>
            </div>
            
            <div class="text-center sm:text-right font-mono text-[10px] text-stone-400 tracking-wider">
                <span>TERIMA KASIH ATAS KUNJUNGAN ANDA</span>
                <span class="block text-[9px] text-stone-300 mt-0.5">Sastra Rasa • Racikan Solo Asli</span>
            </div>
        </div>

    </div>
</footer>
@endif
