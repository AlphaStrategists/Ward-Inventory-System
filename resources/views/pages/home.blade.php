<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ward Inventory - Home</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Inter:wght@400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

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
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

</head>

<body class="min-h-screen">
    <div class="min-h-screen flex items-start justify-center relative overflow-hidden" style="background: linear-gradient(135deg,#1e3a8a 0%,#1d4ed8 55%,#2563eb 100%);">
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-32 -right-32 w-96 h-96 rounded-full bg-white/5"></div>
            <div class="absolute -bottom-24 -left-24 w-80 h-80 rounded-full bg-white/5"></div>
        </div>
        <div class="relative z-10 mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <header class="sticky top-0 z-30 rounded-[2rem] border border-slate-200/80 bg-white/95 backdrop-blur">
                <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-4 sm:px-6 lg:px-8 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center text-center gap-4">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white/15 backdrop-blur border border-white/20 mb-4">
                            <!-- Activity icon (lucide) -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.28em] text-slate-500 text-start">Hospital Inventory Management System</p>
                            <h1 class="text-xl font-semibold tracking-tight text-slate-900 font-outfit text-start">Dashboard</h1>
                        </div>
                    </div>
                    <nav class="hidden items-center gap-2 rounded-[2rem] border border-slate-200 bg-white/95 px-4 py-3 text-sm font-medium text-slate-700 md:flex">
                        <a href="" class="rounded-[2rem] bg-white px-4 py-2 text-slate-700 transition hover:bg-slate-50">Home</a>
                        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                            <button @click="open = !open" class="inline-flex items-center gap-2 rounded-[2rem] bg-white px-4 py-2 text-slate-700 transition hover:bg-slate-50">
                                Surgical
                                <span class="text-slate-400">▾</span>
                            </button>
                            <div x-show="open" x-cloak class="absolute left-0 top-full mt-2 w-52 rounded-[2rem] border border-slate-200 bg-white p-2 shadow-2xl">
                                <a href="#" class="block rounded-[2rem] px-4 py-3 text-sm text-slate-700 transition hover:bg-slate-50">Local purchases</a>
                                <a href="#" class="block rounded-[2rem] px-4 py-3 text-sm text-slate-700 transition hover:bg-slate-50">Surgical consumable 1</a>
                                <a href="#" class="block rounded-[2rem] px-4 py-3 text-sm text-slate-700 transition hover:bg-slate-50">Surgical consumable 2</a>
                            </div>
                        </div>
                        <a href="/ginventory" class="rounded-2xl whitespace-nowrap bg-white px-4 py-2 text-slate-700 transition hover:bg-slate-50">General Inventory</a>

                        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                            <button @click="open = !open" class="inline-flex items-center gap-2 rounded-[2rem] bg-white px-4 py-2 text-slate-700 transition hover:bg-slate-50">
                                Medicine
                                <span class="text-slate-400">▾</span>
                            </button>
                            <div x-show="open" x-cloak class="absolute left-0 top-full mt-2 w-52 rounded-[2rem] border border-slate-200 bg-white p-2 shadow-2xl">
                                <a href="#" class="block rounded-[2rem] px-4 py-3 text-sm text-slate-700 transition hover:bg-slate-50">Nacotic</a>
                                <a href="#" class="block rounded-[2rem] px-4 py-3 text-sm text-slate-700 transition hover:bg-slate-50">Syrup</a>
                                <a href="#" class="block rounded-[2rem] px-4 py-3 text-sm text-slate-700 transition hover:bg-slate-50">IV-fluid</a>
                                <a href="#" class="block rounded-[2rem] px-4 py-3 text-sm text-slate-700 transition hover:bg-slate-50">Bulk</a>
                                <a href="#" class="block rounded-[2rem] px-4 py-3 text-sm text-slate-700 transition hover:bg-slate-50">Oral-Consumable</a>
                                <a href="#" class="block rounded-[2rem] px-4 py-3 text-sm text-slate-700 transition hover:bg-slate-50">Antibiotics</a>
                                <a href="#" class="block rounded-[2rem] px-4 py-3 text-sm text-slate-700 transition hover:bg-slate-50">Oral-Antibiotics</a>
                            </div>
                        </div>
                        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                            <button @click="open = !open" class="inline-flex items-center gap-2 rounded-[2rem] bg-white px-4 py-2 text-slate-700 transition hover:bg-slate-50">
                                Injections
                                <span class="text-slate-400">▾</span>
                            </button>
                            <div x-show="open" x-cloak class="absolute left-0 top-full mt-2 w-52 rounded-[2rem] border border-slate-200 bg-white p-2 shadow-2xl">
                                <a href="#" class="block rounded-[2rem] px-4 py-3 text-sm text-slate-700 transition hover:bg-slate-50">Injections</a>
                                <a href="#" class="block rounded-[2rem] px-4 py-3 text-sm text-slate-700 transition hover:bg-slate-50">Antibiotic-Injections</a>
                            </div>
                        </div>
                        <a href="/patients" class="rounded-2xl bg-white px-4 py-2 text-slate-700 transition hover:bg-slate-50">Patients</a>
                        <a href="#" class="rounded-2xl bg-white px-4 py-2 text-slate-700 transition hover:bg-slate-50">Settings</a>
                        <a href="/" class="rounded-2xl px-4 py-2 text-white transition hover:bg-red-700 bg-red-600">LogOut</a>
                    </nav>
                    <div class="flex items-center justify-between gap-3 md:hidden">
                        <span class="text-sm text-slate-500">Menu</span>
                        <button type="button" class="rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">Open</button>
                    </div>
                </div>
            </header>

            <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl backdrop-blur-xl">
                    <div class="grid gap-8 lg:grid-cols-[1.4fr_0.8fr] lg:items-end">
                        <div class="space-y-4">
                            <div class="inline-flex items-center gap-2 rounded-full bg-blue-500/10 px-3 py-1 text-xs uppercase tracking-[0.25em] text-blue-200">Inventory home</div>
                            <div>
                                <h2 class="text-3xl font-semibold tracking-tight text-white font-outfit">Manage ward inventory with speed and clarity</h2>
                                <p class="mt-3 text-slate-300">Quickly review surgical stock, general inventory, medicines, injections, and patient requests from a single dashboard.</p>
                            </div>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                                    <p class="text-sm uppercase tracking-[0.24em] text-slate-500">Surgical items</p>
                                    <p class="mt-4 text-3xl font-semibold text-slate-900">384</p>
                                    <p class="mt-2 text-sm text-slate-500">Local purchases + consumables</p>
                                </div>
                                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                                    <p class="text-sm uppercase tracking-[0.24em] text-slate-500">Medicines</p>
                                    <p class="mt-4 text-3xl font-semibold text-slate-900">1,742</p>
                                    <p class="mt-2 text-sm text-slate-500">Tablets, syrups and stock units</p>
                                </div>
                            </div>
                        </div>
                        <div class="rounded-[1.75rem] border border-slate-200 bg-white p-6 text-slate-700">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm uppercase tracking-[0.24em] text-slate-500">Quick status</p>
                                    <h3 class="mt-3 text-2xl font-semibold text-slate-900">Current ward totals</h3>
                                </div>
                                <span class="rounded-2xl bg-slate-100 px-3 py-2 text-xs text-slate-500">Updated now</span>
                            </div>
                            <div class="mt-6 space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="rounded-3xl bg-slate-50 p-4">
                                        <p class="text-xs uppercase tracking-[0.24em] text-slate-500">In stock</p>
                                        <p class="mt-3 text-2xl font-semibold text-slate-900">7,120</p>
                                    </div>
                                    <div class="rounded-3xl bg-slate-50 p-4">
                                        <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Low stock</p>
                                        <p class="mt-3 text-2xl font-semibold text-slate-900">74</p>
                                    </div>
                                </div>
                                <div class="rounded-3xl bg-slate-50 p-4">
                                    <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Pending orders</p>
                                    <p class="mt-3 text-2xl font-semibold text-slate-900">18</p>
                                    <p class="mt-2 text-sm text-slate-500">Patient requests and surgical order approvals</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mt-8 grid gap-6 lg:grid-cols-3">
                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-2xl">
                        <div class="flex items-center justify-between gap-3 text-slate-700">
                            <div>
                                <p class="text-xs uppercase tracking-[0.24em]">General Inventory</p>
                                <p class="mt-2 text-xl font-semibold text-slate-900">Ready stock</p>
                            </div>
                            <div class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-blue-500/10 text-blue-200">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="h-5 w-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M5 7v14h14V7M12 11v6" />
                                </svg>
                            </div>
                        </div>
                        <p class="mt-5 text-slate-500">View all general inventory items and stock alerts for ward supplies.</p>
                    </div>
                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-2xl">
                        <div class="flex items-center justify-between gap-3 text-slate-700">
                            <div>
                                <p class="text-xs uppercase tracking-[0.24em]">Medicine</p>
                                <p class="mt-2 text-xl font-semibold text-slate-900">Restock plan</p>
                            </div>
                            <div class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-700">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="h-5 w-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v8m4-4H8" />
                                </svg>
                            </div>
                        </div>
                        <p class="mt-5 text-slate-500">Quickly identify medicine categories that need replenishment.</p>
                    </div>
                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-2xl">
                        <div class="flex items-center justify-between gap-3 text-slate-700">
                            <div>
                                <p class="text-xs uppercase tracking-[0.24em]">Patients</p>
                                <p class="mt-2 text-xl font-semibold text-slate-900">Current cases</p>
                            </div>
                            <div class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-700">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="h-5 w-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 0 0-8 0v2m8-10a4 4 0 1 0-8 0 4 4 0 0 0 8 0Z" />
                                </svg>
                            </div>
                        </div>
                        <p class="mt-5 text-slate-500">See patients waiting for medications, injections, and surgical supplies.</p>
                    </div>
                </section>

                <section class="mt-8 rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl backdrop-blur-xl">
                    <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p class="text-sm uppercase tracking-[0.24em] text-slate-400">Activity feed</p>
                            <h3 class="mt-3 text-2xl font-semibold text-white">Recent actions</h3>
                        </div>
                        <a href="#" class="inline-flex items-center justify-center rounded-2xl bg-slate-900/90 px-4 py-2 text-sm text-slate-100 transition hover:bg-slate-800">View full report</a>
                    </div>
                    <div class="mt-6 grid gap-4 md:grid-cols-3">
                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                            <p class="text-sm uppercase tracking-[0.24em] text-slate-500">Surgical</p>
                            <p class="mt-3 text-lg font-semibold text-slate-900">Local purchase approved</p>
                            <p class="mt-2 text-sm text-slate-500">Order #S-348 has been added to stock.</p>
                        </div>
                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                            <p class="text-sm uppercase tracking-[0.24em] text-slate-500">Medicine</p>
                            <p class="mt-3 text-lg font-semibold text-slate-900">3 injection batches ready</p>
                            <p class="mt-2 text-sm text-slate-500">Jabs and infusion material are now available.</p>
                        </div>
                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                            <p class="text-sm uppercase tracking-[0.24em] text-slate-500">Patients</p>
                            <p class="mt-3 text-lg font-semibold text-slate-900">12 pending approval</p>
                            <p class="mt-2 text-sm text-slate-500">Patient requests waiting for ward nurse review.</p>
                        </div>
                    </div>
                </section>
            </main>
        </div>
</body>

</html>