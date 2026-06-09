@if (!request()->routeIs('login'))
    <div class="w-full bg-[#FFFDF9] border border-stone-200/80 shadow-xl pt-6 pb-8 px-4 relative select-none font-mono text-xs text-stone-700 mx-auto origin-top animate-print-ticket">
        
        <div class="flex items-center justify-between mb-4 lg:hidden">
            <span class="text-xs font-bold uppercase tracking-wider text-stone-800">Menu</span>
            <label for="main-drawer" class="btn btn-square btn-ghost btn-xs text-stone-500 hover:text-stone-900 p-0 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </label>
        </div>

        <div class="border-b border-stone-300 w-full mb-4"></div>

        <div class="text-center mb-6 space-y-1">
            <h2 class="text-sm font-black tracking-wider text-stone-900 uppercase">MIE AYAM PUPUT</h2>
            <p class="text-[10px] text-stone-400 uppercase">Jl. Raya Solo - Pemesanan Internal</p>
            <p class="text-[9px] text-stone-400">TELP: 0812-XXXX-XXXX</p>
            
            <div class="border-b-2 border-dashed border-stone-300/80 pt-3"></div>
            <div class="flex justify-between text-[10px] text-stone-500 pt-1 px-1">
                <span>TGL: {{ date('d/m/Y H:i') }}</span>
                <span>KASIR: #001</span>
            </div>
            <div class="border-b-2 border-dashed border-stone-300/80 pt-1"></div>
        </div>

        <div class="space-y-4">
            <span class="text-[9px] text-stone-400 block px-1 uppercase tracking-wider">DAFTAR MENU UTAMA:</span>
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('dashboard') }}" class="block w-full group">
                        <div class="flex justify-between items-center w-full rounded-md border-l-4 px-3 py-3 transition duration-150 {{ request()->routeIs('dashboard') ? 'bg-stone-100 border-red-500 text-stone-900 font-bold' : 'bg-transparent border-transparent text-stone-500 hover:bg-stone-50 hover:text-stone-800' }}">
                            <span>01. DASHBOARD</span>
                            @if(request()->routeIs('dashboard'))
                                <span class="text-[10px] text-red-600 font-black">[*AKTIF*]</span>
                            @else
                                <span class="text-[10px] opacity-0 group-hover:opacity-100">-></span>
                            @endif
                        </div>
                    </a>
                </li>
                <li>
                    <a href="{{ route('cashier.index') }}" class="block w-full group">
                        <div class="flex justify-between items-center w-full rounded-md border-l-4 px-3 py-3 transition duration-150 {{ request()->routeIs('cashier.*') ? 'bg-stone-100 border-red-500 text-stone-900 font-bold' : 'bg-transparent border-transparent text-stone-500 hover:bg-stone-50 hover:text-stone-800' }}">
                            <span>02. KASIR</span>
                            @if(request()->routeIs('cashier.*'))
                                <span class="text-[10px] text-red-600 font-black">[*AKTIF*]</span>
                            @else
                                <span class="text-[10px] opacity-0 group-hover:opacity-100">-></span>
                            @endif
                        </div>
                    </a>
                </li>
                <li>
                    <a href="{{ route('orders.pending') }}" class="block w-full group">
                        <div class="flex justify-between items-center w-full rounded-md border-l-4 px-3 py-3 transition duration-150 {{ request()->routeIs('orders.*') ? 'bg-stone-100 border-red-500 text-stone-900 font-bold' : 'bg-transparent border-transparent text-stone-500 hover:bg-stone-50 hover:text-stone-800' }}">
                            <span>03. RIWAYAT PESANAN</span>
                            @if(request()->routeIs('orders.*'))
                                <span class="text-[10px] text-red-600 font-black">[*AKTIF*]</span>
                            @else
                                <span class="text-[10px] opacity-0 group-hover:opacity-100">-></span>
                            @endif
                        </div>
                    </a>
                </li>
                <li>
                    <a href="{{ route('products.index') }}" class="block w-full group">
                        <div class="flex justify-between items-center w-full rounded-md border-l-4 px-3 py-3 transition duration-150 {{ request()->routeIs('products.*') ? 'bg-stone-100 border-red-500 text-stone-900 font-bold' : 'bg-transparent border-transparent text-stone-500 hover:bg-stone-50 hover:text-stone-800' }}">
                            <span>04. PRODUK</span>
                            @if(request()->routeIs('products.*'))
                                <span class="text-[10px] text-red-600 font-black">[*AKTIF*]</span>
                            @else
                                <span class="text-[10px] opacity-0 group-hover:opacity-100">-></span>
                            @endif
                        </div>
                    </a>
                </li>
                <li>
                    <a href="{{ route('reports.sales') }}" class="block w-full group">
                        <div class="flex justify-between items-center w-full rounded-md border-l-4 px-3 py-3 transition duration-150 {{ request()->routeIs('reports.sales') ? 'bg-stone-100 border-red-500 text-stone-900 font-bold' : 'bg-transparent border-transparent text-stone-500 hover:bg-stone-50 hover:text-stone-800' }}">
                            <span>05. LAPORAN</span>
                            @if(request()->routeIs('reports.sales'))
                                <span class="text-[10px] text-red-600 font-black">[*AKTIF*]</span>
                            @else
                                <span class="text-[10px] opacity-0 group-hover:opacity-100">-></span>
                            @endif
                        </div>
                    </a>
                </li>
                <li>
                    <a href="{{ route('reports.revenue') }}" class="block w-full group">
                        <div class="flex justify-between items-center w-full rounded-md border-l-4 px-3 py-3 transition duration-150 {{ request()->routeIs('reports.revenue') ? 'bg-stone-100 border-red-500 text-stone-900 font-bold' : 'bg-transparent border-transparent text-stone-500 hover:bg-stone-50 hover:text-stone-800' }}">
                            <span>06. PENDAPATAN</span>
                            @if(request()->routeIs('reports.revenue'))
                                <span class="text-[10px] text-red-600 font-black">[*AKTIF*]</span>
                            @else
                                <span class="text-[10px] opacity-0 group-hover:opacity-100">-></span>
                            @endif
                        </div>
                    </a>
                </li>
                <li>
                    <form action="{{ route('logout') }}" method="POST" class="block w-full group">
                        @csrf
                        <button type="submit" class="block w-full text-left">
                            <div class="flex justify-between items-center w-full rounded-md border-l-4 border-transparent px-3 py-3 text-red-600 transition duration-150 hover:bg-red-50 hover:border-red-500 hover:text-red-700">
                                <span>07. LOGOUT</span>
                                <span class="text-[10px] opacity-0 group-hover:opacity-100">-></span>
                            </div>
                        </button>
                    </form>
                </li>
            </ul>
        </div>

        <div class="mt-6 space-y-1">
            <div class="border-b-2 border-dashed border-stone-300/80 mb-2"></div>
            <div class="flex justify-between font-bold text-stone-800 px-1 text-[11px]">
                <span>TOTAL MENU ACCESS</span>
                <span>7 ITEMS</span>
            </div>
            <div class="border-b border-stone-300 pt-3"></div>
        </div>

        <div class="text-center mt-6 text-[9px] text-stone-400 uppercase tracking-widest space-y-1">
            <p>Jaga Kualitas Rasa & Pelayanan</p>
            <p class="font-bold text-stone-500">*** SIMPAN STRUK INI ***</p>
        </div>

        <div class="absolute bottom-0 left-0 right-0 h-3 bg-transparent" 
             style="background-image: linear-gradient(-135deg, #FFFDF9 6px, transparent 0), linear-gradient(135deg, #FFFDF9 6px, transparent 0); background-size: 12px 12px; background-position: bottom center; filter: drop-shadow(0px 2px 1px rgba(0,0,0,0.05)); transform: translateY(100%);">
        </div>
    </div>
@endif
