@extends('layouts.app')

@section('title', 'Welcome')

@section('content')
    <div class="bg-white dark:bg-[#161615] rounded-3xl shadow-xl overflow-hidden">
        <div class="grid lg:grid-cols-[1.35fr_1fr] gap-6 p-6 lg:p-10">
            <div class="space-y-6">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm text-muted">Welcome to</p>
                        <h1 class="text-3xl font-semibold">{{ config('app.name', 'Laravel') }}</h1>
                    </div>
                    <div class="badge badge-primary badge-lg">Tailwind + DaisyUI</div>
                </div>

                <p class="text-base text-muted">Laravel has an incredibly rich ecosystem. Start building your pages with the shared layout and reuse this master view across all pages.</p>

                <div class="space-y-4">
                    <div class="card bg-base-100 shadow-sm border border-base-300">
                        <div class="card-body">
                            <h2 class="card-title">What to do next</h2>
                            <p>Use the layout at <code>resources/views/layouts/app.blade.php</code> and wrap your page content inside <code>@section('content')</code>.</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a href="https://laravel.com/docs" target="_blank" class="btn btn-outline btn-sm">Documentation</a>
                        <a href="https://laracasts.com" target="_blank" class="btn btn-outline btn-sm">Laracasts</a>
                        <a href="https://cloud.laravel.com" target="_blank" class="btn btn-primary btn-sm">Deploy now</a>
                    </div>
                </div>
            </div>

            <div class="bg-[#fff2f2] dark:bg-[#1D0002] rounded-3xl p-6 flex items-center justify-center">
                <div class="space-y-4 text-center">
                    <p class="text-lg font-medium">Ready to build</p>
                    <div class="mockup-code bg-base-200 p-4 rounded-2xl">
                        <pre><code>&lt;!DOCTYPE html&gt;
&lt;html&gt;
&lt;head&gt;
  @vite(['resources/css/app.css'])
&lt;/head&gt;
&lt;body&gt;
  @extends('layouts.app')
  @section('content')
    ...
  @endsection
&lt;/body&gt;
&lt;/html&gt;</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
