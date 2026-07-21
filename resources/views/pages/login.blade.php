<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In - MedStore</title>

    <!-- CSRF Token (also embedded in the form below) -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Fonts (from the Figma export's fonts.css) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Inter:wght@400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (CDN build — see notes below for a compiled alternative) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        outfit: ["'Outfit'", 'sans-serif'],
                        inter: ["'Inter'", 'sans-serif'],
                        mono: ["'DM Mono'", 'monospace'],
                    },
                },
            },
        };
    </script>

    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen">

    <div class="min-h-screen flex items-center justify-center relative overflow-hidden"
         style="background: linear-gradient(135deg,#1e3a8a 0%,#1d4ed8 55%,#2563eb 100%);">

        <!-- Decorative background circles -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-32 -right-32 w-96 h-96 rounded-full bg-white/5"></div>
            <div class="absolute -bottom-24 -left-24 w-80 h-80 rounded-full bg-white/5"></div>
        </div>

        <div class="relative z-10 w-full max-w-md mx-4">

            <!-- Brand header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white/15 backdrop-blur border border-white/20 mb-4">
                    <!-- Activity icon (lucide) -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-white tracking-tight font-outfit">MedStore</h1>
                <p class="text-blue-200 text-sm mt-1 font-inter">Hospital Inventory Management System</p>
            </div>

            <!-- Login card -->
            <div class="bg-white rounded-2xl shadow-2xl p-8">
                <h2 class="text-xl font-semibold text-slate-800 mb-1 font-outfit">First sign in to your account</h2>
                <p class="text-sm text-slate-500 mb-6 font-inter">Enter your credentials to access inventory</p>

                {{-- Session status (e.g. password reset confirmation) --}}
                @if (session('status'))
                    <div class="mb-4 px-4 py-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-inter">
                        {{ session('status') }}
                    </div>
                @endif

                {{-- General auth error (e.g. "These credentials do not match our records") --}}
                @if ($errors->has('email') && Str::contains($errors->first('email'), ['credentials', 'throttle', 'many']))
                    <div class="mb-4 px-4 py-2.5 rounded-xl bg-red-50 border border-red-200 text-red-600 text-sm font-inter">
                        {{ $errors->first('email') }}
                    </div>
                @endif

                <form method="GET" action="{{ route('home') }}" class="space-y-4">
                    @csrf

                    {{-- Username / Email --}}
                    <div x-data="{email: ''}"  x-effect="console.log('Email updated:', email)">
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5 font-inter">
                            Username or Email
                        </label>
                        <input
                            type="text"
                            id="email"
                            name="email"
                            x-model="email"
                            value="{{ old('email') }}"
                            placeholder="Username or your email"
                            autofocus
                            autocomplete="username"
                            class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('email') ? 'border-red-300' : 'border-slate-200' }} bg-slate-50 text-slate-800 text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all font-inter"
                        >
                        @error('email')
                            <p class="mt-1.5 text-xs text-red-600 font-inter">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5 font-inter">
                            Password
                        </label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('password') ? 'border-red-300' : 'border-slate-200' }} bg-slate-50 text-slate-800 text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all font-inter"
                        >
                        @error('password')
                            <p class="mt-1.5 text-xs text-red-600 font-inter">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Remember me + Forgot password --}}
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-xs text-slate-500 font-inter">
                            <input type="checkbox" name="remember" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            Remember me
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs text-blue-600 hover:text-blue-700 font-inter">
                                Forgot password?
                            </a>
                        @endif
                    </div>

                    <button
                        type="submit"
                        class="w-full py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition-colors mt-2 font-inter"
                    >
                        Sign In
                    </button>
                </form>

                <div class="mt-5 pt-4 border-t border-slate-100 flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
                    <p class="text-xs text-slate-400 font-inter">Secured connection · AlphaStrategies Desgn</p>
                </div>
            </div>
        </div>
    </div>

</body>
</html>