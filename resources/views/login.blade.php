<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Inventory_MGT_Ward(47 & 48)') }} — Login</title>

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

    <!-- =========================================================
         LOGIN SECTION
    ========================================================== -->
    <main class="relative flex min-h-[calc(100vh-73px)] items-center justify-center overflow-hidden px-8 py-16">

        <!-- Soft background accent, echoing the hero gradient on the home page -->
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -top-32 -right-32 h-96 w-96 rounded-full opacity-10 blur-3xl" style="background-color: var(--color-primary);"></div>
            <div class="absolute -bottom-32 -left-32 h-96 w-96 rounded-full opacity-10 blur-3xl" style="background-color: var(--color-accent);"></div>
        </div>

        <div class="relative z-10 grid w-full max-w-5xl grid-cols-2 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">

            <!-- Left panel: brand / context, using the accent gradient from the home hero -->
            <div
                class="flex flex-col justify-between p-10 text-white"
                style="background: linear-gradient(135deg, var(--color-accent) 0%, var(--color-primary) 100%);">

                <div>
                    <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-white/15 backdrop-blur-sm">
                        <i class="fa-solid fa-boxes-stacked text-2xl"></i>
                    </div>

                    <h2 class="text-2xl font-bold leading-snug">
                        Welcome back to the Hospital Inventory Management System
                    </h2>

                    <p class="mt-4 text-sm leading-6 text-blue-50">
                        Sign in to manage medicines, surgical items, general
                        inventory, suppliers and stock — all from one
                        centralized platform.
                    </p>
                </div>

                <ul class="mt-10 space-y-3 text-sm text-blue-50">
                    <li class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-check"></i>
                        Real-time stock tracking
                    </li>
                    <li class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-check"></i>
                        Supplier & purchasing insights
                    </li>
                    <li class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-check"></i>
                        Expiry & batch monitoring
                    </li>
                </ul>
            </div>

            <!-- Right panel: the actual login form -->
            <div class="flex flex-col justify-center p-10">

                <div class="mb-8">
                    <span
                        class="inline-flex rounded-full px-4 py-1.5 text-xs font-bold uppercase tracking-widest"
                        style="background-color: color-mix(in srgb, var(--color-primary) 12%, white); color: var(--color-primary);">
                        Staff Login
                    </span>

                    <h1 class="mt-4 text-2xl font-bold text-slate-900">
                        Sign in to your account
                    </h1>
                    <p class="mt-1 text-sm" style="color: var(--color-secondary);">
                        Enter your credentials to access the dashboard.
                    </p>
                </div>

                <!-- Session status (e.g. after registration or password reset) -->
                @if (session('status'))
                    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        {{ session('status') }}
                    </div>
                @endif

                <!-- General error banner (e.g. invalid credentials) -->
                @if ($errors->any() && !$errors->has('email') && !$errors->has('password'))
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('test') }}" class="space-y-5">
                    @csrf

                    <!-- Email -->
                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">
                            Email address
                        </label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                <i class="fa-solid fa-envelope"></i>
                            </span>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="you@hospital.com"
                                class="w-full rounded-xl border border-slate-200 py-2.5 pl-10 pr-3 text-sm text-slate-800 outline-none transition focus:border-transparent focus:ring-2"
                                style="--tw-ring-color: var(--color-primary);"
                            />
                        </div>
                        @error('email')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="mb-1.5 flex items-center justify-between">
                            <label for="password" class="block text-sm font-medium text-slate-700">
                                Password
                            </label>
                            <a
                                href="{{ route('test') }}"
                                class="text-xs font-semibold hover:underline"
                                style="color: var(--color-primary);">
                                Forgot password?
                            </a>
                        </div>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="w-full rounded-xl border border-slate-200 py-2.5 pl-10 pr-3 text-sm text-slate-800 outline-none transition focus:border-transparent focus:ring-2"
                                style="--tw-ring-color: var(--color-primary);"
                            />
                        </div>
                        @error('password')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember me -->
                    <div class="flex items-center gap-2">
                        <input
                            id="remember"
                            type="checkbox"
                            name="remember"
                            class="h-4 w-4 rounded border-slate-300"
                        />
                        <label for="remember" class="text-sm text-slate-600">
                            Remember me
                        </label>
                    </div>

                    <!-- Submit -->
                    <button
                        type="submit"
                        class="flex w-full items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold text-white shadow-md transition hover:-translate-y-0.5"
                        style="background-color: var(--color-primary);">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        Sign In
                    </button>
                </form>

                <!-- Create a new user -->
                <div class="mt-8 border-t border-slate-100 pt-6 text-center">
                    <p class="text-sm" style="color: var(--color-secondary);">
                        Don't have an account?
                        <a
                            href="{{ route('test') }}"
                            class="font-semibold hover:underline"
                            style="color: var(--color-accent);">
                            Create a new account
                        </a>
                    </p>
                </div>

                <div class="mt-4 text-center">
                    <a href="{{ route('home') }}" class="text-xs hover:underline" style="color: var(--color-secondary)">
                        <i class="fa-solid fa-arrow-left mr-1"></i>
                        Back to home
                    </a>
                </div>

            </div>
        </div>
    </main>

    <!-- =========================================================
         FOOTER (mirrors the home page footer)
    ========================================================== -->
    <footer class="border-t border-slate-200 bg-white">
        <div class="mx-auto max-w-7xl px-8 py-6 text-center">
            <p class="text-sm text-slate-400">
                © {{ date('Y') }} Hospital Inventory Management System
            </p>
        </div>
    </footer>

</body>

</html>