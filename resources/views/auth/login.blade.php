@extends('layouts.app')

@section('title', 'Login - Mie Ayam Puput')

@section('content')
{{-- Halaman login: tempat user memasukkan email dan password untuk masuk ke sistem. --}}
<div class="absolute inset-0 opacity-40 pointer-events-none bg-[radial-gradient(#e4d5b7_1.5px,transparent_1.5px)] [background-size:32px_32px]"></div>
<div class="absolute top-10 left-10 w-72 h-72 bg-amber-100/30 rounded-full blur-2xl pointer-events-none"></div>
<div class="absolute bottom-10 right-10 w-96 h-96 bg-red-100/20 rounded-full blur-3xl pointer-events-none"></div>

<div class="w-full max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-12 items-center justify-center relative z-10 py-16 px-6 select-none">
    
    <div class="hidden lg:flex lg:col-span-5 flex-col items-center justify-center space-y-8 group cursor-pointer">
        
        <div class="relative w-72 h-72 flex items-center justify-center">
            
            <div class="absolute top-4 flex space-x-3 opacity-0 group-hover:opacity-100 transition-opacity duration-500 z-10">
                <div class="w-2 h-12 bg-stone-200/40 rounded-full filter blur-md animate-bounce"></div>
                <div class="w-3 h-16 bg-stone-200/50 rounded-full filter blur-md animate-pulse delay-75"></div>
                <div class="w-2 h-10 bg-stone-200/40 rounded-full filter blur-md animate-bounce delay-150"></div>
            </div>

            <div class="absolute top-16 -left-4 w-56 h-1.5 bg-amber-800 rounded-full transform rotate-[-35deg] shadow-md z-40 transition-transform duration-500 group-hover:translate-x-4 group-hover:-translate-y-2"></div>
            <div class="absolute top-20 -left-6 w-56 h-1.5 bg-amber-800 rounded-full transform rotate-[-30deg] shadow-md z-40 transition-transform duration-500 group-hover:translate-x-2 group-hover:-translate-y-1"></div>

            <div class="absolute bottom-24 w-52 h-24 overflow-visible z-20 flex flex-col items-center justify-end">
                
                <div class="absolute -left-1 bottom-6 w-10 h-14 bg-green-600 rounded-t-3xl rounded-br-full border-l border-green-700 shadow-sm transform -rotate-12 group-hover:rotate-[-20deg] transition-transform duration-300"></div>
                <div class="absolute left-4 bottom-8 w-8 h-12 bg-green-500 rounded-t-2xl rounded-bl-full shadow-sm transform rotate-12"></div>

                <div class="w-46 h-16 bg-amber-500 rounded-b-full rounded-t-[30px] border-b-4 border-amber-600 shadow-inner p-1 relative group-hover:animate-wiggle overflow-hidden">
                    
                    <div class="absolute inset-0 flex flex-wrap justify-center gap-1 pt-1 opacity-80">
                        @for ($i = 0; $i < 4; $i++)
                            <div class="w-10 h-6 border-b-4 border-r-4 border-amber-300 rounded-[40%_20%_60%_30%] transform rotate-12 -space-x-2"></div>
                            <div class="w-10 h-6 border-t-4 border-l-4 border-amber-200 rounded-[20%_50%_30%_60%] transform -rotate-12"></div>
                        @endfor
                    </div>

                    <div class="absolute inset-0 flex justify-center items-center pt-2 z-10 space-x-[-10px]">
                        <div class="w-14 h-8 border-b-4 border-amber-300/90 rounded-[50%_50%_30%_40%] transform rotate-45"></div>
                        <div class="w-12 h-8 border-t-4 border-b-4 border-amber-200 rounded-[30%_60%_40%_50%] transform -rotate-12 mt-2"></div>
                        <div class="w-14 h-7 border-b-4 border-amber-300/90 rounded-[45%_35%_55%_45%] transform rotate-12"></div>
                        <div class="w-11 h-8 border-l-4 border-b-4 border-amber-100/90 rounded-[60%_30%_50%_40%] transform -rotate-45"></div>
                    </div>

                    <div class="absolute inset-x-3 bottom-1 h-10 z-20 flex justify-around">
                        <div class="w-16 h-6 border-b-[3.5px] border-amber-200 rounded-[50%_20%_50%_20%] transform rotate-6"></div>
                        <div class="w-16 h-5 border-b-[3.5px] border-amber-100 rounded-[20%_50%_20%_50%] transform -rotate-6 mt-1"></div>
                    </div>

                </div>

                <div class="absolute top-5 left-8 w-24 h-7 bg-amber-900 rounded-full shadow-md flex items-center justify-around px-2 border border-amber-950 z-30 transform -rotate-3">
                    <div class="w-2.5 h-2.5 bg-amber-800 rounded-sm border border-amber-950"></div>
                    <div class="w-3 h-3 bg-stone-900 rounded-sm shadow-sm"></div>
                    <div class="w-2 h-2.5 bg-amber-700 rounded-sm"></div>
                </div>

                <div class="absolute top-6 left-28 flex space-x-1 z-40 transform rotate-12">
                    <div class="w-2 h-2 bg-green-500 rounded-full border border-green-600 ring-1 ring-green-300/30"></div>
                    <div class="w-2.5 h-2 bg-green-400 rounded-full border border-green-600 mt-1"></div>
                    <div class="w-1.5 h-1.5 bg-green-500 rounded-full border border-green-600"></div>
                </div>

                <div class="absolute -right-2 bottom-4 flex -space-x-1 z-30">
                    <div class="w-9 h-9 bg-stone-400 rounded-full border-2 border-stone-300 shadow-md transform transition-transform duration-300 group-hover:scale-110"></div>
                    <div class="w-7 h-7 bg-stone-400 rounded-full border-2 border-stone-300 shadow-md mt-2"></div>
                </div>
            </div>

            <div class="absolute bottom-4 w-60 h-32 bg-gradient-to-b from-stone-50 to-stone-100 rounded-b-[120px] border-x-4 border-b-4 border-stone-200 shadow-2xl z-30 flex items-center justify-center overflow-hidden">
                <div class="absolute top-0 w-full h-2 bg-red-600"></div>
                <div class="relative w-12 h-12 bg-red-500 rounded-full flex items-center justify-center border-2 border-amber-400 shadow-sm opacity-90 group-hover:scale-110 transition-transform duration-300">
                    <div class="absolute -top-1 w-4 h-3 bg-red-600 rounded-t-full"></div>
                    <span class="text-white text-[10px] font-black font-mono">PUPUT</span>
                </div>
                <div class="absolute bottom-0 w-24 h-2 bg-stone-300 rounded-t-md"></div>
            </div>

        </div>

        <div class="text-center">
            <span class="text-[10px] font-mono tracking-[0.3em] text-red-500 font-bold uppercase block mb-1">Citarasa Asli Solo</span>
            <h2 class="text-2xl font-black text-stone-800 tracking-tight">RESEP TURUN TEMURUN</h2>
            <p class="text-xs text-stone-400 max-w-xs mt-1">Arahkan kursor untuk melihat kehangatan menu racikan dapur kami.</p>
        </div>

    </div>

    <div class="col-span-1 lg:col-span-7 flex justify-center w-full">
        
        <div class="w-full max-w-md bg-stone-800 p-3 pb-5 rounded-[32px] shadow-2xl border-4 border-stone-700/50 relative">
            
            <div class="absolute -top-4 left-1/2 transform -translate-x-1/2 w-32 h-8 bg-gradient-to-b from-amber-400 to-amber-500 rounded-t-xl rounded-b-md shadow-md z-30 border border-amber-300 flex items-center justify-center">
                <div class="w-4 h-4 bg-amber-600 rounded-full border border-amber-700"></div>
            </div>

            <div class="w-full bg-[#FFFDF9] rounded-2xl p-6 md:p-8 relative border border-amber-100/50">
                
                <div class="flex justify-between items-center mb-6 text-[10px] font-mono text-stone-400 tracking-wider border-b border-stone-200/60 pb-3">
                    <span>STRUK: #{{ date('Ymd') }}-AUTH</span>
                    <span class="text-red-500 font-bold">KASIR UTAMA</span>
                </div>

                <div class="text-center mb-8">
                    <div class="inline-block bg-amber-100 text-amber-900 text-[9px] font-mono font-bold px-3 py-1 rounded-md uppercase tracking-widest mb-2 border border-amber-200">
                        Sistem Akses Meja Masuk
                    </div>
                    <h1 class="text-3xl font-black text-stone-800 tracking-tight">
                        Mie Ayam <span class="text-red-500">Puput</span>
                    </h1>
                </div>

                @if($errors->any())
                    <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 text-xs text-red-600">
                        <div class="flex items-center gap-1.5 font-bold mb-1 uppercase tracking-wider">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-red-500">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                            </svg>
                            Validasi Gagal:
                        </div>
                        <ul class="list-disc list-inside space-y-0.5 text-red-500/90 pl-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.attempt') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-stone-500 uppercase tracking-wider mb-2">
                            01. Identitas Akun (Email)
                        </label>
                        <input type="email" name="email" value="{{ old('email') }}" 
                            class="w-full px-4 py-3 rounded-xl bg-stone-100/60 border border-stone-200 text-stone-800 placeholder-stone-400 focus:outline-none focus:border-amber-400 focus:bg-white focus:ring-4 focus:ring-amber-400/10 transition-all duration-200 text-sm font-medium" 
                            placeholder="petugas@mieayampuput.com" required autofocus>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-500 uppercase tracking-wider mb-2">
                            02. Kunci Keamanan (Password)
                        </label>
                        <input type="password" name="password" 
                            class="w-full px-4 py-3 rounded-xl bg-stone-100/60 border border-stone-200 text-stone-800 placeholder-stone-400 focus:outline-none focus:border-amber-400 focus:bg-white focus:ring-4 focus:ring-amber-400/10 transition-all duration-200 text-sm" 
                            placeholder="••••••••" required>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2 text-xs text-stone-500 cursor-pointer select-none font-semibold">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded text-red-600 border-stone-300 focus:ring-red-400/20 accent-red-600">
                            Ingat akun saya di komputer meja ini
                        </label>
                    </div>

                    <div class="pt-2">
                        <button class="w-full py-3.5 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-bold text-xs tracking-widest rounded-xl shadow-lg shadow-red-500/20 transition-all duration-200 transform active:scale-[0.98] flex items-center justify-center space-x-2">
                            <span>PROSES SAJIAN & MASUK</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 4.5l7.5 7.5-7.5 7.5M3 12h15" />
                            </svg>
                        </button>
                    </div>
                </form>

                <div class="mt-8 pt-4 border-t-2 border-dashed border-stone-200 text-center text-[10px] font-mono text-stone-400 uppercase tracking-widest">
                    Selamat Bertugas & Jaga Kualitas!
                </div>

            </div>
        </div>
    </div>

</div>

<style>
@keyframes wiggle {
    0%, 100’% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-4px) rotate(1.5deg); }
}
.group:hover .w-46 {
    animation: wiggle 0.35s ease-in-out infinite;
}
</style>
@endsection
