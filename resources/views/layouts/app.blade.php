<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', config('app.name', 'Laravel'))</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body class="min-h-screen bg-base-200 text-base-content font-sans">
        {{-- Layout utama: membungkus halaman dengan header, sidebar mobile, konten, dan footer. --}}
        <div class="drawer">
            <input id="main-drawer" type="checkbox" class="drawer-toggle" />

            <div class="drawer-content min-h-screen">
                @include('layouts.partials.header')

                <main class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-[280px_minmax(0,1fr)] gap-6">
                        <aside class="hidden lg:block">
                            @include('layouts.partials.sidebar')
                        </aside>

                        <section class="space-y-6">
                            @yield('content')
                        </section>
                    </div>
                </main>
            </div>

            <div class="drawer-side lg:hidden">
                <label for="main-drawer" class="drawer-overlay"></label>
                <aside class="w-72 bg-base-100">
                    @include('layouts.partials.sidebar')
                </aside>
            </div>
        </div>

        @include('layouts.partials.footer')
        @stack('scripts')
    </body>
</html>
