<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title') | {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/app.css')
    @endif
</head>
<body class="min-h-dvh bg-slate-50 font-sans text-slate-950 antialiased dark:bg-slate-950 dark:text-white">
    <main class="mx-auto flex min-h-dvh max-w-xl flex-col justify-center px-6 py-16">
        <h1 class="text-3xl font-semibold tracking-tight text-balance">@yield('heading')</h1>
        <p class="mt-4 text-base leading-7 text-slate-600 dark:text-slate-300">@yield('message')</p>

        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ url('/admin') }}"
               class="inline-flex items-center rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors duration-150 hover:bg-brand-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600">
                Gösterge paneline dön
            </a>
            @hasSection('secondary')
                @yield('secondary')
            @endif
        </div>

        <p class="mt-10 text-xs text-slate-500 dark:text-slate-400">Hata kodu @yield('code')</p>
    </main>
</body>
</html>
