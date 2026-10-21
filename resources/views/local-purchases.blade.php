<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Local Purchases - Ward Inventory System</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Dancing+Script:wght@600&display=swap" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        signature: ['"Dancing Script"', 'cursive'],
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
    </style>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-100 text-slate-800 font-sans min-h-screen antialiased" x-data="localPurchaseApp()">

    <!-- Toast Notifications -->
    <div class="fixed top-5 right-5 z-50 flex flex-col gap-2 pointer-events-none">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="toast.visible" 
                 x-transition
                 class="pointer-events-auto px-4 py-3 rounded-xl shadow-lg text-white font-medium text-sm flex items-center gap-3 bg-emerald-600 min-w-[280px]">
                <i class="fa-solid fa-circle-check text-lg"></i>
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
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 text-xl font-bold shadow-sm">
                        <i class="fa-solid fa-cart-shopping"></i>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-slate-900 leading-tight">Ward Inventory</h1>
                        <p class="text-[11px] font-bold tracking-wider text-slate-400 uppercase">LOCAL PURCHASES</p>
                    </div>
                </div>
                <a href="/" title="Dashboard" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100">
                    <i class="fa-solid fa-house text-sm"></i>
                </a>
            </div>

            <!-- Navigation Links Bar -->
            <div class="px-4 py-2 border-b border-slate-100 bg-slate-50/60 flex items-center gap-1 text-xs">
                <a :href="`/${currentRole}/surgical-consumable-1`" class="px-2 py-1 text-slate-600 hover:text-blue-600 rounded-lg font-medium">Consumable 1</a>
                <span class="text-slate-300">•</span>
                <a :href="`/${currentRole}/surgical-consumable-2`" class="px-2 py-1 text-slate-600 hover:text-blue-600 rounded-lg font-medium">Consumable 2</a>
                <span class="text-slate-300">•</span>
                <span class="px-2 py-1 text-blue-600 font-bold bg-blue-50 rounded-lg">Local Purchases</span>
            </div>

            <!-- Search & Add Action -->
            <div class="p-4 space-y-3">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input type="text" 
                           x-model="sidebarSearch" 
                           placeholder="Search local purchases..." 
                           class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-slate-700">
                </div>

                <template x-if="currentRole === 'admin'">
                    <button @click="showAddItemModal = true" 
                            class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold flex items-center justify-center gap-2 shadow-sm shadow-blue-500/30">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>Add Local Purchase Item</span>
                    </button>
                </template>
                <template x-if="currentRole !== 'admin'">
                    <div class="p-2.5 bg-slate-50 border border-slate-200/70 rounded-xl text-[11px] text-slate-500 flex items-center gap-2">
                        <i class="fa-solid fa-lock text-slate-400"></i>
                        <span>Catalog add restricted to Admin</span>
                    </div>
                </template>
            </div>

            <!-- Item List -->
            <div class="flex-1 overflow-y-auto px-4 pb-4 space-y-2.5 custom-scrollbar">
                <template x-for="item in filteredItems" :key="item.id">
                    <div @click="selectItem(item)" 
                         class="p-3.5 rounded-xl border transition-all cursor-pointer flex items-center justify-between"
                         :class="selectedItem.id === item.id 
                                ? 'bg-blue-50/70 border-blue-200 shadow-sm border-l-4 border-l-blue-600' 
                                : 'bg-white border-slate-200/80 hover:border-slate-300 hover:bg-slate-50/80'">
                        
                        <div class="flex-1 min-w-0 pr-2">
                            <h3 class="font-bold text-sm text-slate-900 truncate uppercase" x-text="item.name"></h3>
                            <!-- Low Stock Warning Label below item name -->
                            <template x-if="isLowStock(item)">
                                <div class="mt-1">
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-extrabold bg-red-100 text-red-700 border border-red-300 uppercase tracking-wide">
                                        <i class="fa-solid fa-triangle-exclamation text-[9px] text-red-600"></i>
                                        Low
                                    </span>
                                </div>
                            </template>
                            <template x-if="currentRole === 'admin'">
                                <div class="flex items-center gap-3 text-slate-400 mt-1">
                                    <button @click.stop="deleteItem(item.id)" title="Delete" class="hover:text-rose-600">
                                        <i class="fa-solid fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <div class="shrink-0 text-right">
                            <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-semibold"
                                  :class="selectedItem.id === item.id ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600'"
                                  x-text="`${item.balance} ${item.unit}`">
                            </span>
                        </div>
                    </div>
                </template>
            </div>
        </aside>

        <!-- ============================================================================ -->
        <!-- MAIN CONTENT AREA -->
        <!-- ============================================================================ -->
        <main class="flex-1 p-8 overflow-y-auto space-y-6">

         

            <!-- 1. Header Banner Card -->
            <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 rounded-2xl p-7 text-white shadow-xl flex items-center justify-between relative overflow-hidden">
                <div>
                    <h2 class="text-3xl font-extrabold uppercase tracking-tight" x-text="selectedItem.name || 'LOCAL PURCHASES'"></h2>
                    
                    <p class="text-blue-100 text-sm font-medium mt-1">Current Ward Stock Balance</p>
                </div>

                <div class="bg-white rounded-2xl px-6 py-4 shadow-lg flex flex-col items-center justify-center shrink-0 min-w-[140px] text-center border border-white/20">
                    <span class="text-2xl font-black text-emerald-600" x-text="`${selectedItem.balance} ${selectedItem.unit}`"></span>
                    <span class="text-[11px] font-bold text-emerald-600 tracking-wider uppercase mt-0.5">AVAILABLE</span>
                </div>
            </div>

            <!-- 2. Controls & Filter Bar -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
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

                <div class="flex items-center gap-3">
                    <div class="relative flex-1 md:w-56">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" x-model="logSearch" placeholder="Search records or notes..." class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                    </div>

                    <input type="date" x-model="logDateFilter" class="py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-600">

                    <button @click="logSearch = ''; logDateFilter = '';" class="px-3.5 py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-xl text-xs font-semibold">
                        Clear
                    </button>

                    <button @click="window.print()" class="px-3 py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-xl text-xs font-semibold">
                        <i class="fa-solid fa-print"></i>
                    </button>
                </div>
            </div>

            <!-- 3. Pharmacy Requisitions Log Table -->
            <div x-show="activeTab === 'requisitions'" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Pharmacy Requisitions Log</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Local Purchases - Requisitions Log</p>
                    </div>

                    <template x-if="currentRole === 'admin' || currentRole === 'nurse'">
                        <button @click="openAddOrderModal()" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl flex items-center gap-2 shadow-sm shadow-blue-500/30">
                            <i class="fa-solid fa-plus text-xs"></i>
                            <span>Add Order</span>
                        </button>
                    </template>
                    <template x-if="currentRole === 'ms'">
                        <div class="px-3.5 py-2 bg-rose-50 border border-rose-200/80 rounded-xl text-rose-700 text-xs font-semibold flex items-center gap-2">
                            <i class="fa-solid fa-pen-nib"></i>
                            <span>MS: Sign in Charge Mode</span>
                        </div>
                    </template>
                    <template x-if="currentRole === 'pharmacist'">
                        <div class="px-3.5 py-2 bg-amber-50 border border-amber-200/80 rounded-xl text-amber-700 text-xs font-semibold flex items-center gap-2">
                            <i class="fa-solid fa-prescription-bottle-medical"></i>
                            <span>Pharmacist Review Mode</span>
                        </div>
                    </template>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200/70 text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">
                                <th class="py-3.5 px-6">Date</th>
                                <th class="py-3.5 px-6">Quantity</th>
                                <th class="py-3.5 px-6">Balance</th>
                                <th class="py-3.5 px-6">Signature in Charge</th>
                                <th class="py-3.5 px-6">Note</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                            <template x-for="log in filteredRequisitions" :key="log.id">
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-4 px-6 font-medium text-slate-900 whitespace-nowrap" x-text="log.date"></td>
                                    
                                    <td class="py-4 px-6 font-bold text-blue-700">
                                        <span class="bg-blue-50 border border-blue-200/60 px-2.5 py-1 rounded-lg" x-text="log.quantity"></span>
                                    </td>

                                    <td class="py-4 px-6 font-semibold text-slate-600">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700" x-text="log.balance"></span>
                                    </td>

                                    <td class="py-4 px-6">
                                        <!-- If pending signature in charge -->
                                        <template x-if="!log.signature_in_charge || log.signature_in_charge.includes('Pending')">
                                            <div>
                                                <!-- MS or Admin can sign -->
                                                <template x-if="currentRole === 'ms' || currentRole === 'admin'">
                                                    <button @click="openSignInChargeModal(log)" 
                                                            class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg font-bold text-xs flex items-center gap-1.5 shadow-sm shadow-rose-500/30 transition-all active:scale-95 animate-pulse">
                                                        <i class="fa-solid fa-file-signature"></i>
                                                        <span>Sign in Charge (MS)</span>
                                                    </button>
                                                </template>
                                                <!-- Nurse / Pharmacist sees pending badge -->
                                                <template x-if="currentRole === 'nurse' || currentRole === 'pharmacist'">
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                        <i class="fa-solid fa-clock"></i>
                                                        <span>Pending MS Approval</span>
                                                    </span>
                                                </template>
                                            </div>
                                        </template>

                                        <!-- If already signed -->
                                        <template x-if="log.signature_in_charge && !log.signature_in_charge.includes('Pending')">
                                            <div class="flex items-center gap-2">
                                                <div @click="inspectSignature(log.signature_in_charge, 'Signature in Charge', log.officer_name)" 
                                                     class="cursor-pointer group flex items-center gap-2.5 bg-slate-50 hover:bg-blue-50 border border-slate-200 hover:border-blue-200 p-2 rounded-xl transition-all">
                                                    <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center font-signature text-blue-600 text-lg overflow-hidden shrink-0">
                                                        <span x-text="log.signature_in_charge[0] || 'S'"></span>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <p class="font-signature text-base text-slate-900 leading-tight truncate group-hover:text-blue-700" x-text="log.signature_in_charge"></p>
                                                        <p class="text-[10px] text-slate-400 truncate" x-text="log.officer_name"></p>
                                                    </div>
                                                    <i class="fa-solid fa-signature text-slate-300 group-hover:text-blue-500 text-xs ml-auto"></i>
                                                </div>

                                                <template x-if="currentRole === 'ms' || currentRole === 'admin'">
                                                    <button @click="openSignInChargeModal(log)" title="Re-sign / Update Signature in Charge" class="p-2 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition-colors">
                                                        <i class="fa-solid fa-pen-nib text-xs"></i>
                                                    </button>
                                                </template>
                                            </div>
                                        </template>
                                    </td>

                                </tr>
                            </template>

                            <tr x-show="filteredRequisitions.length === 0">
                                <td colspan="5" class="text-center py-12 text-slate-400">
                                    <p class="text-sm font-medium">No requisition log records found</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
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
    <!-- MODAL: ADD LOCAL PURCHASE ITEM -->
    <!-- ============================================================================ -->
    <div x-show="showAddItemModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        
        <div @click.outside="showAddItemModal = false" 
             x-show="showAddItemModal"
             x-transition:enter="transition ease-out duration-200 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white rounded-3xl max-w-md w-full shadow-2xl overflow-hidden border border-slate-100">
            
            <div class="p-6 bg-slate-50 border-b border-slate-200/70 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-lg shadow-sm shadow-blue-500/30">
                        <i class="fa-solid fa-cart-plus"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Add Local Purchase Item</h3>
                        <p class="text-xs text-slate-500">Register new item and initial stock</p>
                    </div>
                </div>
                <button @click="showAddItemModal = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form @submit.prevent="submitNewItem()" class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Item Name *</label>
                    <input type="text" x-model="newItem.name" placeholder="e.g. PARACETAMOL INFUSION 100ML" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Current Stock Balance *</label>
                        <input type="number" min="0" x-model="newItem.balance" placeholder="0" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Unit *</label>
                        <input type="text" x-model="newItem.unit" placeholder="Tabs / Bottles / Vials" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" @click="showAddItemModal = false" class="px-4 py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-xl text-xs font-semibold">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-blue-500/30">Add Item</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: ADD ORDER -->
    <div x-show="showAddOrderModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/50 flex items-center justify-center p-4">
        <div @click.outside="showAddOrderModal = false" class="bg-white rounded-3xl max-w-lg w-full shadow-2xl p-6 border border-slate-100">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                <h3 class="text-lg font-bold text-slate-900">Add Pharmacy Requisition</h3>
                <button @click="showAddOrderModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form @submit.prevent="submitNewOrder()" class="space-y-4">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Date</label>
                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md flex items-center gap-1">
                            <i class="fa-solid fa-clock"></i> Live Date
                        </span>
                    </div>
                    <input type="date" x-model="newOrder.date" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Quantity</label>
                    <input type="text" x-model="newOrder.quantity" placeholder="e.g. 20 Tabs" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Current Stock Balance</label>
                    <input type="text" x-model="newOrder.balance" readonly class="w-full px-3.5 py-2 bg-slate-100 border border-slate-200 rounded-xl text-xs text-slate-700 font-semibold">
                </div>

                <!-- Admin Signature Form -->
                <template x-if="currentRole === 'admin'">
                    <div class="bg-blue-50/60 p-4 rounded-2xl border border-blue-100 space-y-2">
                        <label class="block text-xs font-extrabold text-blue-900 uppercase">Signature in Charge</label>
                        <input type="text" x-model="newOrder.officer_name" placeholder="Officer Name (e.g. Sr. Perera)" required class="w-full px-3 py-2 bg-white border border-blue-200 rounded-xl text-xs">
                        <input type="text" x-model="newOrder.signature_in_charge" placeholder="Digital Signature Text" required class="w-full px-3 py-2 bg-white border border-blue-200 rounded-xl text-xs font-signature text-lg text-blue-800">
                    </div>
                </template>

                <!-- Nurse Signature Notice -->
                <template x-if="currentRole === 'nurse'">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Requesting Staff Nurse</label>
                            <input type="text" x-model="newOrder.officer_name" placeholder="Staff Nurse Name" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium">
                        </div>
                        <div class="p-3.5 bg-amber-50 rounded-2xl border border-amber-200 text-xs text-amber-800 space-y-1">
                            <div class="font-bold flex items-center gap-1.5">
                                <i class="fa-solid fa-circle-info"></i> MS Authorization Required
                            </div>
                            <p class="text-[11px] text-amber-700 leading-relaxed">
                                Submitted orders will be flagged as <strong>Pending MS Approval</strong>. The Medical Superintendent (MS) will review and digitally sign the request.
                            </p>
                        </div>
                    </div>
                </template>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Note</label>
                    <textarea x-model="newOrder.note" rows="2" placeholder="Urgent inpatient management details..." class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs"></textarea>
                </div>

                <div class="pt-2 flex justify-end gap-3">
                    <button type="button" @click="showAddOrderModal = false" class="px-4 py-2 border border-slate-200 text-slate-600 rounded-xl text-xs font-semibold">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/30">Save Order</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: SIGN IN CHARGE (MS) -->
    <div x-show="showSignInChargeModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.outside="showSignInChargeModal = false" class="bg-white rounded-3xl max-w-md w-full shadow-2xl p-6 border border-slate-100">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center font-bold text-lg shadow-sm shadow-rose-500/30">
                        <i class="fa-solid fa-file-signature"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Sign in Charge Authorization (MS)</h3>
                        <p class="text-xs text-slate-500">Medical Superintendent order approval</p>
                    </div>
                </div>
                <button @click="showSignInChargeModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <!-- Target Details Preview -->
            <div class="mb-4 p-3.5 bg-slate-50 rounded-2xl border border-slate-200/70 space-y-2 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Local Purchase Item:</span>
                    <strong class="text-slate-900 uppercase font-bold" x-text="signInChargeTarget?.item_name"></strong>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Order Quantity:</span>
                    <span class="font-bold text-blue-700" x-text="signInChargeTarget?.quantity"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Order Placed Date:</span>
                    <span class="font-medium text-slate-700" x-text="signInChargeTarget?.date"></span>
                </div>
                <div class="flex items-center justify-between pt-1 border-t border-slate-200/60">
                    <span class="text-slate-500">Live Signing Date:</span>
                    <span class="px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold" x-text="getLiveDate()"></span>
                </div>
            </div>

            <form @submit.prevent="submitSignInCharge()" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">MS Name / Designation *</label>
                    <input type="text" x-model="signInChargeForm.officer_name" required placeholder="e.g. MS Dr. K. Perera (Medical Superintendent)" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
                </div>

                <div class="bg-rose-50/60 p-4 rounded-2xl border border-rose-100 space-y-2">
                    <label class="block text-xs font-extrabold text-rose-900 uppercase">MS Digital Signature *</label>
                    <input type="text" x-model="signInChargeForm.signature_in_charge" required placeholder="Type signature text (e.g. K. Perera)" class="w-full px-3.5 py-2 bg-white border border-rose-200 rounded-xl text-sm font-signature text-xl text-rose-800">
                    <p class="text-[10px] text-rose-600">This confirms official Medical Superintendent requisition approval</p>
                </div>

                <div class="pt-2 flex justify-end gap-3">
                    <button type="button" @click="showSignInChargeModal = false" class="px-4 py-2 border border-slate-200 text-slate-600 rounded-xl text-xs font-semibold">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-500/30">Confirm Signature</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: SIGNATURE INSPECTION -->
    <div x-show="showSignatureModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 flex items-center justify-center p-4">
        <div @click.outside="showSignatureModal = false" class="bg-white rounded-3xl max-w-sm w-full p-6 text-center border border-slate-100">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider" x-text="inspectedSignatureType"></h4>
            <h3 class="text-xl font-bold text-slate-900 mt-1" x-text="inspectedOfficerName"></h3>

            <div class="my-6 p-6 bg-slate-50 rounded-2xl border border-slate-200/80 flex items-center justify-center">
                <span class="font-signature text-4xl text-blue-900 tracking-wide select-none" x-text="inspectedSignatureText"></span>
            </div>

            <button @click="showSignatureModal = false" class="w-full py-2.5 bg-slate-900 text-white text-xs font-bold rounded-xl">Close</button>
        </div>
    </div>

    <!-- ============================================================================ -->
    <!-- MODAL: ADD STOCK ADJUSTMENT -->
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
                            <template x-for="item in items" :key="item.id">
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
                    <textarea x-model="newAdjustment.reason" rows="2" required placeholder="Specify why the stock is being adjusted (e.g. audit recount, damaged package, supplier warranty return)..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500"></textarea>
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
                        <template x-for="item in items" :key="item.id">
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

    <!-- ALPINE JS APP STATE -->
    <script>
        function localPurchaseApp() {
            return {
                sidebarSearch: '',
                logSearch: '',
                csrfToken: '',
                logDateFilter: '',
                activeTab: 'requisitions',
                currentRole: 'admin',
                showAddOrderModal: false,
                showAddItemModal: false,
                showSignatureModal: false,
                showAddAdjustmentModal: false,
                showAddDailyCountModal: false,
                showSignInChargeModal: false,
                signInChargeTarget: null,
                signInChargeForm: {
                    officer_name: '',
                    signature_in_charge: ''
                },

                inspectedSignatureText: '',
                inspectedSignatureType: '',
                inspectedOfficerName: '',
                toasts: [],

                items: [],
                selectedItem: { id: null, name: '', balance: 0, unit: '' },
                requisitions: [],
                adjustments: [],
                dailyCounts: [],

                newOrder: {
                    date: new Date().toISOString().split('T')[0],
                    quantity: '',
                    balance: '',
                    officer_name: '',
                    signature_in_charge: '',
                    note: ''
                },

                newItem: {
                    name: '',
                    balance: '',
                    unit: 'Tabs'
                },

                newAdjustment: {
                    date: new Date().toISOString().split('T')[0],
                    item_name: '',
                    type: 'COUNT_CORRECTION_ADD',
                    amount: '',
                    reason: '',
                    adjusted_by: 'Sr. Perera (RN-402)'
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
                    const now = new Date();
                    const year = now.getFullYear();
                    const month = String(now.getMonth() + 1).padStart(2, '0');
                    const day = String(now.getDate()).padStart(2, '0');
                    return `${year}-${month}-${day}`;
                },

                isLowStock(item) {
                    if (!item) return false;
                    const bal = item.balance !== undefined ? item.balance : item.stock;
                    if (bal === null || bal === undefined) return false;
                    const num = parseInt(bal, 10);
                    return !isNaN(num) && num <= 10;
                },



                openSignInChargeModal(log) {
                    this.signInChargeTarget = log;
                    this.signInChargeForm = {
                        officer_name: (log.officer_name && !log.officer_name.includes('Nurse')) ? log.officer_name : 'MS Dr. K. Perera (Medical Superintendent)',
                        signature_in_charge: (log.signature_in_charge && !log.signature_in_charge.includes('Pending')) ? log.signature_in_charge : 'K. Perera'
                    };
                    this.showSignInChargeModal = true;
                },

                async submitSignInCharge() {
                    if (!this.signInChargeTarget) return;
                    try {
                        const payload = {
                            signature_in_charge: this.signInChargeForm.signature_in_charge,
                            officer_name: this.signInChargeForm.officer_name,
                            date: this.getLiveDate()
                        };
                        const res = await fetch(`/api/local-purchases/requisitions/${this.signInChargeTarget.id}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });
                        if (res.ok) {
                            this.signInChargeTarget.signature_in_charge = payload.signature_in_charge;
                            this.signInChargeTarget.officer_name = payload.officer_name;
                            this.showToast('Local purchase requisition signed in charge by MS!');
                            this.showSignInChargeModal = false;
                        }
                    } catch (e) {
                        this.showToast('Error updating signature in charge', 'error');
                    }
                },

                init() {
                    this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    
                    this.currentRole = '{{ $activeRole }}';

                    this.fetchItems();
                    this.fetchRequisitions();
                    this.fetchAdjustments();
                    this.fetchDailyCounts();
                },

                async fetchItems() {
                    try {
                        const res = await fetch('/api/local-purchases/items');
                        if (res.ok) {
                            const data = await res.json();
                            if (data && data.length > 0) {
                                this.items = data;
                                const currentId = this.selectedItem.id;
                                const match = this.items.find(i => i.id === currentId);
                                this.selectedItem = match || this.items[0];
                            }
                        }
                    } catch (e) {
                        console.error('Error fetching local purchase items:', e);
                    }
                },

                async fetchRequisitions() {
                    try {
                        const res = await fetch('/api/local-purchases/requisitions');
                        if (res.ok) {
                            const data = await res.json();
                            if (data) {
                                this.requisitions = data;
                            }
                        }
                    } catch (e) {
                        console.error('Error fetching requisitions:', e);
                    }
                },

                async fetchAdjustments() {
                    try {
                        const res = await fetch('/api/local-purchases/adjustments');
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
                        const res = await fetch('/api/local-purchases/daily-counts');
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

                get filteredItems() {
                    if (!this.sidebarSearch.trim()) return this.items;
                    return this.items.filter(i => i.name.toLowerCase().includes(this.sidebarSearch.toLowerCase()));
                },

                get filteredRequisitions() {
                    return this.requisitions.filter(log => {
                        const matchesItem = !this.selectedItem.name || 
                                            log.item_name.toLowerCase() === this.selectedItem.name.toLowerCase() ||
                                            log.item_name.toLowerCase().includes(this.selectedItem.name.toLowerCase());
                        const q = this.logSearch.toLowerCase().trim();
                        const matchesSearch = !q || (log.note && log.note.toLowerCase().includes(q)) || (log.item_name && log.item_name.toLowerCase().includes(q)) || (log.officer_name && log.officer_name.toLowerCase().includes(q));
                        const matchesDate = !this.logDateFilter || log.date === this.logDateFilter;
                        return matchesItem && matchesSearch && matchesDate;
                    });
                },

                get filteredAdjustments() {
                    return this.adjustments.filter(adj => {
                        const currentName = this.selectedItem.name ? this.selectedItem.name.toLowerCase() : '';
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
                        const currentName = this.selectedItem.name ? this.selectedItem.name.toLowerCase() : '';
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

                selectItem(item) {
                    this.selectedItem = item;
                },

                openAddOrderModal() {
                    this.newOrder.date = this.getLiveDate();
                    this.newOrder.balance = `${this.selectedItem.balance} ${this.selectedItem.unit}`;
                    if (this.currentRole === 'nurse') {
                        this.newOrder.officer_name = 'Staff Nurse (Ward 48)';
                        this.newOrder.signature_in_charge = 'Pending MS Approval';
                    } else {
                        this.newOrder.officer_name = this.newOrder.officer_name || 'MS Dr. K. Perera';
                        this.newOrder.signature_in_charge = this.newOrder.signature_in_charge || 'K. Perera';
                    }
                    this.showAddOrderModal = true;
                },

                async submitNewOrder() {
                    try {
                        const payload = {
                            date: this.newOrder.date || this.getLiveDate(),
                            item_name: this.selectedItem.name,
                            quantity: this.newOrder.quantity,
                            balance: this.newOrder.balance,
                            officer_name: this.newOrder.officer_name || (this.currentRole === 'nurse' ? 'Staff Nurse (Ward 48)' : 'MS Dr. K. Perera'),
                            signature_in_charge: this.newOrder.signature_in_charge || (this.currentRole === 'nurse' ? 'Pending MS Approval' : 'K. Perera'),
                            note: this.newOrder.note
                        };
                        const res = await fetch('/api/local-purchases/requisitions', {
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
                                id: r.id || Date.now(),
                                date: r.date || this.getLiveDate(),
                                item_name: r.item_name || this.selectedItem.name,
                                quantity: r.quantity || this.newOrder.quantity,
                                balance: r.balance || this.newOrder.balance,
                                signature_in_charge: r.signature_in_charge || (this.currentRole === 'nurse' ? 'Pending MS Approval' : 'K. Perera'),
                                officer_name: r.officer_name || (this.currentRole === 'nurse' ? 'Staff Nurse (Ward 48)' : 'MS Dr. K. Perera'),
                                note: r.note || this.newOrder.note
                            });

                            this.showToast('Requisition record saved!');
                            this.showAddOrderModal = false;
                            this.newOrder.quantity = '';
                            this.newOrder.officer_name = '';
                            this.newOrder.signature_in_charge = '';
                            this.newOrder.note = '';

                            this.fetchItems();
                        }
                    } catch (e) {
                        this.showToast('Error saving requisition record', 'error');
                    }
                },

                async submitNewItem() {
                    if (!this.newItem.name.trim()) return;
                    try {
                        const payload = {
                            name: this.newItem.name.trim().toUpperCase(),
                            balance: parseInt(this.newItem.balance) || 0,
                            unit: this.newItem.unit || 'Tabs'
                        };
                        const res = await fetch('/api/local-purchases/items', {
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
                            this.items.push(item);
                            this.selectedItem = item;
                            this.showToast('Local purchase item added!');
                            this.showAddItemModal = false;
                            this.newItem.name = '';
                            this.newItem.balance = '';
                        }
                    } catch (e) {
                        this.showToast('Error adding item', 'error');
                    }
                },

                async openEditItemModal(item) {
                    const newName = prompt('Edit Item Name:', item.name);
                    if (newName && newName.trim()) {
                        const updatedName = newName.trim().toUpperCase();
                        try {
                            const res = await fetch(`/api/local-purchases/items/${item.id}`, {
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
                                if (this.selectedItem.id === item.id) {
                                    this.selectedItem.name = updatedName;
                                }
                                this.showToast('Item updated successfully');
                            }
                        } catch (e) {
                            this.showToast('Error updating item', 'error');
                        }
                    }
                },

                async deleteItem(id) {
                    if (confirm('Are you sure you want to delete this item?')) {
                        try {
                            const res = await fetch(`/api/local-purchases/items/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': this.csrfToken,
                                    'Accept': 'application/json'
                                }
                            });
                            if (res.ok) {
                                this.items = this.items.filter(i => i.id !== id);
                                if (this.selectedItem.id === id && this.items.length > 0) {
                                    this.selectedItem = this.items[0];
                                }
                                this.showToast('Item removed');
                            }
                        } catch (e) {
                            this.showToast('Error deleting item', 'error');
                        }
                    }
                },

                async deleteRequisition(id) {
                    if (confirm('Delete this record?')) {
                        try {
                            const res = await fetch(`/api/local-purchases/requisitions/${id}`, {
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
                    this.newAdjustment.item_name = this.selectedItem.name || (this.items[0]?.name || '');
                    this.newAdjustment.amount = '';
                    this.newAdjustment.reason = '';
                    this.newAdjustment.type = 'COUNT_CORRECTION_ADD';
                    this.newAdjustment.date = this.getLiveDate();
                    this.showAddAdjustmentModal = true;
                },

                onAdjustmentItemChange() {
                    // Triggers reactivity
                },

                getCurrentItemUnit() {
                    const match = this.items.find(c => c.name === this.newAdjustment.item_name);
                    return match ? match.unit : (this.selectedItem.unit || 'Tabs');
                },

                getCurrentItemBalance() {
                    const match = this.items.find(c => c.name === this.newAdjustment.item_name);
                    const bal = match ? match.balance : (this.selectedItem.balance || 0);
                    const unit = this.getCurrentItemUnit();
                    return `${bal} ${unit}`;
                },

                getProjectedAdjustmentBalance() {
                    const match = this.items.find(c => c.name === this.newAdjustment.item_name);
                    const currentBal = match ? parseInt(match.balance) || 0 : (parseInt(this.selectedItem.balance) || 0);
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
                    const match = this.items.find(c => c.name === this.newAdjustment.item_name);
                    const unit = match ? match.unit : 'Tabs';
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
                        const res = await fetch('/api/local-purchases/adjustments', {
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
                                if (this.selectedItem.id === match.id) {
                                    this.selectedItem.balance = match.balance;
                                }
                            }

                            this.showToast('Stock adjustment recorded successfully!');
                            this.showAddAdjustmentModal = false;
                            this.newAdjustment.amount = '';
                            this.newAdjustment.reason = '';
                        }
                    } catch (e) {
                        this.showToast('Error recording stock adjustment');
                    }
                },

                openAddDailyCountModal() {
                    this.newDailyCount.item_name = this.selectedItem.name || (this.items[0]?.name || '');
                    this.newDailyCount.qty_given = '';
                    this.newDailyCount.remarks = '';
                    this.newDailyCount.date = this.getLiveDate();
                    this.showAddDailyCountModal = true;
                },

                getCurrentDailyUnit() {
                    const match = this.items.find(i => i.name === this.newDailyCount.item_name);
                    return match ? match.unit : (this.selectedItem.unit || 'Tabs');
                },

                getCurrentDailyBalance() {
                    const match = this.items.find(i => i.name === this.newDailyCount.item_name);
                    const bal = match ? match.balance : (this.selectedItem.balance || 0);
                    const unit = this.getCurrentDailyUnit();
                    return `${bal} ${unit}`;
                },

                getProjectedDailyCountBalance() {
                    const match = this.items.find(i => i.name === this.newDailyCount.item_name);
                    const currentBal = match ? parseInt(match.balance) || 0 : (parseInt(this.selectedItem.balance) || 0);
                    const qty = parseInt(this.newDailyCount.qty_given) || 0;
                    const unit = this.getCurrentDailyUnit();
                    const projected = Math.max(0, currentBal - qty);
                    return `${projected} ${unit}`;
                },

                async submitNewDailyCount() {
                    const match = this.items.find(i => i.name === this.newDailyCount.item_name);
                    const unit = match ? match.unit : 'Tabs';
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
                        const res = await fetch('/api/local-purchases/daily-counts', {
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
                                if (this.selectedItem.id === match.id) {
                                    this.selectedItem.balance = match.balance;
                                }
                            }

                            this.showToast('Daily count recorded successfully!');
                            this.showAddDailyCountModal = false;
                            this.newDailyCount.qty_given = '';
                            this.newDailyCount.remarks = '';
                        }
                    } catch (e) {
                        this.showToast('Error recording daily count');
                    }
                },

                async deleteAdjustment(id) {
                    if (confirm('Delete this stock adjustment record?')) {
                        try {
                            const res = await fetch(`/api/local-purchases/adjustments/${id}`, {
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
                            this.showToast('Error deleting adjustment record');
                        }
                    }
                },

                inspectSignature(sigText, type, officer) {
                    this.inspectedSignatureText = sigText;
                    this.inspectedSignatureType = type;
                    this.inspectedOfficerName = officer;
                    this.showSignatureModal = true;
                },

                showToast(msg) {
                    const id = Date.now();
                    this.toasts.push({ id, message: msg, visible: true });
                    setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, 3000);
                }
            }
        }
    </script>
</body>
</html>
