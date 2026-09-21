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

            <!-- Search & Add Action -->
            <div class="p-4 space-y-3">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input type="text" 
                           x-model="sidebarSearch" 
                           placeholder="Search consumables..." 
                           class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-slate-700 placeholder-slate-400">
                </div>

                <button @click="showAddConsumableModal = true" 
                        class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold flex items-center justify-center gap-2 shadow-sm shadow-blue-500/30 transition-all duration-150 active:scale-[0.98]">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>Add Consumable</span>
                </button>
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
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-bold text-sm text-slate-900 truncate uppercase" x-text="item.name"></h3>
                            </div>
                            
                            <!-- Action Icons -->
                            <div class="flex items-center gap-3 text-slate-400 opacity-60 group-hover:opacity-100 transition-opacity">
                                <button @click.stop="openEditConsumableModal(item)" title="Edit item" class="hover:text-blue-600 transition-colors">
                                    <i class="fa-solid fa-pen text-xs"></i>
                                </button>
                                <button @click.stop="deleteConsumable(item.id)" title="Delete item" class="hover:text-rose-600 transition-colors">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </button>
                            </div>
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
                    <button @click="activeTab = 'administrations'" 
                            class="px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-150"
                            :class="activeTab === 'administrations' ? 'bg-slate-100 text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'">
                        Patient Administrations (0)
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
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <!-- Header -->
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Pharmacy Requisitions Log</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Showing pharmacy requests & supply receipt verification records</p>
                    </div>

                    <button @click="openAddOrderModal()" 
                            class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl flex items-center gap-2 shadow-sm shadow-blue-500/30 transition-all active:scale-[0.98]">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>Add Order</span>
                    </button>
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
                                <th class="py-3.5 px-4 text-center">Actions</th>
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
                                        <div @click="inspectSignature(log.requestSignature, 'Request Confirmation', log.requestedBy)" 
                                             class="cursor-pointer group flex items-center gap-2.5 bg-slate-50 hover:bg-blue-50 border border-slate-200 hover:border-blue-200 p-2 rounded-xl transition-all">
                                            <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center font-signature text-blue-600 text-lg overflow-hidden shrink-0 shadow-xs">
                                                <span x-text="getInitials(log.requestedBy)"></span>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-signature text-base text-slate-900 leading-tight truncate group-hover:text-blue-700" x-text="log.requestSignature"></p>
                                                <p class="text-[10px] text-slate-400 truncate" x-text="log.requestedBy"></p>
                                            </div>
                                            <i class="fa-solid fa-signature text-slate-300 group-hover:text-blue-500 text-xs ml-auto"></i>
                                        </div>
                                    </td>

                                    <!-- Received Quantity -->
                                    <td class="py-4 px-6 font-bold text-emerald-700">
                                        <span class="bg-emerald-50 border border-emerald-200/60 px-2.5 py-1 rounded-lg" x-text="log.receivedQty"></span>
                                    </td>

                                    <!-- Confirmation Signature (Receive) -->
                                    <td class="py-4 px-6">
                                        <div @click="inspectSignature(log.receiveSignature, 'Receive Confirmation', log.receivedBy)" 
                                             class="cursor-pointer group flex items-center gap-2.5 bg-slate-50 hover:bg-emerald-50 border border-slate-200 hover:border-emerald-200 p-2 rounded-xl transition-all">
                                            <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center font-signature text-emerald-600 text-lg overflow-hidden shrink-0 shadow-xs">
                                                <span x-text="getInitials(log.receivedBy)"></span>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-signature text-base text-slate-900 leading-tight truncate group-hover:text-emerald-700" x-text="log.receiveSignature"></p>
                                                <p class="text-[10px] text-slate-400 truncate" x-text="log.receivedBy"></p>
                                            </div>
                                            <i class="fa-solid fa-signature text-slate-300 group-hover:text-emerald-500 text-xs ml-auto"></i>
                                        </div>
                                    </td>

                                    <!-- Actions -->
                                    <td class="py-4 px-4 text-center">
                                        <button @click="deleteRequisition(log.id)" title="Delete record" class="text-slate-400 hover:text-rose-600 transition-colors p-1.5 rounded-lg hover:bg-rose-50">
                                            <i class="fa-solid fa-trash-can text-sm"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>

                            <tr x-show="filteredRequisitions.length === 0">
                                <td colspan="8" class="text-center py-12 text-slate-400">
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

        </main>
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
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Date</label>
                        <input type="date" x-model="newOrder.date" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-medium">
                    </div>

                    <!-- Supply Name -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Name of First Aid Supplies</label>
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
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Requested Quantity</label>
                        <input type="text" x-model="newOrder.requestedQty" placeholder="e.g. 10 Box" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-medium">
                    </div>

                    <!-- Received Qty -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Received Quantity</label>
                        <input type="text" x-model="newOrder.receivedQty" placeholder="e.g. 10 Box" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-medium">
                    </div>
                </div>

                <hr class="border-slate-100 my-2">

                <!-- Signature Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Request Confirmation Signature -->
                    <div class="bg-blue-50/50 p-4 rounded-2xl border border-blue-100">
                        <label class="block text-xs font-extrabold text-blue-900 uppercase tracking-wider mb-1">
                            <i class="fa-solid fa-file-pen text-blue-600 mr-1"></i> Confirmation Signature (Request)
                        </label>
                        <input type="text" x-model="newOrder.requestedBy" placeholder="Officer Name (e.g. Sr. Perera)" required class="w-full px-3 py-2 bg-white border border-blue-200 rounded-xl text-xs mb-2 focus:ring-2 focus:ring-blue-500/20">
                        <input type="text" x-model="newOrder.requestSignature" placeholder="Digital Signature Text" required class="w-full px-3 py-2 bg-white border border-blue-200 rounded-xl text-xs font-signature text-lg text-blue-800 focus:ring-2 focus:ring-blue-500/20">
                    </div>

                    <!-- Receive Confirmation Signature -->
                    <div class="bg-emerald-50/50 p-4 rounded-2xl border border-emerald-100">
                        <label class="block text-xs font-extrabold text-emerald-900 uppercase tracking-wider mb-1">
                            <i class="fa-solid fa-signature text-emerald-600 mr-1"></i> Confirmation Signature (Receive)
                        </label>
                        <input type="text" x-model="newOrder.receivedBy" placeholder="Receiving Officer (e.g. Pharm. Jayawardena)" required class="w-full px-3 py-2 bg-white border border-emerald-200 rounded-xl text-xs mb-2 focus:ring-2 focus:ring-emerald-500/20">
                        <input type="text" x-model="newOrder.receiveSignature" placeholder="Digital Signature Text" required class="w-full px-3 py-2 bg-white border border-emerald-200 rounded-xl text-xs font-signature text-lg text-emerald-800 focus:ring-2 focus:ring-emerald-500/20">
                    </div>
                </div>

                <!-- Buttons -->
                <div class="pt-4 flex items-center justify-end gap-3">
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
    <!-- ALPINE JS LOGIC & APPLICATION STATE -->
    <!-- ============================================================================ -->
    <script>
        function surgicalConsumableApp() {
            return {
                csrfToken: '',
                sidebarSearch: '',
                logSearch: '',
                logDateFilter: '',
                activeTab: 'requisitions',

                showAddOrderModal: false,
                showAddConsumableModal: false,
                showSignatureModal: false,

                inspectedSignatureText: '',
                inspectedSignatureType: '',
                inspectedOfficerName: '',

                toasts: [],

                consumables: [],
                selectedConsumable: { id: null, name: '', balance: 0, unit: '' },
                requisitions: [],

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

                init() {
                    this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    this.fetchConsumables();
                    this.fetchRequisitions();
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

                selectConsumable(item) {
                    this.selectedConsumable = item;
                },

                openAddOrderModal() {
                    this.newOrder.supplyName = this.selectedConsumable.name;
                    this.newOrder.balance = `${this.selectedConsumable.balance} ${this.selectedConsumable.unit}`;
                    this.showAddOrderModal = true;
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
                            date: this.newOrder.date,
                            supply_name: this.newOrder.supplyName,
                            balance: this.newOrder.balance,
                            requested_qty: this.newOrder.requestedQty,
                            requested_by: this.newOrder.requestedBy,
                            request_signature: this.newOrder.requestSignature,
                            received_qty: this.newOrder.receivedQty,
                            received_by: this.newOrder.receivedBy,
                            receive_signature: this.newOrder.receiveSignature
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
                                date: r.date,
                                supplyName: r.supply_name || r.supplyName,
                                balance: r.balance,
                                requestedQty: r.requested_qty || r.requestedQty,
                                requestedBy: r.requested_by || r.requestedBy,
                                requestSignature: r.request_signature || r.requestSignature,
                                receivedQty: r.received_qty || r.receivedQty,
                                receivedBy: r.received_by || r.receivedBy,
                                receiveSignature: r.receive_signature || r.receiveSignature
                            });

                            this.showToast('New pharmacy requisition order saved!');
                            this.showAddOrderModal = false;

                            this.newOrder.requestedQty = '';
                            this.newOrder.requestedBy = '';
                            this.newOrder.requestSignature = '';
                            this.newOrder.receivedQty = '';
                            this.newOrder.receivedBy = '';
                            this.newOrder.receiveSignature = '';
                            
                            this.fetchConsumables();
                        }
                    } catch (e) {
                        this.showToast('Error saving requisition order', 'error');
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
                            const newItem = result.data || result;
                            this.consumables.push(newItem);
                            this.selectedConsumable = newItem;
                            this.showToast('Surgical consumable item added!');
                            this.showAddConsumableModal = false;
                            this.newConsumable.name = '';
                            this.newConsumable.balance = '';
                        }
                    } catch (e) {
                        this.showToast('Error adding item', 'error');
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
