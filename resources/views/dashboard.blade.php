<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Inventory_MGT_Ward(47 & 48)') }} — Admin</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Font Awesome (used on the home page) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-50 text-slate-800">

    <!-- =========================================================
         TOP BRAND BAR (mirrors the home page brand mark)
    ========================================================== -->
    <div class="border-b border-slate-200 bg-white sticky top-0 z-50">
        <div class="mx-auto flex max-w-7xl items-center gap-3 px-8 py-4">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl text-white shadow-md" style="background-color: var(--color-primary);">
                    <i class="fa-solid fa-plus text-lg"></i>
                </div>
                <div>
                    <span class="block text-base font-bold text-slate-800">
                        Hospital Inventory Management System
                    </span>
                    <span class="block text-[10px] font-medium uppercase tracking-wider" style="color: var(--color-secondary);">
                        Hospital Management
                    </span>
                </div>
            </a>
        </div>
    </div>

    <div class="mx-auto max-w-7xl h-screen px-8 py-6">
        <h1>Admin dashboard</h1>
    </div>

    <!-- =========================================================
         FOOTER (mirrors the home page footer)
    ========================================================== -->
    <footer class="border-t border-slate-200 bg-white sticky bottom-0 z-50">
        <div class="mx-auto max-w-7xl px-8 py-6 text-center">
            <p class="text-sm text-slate-400">
                © {{ date('Y') }} Hospital Inventory Management System
            </p>
        </div>
    </footer>

</body>

</html>