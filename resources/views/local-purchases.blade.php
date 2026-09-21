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
                <a href="/surgical-consumable-1" class="px-2 py-1 text-slate-600 hover:text-blue-600 rounded-lg font-medium">Consumable 1</a>
                <span class="text-slate-300">•</span>
                <a href="/surgical-consumable-2" class="px-2 py-1 text-slate-600 hover:text-blue-600 rounded-lg font-medium">Consumable 2</a>
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

                <button @click="showAddItemModal = true" 
                        class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold flex items-center justify-center gap-2 shadow-sm shadow-blue-500/30">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>Add Local Purchase Item</span>
                </button>
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
                            <div class="flex items-center gap-3 text-slate-400 mt-1">
                                <button @click.stop="deleteItem(item.id)" title="Delete" class="hover:text-rose-600">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </button>
                            </div>
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
                <div class="flex items-center gap-2">
                    <button class="px-4 py-2 rounded-xl text-sm font-semibold bg-slate-100 text-slate-900 shadow-xs">
                        Pharmacy Orders (<span x-text="filteredRequisitions.length"></span>)
                    </button>
                    <button class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-500 hover:bg-slate-50">
                        Patient Administrations (0)
                    </button>
                </div>

                <div class="flex items-center gap-3">
                    <div class="relative flex-1 md:w-56">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" x-model="logSearch" placeholder="Search BHT or notes..." class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
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
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Pharmacy Requisitions Log</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Local Purchases - BHT Patient Requisitions</p>
                    </div>

                    <button @click="openAddOrderModal()" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl flex items-center gap-2 shadow-sm shadow-blue-500/30">
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
                                <th class="py-3.5 px-6">BHT</th>
                                <th class="py-3.5 px-6">Quantity</th>
                                <th class="py-3.5 px-6">Balance</th>
                                <th class="py-3.5 px-6">Signature in Charge</th>
                                <th class="py-3.5 px-6">Note</th>
                                <th class="py-3.5 px-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                            <template x-for="log in filteredRequisitions" :key="log.id">
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-4 px-6 font-medium text-slate-900 whitespace-nowrap" x-text="log.date"></td>
                                    
                                    <td class="py-4 px-6 font-bold text-slate-900">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200 font-mono text-xs" x-text="log.bht_no"></span>
                                    </td>

                                    <td class="py-4 px-6 font-bold text-blue-700">
                                        <span class="bg-blue-50 border border-blue-200/60 px-2.5 py-1 rounded-lg" x-text="log.quantity"></span>
                                    </td>

                                    <td class="py-4 px-6 font-semibold text-slate-600">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700" x-text="log.balance"></span>
                                    </td>

                                    <td class="py-4 px-6">
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
                                    </td>

                                    <td class="py-4 px-6 text-slate-600 max-w-xs truncate" x-text="log.note || '-'"></td>

                                    <td class="py-4 px-4 text-center">
                                        <button @click="deleteRequisition(log.id)" title="Delete" class="text-slate-400 hover:text-rose-600 p-1.5 rounded-lg hover:bg-rose-50">
                                            <i class="fa-solid fa-trash-can text-sm"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>

                            <tr x-show="filteredRequisitions.length === 0">
                                <td colspan="7" class="text-center py-12 text-slate-400">
                                    <p class="text-sm font-medium">No requisition log records found</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
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
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Date</label>
                    <input type="date" x-model="newOrder.date" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">BHT Number</label>
                        <input type="text" x-model="newOrder.bht_no" placeholder="e.g. BHT-88410" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Quantity</label>
                        <input type="text" x-model="newOrder.quantity" placeholder="e.g. 20 Tabs" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Current Stock Balance</label>
                    <input type="text" x-model="newOrder.balance" readonly class="w-full px-3.5 py-2 bg-slate-100 border border-slate-200 rounded-xl text-xs text-slate-700 font-semibold">
                </div>

                <div class="bg-blue-50/60 p-4 rounded-2xl border border-blue-100 space-y-2">
                    <label class="block text-xs font-extrabold text-blue-900 uppercase">Signature in Charge</label>
                    <input type="text" x-model="newOrder.officer_name" placeholder="Officer Name (e.g. Sr. Perera)" required class="w-full px-3 py-2 bg-white border border-blue-200 rounded-xl text-xs">
                    <input type="text" x-model="newOrder.signature_in_charge" placeholder="Digital Signature Text" required class="w-full px-3 py-2 bg-white border border-blue-200 rounded-xl text-xs font-signature text-lg text-blue-800">
                </div>

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

    <!-- ALPINE JS APP STATE -->
    <script>
        function localPurchaseApp() {
            return {
                sidebarSearch: '',
                logSearch: '',
                csrfToken: '',
                sidebarSearch: '',
                logSearch: '',
                logDateFilter: '',
                showAddOrderModal: false,
                showAddItemModal: false,
                showSignatureModal: false,

                inspectedSignatureText: '',
                inspectedSignatureType: '',
                inspectedOfficerName: '',
                toasts: [],

                items: [],
                selectedItem: { id: null, name: '', balance: 0, unit: '' },
                requisitions: [],

                newOrder: {
                    date: new Date().toISOString().split('T')[0],
                    bht_no: '',
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

                init() {
                    this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    this.fetchItems();
                    this.fetchRequisitions();
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
                        const matchesSearch = !q || log.bht_no.toLowerCase().includes(q) || (log.note && log.note.toLowerCase().includes(q));
                        const matchesDate = !this.logDateFilter || log.date === this.logDateFilter;
                        return matchesItem && matchesSearch && matchesDate;
                    });
                },

                selectItem(item) {
                    this.selectedItem = item;
                },

                openAddOrderModal() {
                    this.newOrder.balance = `${this.selectedItem.balance} ${this.selectedItem.unit}`;
                    this.showAddOrderModal = true;
                },

                async submitNewOrder() {
                    try {
                        const payload = {
                            date: this.newOrder.date,
                            item_name: this.selectedItem.name,
                            bht_no: this.newOrder.bht_no,
                            quantity: this.newOrder.quantity,
                            balance: this.newOrder.balance,
                            officer_name: this.newOrder.officer_name,
                            signature_in_charge: this.newOrder.signature_in_charge,
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
                            this.requisitions.unshift(r);

                            this.showToast('Requisition record saved!');
                            this.showAddOrderModal = false;
                            this.newOrder.bht_no = '';
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
