{{-- Header atas: berisi tombol hamburger, nama aplikasi, user aktif, dan shortcut dashboard. --}}
<header id="main-navbar" class="navbar sticky top-0 z-50 bg-[#FFFDF9] border-b-2 border-dashed border-amber-200 px-4 py-3 lg:px-8 select-none transition-all duration-300 ease-out">
    
    <div class="flex-none lg:hidden mr-1">
        <label for="main-drawer" class="btn btn-square btn-ghost btn-sm text-stone-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </label>
    </div>

    <div class="flex-1">
        <div class="flex items-center space-x-2 sm:space-x-3">
            
            <div class="relative w-10 h-9 sm:w-12 sm:h-11 flex items-end justify-center pb-1 transform scale-90 sm:scale-100 origin-bottom-left">
                <div class="absolute top-1 left-2 w-10 h-0.5 bg-amber-800 transform rotate-[25deg] origin-center opacity-80"></div>
                <div class="absolute top-2 left-1 w-10 h-0.5 bg-amber-800 transform rotate-[15deg] origin-center opacity-80"></div>

                <div class="absolute bottom-4 z-10 flex flex-col items-center">
                    <div class="flex space-x-0.5 mb-[-2px]">
                        <div class="w-3 h-2.5 rounded-full border-t-2 border-amber-400 bg-transparent"></div>
                        <div class="w-3 h-2.5 rounded-full border-t-2 border-amber-400 bg-transparent"></div>
                    </div>
                    <div class="absolute -top-1.5 flex space-x-0.5 z-20">
                        <div class="w-2.5 h-2.5 bg-stone-400 rounded-full border border-stone-500/30 shadow-sm"></div>
                        <div class="w-2 h-2 bg-stone-400 rounded-full border border-stone-500/30 shadow-sm mt-0.5"></div>
                    </div>
                </div>

                <div class="w-9 h-4 sm:w-10 sm:h-5 bg-gradient-to-b from-red-500 to-red-600 rounded-b-full border-x border-b border-amber-300 shadow-md relative z-30">
                    <div class="absolute top-1 left-1/2 transform -translate-x-1/2 w-6 h-[2px] bg-amber-300/60 rounded-full"></div>
                </div>
            </div>

            <div class="flex flex-col text-left leading-none">
                <span class="text-sm sm:text-base font-black text-stone-800 tracking-tight uppercase whitespace-nowrap">
                    Mie Ayam <span class="text-red-500">Puput</span>
                </span>
                <span class="text-[8px] sm:text-[9px] font-mono tracking-widest text-stone-400 uppercase mt-0.5">Sistem Kasir</span>
            </div>

        </div>
    </div>

    <div class="flex-none gap-2">
        @if (Route::has('login'))
            <nav class="flex items-center">
                
                @auth
                    <div class="hidden sm:flex items-center gap-3">
                        <div class="flex flex-col text-right leading-none mr-1">
                            <span class="text-xs font-bold text-stone-700">{{ Auth::user()->name ?? 'Petugas' }}</span>
                            <span class="text-[9px] font-mono text-amber-600 uppercase tracking-wider mt-0.5">Aktif di Meja</span>
                        </div>
                        <a href="{{ url('/dashboard') }}" class="px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-bold text-xs tracking-wider rounded-xl shadow-md shadow-red-500/10 transition-all duration-200 active:scale-[0.98] flex items-center space-x-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" />
                            </svg>
                            <span>DASHBOARD</span>
                        </a>
                    </div>

                    <div class="flex sm:hidden items-center gap-1.5">
                        <a href="{{ url('/dashboard') }}" aria-label="Dashboard" class="p-2 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl shadow-md transition-all duration-200 active:scale-[0.95] flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" />
                            </svg>
                        </a>
                    </div>

                @else
                    @if (!request()->routeIs('login'))
                        <a href="{{ route('login') }}" class="hidden sm:flex px-4 py-2 border-2 border-amber-300 hover:bg-amber-50 text-stone-700 font-bold text-xs tracking-wider rounded-xl transition-all duration-200 active:scale-[0.98] items-center space-x-1.5">
                            <span>MASUK SISTEM</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5 text-amber-600">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                            </svg>
                        </a>

                        <a href="{{ route('login') }}" aria-label="Masuk" class="flex sm:hidden p-2 border-2 border-amber-300 text-stone-700 rounded-xl transition-all duration-200 active:scale-[0.95] items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-amber-600">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                            </svg>
                        </a>
                    @endif
                @endauth

            </nav>
        @endif
    </div>
</header>

@once
    @push('scripts')
        <script>
            const navbar = document.getElementById('main-navbar');

            function updateNavbar() {
                if (!navbar) {
                    return;
                }

                const isScrolled = window.scrollY > 12;
                navbar.classList.toggle('bg-[#FFFDF9]/95', isScrolled);
                navbar.classList.toggle('backdrop-blur-md', isScrolled);
                navbar.classList.toggle('shadow-lg', isScrolled);
                navbar.classList.toggle('shadow-stone-900/5', isScrolled);
                navbar.classList.toggle('py-2', isScrolled);
                navbar.classList.toggle('py-3', !isScrolled);
            }

            window.addEventListener('scroll', updateNavbar, { passive: true });
            updateNavbar();
        </script>
    @endpush
@endonce
