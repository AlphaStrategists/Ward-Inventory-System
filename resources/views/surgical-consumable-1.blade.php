<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Surgical Consumable 1 - Ward Inventory System</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Reenie+Beanie&family=Dancing+Script:wght@600&display=swap" rel="stylesheet">
    
    <!-- FontAwesome for crisp icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Tailwind CSS CDN for instant styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        signature: ['"Dancing Script"', 'cursive'],
                        signatureAlt: ['"Reenie Beanie"', 'cursive'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-100 text-slate-800 font-sans min-h-screen antialiased selection:bg-blue-500 selection:text-white"
      x-data="surgicalConsumableApp()">

    <!-- Toast Notification Container -->
    <div class="fixed top-5 right-5 z-50 flex flex-col gap-2 pointer-events-none">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="toast.visible" 
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                 class="pointer-events-auto px-4 py-3 rounded-xl shadow-lg text-white font-medium text-sm flex items-center gap-3 min-w-[280px]"
                 :class="toast.type === 'success' ? 'bg-emerald-600' : 'bg-rose-600'">
                <i :class="toast.type === 'success' ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-exclamation'" class="text-lg"></i>
                <span x-text="toast.message"></span>
            </div>
        </template>
    </div>

    <!-- Layout Wrapper -->
    <div class="flex min-h-screen">

        <!-- ============================================================================ -->
        <!-- LEFT SIDEBAR -->
        <!-- ============================================================================ -->
        <aside class="w-80 bg-white border-r border-slate-200 shrink-0 flex flex-col h-screen sticky top-0 z-20">
            <!-- Brand & Title Header -->
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 text-xl font-bold shadow-sm">
                        <i class="fa-solid fa-flask"></i>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-slate-900 leading-tight">Ward Inventory</h1>
                        <p class="text-[11px] font-bold tracking-wider text-slate-400 uppercase">SURGICAL CONSUMABLE 1</p>
                    </div>
                </div>
                <a href="/" title="Back to Dashboard" class="text-slate-400 hover:text-slate-600 transition-colors p-1.5 rounded-lg hover:bg-slate-100">
                    <i class="fa-solid fa-house text-sm"></i>
                </a>
            </div>

            <!-- Navigation Links Bar -->
            <div class="px-4 py-2 border-b border-slate-100 bg-slate-50/60 flex items-center gap-1 text-xs">
                <span class="px-2.5 py-1 text-blue-600 font-bold bg-blue-50 rounded-lg">Consumable 1</span>
                <span class="text-slate-300">•</span>
                <a :href="`/${currentRole}/surgical-consumable-2`" class="px-2.5 py-1 text-slate-600 hover:text-blue-600 rounded-lg font-medium">Consumable 2</a>
                <span class="text-slate-300">•</span>
                <a :href="`/${currentRole}/local-purchases`" class="px-2.5 py-1 text-slate-600 hover:text-blue-600 rounded-lg font-medium">Local Purchases</a>
            </div>

            <!-- Search & Add Action -->
            <div class="p-4 space-y-3">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input type="text" 
                           x-model="sidebarSearch" 
                           placeholder="Search consumables..." 
                           class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-slate-700 placeholder-slate-400">
                </div>

                <template x-if="currentRole === 'admin'">
                    <button @click="showAddConsumableModal = true" 
                            class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold flex items-center justify-center gap-2 shadow-sm shadow-blue-500/30 transition-all duration-150 active:scale-[0.98]">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>Add Consumable</span>
                    </button>
                </template>
                <template x-if="currentRole !== 'admin'">
                    <div class="p-2.5 bg-slate-50 border border-slate-200/70 rounded-xl text-[11px] text-slate-500 flex items-center gap-2">
                        <i class="fa-solid fa-lock text-slate-400"></i>
                        <span>Catalog add restricted to Admin</span>
                    </div>
                </template>
            </div>

            <!-- Item List (Cards) -->
            <div class="flex-1 overflow-y-auto px-4 pb-4 space-y-2.5 custom-scrollbar">
                <template x-for="item in filteredConsumables" :key="item.id">
                    <div @click="selectConsumable(item)" 
                         class="p-3.5 rounded-xl border transition-all duration-150 cursor-pointer relative group flex items-center justify-between"
                         :class="selectedConsumable.id === item.id 
                                ? 'bg-blue-50/70 border-blue-200 shadow-sm border-l-4 border-l-blue-600' 
                                : 'bg-white border-slate-200/80 hover:border-slate-300 hover:bg-slate-50/80'">
                        
                        <div class="flex-1 min-w-0 pr-2">
                            <div class="flex items-center gap-2 mb-0.5">
                                <h3 class="font-bold text-sm text-slate-900 truncate uppercase" x-text="item.name"></h3>
                            </div>
                            
                            <!-- Low Stock Warning Label below item name -->
                            <template x-if="isLowStock(item)">
                                <div class="mb-1">
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-extrabold bg-red-100 text-red-700 border border-red-300 uppercase tracking-wide">
                                        <i class="fa-solid fa-triangle-exclamation text-[9px] text-red-600"></i>
                                        Low
                                    </span>
                                </div>
                            </template>
                            
                            <!-- Action Icons (Admin Only) -->
                            <template x-if="currentRole === 'admin'">
                                <div class="flex items-center gap-3 text-slate-400 opacity-60 group-hover:opacity-100 transition-opacity">
                                    <button @click.stop="openEditConsumableModal(item)" title="Edit item" class="hover:text-blue-600 transition-colors">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </button>
                                    <button @click.stop="deleteConsumable(item.id)" title="Delete item" class="hover:text-rose-600 transition-colors">
                                        <i class="fa-solid fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <!-- Stock Badge -->
                        <div class="shrink-0 text-right">
                            <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-semibold"
                                  :class="selectedConsumable.id === item.id ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600'"
                                  x-text="`${item.balance} ${item.unit}`">
                            </span>
                        </div>
                    </div>
                </template>

                <div x-show="filteredConsumables.length === 0" class="text-center py-8 text-slate-400 text-xs">
                    No consumables matching "<span x-text="sidebarSearch"></span>"
                </div>
            </div>

            <!-- Footer Stats -->
            <div class="p-4 border-t border-slate-100 bg-slate-50/50 text-xs text-slate-500 flex justify-between items-center">
                <span>Total Items: <strong class="text-slate-700" x-text="consumables.length"></strong></span>
                <span class="inline-flex items-center gap-1.5 text-emerald-600 font-medium">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Ward Stock Sync
                </span>
            </div>
        </aside>

        <!-- ============================================================================ -->
        <!-- MAIN CONTENT AREA -->
        <!-- ============================================================================ -->
        <main class="flex-1 p-8 overflow-y-auto space-y-6">

            <!-- Active Role Switcher Header -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Active View:</span>
                    <span class="px-3 py-1.5 rounded-lg bg-slate-100 border border-slate-200 text-xs font-bold capitalize" x-text="currentRole === 'ms' ? 'MS' : currentRole"></span>
                    <a href="/" class="text-xs text-blue-600 hover:underline">Change role</a>
                </div>

                <div class="flex items-center gap-2 text-xs">
                    <template x-if="currentRole === 'admin'">
                        <span class="px-3 py-1.5 rounded-xl bg-indigo-50 border border-indigo-200/70 text-indigo-700 font-semibold flex items-center gap-1.5">
                            <i class="fa-solid fa-shield-halved"></i>
                            Admin Mode: Full access to all actions across the system
                        </span>
                    </template>
                    <template x-if="currentRole === 'nurse'">
                        <span class="px-3 py-1.5 rounded-xl bg-teal-50 border border-teal-200/70 text-teal-700 font-semibold flex items-center gap-1.5">
                            <i class="fa-solid fa-clipboard-check"></i>
                            Nurse Mode: Add orders, update daily counts & stock adjustments (live date enabled)
                        </span>
                    </template>
                    <template x-if="currentRole === 'ms'">
                        <span class="px-3 py-1.5 rounded-xl bg-rose-50 border border-rose-200/70 text-rose-700 font-semibold flex items-center gap-1.5">
                            <i class="fa-solid fa-file-signature"></i>
                            MS Mode: Review & sign confirmation signatures for nurse requests & receipts
                        </span>
                    </template>
                    <template x-if="currentRole === 'pharmacist'">
                        <span class="px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200/70 text-amber-700 font-semibold flex items-center gap-1.5">
                            <i class="fa-solid fa-prescription-bottle-medical"></i>
                            Pharmacist Mode: Review pharmacy requisitions & monitor ward stock levels
                        </span>
                    </template>
                </div>
            </div>

            <!-- 1. Header Banner Card (Stock Banner) -->
            <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 rounded-2xl p-7 text-white shadow-xl shadow-blue-600/10 flex items-center justify-between relative overflow-hidden">
                <!-- Background decorative glow circle -->
                <div class="absolute -right-10 -bottom-10 w-60 h-60 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>

                <div>
                    <h2 class="text-3xl font-extrabold uppercase tracking-tight drop-shadow-sm" x-text="selectedConsumable.name || 'SURGICAL CONSUMABLE 1'"></h2>

                    <p class="text-blue-100 text-sm font-medium mt-1">Current Ward Stock Balance</p>
                </div>

                <!-- Right Corner Stock Pill -->
                <div class="bg-white rounded-2xl px-6 py-4 shadow-lg flex flex-col items-center justify-center shrink-0 min-w-[140px] text-center border border-white/20">
                    <span class="text-2xl font-black text-emerald-600 tracking-tight" x-text="`${selectedConsumable.balance} ${selectedConsumable.unit}`"></span>
                    <span class="text-[11px] font-bold text-emerald-600 tracking-wider uppercase mt-0.5">AVAILABLE</span>
                </div>
            </div>

            <!-- 2. Navigation Tabs & Search/Filter Controls Bar -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                
                <!-- Left Tabs -->
                <div class="flex items-center gap-2 border-b md:border-b-0 border-slate-100 pb-2 md:pb-0">
                    <button @click="activeTab = 'requisitions'" 
                            class="px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-150"
                            :class="activeTab === 'requisitions' ? 'bg-slate-100 text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'">
                        Pharmacy Orders (<span x-text="filteredRequisitions.length"></span>)
                    </button>
                    <button @click="activeTab = 'daily_counts'" 
                            class="px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-150 flex items-center gap-1.5"
                            :class="activeTab === 'daily_counts' ? 'bg-blue-50 text-blue-800 shadow-xs border border-blue-200/70 font-bold' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'">
                        <i class="fa-solid fa-clipboard-check text-xs text-blue-600"></i>
                        <span>Daily Count (<span x-text="filteredDailyCounts.length"></span>)</span>
                    </button>
                    <button @click="activeTab = 'adjustments'" 
                            class="px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-150 flex items-center gap-1.5"
                            :class="activeTab === 'adjustments' ? 'bg-amber-50 text-amber-800 shadow-xs border border-amber-200/70 font-bold' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'">
                        <i class="fa-solid fa-sliders text-xs text-amber-600"></i>
                        <span>Stock Adjustments (<span x-text="filteredAdjustments.length"></span>)</span>
                    </button>
                </div>

                <!-- Right Search, Date Filter & Clear Controls -->
                <div class="flex items-center gap-3">
                    <div class="relative flex-1 md:w-56">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" 
                               x-model="logSearch" 
                               placeholder="Search records..." 
                               class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-slate-700 placeholder-slate-400">
                    </div>

                    <div class="relative">
                        <input type="date" 
                               x-model="logDateFilter" 
                               class="py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>

                    <button @click="clearLogFilters()" 
                            class="px-3.5 py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-colors">
                        <i class="fa-solid fa-xmark text-xs"></i>
                        <span>Clear</span>
                    </button>

                    <button @click="printRequisitionReport()" 
                            title="Print Requisition Log"
                            class="px-3 py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-colors">
                        <i class="fa-solid fa-print"></i>
                    </button>
                </div>
            </div>

            <!-- 3. Pharmacy Requisitions Log Card (Table Section) -->
            <div x-show="activeTab === 'requisitions'" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <!-- Header -->
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Pharmacy Requisitions Log</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Showing pharmacy requests & supply receipt verification records</p>
                    </div>

                    <template x-if="currentRole === 'admin' || currentRole === 'nurse'">
                        <button @click="openAddOrderModal()" 
                                class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl flex items-center gap-2 shadow-sm shadow-blue-500/30 transition-all active:scale-[0.98]">
                            <i class="fa-solid fa-plus text-xs"></i>
                            <span>Add Order</span>
                        </button>
                    </template>
                    <template x-if="currentRole === 'ms'">
                        <span class="px-3.5 py-2 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-xs font-bold flex items-center gap-2 shadow-xs">
                            <i class="fa-solid fa-signature"></i>
                            <span>MS Approval & Authorization Mode</span>
                        </span>
                    </template>
                    <template x-if="currentRole === 'pharmacist'">
                        <span class="px-3.5 py-2 bg-amber-50 border border-amber-200 text-amber-700 rounded-xl text-xs font-bold flex items-center gap-2 shadow-xs">
                            <i class="fa-solid fa-prescription-bottle-medical"></i>
                            <span>Pharmacist Review Mode</span>
                        </span>
                    </template>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200/70 text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">
                                <th class="py-3.5 px-6">Date</th>
                                <th class="py-3.5 px-6">Name of the First Aid Supplies</th>
                                <th class="py-3.5 px-6">Balance</th>
                                <th class="py-3.5 px-6">Requested Quantity</th>
                                <th class="py-3.5 px-6">Confirmation Signature (Request)</th>
                                <th class="py-3.5 px-6">Received Quantity</th>
                                <th class="py-3.5 px-6">Confirmation Signature (Receive)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                            <template x-for="log in filteredRequisitions" :key="log.id">
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <!-- Date -->
                                    <td class="py-4 px-6 font-medium text-slate-900 whitespace-nowrap" x-text="log.date"></td>
                                    
                                    <!-- Name of First Aid Supplies -->
                                    <td class="py-4 px-6 font-bold text-slate-900">
                                        <span x-text="log.supplyName"></span>
                                    </td>

                                    <!-- Balance -->
                                    <td class="py-4 px-6 font-semibold text-slate-600">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700" x-text="log.balance"></span>
                                    </td>

                                    <!-- Requested Quantity -->
                                    <td class="py-4 px-6 font-bold text-blue-700">
                                        <span class="bg-blue-50 border border-blue-200/60 px-2.5 py-1 rounded-lg" x-text="log.requestedQty"></span>
                                    </td>

                                    <!-- Confirmation Signature (Request) -->
                                    <td class="py-4 px-6">
                                        <!-- If signed -->
                                        <template x-if="isRequestConfirmed(log)">
                                            <div @click="inspectSignature(log.requestSignature, 'Request Confirmation', log.requestedBy)" 
                                                 class="cursor-pointer group flex items-center gap-2.5 bg-slate-50 hover:bg-blue-50 border border-slate-200 hover:border-blue-200 p-2 rounded-xl transition-all">
                                                <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center font-signature text-blue-600 text-lg overflow-hidden shrink-0 shadow-xs">
                                                    <span x-text="getInitials(log.requestedBy)"></span>
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="font-signature text-base text-slate-900 leading-tight truncate group-hover:text-blue-700" x-text="log.requestSignature"></p>
                                                    <p class="text-[10px] text-slate-400 truncate" x-text="log.requestedBy"></p>
                                                </div>
                                                <template x-if="currentRole === 'ms' || currentRole === 'admin'">
                                                    <button @click.stop="openSignModal(log, 'request')" title="Update Signature" class="ml-auto text-slate-400 hover:text-blue-600">
                                                        <i class="fa-solid fa-pen text-[10px]"></i>
                                                    </button>
                                                </template>
                                                <template x-if="currentRole !== 'ms' && currentRole !== 'admin'">
                                                    <i class="fa-solid fa-signature text-slate-300 group-hover:text-blue-500 text-xs ml-auto"></i>
                                                </template>
                                            </div>
                                        </template>

                                        <!-- If NOT confirmed yet -->
                                        <template x-if="!isRequestConfirmed(log)">
                                            <div>
                                                <template x-if="currentRole === 'ms' || currentRole === 'admin'">
                                                    <button @click="openSignModal(log, 'request')" 
                                                            class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-sm shadow-rose-500/30 transition-all active:scale-95 animate-pulse">
                                                        <i class="fa-solid fa-signature"></i>
                                                        <span>Sign Request (MS)</span>
                                                    </button>
                                                </template>
                                                <template x-if="currentRole === 'nurse' || currentRole === 'pharmacist'">
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs bg-amber-50 border border-amber-200 text-amber-700 font-semibold">
                                                        <i class="fa-solid fa-clock"></i>
                                                        <span>Pending MS Approval</span>
                                                    </span>
                                                </template>
                                            </div>
                                        </template>
                                    </td>

                                    <!-- Received Quantity -->
                                    <td class="py-4 px-6 font-bold text-emerald-700">
                                        <template x-if="log.receivedQty && log.receivedQty.trim()">
                                            <div class="flex items-center gap-2">
                                                <span class="bg-emerald-50 border border-emerald-200/60 px-2.5 py-1 rounded-lg" x-text="log.receivedQty"></span>
                                                <template x-if="currentRole === 'nurse' || currentRole === 'admin'">
                                                    <button @click="openUpdateReceivedModal(log)" title="Update Received Quantity" class="text-slate-400 hover:text-emerald-600">
                                                        <i class="fa-solid fa-pen text-[10px]"></i>
                                                    </button>
                                                </template>
                                            </div>
                                        </template>
                                        <template x-if="!log.receivedQty || !log.receivedQty.trim()">
                                            <div>
                                                <template x-if="currentRole === 'nurse' || currentRole === 'admin'">
                                                    <button @click="openUpdateReceivedModal(log)" 
                                                            class="px-2.5 py-1.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-xs transition-all active:scale-95">
                                                        <i class="fa-solid fa-box-open"></i>
                                                        <span>Update Received</span>
                                                    </button>
                                                </template>
                                                <template x-if="currentRole === 'ms'">
                                                    <span class="text-slate-400 italic text-[11px]">
                                                        <i class="fa-solid fa-hourglass-start mr-1"></i>Awaiting Nurse
                                                    </span>
                                                </template>
                                            </div>
                                        </template>
                                    </td>

                                    <!-- Confirmation Signature (Receive) -->
                                    <td class="py-4 px-6">
                                        <!-- If signed -->
                                        <template x-if="isReceiveConfirmed(log)">
                                            <div @click="inspectSignature(log.receiveSignature, 'Receive Confirmation', log.receivedBy)" 
                                                 class="cursor-pointer group flex items-center gap-2.5 bg-slate-50 hover:bg-emerald-50 border border-slate-200 hover:border-emerald-200 p-2 rounded-xl transition-all">
                                                <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center font-signature text-emerald-600 text-lg overflow-hidden shrink-0 shadow-xs">
                                                    <span x-text="getInitials(log.receivedBy)"></span>
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="font-signature text-base text-slate-900 leading-tight truncate group-hover:text-emerald-700" x-text="log.receiveSignature"></p>
                                                    <p class="text-[10px] text-slate-400 truncate" x-text="log.receivedBy"></p>
                                                </div>
                                                <template x-if="currentRole === 'ms' || currentRole === 'admin'">
                                                    <button @click.stop="openSignModal(log, 'receive')" title="Update Signature" class="ml-auto text-slate-400 hover:text-emerald-600">
                                                        <i class="fa-solid fa-pen text-[10px]"></i>
                                                    </button>
                                                </template>
                                                <template x-if="currentRole !== 'ms' && currentRole !== 'admin'">
                                                    <i class="fa-solid fa-signature text-slate-300 group-hover:text-emerald-500 text-xs ml-auto"></i>
                                                </template>
                                            </div>
                                        </template>

                                        <!-- If NOT confirmed yet -->
                                        <template x-if="!isReceiveConfirmed(log)">
                                            <div>
                                                <!-- If quantity received has been entered by nurse -->
                                                <template x-if="log.receivedQty && log.receivedQty.trim()">
                                                    <div>
                                                        <template x-if="currentRole === 'ms' || currentRole === 'admin'">
                                                            <button @click="openSignModal(log, 'receive')" 
                                                                    class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-sm shadow-rose-500/30 transition-all active:scale-95 animate-pulse">
                                                                <i class="fa-solid fa-signature"></i>
                                                                <span>Sign Receive (MS)</span>
                                                            </button>
                                                        </template>
                                                        <template x-if="currentRole === 'nurse' || currentRole === 'pharmacist'">
                                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs bg-amber-50 border border-amber-200 text-amber-700 font-semibold">
                                                                <i class="fa-solid fa-clock"></i>
                                                                <span>Pending MS Approval</span>
                                                            </span>
                                                        </template>
                                                    </div>
                                                </template>

                                                <!-- If quantity received has NOT been entered yet -->
                                                <template x-if="!log.receivedQty || !log.receivedQty.trim()">
                                                    <span class="text-slate-400 text-[11px] italic">
                                                        Receive after quantity updated
                                                    </span>
                                                </template>
                                            </div>
                                        </template>
                                    </td>

                                </tr>
                            </template>

                            <tr x-show="filteredRequisitions.length === 0">
                                <td colspan="7" class="text-center py-12 text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i class="fa-solid fa-folder-open text-3xl text-slate-300"></i>
                                        <p class="text-sm font-medium">No pharmacy requisition records found</p>
                                        <p class="text-xs text-slate-400">Try adjusting your search terms or date filter</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Pagination -->
                <div class="p-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between text-xs text-slate-500">
                    <div>
                        Showing <strong class="text-slate-800" x-text="filteredRequisitions.length"></strong> records
                    </div>
                    <div class="flex items-center gap-2">
                        <button class="px-3 py-1.5 border border-slate-200 rounded-lg bg-white hover:bg-slate-50 disabled:opacity-50 text-slate-600 font-semibold" disabled>Previous</button>
                        <button class="px-3 py-1.5 border border-slate-200 rounded-lg bg-white hover:bg-slate-50 disabled:opacity-50 text-slate-600 font-semibold" disabled>Next</button>
                    </div>
                </div>
            </div>

            <!-- 4. Daily Count Log Card -->
            <div x-show="activeTab === 'daily_counts'" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <!-- Header -->
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Daily Count Log</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Daily ward consumption, quantity given, balance, and sister's initials verification</p>
                    </div>

                    <template x-if="currentRole === 'admin' || currentRole === 'nurse'">
                        <button @click="openAddDailyCountModal()" 
                                class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl flex items-center gap-2 shadow-sm shadow-blue-500/30 transition-all active:scale-[0.98]">
                            <i class="fa-solid fa-plus text-xs"></i>
                            <span>Record Daily Count</span>
                        </button>
                    </template>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200/70 text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">
                                <th class="py-3.5 px-6">Date</th>
                                <th class="py-3.5 px-6">Item Name</th>
                                <th class="py-3.5 px-6">Qty Given</th>
                                <th class="py-3.5 px-6">Balance</th>
                                <th class="py-3.5 px-6">Sister's Initials</th>
                                <th class="py-3.5 px-6">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                            <template x-for="count in filteredDailyCounts" :key="count.id">
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <!-- Date -->
                                    <td class="py-4 px-6 font-medium text-slate-900 whitespace-nowrap" x-text="count.date"></td>

                                    <!-- Item Name -->
                                    <td class="py-4 px-6 font-bold text-slate-900" x-text="count.item_name || count.itemName"></td>

                                    <!-- Qty Given -->
                                    <td class="py-4 px-6 font-bold text-blue-700">
                                        <span class="bg-blue-50 border border-blue-200/60 px-2.5 py-1 rounded-lg" x-text="count.qty_given || count.qtyGiven"></span>
                                    </td>

                                    <!-- Balance -->
                                    <td class="py-4 px-6 font-semibold text-slate-600">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700" x-text="count.balance"></span>
                                    </td>

                                    <!-- Sister's Initials -->
                                    <td class="py-4 px-6">
                                        <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200/70">
                                            <i class="fa-solid fa-user-nurse text-blue-600 text-xs"></i>
                                            <span class="font-bold text-slate-800 tracking-wider font-mono text-xs" x-text="count.sisters_initials || count.sistersInitials"></span>
                                        </div>
                                    </td>

                                    <!-- Remarks -->
                                    <td class="py-4 px-6 text-slate-600 max-w-xs">
                                        <p class="truncate" :title="count.remarks" x-text="count.remarks || '—'"></p>
                                    </td>
                                </tr>
                            </template>

                            <tr x-show="filteredDailyCounts.length === 0">
                                <td colspan="6" class="text-center py-12 text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i class="fa-solid fa-clipboard-list text-3xl text-slate-300"></i>
                                        <p class="text-sm font-medium">No daily count records found</p>
                                        <p class="text-xs text-slate-400">Try adjusting your filters or click 'Record Daily Count' to add one</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Pagination -->
                <div class="p-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between text-xs text-slate-500">
                    <div>
                        Showing <strong class="text-slate-800" x-text="filteredDailyCounts.length"></strong> daily count records
                    </div>
                    <div class="flex items-center gap-2">
                        <button class="px-3 py-1.5 border border-slate-200 rounded-lg bg-white hover:bg-slate-50 disabled:opacity-50 text-slate-600 font-semibold" disabled>Previous</button>
                        <button class="px-3 py-1.5 border border-slate-200 rounded-lg bg-white hover:bg-slate-50 disabled:opacity-50 text-slate-600 font-semibold" disabled>Next</button>
                    </div>
                </div>
            </div>

            <!-- 5. Stock Adjustments Card -->
            <div x-show="activeTab === 'adjustments'" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <!-- Header -->
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Stock Adjustments Log</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Physical audit counts, damaged, expired, or returned stock records</p>
                    </div>

                    <template x-if="currentRole === 'admin' || currentRole === 'nurse'">
                        <button @click="openAddAdjustmentModal()" 
                                class="px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-xl flex items-center gap-2 shadow-sm shadow-amber-500/30 transition-all active:scale-[0.98]">
                            <i class="fa-solid fa-sliders text-xs"></i>
                            <span>New Stock Adjustment</span>
                        </button>
                    </template>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200/70 text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">
                                <th class="py-3.5 px-6">Date</th>
                                <th class="py-3.5 px-6">Item Name</th>
                                <th class="py-3.5 px-6">Type</th>
                                <th class="py-3.5 px-6">Quantity</th>
                                <th class="py-3.5 px-6">Balance</th>
                                <th class="py-3.5 px-6">Reasons / Notes</th>
                                <th class="py-3.5 px-6">Adjusted By</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                            <template x-for="adj in filteredAdjustments" :key="adj.id">
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <!-- Date -->
                                    <td class="py-4 px-6 font-medium text-slate-900 whitespace-nowrap" x-text="adj.date"></td>

                                    <!-- Item Name -->
                                    <td class="py-4 px-6 font-bold text-slate-900" x-text="adj.item_name || adj.itemName"></td>

                                    <!-- Type -->
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold"
                                              :class="getAdjustmentTypeBadgeClass(adj.type)">
                                            <i :class="getAdjustmentTypeIcon(adj.type)" class="text-[10px]"></i>
                                            <span x-text="formatAdjustmentType(adj.type)"></span>
                                        </span>
                                    </td>

                                    <!-- Quantity -->
                                    <td class="py-4 px-6 font-bold">
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold"
                                              :class="adj.quantity.toString().startsWith('-') ? 'bg-rose-50 text-rose-700 border border-rose-200/70' : 'bg-emerald-50 text-emerald-700 border border-emerald-200/70'"
                                              x-text="adj.quantity"></span>
                                    </td>

                                    <!-- Balance -->
                                    <td class="py-4 px-6 font-semibold text-slate-600">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700" x-text="adj.balance"></span>
                                    </td>

                                    <!-- Reasons / Notes -->
                                    <td class="py-4 px-6 text-slate-600 max-w-xs">
                                        <p class="truncate" :title="adj.reason" x-text="adj.reason"></p>
                                    </td>

                                    <!-- Adjusted By -->
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-[10px] font-bold">
                                                <i class="fa-solid fa-user-nurse"></i>
                                            </div>
                                            <span class="font-medium text-slate-800" x-text="adj.adjusted_by || adj.adjustedBy"></span>
                                        </div>
                                    </td>

                                </tr>
                            </template>

                            <tr x-show="filteredAdjustments.length === 0">
                                <td colspan="7" class="text-center py-12 text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i class="fa-solid fa-sliders text-3xl text-slate-300"></i>
                                        <p class="text-sm font-medium">No stock adjustment records found</p>
                                        <p class="text-xs text-slate-400">Try adjusting your filters or click 'New Stock Adjustment' to record one</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Pagination -->
                <div class="p-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between text-xs text-slate-500">
                    <div>
                        Showing <strong class="text-slate-800" x-text="filteredAdjustments.length"></strong> adjustments
                    </div>
                    <div class="flex items-center gap-2">
                        <button class="px-3 py-1.5 border border-slate-200 rounded-lg bg-white hover:bg-slate-50 disabled:opacity-50 text-slate-600 font-semibold" disabled>Previous</button>
                        <button class="px-3 py-1.5 border border-slate-200 rounded-lg bg-white hover:bg-slate-50 disabled:opacity-50 text-slate-600 font-semibold" disabled>Next</button>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- ============================================================================ -->
    <!-- MODAL: ADD SURGICAL CONSUMABLE -->
    <!-- ============================================================================ -->
    <div x-show="showAddConsumableModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        
        <div @click.outside="showAddConsumableModal = false" 
             x-show="showAddConsumableModal"
             x-transition:enter="transition ease-out duration-200 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white rounded-3xl max-w-md w-full shadow-2xl overflow-hidden border border-slate-100">
            
            <div class="p-6 bg-slate-50 border-b border-slate-200/70 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-lg shadow-sm shadow-blue-500/30">
                        <i class="fa-solid fa-flask"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Add Consumable</h3>
                        <p class="text-xs text-slate-500">Register new item and initial stock</p>
                    </div>
                </div>
                <button @click="showAddConsumableModal = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form @submit.prevent="submitNewConsumable()" class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Item Name *</label>
                    <input type="text" x-model="newConsumable.name" placeholder="e.g. GAUZE ROLLS 4 INCH" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Current Stock Balance *</label>
                        <input type="number" min="0" x-model="newConsumable.balance" placeholder="0" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Unit *</label>
                        <input type="text" x-model="newConsumable.unit" placeholder="Pcs / Roll / Box" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" @click="showAddConsumableModal = false" class="px-4 py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-xl text-xs font-semibold">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-blue-500/30">Add Item</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================================ -->
    <!-- MODAL 1: ADD PHARMACY ORDER / REQUISITION -->
    <!-- ============================================================================ -->
    <div x-show="showAddOrderModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        
        <div @click.outside="showAddOrderModal = false" 
             x-show="showAddOrderModal"
             x-transition:enter="transition ease-out duration-200 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150 transform"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl overflow-hidden border border-slate-100">
            
            <!-- Modal Header -->
            <div class="p-6 bg-slate-50 border-b border-slate-200/70 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-lg shadow-sm shadow-blue-500/30">
                        <i class="fa-solid fa-file-signature"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">New Pharmacy Requisition</h3>
                        <p class="text-xs text-slate-500">Record a new first aid supply request & receiving verification</p>
                    </div>
                </div>

                <button @click="showAddOrderModal = false" class="text-slate-400 hover:text-slate-600 p-2 rounded-xl hover:bg-slate-200/60 transition-colors">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <!-- Modal Form -->
            <form @submit.prevent="submitNewOrder()" class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Date -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Date *</label>
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200/60">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Live Date
                            </span>
                        </div>
                        <input type="date" x-model="newOrder.date" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-medium">
                    </div>

                    <!-- Supply Name -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Name of First Aid Supplies *</label>
                        <select x-model="newOrder.supplyName" @change="updateOrderBalanceFromSupply()" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-medium">
                            <template x-for="item in consumables" :key="item.id">
                                <option :value="item.name" x-text="`${item.name} (${item.balance} ${item.unit})`"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Balance -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Current Stock Balance</label>
                        <input type="text" x-model="newOrder.balance" required readonly class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700">
                    </div>

                    <!-- Requested Qty -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Requested Quantity *</label>
                        <input type="text" x-model="newOrder.requestedQty" placeholder="e.g. 10 Box" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>

                    <!-- Requester Nurse Name -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Requested By (Nurse Name) *</label>
                        <input type="text" x-model="newOrder.requestedBy" placeholder="e.g. Nurse Kamani (RN-308)" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>
                </div>

                <!-- Nurse Info Box -->
                <template x-if="currentRole === 'nurse'">
                    <div class="p-3.5 bg-blue-50/80 border border-blue-200/80 rounded-2xl text-xs text-blue-800 space-y-1">
                        <div class="flex items-center gap-1.5 font-bold">
                            <i class="fa-solid fa-circle-info text-blue-600"></i>
                            <span>Nurse Order Workflow</span>
                        </div>
                        <p class="text-blue-700 text-[11px] leading-relaxed">
                            Once submitted with today's live date, the MS will sign the <strong>Confirmation Signature (Request)</strong>. When supplies are physically delivered to the ward, you can update the <strong>Received Quantity</strong>.
                        </p>
                    </div>
                </template>

                <!-- Admin Signature Section (Shown for Admin Only) -->
                <template x-if="currentRole === 'admin'">
                    <div>
                        <hr class="border-slate-100 my-2">
                        <p class="text-[11px] font-bold text-indigo-700 uppercase tracking-wider mb-2">Admin Advanced Initializations (Optional)</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Request Confirmation Signature -->
                            <div class="bg-blue-50/50 p-4 rounded-2xl border border-blue-100">
                                <label class="block text-xs font-extrabold text-blue-900 uppercase tracking-wider mb-1">
                                    <i class="fa-solid fa-file-pen text-blue-600 mr-1"></i> Confirmation Signature (Request)
                                </label>
                                <input type="text" x-model="newOrder.requestSignature" placeholder="Digital Signature (e.g. M. Perera)" class="w-full px-3 py-2 bg-white border border-blue-200 rounded-xl text-xs font-signature text-lg text-blue-800 focus:ring-2 focus:ring-blue-500/20">
                            </div>

                            <!-- Receive Confirmation Signature -->
                            <div class="bg-emerald-50/50 p-4 rounded-2xl border border-emerald-100">
                                <label class="block text-xs font-extrabold text-emerald-900 uppercase tracking-wider mb-1">
                                    <i class="fa-solid fa-signature text-emerald-600 mr-1"></i> Initial Received Qty (Optional)
                                </label>
                                <input type="text" x-model="newOrder.receivedQty" placeholder="e.g. 10 Box" class="w-full px-3 py-2 bg-white border border-emerald-200 rounded-xl text-xs mb-2">
                                <input type="text" x-model="newOrder.receiveSignature" placeholder="Receive Signature" class="w-full px-3 py-2 bg-white border border-emerald-200 rounded-xl text-xs font-signature text-lg text-emerald-800 focus:ring-2 focus:ring-emerald-500/20">
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Buttons -->
                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" @click="showAddOrderModal = false" class="px-5 py-2.5 border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-xl text-xs font-semibold">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/30">
                        Save Order Record
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================================ -->
    <!-- MODAL 2: ADD NEW SURGICAL CONSUMABLE ITEM -->
    <!-- ============================================================================ -->
    <div x-show="showAddConsumableModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        
        <div @click.outside="showAddConsumableModal = false" 
             x-show="showAddConsumableModal"
             x-transition:enter="transition ease-out duration-200 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white rounded-3xl max-w-md w-full shadow-2xl overflow-hidden border border-slate-100">
            
            <div class="p-6 bg-slate-50 border-b border-slate-200/70 flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-900">Add Surgical Consumable</h3>
                <button @click="showAddConsumableModal = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form @submit.prevent="submitNewConsumable()" class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Item Name</label>
                    <input type="text" x-model="newConsumable.name" placeholder="e.g. SURGICAL GLOVES 7.5" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Current Stock Balance</label>
                        <input type="number" x-model="newConsumable.balance" placeholder="30" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Unit</label>
                        <input type="text" x-model="newConsumable.unit" placeholder="Box / Pcs / Roll" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium">
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-end gap-3">
                    <button type="button" @click="showAddConsumableModal = false" class="px-4 py-2 border border-slate-200 text-slate-600 rounded-xl text-xs font-semibold">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold shadow-sm">Add Item</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================================ -->
    <!-- MODAL 3: SIGNATURE INSPECTION MODAL -->
    <!-- ============================================================================ -->
    <div x-show="showSignatureModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        
        <div @click.outside="showSignatureModal = false" 
             class="bg-white rounded-3xl max-w-sm w-full shadow-2xl p-6 text-center border border-slate-100">
            
            <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 mx-auto flex items-center justify-center text-xl font-bold mb-3 border border-emerald-100">
                <i class="fa-solid fa-shield-check"></i>
            </div>

            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider" x-text="inspectedSignatureType"></h4>
            <h3 class="text-xl font-bold text-slate-900 mt-1" x-text="inspectedOfficerName"></h3>

            <div class="my-6 p-6 bg-slate-50 rounded-2xl border border-slate-200/80 flex items-center justify-center">
                <span class="font-signature text-4xl text-blue-900 tracking-wide select-none" x-text="inspectedSignatureText"></span>
            </div>

            <div class="bg-emerald-50/80 text-emerald-800 text-[11px] font-semibold py-2 px-3 rounded-xl flex items-center justify-center gap-1.5 mb-5">
                <i class="fa-solid fa-circle-check text-emerald-600"></i>
                <span>Cryptographically Verified & Timestamped</span>
            </div>

            <button @click="showSignatureModal = false" class="w-full py-2.5 bg-slate-900 text-white text-xs font-bold rounded-xl hover:bg-slate-800 transition-colors">
                Close Verification View
            </button>
        </div>
    </div>

    <!-- ============================================================================ -->
    <!-- MODAL 4: ADD STOCK ADJUSTMENT -->
    <!-- ============================================================================ -->
    <div x-show="showAddAdjustmentModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        
        <div @click.outside="showAddAdjustmentModal = false" 
             x-show="showAddAdjustmentModal"
             x-transition:enter="transition ease-out duration-200 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150 transform"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-3xl max-w-xl w-full shadow-2xl overflow-hidden border border-slate-100">
            
            <!-- Header -->
            <div class="p-6 bg-amber-500/10 border-b border-amber-500/20 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-600 text-white flex items-center justify-center font-bold text-lg shadow-sm shadow-amber-500/30">
                        <i class="fa-solid fa-sliders"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">New Stock Adjustment</h3>
                        <p class="text-xs text-slate-500">Record inventory audit surplus/deficit, damage, or returns</p>
                    </div>
                </div>

                <button @click="showAddAdjustmentModal = false" class="text-slate-400 hover:text-slate-600 p-2 rounded-xl hover:bg-slate-200/60 transition-colors">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="submitNewAdjustment()" class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Date -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Date *</label>
                        <input type="date" x-model="newAdjustment.date" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 font-medium">
                    </div>

                    <!-- Item Name -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Item *</label>
                        <select x-model="newAdjustment.item_name" @change="onAdjustmentItemChange()" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 font-medium">
                            <template x-for="item in consumables" :key="item.id">
                                <option :value="item.name" x-text="item.name + ' (Bal: ' + item.balance + ' ' + item.unit + ')'"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Adjustment Type -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Adjustment Type *</label>
                        <select x-model="newAdjustment.type" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 font-medium">
                            <option value="COUNT_CORRECTION_ADD">Audit Surplus (+) Increase Stock</option>
                            <option value="COUNT_CORRECTION_SUB">Audit Deficit (-) Decrease Stock</option>
                            <option value="DAMAGED">Damaged / Broken (-) Decrease Stock</option>
                            <option value="EXPIRY">Expired Stock (-) Discarded</option>
                            <option value="RETURN">Return to Pharmacy / Supplier (-)</option>
                            <option value="LOSS">Missing / Lost Stock (-)</option>
                        </select>
                    </div>

                    <!-- Quantity -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Quantity *</label>
                        <div class="relative">
                            <input type="number" min="1" step="1" x-model="newAdjustment.amount" required placeholder="e.g. 5" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 font-bold">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400" x-text="getCurrentItemUnit()"></span>
                        </div>
                    </div>
                </div>

                <!-- Projected Balance Preview -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/70 flex items-center justify-between text-xs">
                    <span class="text-slate-500">Current Balance: <strong class="text-slate-800" x-text="getCurrentItemBalance()"></strong></span>
                    <i class="fa-solid fa-arrow-right text-slate-400 text-xs"></i>
                    <span class="text-slate-500">Projected Balance: <strong class="text-amber-700 font-bold" x-text="getProjectedAdjustmentBalance()"></strong></span>
                </div>

                <!-- Reasons / Notes -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Reasons / Notes *</label>
                    <textarea x-model="newAdjustment.reason" rows="2" required placeholder="Specify why the stock is being adjusted (e.g. audit recount, torn packaging, expired batch)..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500"></textarea>
                </div>

                <!-- Adjusted By -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Adjusted By *</label>
                    <input type="text" x-model="newAdjustment.adjusted_by" required placeholder="e.g. Sr. Perera (RN-402)" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 font-medium">
                </div>

                <!-- Actions -->
                <div class="pt-2 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" @click="showAddAdjustmentModal = false" class="px-4 py-2.5 border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-xl text-xs font-semibold transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-amber-500/30 transition-all active:scale-[0.98]">
                        Save Adjustment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================================ -->
    <!-- MODAL 4: RECORD DAILY COUNT -->
    <!-- ============================================================================ -->
    <div x-show="showAddDailyCountModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        
        <div @click.outside="showAddDailyCountModal = false" 
             x-show="showAddDailyCountModal"
             x-transition:enter="transition ease-out duration-200 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150 transform"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-3xl max-w-lg w-full shadow-2xl overflow-hidden border border-slate-100">
            
            <!-- Modal Header -->
            <div class="p-6 bg-slate-50 border-b border-slate-200/70 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-lg shadow-sm shadow-blue-500/30">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Record Daily Count</h3>
                        <p class="text-xs text-slate-500">Log daily ward dispensing & update remaining balance</p>
                    </div>
                </div>

                <button @click="showAddDailyCountModal = false" class="w-8 h-8 rounded-full hover:bg-slate-200 text-slate-400 hover:text-slate-600 flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="submitNewDailyCount()" class="p-6 space-y-4">
                <!-- Date -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Date *</label>
                    <input type="date" x-model="newDailyCount.date" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-medium">
                </div>

                <!-- Item Selection -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Item Name *</label>
                    <select x-model="newDailyCount.item_name" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-semibold text-slate-800">
                        <template x-for="item in consumables" :key="item.id">
                            <option :value="item.name" x-text="`${item.name} (${item.balance} ${item.unit} available)`"></option>
                        </template>
                    </select>
                </div>

                <!-- Qty Given -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Quantity Given *</label>
                    <div class="relative">
                        <input type="number" min="1" step="1" x-model="newDailyCount.qty_given" required placeholder="e.g. 2" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-bold">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400" x-text="getCurrentDailyUnit()"></span>
                    </div>
                </div>

                <!-- Projected Balance Preview -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/70 flex items-center justify-between text-xs">
                    <span class="text-slate-500">Current Balance: <strong class="text-slate-800" x-text="getCurrentDailyBalance()"></strong></span>
                    <i class="fa-solid fa-arrow-right text-slate-400 text-xs"></i>
                    <span class="text-slate-500">Projected Balance: <strong class="text-blue-700 font-bold" x-text="getProjectedDailyCountBalance()"></strong></span>
                </div>

                <!-- Sister's Initials -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Sister's Initials *</label>
                    <input type="text" x-model="newDailyCount.sisters_initials" required placeholder="e.g. K.P." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-bold uppercase tracking-wider">
                </div>

                <!-- Remarks -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Remarks</label>
                    <input type="text" x-model="newDailyCount.remarks" placeholder="e.g. Ward routine dispensing, morning shift round..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-medium">
                </div>

                <!-- Actions -->
                <div class="pt-2 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" @click="showAddDailyCountModal = false" class="px-4 py-2.5 border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-xl text-xs font-semibold transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-blue-500/30 transition-all active:scale-[0.98]">
                        Save Daily Count
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================================ -->
    <!-- MODAL 5: MS CONFIRMATION SIGNATURE -->
    <!-- ============================================================================ -->
    <div x-show="showSignConfirmationModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        
        <div @click.outside="showSignConfirmationModal = false" 
             x-show="showSignConfirmationModal"
             x-transition:enter="transition ease-out duration-200 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white rounded-3xl max-w-md w-full shadow-2xl overflow-hidden border border-slate-100">
            
            <div class="p-6 bg-rose-50 border-b border-rose-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center font-bold text-lg shadow-sm shadow-rose-500/30">
                        <i class="fa-solid fa-signature"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900" x-text="signModalTarget.type === 'request' ? 'Confirmation Signature (Request)' : 'Confirmation Signature (Receive)'"></h3>
                        <p class="text-xs text-rose-600 font-medium">MS (Medical Superintendent) Authorization</p>
                    </div>
                </div>
                <button @click="showSignConfirmationModal = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form @submit.prevent="submitConfirmationSignature()" class="p-6 space-y-4">
                <div class="p-3 bg-slate-50 border border-slate-200/70 rounded-xl space-y-1.5 text-xs">
                    <div class="flex justify-between text-slate-500">
                        <span>First Aid Supply:</span>
                        <strong class="text-slate-800" x-text="signModalTarget.supplyName"></strong>
                    </div>
                    <div class="flex justify-between text-slate-500">
                        <span>Quantity:</span>
                        <strong class="text-blue-700 font-bold" x-text="signModalTarget.type === 'request' ? signModalTarget.requestedQty : signModalTarget.receivedQty"></strong>
                    </div>
                    <div class="flex justify-between text-slate-500">
                        <span>Signing Date:</span>
                        <span class="inline-flex items-center gap-1 font-bold text-emerald-600">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span x-text="getLiveDate()"></span> (Live Date)
                        </span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">MS Name / Designation *</label>
                    <input type="text" x-model="signModalForm.signerName" required placeholder="MS Dr. M. Fernando (Medical Superintendent)" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">MS Digital Confirmation Signature *</label>
                    <input type="text" x-model="signModalForm.signature" required placeholder="M. Fernando" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-signature text-xl text-rose-800 focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                </div>

                <div class="pt-2 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" @click="showSignConfirmationModal = false" class="px-4 py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-xl text-xs font-semibold">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-rose-500/30">Confirm & Sign</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================================ -->
    <!-- MODAL 6: NURSE UPDATE RECEIVED QUANTITY -->
    <!-- ============================================================================ -->
    <div x-show="showUpdateReceivedModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        
        <div @click.outside="showUpdateReceivedModal = false" 
             x-show="showUpdateReceivedModal"
             x-transition:enter="transition ease-out duration-200 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white rounded-3xl max-w-md w-full shadow-2xl overflow-hidden border border-slate-100">
            
            <div class="p-6 bg-teal-50 border-b border-teal-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-teal-600 text-white flex items-center justify-center font-bold text-lg shadow-sm shadow-teal-500/30">
                        <i class="fa-solid fa-box-open"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Update Received Quantity</h3>
                        <p class="text-xs text-teal-700 font-medium">Record delivery verification with live date</p>
                    </div>
                </div>
                <button @click="showUpdateReceivedModal = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form @submit.prevent="submitUpdateReceived()" class="p-6 space-y-4">
                <div class="p-3 bg-slate-50 border border-slate-200/70 rounded-xl space-y-1.5 text-xs">
                    <div class="flex justify-between text-slate-500">
                        <span>First Aid Supply:</span>
                        <strong class="text-slate-800" x-text="updateReceivedTarget.supplyName"></strong>
                    </div>
                    <div class="flex justify-between text-slate-500">
                        <span>Requested Quantity:</span>
                        <strong class="text-blue-700 font-bold" x-text="updateReceivedTarget.requestedQty"></strong>
                    </div>
                    <div class="flex justify-between text-slate-500">
                        <span>Receipt Live Date:</span>
                        <span class="inline-flex items-center gap-1 font-bold text-emerald-600">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span x-text="getLiveDate()"></span> (Today)
                        </span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Received Quantity *</label>
                    <input type="text" x-model="updateReceivedForm.receivedQty" required placeholder="e.g. 10 Box" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Received By (Staff Nurse / Pharmacist) *</label>
                    <input type="text" x-model="updateReceivedForm.receivedBy" required placeholder="Nurse Kamani (RN-308)" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                </div>

                <div class="pt-2 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" @click="showUpdateReceivedModal = false" class="px-4 py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-xl text-xs font-semibold">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-teal-500/30">Save Received Qty</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================================ -->
    <!-- ALPINE JS LOGIC & APPLICATION STATE -->
    <!-- ============================================================================ -->
    <script>
        function surgicalConsumableApp() {
            return {
                csrfToken: '',
                currentRole: 'admin', // 'admin' | 'nurse' | 'ms' | 'pharmacist'
                sidebarSearch: '',
                logSearch: '',
                logDateFilter: '',
                activeTab: 'requisitions',

                showAddOrderModal: false,
                showAddConsumableModal: false,
                showSignatureModal: false,
                showAddAdjustmentModal: false,
                showAddDailyCountModal: false,
                showSignConfirmationModal: false,
                showUpdateReceivedModal: false,

                signModalTarget: { id: null, type: 'request', supplyName: '', requestedQty: '', receivedQty: '' },
                signModalForm: { signerName: 'Sr. M. Fernando (Sister In-Charge)', signature: 'M. Fernando' },

                updateReceivedTarget: { id: null, supplyName: '', requestedQty: '' },
                updateReceivedForm: { receivedQty: '', receivedBy: 'Nurse Kamani (RN-308)' },

                inspectedSignatureText: '',
                inspectedSignatureType: '',
                inspectedOfficerName: '',

                toasts: [],

                consumables: [],
                selectedConsumable: { id: null, name: '', balance: 0, unit: '' },
                requisitions: [],
                adjustments: [],
                dailyCounts: [],

                newOrder: {
                    date: new Date().toISOString().split('T')[0],
                    supplyName: '',
                    balance: '',
                    requestedQty: '',
                    requestedBy: '',
                    requestSignature: '',
                    receivedQty: '',
                    receivedBy: '',
                    receiveSignature: ''
                },

                newConsumable: {
                    name: '',
                    balance: '',
                    unit: 'Box'
                },

                newAdjustment: {
                    date: new Date().toISOString().split('T')[0],
                    item_name: '',
                    type: 'COUNT_CORRECTION_ADD',
                    amount: '',
                    reason: '',
                    adjusted_by: 'Nurse Kamani (RN-308)'
                },

                newDailyCount: {
                    date: new Date().toISOString().split('T')[0],
                    item_name: '',
                    qty_given: '',
                    balance: '',
                    sisters_initials: 'K.P.',
                    remarks: ''
                },

                getLiveDate() {
                    const today = new Date();
                    const year = today.getFullYear();
                    const month = String(today.getMonth() + 1).padStart(2, '0');
                    const day = String(today.getDate()).padStart(2, '0');
                    return `${year}-${month}-${day}`;
                },

                isLowStock(item) {
                    if (!item) return false;
                    const bal = item.balance !== undefined ? item.balance : item.stock;
                    if (bal === null || bal === undefined) return false;
                    const num = parseInt(bal, 10);
                    return !isNaN(num) && num <= 10;
                },



                isRequestConfirmed(log) {
                    return log.requestSignature && 
                           !log.requestSignature.includes('Pending') &&
                           log.requestSignature.trim() !== '';
                },

                isReceiveConfirmed(log) {
                    return log.receiveSignature && 
                           !log.receiveSignature.includes('Pending') &&
                           log.receiveSignature.trim() !== '';
                },

                openSignModal(log, type) {
                    this.signModalTarget = {
                        id: log.id,
                        type: type,
                        supplyName: log.supplyName,
                        requestedQty: log.requestedQty,
                        receivedQty: log.receivedQty
                    };
                    this.signModalForm.signerName = 'MS Dr. M. Fernando (Medical Superintendent)';
                    this.signModalForm.signature = 'M. Fernando';
                    this.showSignConfirmationModal = true;
                },

                async submitConfirmationSignature() {
                    const target = this.signModalTarget;
                    const log = this.requisitions.find(r => r.id === target.id);
                    if (!log) return;

                    const payload = {
                        date: this.getLiveDate()
                    };

                    if (target.type === 'request') {
                        log.requestSignature = this.signModalForm.signature;
                        log.requestedBy = this.signModalForm.signerName;
                        payload.request_signature = this.signModalForm.signature;
                        payload.requested_by = this.signModalForm.signerName;
                    } else {
                        log.receiveSignature = this.signModalForm.signature;
                        log.receivedBy = this.signModalForm.signerName;
                        payload.receive_signature = this.signModalForm.signature;
                        payload.received_by = this.signModalForm.signerName;
                    }

                    try {
                        await fetch(`/api/requisitions/${log.id}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });
                    } catch (e) {
                        console.error('Error updating signature:', e);
                    }

                    this.showSignConfirmationModal = false;
                    this.showToast(`${target.type === 'request' ? 'Request' : 'Receive'} confirmation signed by MS!`);
                },

                openUpdateReceivedModal(log) {
                    this.updateReceivedTarget = {
                        id: log.id,
                        supplyName: log.supplyName,
                        requestedQty: log.requestedQty
                    };
                    this.updateReceivedForm.receivedQty = log.requestedQty || '';
                    this.updateReceivedForm.receivedBy = 'Nurse Kamani (RN-308)';
                    this.showUpdateReceivedModal = true;
                },

                async submitUpdateReceived() {
                    const target = this.updateReceivedTarget;
                    const log = this.requisitions.find(r => r.id === target.id);
                    if (!log) return;

                    log.receivedQty = this.updateReceivedForm.receivedQty;
                    log.receivedBy = this.updateReceivedForm.receivedBy;
                    log.date = this.getLiveDate();

                    try {
                        await fetch(`/api/requisitions/${log.id}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                received_qty: log.receivedQty,
                                received_by: log.receivedBy,
                                date: log.date
                            })
                        });
                    } catch (e) {
                        console.error('Error updating received qty:', e);
                    }

                    this.showUpdateReceivedModal = false;
                    this.showToast('Received quantity updated with live date! Ready for MS approval.');
                },

                init() {
                    this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    
                    this.currentRole = '{{ $activeRole }}';

                    this.fetchConsumables();
                    this.fetchRequisitions();
                    this.fetchAdjustments();
                    this.fetchDailyCounts();
                },

                async fetchConsumables() {
                    try {
                        const res = await fetch('/api/consumables');
                        if (res.ok) {
                            const data = await res.json();
                            if (data && data.length > 0) {
                                this.consumables = data;
                                const currentId = this.selectedConsumable.id;
                                const match = this.consumables.find(c => c.id === currentId);
                                this.selectedConsumable = match || this.consumables[0];
                            }
                        }
                    } catch (e) {
                        console.error('Error fetching consumables:', e);
                    }
                },

                async fetchRequisitions() {
                    try {
                        const res = await fetch('/api/requisitions');
                        if (res.ok) {
                            const data = await res.json();
                            if (data) {
                                this.requisitions = data.map(r => ({
                                    id: r.id,
                                    date: r.date,
                                    supplyName: r.supply_name || r.supplyName,
                                    balance: r.balance,
                                    requestedQty: r.requested_qty || r.requestedQty,
                                    requestedBy: r.requested_by || r.requestedBy,
                                    requestSignature: r.request_signature || r.requestSignature,
                                    receivedQty: r.received_qty || r.receivedQty,
                                    receivedBy: r.received_by || r.receivedBy,
                                    receiveSignature: r.receive_signature || r.receiveSignature
                                }));
                            }
                        }
                    } catch (e) {
                        console.error('Error fetching requisitions:', e);
                    }
                },

                async fetchAdjustments() {
                    try {
                        const res = await fetch('/api/adjustments');
                        if (res.ok) {
                            const data = await res.json();
                            if (data) {
                                this.adjustments = data;
                            }
                        }
                    } catch (e) {
                        console.error('Error fetching adjustments:', e);
                    }
                },

                async fetchDailyCounts() {
                    try {
                        const res = await fetch('/api/daily-counts');
                        if (res.ok) {
                            const data = await res.json();
                            if (data) {
                                this.dailyCounts = data;
                            }
                        }
                    } catch (e) {
                        console.error('Error fetching daily counts:', e);
                    }
                },

                get filteredConsumables() {
                    if (!this.sidebarSearch.trim()) return this.consumables;
                    const query = this.sidebarSearch.toLowerCase();
                    return this.consumables.filter(c => c.name.toLowerCase().includes(query));
                },

                get filteredRequisitions() {
                    return this.requisitions.filter(log => {
                        const matchesItem = !this.selectedConsumable.name || 
                                            log.supplyName.toLowerCase() === this.selectedConsumable.name.toLowerCase() ||
                                            log.supplyName.toLowerCase().includes(this.selectedConsumable.name.toLowerCase());
                        
                        const searchQuery = this.logSearch.toLowerCase().trim();
                        const matchesSearch = !searchQuery || 
                            log.supplyName.toLowerCase().includes(searchQuery) ||
                            (log.requestedBy && log.requestedBy.toLowerCase().includes(searchQuery)) ||
                            (log.receivedBy && log.receivedBy.toLowerCase().includes(searchQuery)) ||
                            (log.date && log.date.includes(searchQuery));

                        const matchesDate = !this.logDateFilter || log.date === this.logDateFilter;

                        return matchesItem && matchesSearch && matchesDate;
                    });
                },

                get filteredAdjustments() {
                    return this.adjustments.filter(adj => {
                        const currentName = this.selectedConsumable.name ? this.selectedConsumable.name.toLowerCase() : '';
                        const adjItem = (adj.item_name || adj.itemName || '').toLowerCase();
                        const matchesItem = !currentName || adjItem === currentName || adjItem.includes(currentName) || currentName.includes(adjItem);

                        const q = this.logSearch.toLowerCase().trim();
                        const matchesSearch = !q ||
                            adjItem.includes(q) ||
                            (adj.type && adj.type.toLowerCase().includes(q)) ||
                            (adj.reason && adj.reason.toLowerCase().includes(q)) ||
                            ((adj.adjusted_by || adj.adjustedBy || '').toLowerCase().includes(q)) ||
                            (adj.date && adj.date.includes(q));

                        const matchesDate = !this.logDateFilter || adj.date === this.logDateFilter;

                        return matchesItem && matchesSearch && matchesDate;
                    });
                },

                get filteredDailyCounts() {
                    return this.dailyCounts.filter(dc => {
                        const currentName = this.selectedConsumable.name ? this.selectedConsumable.name.toLowerCase() : '';
                        const dcItem = (dc.item_name || dc.itemName || '').toLowerCase();
                        const matchesItem = !currentName || dcItem === currentName || dcItem.includes(currentName) || currentName.includes(dcItem);

                        const q = this.logSearch.toLowerCase().trim();
                        const matchesSearch = !q ||
                            dcItem.includes(q) ||
                            ((dc.sisters_initials || dc.sistersInitials || '').toLowerCase().includes(q)) ||
                            ((dc.remarks || '').toLowerCase().includes(q)) ||
                            (dc.date && dc.date.includes(q));

                        const matchesDate = !this.logDateFilter || dc.date === this.logDateFilter;

                        return matchesItem && matchesSearch && matchesDate;
                    });
                },

                selectConsumable(item) {
                    this.selectedConsumable = item;
                },

                openAddOrderModal() {
                    this.newOrder.date = this.getLiveDate();
                    this.newOrder.supplyName = this.selectedConsumable.name;
                    this.newOrder.balance = `${this.selectedConsumable.balance} ${this.selectedConsumable.unit}`;
                    this.newOrder.requestedQty = '';
                    if (this.currentRole === 'nurse') {
                        this.newOrder.requestedBy = 'Nurse Kamani (RN-308)';
                        this.newOrder.requestSignature = 'Pending MS Approval';
                        this.newOrder.receivedQty = '';
                        this.newOrder.receivedBy = '';
                        this.newOrder.receiveSignature = 'Pending MS Approval';
                    } else {
                        this.newOrder.requestedBy = 'Sr. Perera (RN-402)';
                        this.newOrder.requestSignature = 'M. Perera';
                        this.newOrder.receivedQty = '';
                        this.newOrder.receivedBy = '';
                        this.newOrder.receiveSignature = '';
                    }
                    this.showAddOrderModal = true;
                },

                openAddDailyCountModal() {
                    this.newDailyCount.date = this.getLiveDate();
                    this.newDailyCount.item_name = this.selectedConsumable.name;
                    this.newDailyCount.qty_given = '';
                    this.showAddDailyCountModal = true;
                },

                openAddAdjustmentModal() {
                    this.newAdjustment.date = this.getLiveDate();
                    this.newAdjustment.item_name = this.selectedConsumable.name;
                    this.newAdjustment.amount = '';
                    this.showAddAdjustmentModal = true;
                },

                updateOrderBalanceFromSupply() {
                    const match = this.consumables.find(c => c.name === this.newOrder.supplyName);
                    if (match) {
                        this.newOrder.balance = `${match.balance} ${match.unit}`;
                    }
                },

                async submitNewOrder() {
                    try {
                        const payload = {
                            date: this.newOrder.date || this.getLiveDate(),
                            supply_name: this.newOrder.supplyName,
                            balance: this.newOrder.balance,
                            requested_qty: this.newOrder.requestedQty,
                            requested_by: this.newOrder.requestedBy || (this.currentRole === 'nurse' ? 'Nurse Kamani (RN-308)' : 'Sr. Perera (RN-402)'),
                            request_signature: this.newOrder.requestSignature || (this.currentRole === 'nurse' ? 'Pending MS Approval' : 'M. Perera'),
                            received_qty: this.newOrder.receivedQty || '',
                            received_by: this.newOrder.receivedBy || '',
                            receive_signature: this.newOrder.receiveSignature || (this.currentRole === 'nurse' ? 'Pending MS Approval' : '')
                        };
                        const res = await fetch('/api/requisitions', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });
                        if (res.ok) {
                            const result = await res.json();
                            const r = result.data || result;
                            this.requisitions.unshift({
                                id: r.id,
                                date: r.date || this.getLiveDate(),
                                supplyName: r.supply_name || r.supplyName,
                                balance: r.balance,
                                requestedQty: r.requested_qty || r.requestedQty,
                                requestedBy: r.requested_by || r.requestedBy,
                                requestSignature: r.request_signature || r.requestSignature || 'Pending MS Approval',
                                receivedQty: r.received_qty || r.receivedQty || '',
                                receivedBy: r.received_by || r.receivedBy || '',
                                receiveSignature: r.receive_signature || r.receiveSignature || 'Pending MS Approval'
                            });

                            this.showToast('Order recorded successfully with live date!');
                            this.showAddOrderModal = false;

                            this.newOrder.requestedQty = '';
                            this.newOrder.receivedQty = '';
                            
                            this.fetchConsumables();
                        }
                    } catch (e) {
                        this.showToast('Error saving requisition order', 'error');
                    }
                },

                async submitNewConsumable() {
                    if (!this.newConsumable.name.trim()) return;
                    try {
                        const payload = {
                            name: this.newConsumable.name.trim().toUpperCase(),
                            balance: parseInt(this.newConsumable.balance) || 0,
                            unit: this.newConsumable.unit || 'Box'
                        };
                        const res = await fetch('/api/consumables', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });
                        if (res.ok) {
                            const result = await res.json();
                            const item = result.data || result;
                            this.consumables.push(item);
                            this.selectedConsumable = item;
                            this.showToast('Consumable item added successfully!');
                            this.showAddConsumableModal = false;
                            this.newConsumable.name = '';
                            this.newConsumable.balance = '';
                        }
                    } catch (e) {
                        this.showToast('Error adding consumable item', 'error');
                    }
                },

                async openEditConsumableModal(item) {
                    const newName = prompt('Edit Consumable Name:', item.name);
                    if (newName && newName.trim()) {
                        const updatedName = newName.trim().toUpperCase();
                        try {
                            const res = await fetch(`/api/consumables/${item.id}`, {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': this.csrfToken,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ name: updatedName })
                            });
                            if (res.ok) {
                                item.name = updatedName;
                                if (this.selectedConsumable.id === item.id) {
                                    this.selectedConsumable.name = updatedName;
                                }
                                this.showToast('Item updated successfully');
                            }
                        } catch (e) {
                            this.showToast('Error updating item', 'error');
                        }
                    }
                },

                async deleteConsumable(id) {
                    if (confirm('Are you sure you want to delete this consumable item?')) {
                        try {
                            const res = await fetch(`/api/consumables/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': this.csrfToken,
                                    'Accept': 'application/json'
                                }
                            });
                            if (res.ok) {
                                this.consumables = this.consumables.filter(c => c.id !== id);
                                if (this.selectedConsumable.id === id && this.consumables.length > 0) {
                                    this.selectedConsumable = this.consumables[0];
                                }
                                this.showToast('Consumable item removed');
                            }
                        } catch (e) {
                            this.showToast('Error deleting item', 'error');
                        }
                    }
                },

                async deleteRequisition(id) {
                    if (confirm('Delete this requisition record?')) {
                        try {
                            const res = await fetch(`/api/requisitions/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': this.csrfToken,
                                    'Accept': 'application/json'
                                }
                            });
                            if (res.ok) {
                                this.requisitions = this.requisitions.filter(r => r.id !== id);
                                this.showToast('Record deleted');
                            }
                        } catch (e) {
                            this.showToast('Error deleting record', 'error');
                        }
                    }
                },

                openAddAdjustmentModal() {
                    this.newAdjustment.item_name = this.selectedConsumable.name || (this.consumables[0]?.name || '');
                    this.newAdjustment.amount = '';
                    this.newAdjustment.reason = '';
                    this.newAdjustment.type = 'COUNT_CORRECTION_ADD';
                    this.newAdjustment.date = new Date().toISOString().split('T')[0];
                    this.showAddAdjustmentModal = true;
                },

                onAdjustmentItemChange() {
                    // Triggers reactivity
                },

                getCurrentItemUnit() {
                    const match = this.consumables.find(c => c.name === this.newAdjustment.item_name);
                    return match ? match.unit : (this.selectedConsumable.unit || 'Units');
                },

                getCurrentItemBalance() {
                    const match = this.consumables.find(c => c.name === this.newAdjustment.item_name);
                    const bal = match ? match.balance : (this.selectedConsumable.balance || 0);
                    const unit = this.getCurrentItemUnit();
                    return `${bal} ${unit}`;
                },

                getProjectedAdjustmentBalance() {
                    const match = this.consumables.find(c => c.name === this.newAdjustment.item_name);
                    const currentBal = match ? parseInt(match.balance) || 0 : (parseInt(this.selectedConsumable.balance) || 0);
                    const qty = parseInt(this.newAdjustment.amount) || 0;
                    const unit = this.getCurrentItemUnit();
                    const isPositive = ['COUNT_CORRECTION_ADD', 'RETURN_IN', 'SURPLUS'].includes(this.newAdjustment.type);
                    const projected = isPositive ? (currentBal + qty) : Math.max(0, currentBal - qty);
                    return `${projected} ${unit}`;
                },

                formatAdjustmentType(type) {
                    const map = {
                        'COUNT_CORRECTION_ADD': 'Audit Surplus (+)',
                        'COUNT_CORRECTION_SUB': 'Audit Deficit (-)',
                        'DAMAGED': 'Damaged / Broken (-)',
                        'EXPIRY': 'Expired Stock (-)',
                        'RETURN': 'Returned to Pharmacy (-)',
                        'LOSS': 'Missing / Lost (-)'
                    };
                    return map[type] || type;
                },

                getAdjustmentTypeBadgeClass(type) {
                    if (type === 'COUNT_CORRECTION_ADD') {
                        return 'bg-emerald-50 text-emerald-700 border border-emerald-200/70';
                    } else if (type === 'DAMAGED' || type === 'EXPIRY' || type === 'LOSS' || type === 'COUNT_CORRECTION_SUB') {
                        return 'bg-rose-50 text-rose-700 border border-rose-200/70';
                    }
                    return 'bg-amber-50 text-amber-700 border border-amber-200/70';
                },

                getAdjustmentTypeIcon(type) {
                    if (type === 'COUNT_CORRECTION_ADD') return 'fa-solid fa-plus-circle';
                    if (type === 'DAMAGED') return 'fa-solid fa-triangle-exclamation';
                    if (type === 'EXPIRY') return 'fa-solid fa-hourglass-end';
                    if (type === 'RETURN') return 'fa-solid fa-rotate-left';
                    if (type === 'LOSS') return 'fa-solid fa-circle-question';
                    return 'fa-solid fa-circle-minus';
                },

                async submitNewAdjustment() {
                    const match = this.consumables.find(c => c.name === this.newAdjustment.item_name);
                    const unit = match ? match.unit : 'Box';
                    const amount = parseInt(this.newAdjustment.amount) || 0;
                    const isPositive = ['COUNT_CORRECTION_ADD', 'RETURN_IN', 'SURPLUS'].includes(this.newAdjustment.type);
                    const qtyStr = isPositive ? `+${amount} ${unit}` : `-${amount} ${unit}`;
                    const projectedBalStr = this.getProjectedAdjustmentBalance();

                    const payload = {
                        date: this.newAdjustment.date,
                        item_name: this.newAdjustment.item_name,
                        type: this.newAdjustment.type,
                        quantity: qtyStr,
                        balance: projectedBalStr,
                        reason: this.newAdjustment.reason,
                        adjusted_by: this.newAdjustment.adjusted_by
                    };

                    try {
                        const res = await fetch('/api/adjustments', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });

                        if (res.ok) {
                            const result = await res.json();
                            const saved = result.data || payload;
                            if (!saved.id) saved.id = Date.now();
                            this.adjustments.unshift(saved);

                            // Update local balance
                            if (match) {
                                const curr = parseInt(match.balance) || 0;
                                match.balance = isPositive ? (curr + amount) : Math.max(0, curr - amount);
                                if (this.selectedConsumable.id === match.id) {
                                    this.selectedConsumable.balance = match.balance;
                                }
                            }

                            this.showToast('Stock adjustment recorded successfully!');
                            this.showAddAdjustmentModal = false;
                            this.newAdjustment.amount = '';
                            this.newAdjustment.reason = '';
                        }
                    } catch (e) {
                        this.showToast('Error recording stock adjustment', 'error');
                    }
                },

                openAddDailyCountModal() {
                    this.newDailyCount.item_name = this.selectedConsumable.name || (this.consumables[0]?.name || '');
                    this.newDailyCount.qty_given = '';
                    this.newDailyCount.remarks = '';
                    this.newDailyCount.date = new Date().toISOString().split('T')[0];
                    this.showAddDailyCountModal = true;
                },

                getCurrentDailyUnit() {
                    const match = this.consumables.find(c => c.name === this.newDailyCount.item_name);
                    return match ? match.unit : (this.selectedConsumable.unit || 'Box');
                },

                getCurrentDailyBalance() {
                    const match = this.consumables.find(c => c.name === this.newDailyCount.item_name);
                    const bal = match ? match.balance : (this.selectedConsumable.balance || 0);
                    const unit = this.getCurrentDailyUnit();
                    return `${bal} ${unit}`;
                },

                getProjectedDailyCountBalance() {
                    const match = this.consumables.find(c => c.name === this.newDailyCount.item_name);
                    const currentBal = match ? parseInt(match.balance) || 0 : (parseInt(this.selectedConsumable.balance) || 0);
                    const qty = parseInt(this.newDailyCount.qty_given) || 0;
                    const unit = this.getCurrentDailyUnit();
                    const projected = Math.max(0, currentBal - qty);
                    return `${projected} ${unit}`;
                },

                async submitNewDailyCount() {
                    const match = this.consumables.find(c => c.name === this.newDailyCount.item_name);
                    const unit = match ? match.unit : 'Box';
                    const amount = parseInt(this.newDailyCount.qty_given) || 0;
                    const qtyStr = `${amount} ${unit}`;
                    const projectedBalStr = this.getProjectedDailyCountBalance();

                    const payload = {
                        date: this.newDailyCount.date,
                        item_name: this.newDailyCount.item_name,
                        qty_given: qtyStr,
                        balance: projectedBalStr,
                        sisters_initials: this.newDailyCount.sisters_initials.toUpperCase(),
                        remarks: this.newDailyCount.remarks || ''
                    };

                    try {
                        const res = await fetch('/api/daily-counts', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });

                        if (res.ok) {
                            const result = await res.json();
                            const saved = result.data || payload;
                            if (!saved.id) saved.id = Date.now();
                            this.dailyCounts.unshift(saved);

                            if (match) {
                                const curr = parseInt(match.balance) || 0;
                                match.balance = Math.max(0, curr - amount);
                                if (this.selectedConsumable.id === match.id) {
                                    this.selectedConsumable.balance = match.balance;
                                }
                            }

                            this.showToast('Daily count recorded successfully!');
                            this.showAddDailyCountModal = false;
                            this.newDailyCount.qty_given = '';
                            this.newDailyCount.remarks = '';
                        }
                    } catch (e) {
                        this.showToast('Error recording daily count', 'error');
                    }
                },

                async deleteAdjustment(id) {
                    if (confirm('Delete this stock adjustment record?')) {
                        try {
                            const res = await fetch(`/api/adjustments/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': this.csrfToken,
                                    'Accept': 'application/json'
                                }
                            });
                            if (res.ok) {
                                this.adjustments = this.adjustments.filter(a => a.id !== id);
                                this.showToast('Stock adjustment deleted');
                            }
                        } catch (e) {
                            this.showToast('Error deleting adjustment record', 'error');
                        }
                    }
                },

                clearLogFilters() {
                    this.logSearch = '';
                    this.logDateFilter = '';
                },

                inspectSignature(sigText, type, officer) {
                    this.inspectedSignatureText = sigText;
                    this.inspectedSignatureType = type;
                    this.inspectedOfficerName = officer;
                    this.showSignatureModal = true;
                },

                getInitials(name) {
                    if (!name) return 'DS';
                    return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
                },

                printRequisitionReport() {
                    window.print();
                },

                showToast(message, type = 'success') {
                    const id = Date.now();
                    this.toasts.push({ id, message, type, visible: true });
                    setTimeout(() => {
                        const t = this.toasts.find(toast => toast.id === id);
                        if (t) t.visible = false;
                    }, 3500);
                }
            }
        }
    </script>

</body>
</html>
