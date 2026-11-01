<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Surgical Consumable 2 - Ward Inventory System</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        [x-cloak] { display: none !important; }
        html, body { min-height: 100%; }
        body { font-family: Inter, sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { width: 7px; height: 7px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>

<body class="bg-slate-50 text-slate-800" x-data="surgicalConsumableApp()" x-init="init()">

<div class="min-h-screen flex">

    <!-- ================================================================ -->
    <!-- SIDEBAR -->
    <!-- ================================================================ -->
    <aside class="w-[310px] shrink-0 bg-white border-r border-slate-200 flex flex-col sticky top-0 h-screen">
        <div class="p-6 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-sm">
                    <i class="fa-solid fa-flask text-lg"></i>
                </div>
                <div>
                    <h1 class="font-extrabold text-xl text-blue-900 leading-tight">Ward Inventory</h1>
                    <p class="text-[10px] font-bold tracking-[0.18em] text-slate-400 uppercase">Surgical Consumables 2</p>
                </div>
            </div>
        </div>

        <div class="p-5 border-b border-slate-100 space-y-3">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input
                    type="text"
                    x-model="sidebarSearch"
                    placeholder="Search consumables..."
                    class="w-full pl-10 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10"
                >
            </div>

            <template x-if="currentRole === 'admin'">
                <button
                    @click="openAddConsumableModal()"
                    class="w-full py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold flex items-center justify-center gap-2"
                >
                    <i class="fa-solid fa-plus"></i>
                    Add Consumable
                </button>
            </template>
        </div>

        <div class="flex-1 overflow-y-auto custom-scrollbar p-4 space-y-2">
            <template x-for="item in filteredConsumables" :key="item.id">
                <button
                    @click="selectConsumable(item)"
                    class="w-full text-left p-3.5 rounded-xl border transition-all"
                    :class="selectedConsumable.id === item.id
                        ? 'bg-blue-50 border-blue-200 border-l-4 border-l-blue-600'
                        : 'bg-white border-slate-200 hover:bg-slate-50 hover:border-slate-300'"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="font-bold text-sm text-slate-900 uppercase truncate" x-text="item.name"></div>
                            <template x-if="isLowStock(item)">
                                <span class="inline-flex mt-1 items-center gap-1 px-1.5 py-0.5 rounded bg-red-50 border border-red-200 text-red-700 text-[9px] font-extrabold uppercase">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    Low stock
                                </span>
                            </template>
                        </div>
                        <span
                            class="shrink-0 px-2.5 py-1 rounded-lg text-[11px] font-bold"
                            :class="selectedConsumable.id === item.id ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600'"
                            x-text="`${item.balance} ${item.unit}`"
                        ></span>
                    </div>
                </button>
            </template>

            <div x-show="filteredConsumables.length === 0" class="text-center py-10 text-slate-400 text-xs">
                No consumables found.
            </div>
        </div>

        <div class="p-4 border-t border-slate-100 bg-slate-50/60 text-[11px] text-slate-500 flex justify-between">
            <span>Total items: <strong class="text-slate-700" x-text="consumables.length"></strong></span>
            <span class="text-emerald-600 font-bold">● Live Stock</span>
        </div>
    </aside>

    <!-- ================================================================ -->
    <!-- MAIN -->
    <!-- ================================================================ -->
    <main class="flex-1 min-w-0">

        <!-- TOP BAR -->
        <header class="h-[82px] bg-white border-b border-slate-200 px-8 flex items-center justify-between sticky top-0 z-20">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-sm">
                    <i class="fa-solid fa-plus"></i>
                </div>
                <div>
                    <h2 class="font-extrabold text-lg text-slate-900">Hospital Inventory Management System</h2>
                    <p class="text-[10px] font-bold tracking-[0.18em] text-slate-400 uppercase">Ward Stock Management</p>
                </div>
            </div>

            <div class="relative" x-data="{ userMenuOpen: false }">
                <button
                    type="button"
                    @click="userMenuOpen = !userMenuOpen"
                    @click.outside="userMenuOpen = false"
                    class="flex items-center gap-3 rounded-xl px-2 py-1.5 hover:bg-slate-50 transition"
                >
                    <div class="hidden md:block text-right">
                        <div class="text-sm font-bold text-slate-800" x-text="currentUserName || 'Authenticated User'"></div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase" x-text="currentRole === 'ms' ? 'Medical Superintendent' : currentRole"></div>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-blue-50 border border-blue-100 text-blue-700 flex items-center justify-center font-extrabold" x-text="getInitials(currentUserName || currentRole)"></div>
                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                </button>

                <div
                    x-show="userMenuOpen"
                    x-cloak
                    x-transition
                    class="absolute right-0 mt-2 w-56 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 overflow-hidden"
                >
                    <div class="px-4 py-3 border-b border-slate-100">
                        <div class="text-xs font-extrabold text-slate-800" x-text="currentUserName || 'Authenticated User'"></div>
                        <div class="text-[10px] text-slate-400 uppercase font-bold mt-1" x-text="currentRole === 'ms' ? 'Medical Superintendent' : currentRole"></div>
                    </div>

                    <form method="POST" action="{{ url('/logout') }}">
                        @csrf
                        <button
                            type="submit"
                            class="w-full px-4 py-3 flex items-center gap-3 text-left text-xs font-bold text-red-600 hover:bg-red-50 transition"
                        >
                            <i class="fa-solid fa-right-from-bracket w-4"></i>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <div class="p-8 space-y-6">

            <!-- HERO -->
            <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 rounded-2xl p-7 text-white shadow-xl shadow-blue-600/10 flex items-center justify-between overflow-hidden relative">
                <div class="relative z-10">
                    <h2 class="text-3xl font-extrabold uppercase tracking-tight" x-text="selectedConsumable.name || 'SURGICAL CONSUMABLE 2'"></h2>
                    <p class="text-blue-100 text-sm font-medium mt-1">Current Ward Stock Balance</p>
                </div>

                <div class="relative z-10 bg-white rounded-2xl px-6 py-4 shadow-lg min-w-[155px] text-center">
                    <div class="text-2xl font-black text-blue-700" x-text="`${selectedConsumable.balance} ${selectedConsumable.unit}`"></div>
                    <div class="text-[10px] font-extrabold text-blue-600 tracking-widest uppercase mt-1">Available</div>
                </div>
            </div>

            <!-- TABS / FILTERS -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-3 flex flex-col xl:flex-row xl:items-center justify-between gap-3">
                <div class="flex items-center gap-1">
                    <button
                        @click="activeTab = 'requisitions'"
                        class="px-4 py-2.5 rounded-xl text-xs font-bold transition"
                        :class="activeTab === 'requisitions' ? 'bg-slate-100 text-slate-900' : 'text-slate-500 hover:bg-slate-50'"
                    >
                        Surgical Consumable Orders (<span x-text="requisitions.length"></span>)
                    </button>
                    <button
                        @click="activeTab = 'daily'"
                        class="px-4 py-2.5 rounded-xl text-xs font-bold transition"
                        :class="activeTab === 'daily' ? 'bg-slate-100 text-slate-900' : 'text-slate-500 hover:bg-slate-50'"
                    >
                        Daily Count (<span x-text="filteredDailyCounts.length"></span>)
                    </button>
                    <button
                        @click="activeTab = 'adjustments'"
                        class="px-4 py-2.5 rounded-xl text-xs font-bold transition"
                        :class="activeTab === 'adjustments' ? 'bg-slate-100 text-slate-900' : 'text-slate-500 hover:bg-slate-50'"
                    >
                        Stock Adjustments (<span x-text="adjustments.length"></span>)
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input
                            type="text"
                            x-model="logSearch"
                            placeholder="Search records..."
                            class="w-[190px] pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-blue-500"
                        >
                    </div>
                    <input
                        type="date"
                        x-model="logDateFilter"
                        class="px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-600 outline-none focus:border-blue-500"
                    >
                    <button
                        @click="clearFilters()"
                        class="px-3 py-2.5 border border-slate-200 bg-white rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-50"
                    >
                        <i class="fa-solid fa-xmark mr-1"></i>Clear
                    </button>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- REQUISITION LOG -->
            <!-- ============================================================ -->
            <section x-show="activeTab === 'requisitions'" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

                <div class="p-6 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-900">Surgical Consumable Requisitions Log</h3>
                    </div>

                    <template x-if="currentRole === 'nurse' || currentRole === 'admin'">
                        <button
                            @click="openAddOrderModal()"
                            class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold flex items-center gap-2 shadow-sm"
                        >
                            <i class="fa-solid fa-plus"></i>
                            Add Order
                        </button>
                    </template>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[1250px]">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200 text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">
                                <th class="py-4 px-5">Date</th>
                                <th class="py-4 px-5">Balance</th>
                                <th class="py-4 px-5">Quantity Requested</th>
                                <th class="py-4 px-5">Requested By</th>
                                <th class="py-4 px-5">MS Approval</th>
                                <th class="py-4 px-5">Quantity Received</th>
                                <th class="py-4 px-5">Receiving Officer</th>
                                <th class="py-4 px-5 text-right">Action</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 text-xs">
                            <template x-for="log in filteredRequisitions" :key="log.id">
                                <tr class="hover:bg-slate-50/70 transition-colors">

                                    <!-- DATE -->
                                    <td class="py-4 px-5 whitespace-nowrap">
                                        <div class="font-bold text-slate-900" x-text="formatDateTime(log.date)"></div>
                                        <div class="mt-1 inline-flex px-2 py-0.5 rounded bg-blue-50 text-blue-600 font-mono text-[10px] font-bold" x-text="log.req_no"></div>
                                    </td>

                                    <!-- BALANCE -->
                                    <td class="py-4 px-5 whitespace-nowrap">
                                        <span class="inline-flex px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200 text-slate-700 font-bold" x-text="log.balance || '—'"></span>
                                    </td>

                                    <!-- REQUESTED QUANTITY -->
                                    <td class="py-4 px-5 whitespace-nowrap">
                                        <span class="inline-flex px-2.5 py-1 rounded-lg bg-blue-50 border border-blue-200 text-blue-700 font-extrabold" x-text="log.requested_qty || '—'"></span>
                                    </td>

                                    <!-- REQUESTED BY -->
                                    <td class="py-4 px-5">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full bg-teal-50 border border-teal-100 text-teal-700 flex items-center justify-center">
                                                <i class="fa-solid fa-user-nurse text-xs"></i>
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-800 whitespace-nowrap" x-text="log.requested_by || '—'"></div>
                                                
                                            </div>
                                        </div>
                                    </td>

                                    <!-- MS APPROVAL -->
                                    <td class="py-4 px-5">
                                        <template x-if="getOrderStatus(log) === 'pending'">
                                            <div>
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-700 text-[10px] font-extrabold">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                    Pending
                                                </span>
                                                <div class="text-[10px] text-slate-400 mt-1">Waiting for MS</div>
                                                <template x-if="currentRole === 'ms' || currentRole === 'admin'">
                                                    <button
                                                        type="button"
                                                        @click="openMsApprovalModal(log)"
                                                        class="mt-2 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-bold transition"
                                                    >
                                                        <i class="fa-solid fa-check"></i>
                                                        Review & Approve
                                                    </button>
                                                </template>
                                            </div>
                                        </template>

                                        <template x-if="getOrderStatus(log) === 'approved' || getOrderStatus(log) === 'received'">
                                            <div>
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-[10px] font-extrabold">
                                                    <i class="fa-solid fa-circle-check"></i>
                                                    Approved
                                                </span>
                                                <div class="text-[10px] text-slate-500 mt-1 font-semibold" x-text="log.approved_by || '—'"></div>
                                            </div>
                                        </template>

                                        <template x-if="getOrderStatus(log) === 'rejected'">
                                            <div>
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-50 border border-red-200 text-red-700 text-[10px] font-extrabold">
                                                    <i class="fa-solid fa-circle-xmark"></i>
                                                    Rejected
                                                </span>
                                                <div class="text-[10px] text-slate-500 mt-1" x-text="log.approved_by || 'MS' "></div>
                                            </div>
                                        </template>
                                    </td>

                                    <!-- RECEIVED QUANTITY -->
                                    <td class="py-4 px-5 whitespace-nowrap">

                                        <!-- Approved order: nurse records received quantity here. -->
                                        <template x-if="
                                            getOrderStatus(log) === 'approved' &&
                                            currentRole === 'nurse'
                                        ">
                                            <div class="flex items-center gap-1.5">
                                                <input
                                                    type="number"
                                                    min="1"
                                                    :max="Number(log.items?.[0]?.requested_qty_num || 0)"
                                                    x-model.number="log.receive_qty_input"
                                                    placeholder="Qty"
                                                    class="w-20 px-2.5 py-2 bg-white border border-slate-200 rounded-lg text-xs font-extrabold text-slate-700 text-center outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10"
                                                >
                                                <span
                                                    class="text-[10px] text-slate-400 font-bold"
                                                    x-text="log.items?.[0]?.unit || 'Box'"
                                                ></span>
                                                <button
                                                    type="button"
                                                    @click="submitInlineReceive(log)"
                                                    title="Save received quantity"
                                                    class="w-8 h-8 rounded-lg bg-teal-600 hover:bg-teal-700 text-white flex items-center justify-center transition"
                                                >
                                                    <i class="fa-solid fa-check text-[11px]"></i>
                                                </button>
                                            </div>
                                        </template>

                                        <!-- Already received -->
                                        <template x-if="log.received_qty">
                                            <span class="inline-flex px-2.5 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 font-extrabold" x-text="log.received_qty"></span>
                                        </template>

                                        <!-- Pending / not yet received for non-nurse views -->
                                        <template x-if="!log.received_qty && getOrderStatus(log) !== 'approved'">
                                            <span class="text-slate-300 font-bold">—</span>
                                        </template>

                                    </td>

                                    <!-- RECEIVING OFFICER -->
                                    <td class="py-4 px-5">
                                        <template x-if="log.received_by">
                                            <div class="flex items-center gap-2">
                                                <div class="w-8 h-8 rounded-full bg-cyan-50 border border-cyan-100 text-cyan-700 flex items-center justify-center">
                                                    <i class="fa-solid fa-user-nurse text-xs"></i>
                                                </div>
                                                <div>
                                                    <div class="font-bold text-slate-800 whitespace-nowrap" x-text="log.received_by"></div>
                                                    <div class="text-[10px] text-slate-400">Receiving nurse</div>
                                                </div>
                                            </div>
                                        </template>
                                        <template x-if="!log.received_by">
                                            <span class="text-slate-300 font-bold">—</span>
                                        </template>
                                    </td>

                                    <!-- ACTION: DELETE ONLY FOR THE NURSE'S OWN PENDING REQUEST -->
                                    <td class="py-4 px-5 text-right whitespace-nowrap">
                                        <template x-if="
                                            currentRole === 'nurse' &&
                                            getOrderStatus(log) === 'pending' &&
                                            Number(log.requested_by_id) === Number(currentUserId)
                                        ">
                                            <button
                                                type="button"
                                                @click="deleteRequisition(log.id)"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-50 border border-red-200 text-red-600 hover:bg-red-100 text-xs font-bold transition"
                                            >
                                                <i class="fa-solid fa-trash"></i>
                                                Delete
                                            </button>
                                        </template>
                                    </td>
                                </tr>
                            </template>

                            <tr x-show="filteredRequisitions.length === 0">
                                <td colspan="8" class="py-16 text-center">
                                    <div class="flex flex-col items-center gap-2 text-slate-400">
                                        <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center text-xl">
                                            <i class="fa-solid fa-folder-open"></i>
                                        </div>
                                        <p class="text-sm font-bold text-slate-500">No requisition records found</p>
                                        <p class="text-xs">Create a new order or change the filters.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex justify-between items-center text-xs text-slate-500">
                    <span>Showing <strong class="text-slate-800" x-text="filteredRequisitions.length"></strong> record(s)</span>
                </div>
            </section>

            <!-- DAILY COUNT -->
<section x-show="activeTab === 'daily'" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-6 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-extrabold text-slate-900">Daily Count</h3>
            <p class="text-xs text-slate-500 mt-1">
                End-of-day stock issued by the nurse. Recording a daily count reduces the available stock immediately.
            </p>
        </div>

        <template x-if="currentRole === 'nurse' || currentRole === 'admin'">
            <button
                @click="openDailyCountModal()"
                class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold flex items-center gap-2 shadow-sm"
            >
                <i class="fa-solid fa-plus"></i>
                Add Daily Count
            </button>
        </template>
    </div>

    <div class="p-4 border-b border-slate-100 bg-slate-50/60 flex flex-wrap items-center justify-between gap-2">
        <div class="text-xs text-slate-500">
            Selected item:
            <strong class="text-slate-800" x-text="selectedConsumable.name || '—'"></strong>
        </div>
        <div class="text-xs font-bold text-indigo-700">
            Current balance:
            <span x-text="`${selectedConsumable.balance ?? 0} ${selectedConsumable.unit || ''}`"></span>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-slate-50 border-b border-slate-200 text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">
                <tr>
                    <th class="py-3 px-5">Date &amp; Time</th>
                    <th class="py-3 px-5">Quantity Given</th>
                    <th class="py-3 px-5">Balance</th>
                    <th class="py-3 px-5">Sisters Initials</th>
                    <th class="py-3 px-5">Remarks</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <template x-for="record in filteredDailyCounts" :key="record.id">
                    <tr class="hover:bg-slate-50/70">
                        <td class="py-4 px-5 text-xs font-semibold text-slate-700" x-text="formatDateTime(record.date)"></td>
                        <td class="py-4 px-5">
                            <span class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 text-xs font-extrabold"
                                  x-text="`${record.qty_given} ${record.unit || selectedConsumable.unit || ''}`"></span>
                        </td>
                        <td class="py-4 px-5">
                            <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-extrabold"
                                  x-text="`${record.balance} ${record.unit || selectedConsumable.unit || ''}`"></span>
                        </td>
                        <td class="py-4 px-5 text-xs font-extrabold text-slate-700" x-text="record.sisters_initials || '—'"></td>
                        <td class="py-4 px-5 text-xs text-slate-600" x-text="record.remarks || '—'"></td>
                    </tr>
                </template>

                <tr x-show="filteredDailyCounts.length === 0">
                    <td colspan="5" class="py-12 text-center text-xs text-slate-400">
                        No daily counts recorded for this item.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

            <!-- STOCK ADJUSTMENTS -->
            <section x-show="activeTab === 'adjustments'" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-900">Stock Adjustments Log</h3>
                        <p class="text-xs text-slate-500 mt-1">Existing ward stock adjustment records.</p>
                    </div>
                    <template x-if="currentRole === 'nurse' || currentRole === 'admin'">
                        <button @click="openAdjustmentModal()" class="px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold">
                            <i class="fa-solid fa-sliders mr-1"></i> New Adjustment
                        </button>
                    </template>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left min-w-[900px]">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[10px] font-extrabold text-slate-500 uppercase">
                                <th class="py-4 px-6">Date</th>
                                <th class="py-4 px-6">Item</th>
                                <th class="py-4 px-6">Type</th>
                                <th class="py-4 px-6">Quantity</th>
                                <th class="py-4 px-6">Reason</th>
                                <th class="py-4 px-6">Adjusted By</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            <template x-for="adj in filteredAdjustments" :key="adj.id">
                                <tr class="hover:bg-slate-50">
                                    <td class="py-4 px-6" x-text="adj.date"></td>
                                    <td class="py-4 px-6 font-bold" x-text="adj.item_name"></td>
                                    <td class="py-4 px-6" x-text="adj.type"></td>
                                    <td class="py-4 px-6 font-bold" x-text="adj.quantity"></td>
                                    <td class="py-4 px-6 max-w-sm truncate" :title="adj.reason" x-text="adj.reason"></td>
                                    <td class="py-4 px-6 font-semibold" x-text="adj.adjusted_by"></td>
                                </tr>
                            </template>
                            <tr x-show="filteredAdjustments.length === 0">
                                <td colspan="6" class="py-12 text-center text-slate-400">No stock adjustment records found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
</div>

<!-- ================================================================ -->
<!-- ADD ORDER MODAL -->
<!-- ================================================================ -->
<div x-show="showAddOrderModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
    <div @click.outside="showAddOrderModal = false" class="w-full max-w-3xl bg-white rounded-3xl shadow-2xl overflow-hidden">
        <div class="p-6 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-extrabold text-slate-900">Add Order</h3>
                <p class="text-xs text-slate-500 mt-1">Create a new surgical consumable request for MS approval.</p>
            </div>
            <button @click="showAddOrderModal = false" class="w-9 h-9 rounded-xl hover:bg-slate-200 text-slate-500">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form @submit.prevent="submitNewOrder()" class="p-6 space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-[10px] font-extrabold text-slate-600 uppercase tracking-wider">Order Date & Time *</label>
                        <span class="text-[9px] font-extrabold text-emerald-600 uppercase">● Live</span>
                    </div>
                    <input
                        type="datetime-local"
                        x-model="newOrder.date"
                        required
                        class="w-full px-3.5 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10"
                    >
                    <p class="text-[10px] text-slate-400 mt-1">Current date/time is filled automatically. You may change it manually.</p>
                </div>

                <div>
                    <label class="block text-[10px] font-extrabold text-slate-600 uppercase tracking-wider mb-1.5">Requested By</label>
                    <div class="w-full px-3.5 py-3 bg-slate-100 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 flex items-center gap-2">
                        <i class="fa-solid fa-user-nurse text-teal-600"></i>
                        <span x-text="currentUserName || '—'"></span>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">Automatically taken from the authenticated nurse.</p>
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-[10px] font-extrabold text-slate-600 uppercase tracking-wider">Quantity Requested *</label>
                    <button type="button" @click="addOrderItemRow()" class="px-3 py-1.5 rounded-lg bg-blue-50 border border-blue-200 text-blue-700 text-[10px] font-bold">
                        <i class="fa-solid fa-plus mr-1"></i>Add Item
                    </button>
                </div>

                <div class="border border-slate-200 rounded-2xl overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 border-b border-slate-200 text-[10px] uppercase font-extrabold text-slate-500">
                            <tr>
                                <th class="py-3 px-4">Consumable</th>
                                <th class="py-3 px-4">Current Balance</th>
                                <th class="py-3 px-4">Quantity Requested</th>
                                <th class="py-3 px-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(item, index) in newOrder.items" :key="index">
                                <tr>
                                    <td class="py-3 px-4">
                                        <select x-model="item.name" @change="onOrderItemChange(item)" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold outline-none focus:border-blue-500">
                                            <template x-for="c in consumables" :key="c.id">
                                                <option :value="c.name" x-text="c.name"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200 text-[11px] font-bold text-slate-700" x-text="item.balance"></span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-1.5">
                                            <input type="number" min="1" step="1" x-model.number="item.requested_qty_num" required class="w-24 px-3 py-2 bg-blue-50 border border-blue-200 rounded-lg text-xs font-extrabold text-blue-700 text-center outline-none focus:border-blue-500">
                                            <span class="text-[10px] text-slate-400 font-semibold" x-text="item.unit"></span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-2 text-center">
                                        <button type="button" @click="removeOrderItemRow(index)" :disabled="newOrder.items.length === 1" class="text-red-500 hover:bg-red-50 p-2 rounded-lg disabled:opacity-20">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-blue-50 border border-blue-200 text-xs text-blue-800 flex gap-3">
                <i class="fa-solid fa-circle-info text-blue-600 mt-0.5"></i>
                <div>
                    <div class="font-extrabold">Step 1 — Nurse submits request</div>
                    <div class="text-[11px] mt-1">The request will appear in the MS account as Pending. Until the MS approves it, the requesting nurse can delete the request.</div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                <button type="button" @click="showAddOrderModal = false" class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">Cancel</button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold">
                    <i class="fa-solid fa-paper-plane mr-1"></i>Submit Order
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================================================================ -->
<!-- MS APPROVAL MODAL -->
<!-- ================================================================ -->
<div x-show="showMsApprovalModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
    <div @click.outside="showMsApprovalModal = false" class="w-full max-w-3xl bg-white rounded-3xl shadow-2xl overflow-hidden">
        <div class="p-6 bg-rose-50 border-b border-rose-100 flex items-center justify-between">
            <div>
                <div class="text-[10px] font-extrabold text-rose-600 uppercase tracking-wider">MS Review</div>
                <h3 class="text-lg font-extrabold text-slate-900 mt-1">Review & Approve Requisition</h3>
                <p class="text-xs text-slate-500 mt-1" x-text="msModalOrder.req_no"></p>
            </div>
            <button @click="showMsApprovalModal = false" class="w-9 h-9 rounded-xl hover:bg-rose-100 text-slate-500">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form @submit.prevent="submitMsApproval()" class="p-6 space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                    <div class="text-[10px] font-extrabold uppercase text-slate-400">Requested Date</div>
                    <div class="font-bold text-slate-800 mt-1" x-text="formatDateTime(msModalOrder.date)"></div>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                    <div class="text-[10px] font-extrabold uppercase text-slate-400">Requested By</div>
                    <div class="font-bold text-slate-800 mt-1" x-text="msModalOrder.requested_by"></div>
                </div>
            </div>

            <div>
                <div class="text-[10px] font-extrabold text-slate-600 uppercase tracking-wider mb-2">Requested Quantities</div>
                <div class="border border-slate-200 rounded-2xl overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 border-b border-slate-200 text-[10px] font-extrabold text-slate-500 uppercase">
                            <tr>
                                <th class="py-3 px-4">Consumable</th>
                                <th class="py-3 px-4">Balance</th>
                                <th class="py-3 px-4">Quantity Requested</th>
                                <th class="py-3 px-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(item, index) in msModalOrder.items" :key="item.id || index">
                                <tr>
                                    <td class="py-3 px-4 font-bold text-slate-800" x-text="item.name"></td>
                                    <td class="py-3 px-4">
                                        <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-[11px] font-bold" x-text="item.balance || '—'"></span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-2">
                                            <input type="number" min="1" step="1" x-model.number="item.requested_qty_num" required class="w-24 px-3 py-2 bg-blue-50 border border-blue-200 rounded-lg text-xs font-extrabold text-blue-700 text-center outline-none focus:border-blue-500">
                                            <span class="text-[10px] text-slate-400 font-bold" x-text="item.unit"></span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-2 text-center">
                                        <button type="button" @click="removeMsItemRow(index)" class="text-red-500 hover:bg-red-50 p-2 rounded-lg">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <p class="text-[10px] text-slate-400 mt-2">The MS can change the quantity requested before approval.</p>
            </div>

            <div>
                <label class="block text-[10px] font-extrabold text-slate-600 uppercase tracking-wider mb-1.5">MS Remark (Optional)</label>
                <textarea x-model="msModalOrder.remark" rows="3" class="w-full px-3.5 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-blue-500" placeholder="Optional approval note..."></textarea>
            </div>

            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white text-rose-600 flex items-center justify-center border border-rose-100">
                    <i class="fa-solid fa-user-doctor"></i>
                </div>
                <div>
                    <div class="text-[10px] font-extrabold text-rose-500 uppercase">Approving MS</div>
                    <div class="text-sm font-extrabold text-slate-800" x-text="currentUserName || '—'"></div>
                    <div class="text-[10px] text-slate-500">Automatically taken from the logged-in account.</div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                <button type="button" @click="openRejectModalFromMsModal()" class="px-5 py-2.5 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs font-bold">Reject</button>
                <button type="button" @click="showMsApprovalModal = false" class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600">Cancel</button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold">
                    <i class="fa-solid fa-circle-check mr-1"></i>Approve Request
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================================================================ -->
<!-- NURSE RECEIVE MODAL -->
<!-- ================================================================ -->
<div x-show="showNurseReceiveModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
    <div @click.outside="showNurseReceiveModal = false" class="w-full max-w-2xl bg-white rounded-3xl shadow-2xl overflow-hidden">
        <div class="p-6 bg-teal-50 border-b border-teal-100 flex items-center justify-between">
            <div>
                <div class="text-[10px] font-extrabold text-teal-600 uppercase tracking-wider">Nurse Receipt</div>
                <h3 class="text-lg font-extrabold text-slate-900 mt-1">Record Quantity Received</h3>
                <p class="text-xs text-slate-500 mt-1" x-text="receiveModalOrder.req_no"></p>
            </div>
            <button @click="showNurseReceiveModal = false" class="w-9 h-9 rounded-xl hover:bg-teal-100 text-slate-500">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form @submit.prevent="submitNurseReceive()" class="p-6 space-y-5">
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                <div class="text-[10px] font-extrabold text-slate-400 uppercase">Receiving Officer</div>
                <div class="flex items-center gap-2 mt-1">
                    <div class="w-8 h-8 rounded-full bg-teal-50 text-teal-700 flex items-center justify-center">
                        <i class="fa-solid fa-user-nurse text-xs"></i>
                    </div>
                    <span class="font-extrabold text-slate-800" x-text="currentUserName || '—'"></span>
                </div>
                <div class="text-[10px] text-slate-400 mt-1">Automatically taken from the logged-in nurse.</div>
            </div>

            <div class="border border-slate-200 rounded-2xl overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b border-slate-200 text-[10px] font-extrabold text-slate-500 uppercase">
                        <tr>
                            <th class="py-3 px-4">Consumable</th>
                            <th class="py-3 px-4">MS Approved Qty</th>
                            <th class="py-3 px-4">Quantity Received</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="item in receiveModalOrder.items" :key="item.id">
                            <tr>
                                <td class="py-4 px-4 font-bold text-slate-800" x-text="item.name"></td>
                                <td class="py-4 px-4">
                                    <span class="px-2.5 py-1 rounded-lg bg-blue-50 border border-blue-200 text-blue-700 text-[11px] font-extrabold" x-text="`${item.requested_qty_num} ${item.unit}`"></span>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-2">
                                        <input
                                            type="number"
                                            min="1"
                                            :max="item.requested_qty_num"
                                            x-model.number="item.received_qty_num"
                                            required
                                            class="w-24 px-3 py-2.5 bg-teal-50 border border-teal-300 rounded-xl text-xs font-extrabold text-teal-700 text-center outline-none focus:border-teal-500"
                                        >
                                        <span class="text-[10px] text-slate-400 font-bold" x-text="item.unit"></span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-1">Cannot exceed MS-approved quantity.</div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 flex gap-3">
                <i class="fa-solid fa-circle-info text-emerald-600 mt-0.5"></i>
                <div>
                    <div class="font-extrabold">Receiving this order updates ward stock.</div>
                    <div class="text-[11px] mt-1">The receiving nurse is saved automatically and the received quantity is added to the ward stock ledger.</div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                <button type="button" @click="showNurseReceiveModal = false" class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600">Cancel</button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold">
                    <i class="fa-solid fa-box-open mr-1"></i>Save Received Quantity
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================================================================ -->
<!-- REJECT MODAL -->
<!-- ================================================================ -->
<div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div @click.outside="showRejectModal = false" class="w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden">
        <div class="p-6 bg-red-50 border-b border-red-100">
            <div class="text-[10px] font-extrabold text-red-600 uppercase tracking-wider">MS Decision</div>
            <h3 class="text-lg font-extrabold text-slate-900 mt-1">Reject Requisition</h3>
            <p class="text-xs text-slate-500 mt-1" x-text="rejectModalOrder.req_no"></p>
        </div>
        <form @submit.prevent="submitRejectOrder()" class="p-6 space-y-4">
            <div>
                <label class="block text-[10px] font-extrabold text-slate-600 uppercase tracking-wider mb-1.5">Reason *</label>
                <textarea x-model="rejectModalOrder.reason" required rows="4" class="w-full px-3.5 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-red-500" placeholder="Enter the reason for rejection..."></textarea>
            </div>
            <div class="text-[10px] text-slate-500">Rejected by: <strong x-text="currentUserName"></strong></div>
            <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                <button type="button" @click="showRejectModal = false" class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600">Cancel</button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold">Reject Request</button>
            </div>
        </form>
    </div>
</div>

<!-- ================================================================ -->
<!-- ADD DAILY COUNT MODAL -->
<!-- ================================================================ -->
<div x-show="showDailyCountModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
    <div @click.outside="showDailyCountModal = false" class="w-full max-w-xl bg-white rounded-3xl shadow-2xl overflow-hidden">
        <div class="p-6 bg-indigo-50 border-b border-indigo-100 flex items-center justify-between">
            <div>
                <div class="text-[10px] font-extrabold text-indigo-600 uppercase tracking-wider">End-of-Day Stock</div>
                <h3 class="text-lg font-extrabold text-slate-900 mt-1">Add Daily Count</h3>
                <p class="text-xs text-slate-500 mt-1" x-text="selectedConsumable.name || 'Selected surgical item'"></p>
            </div>
            <button @click="showDailyCountModal = false" class="w-9 h-9 rounded-xl hover:bg-indigo-100 text-slate-500">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form @submit.prevent="submitDailyCount()" class="p-6 space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-[10px] font-extrabold text-slate-600 uppercase tracking-wider">Date &amp; Time *</label>
                        <span class="text-[9px] font-extrabold text-emerald-600 uppercase">● Live</span>
                    </div>
                    <input
                        type="datetime-local"
                        x-model="newDailyCount.date"
                        required
                        class="w-full px-3.5 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold outline-none focus:border-indigo-500"
                    >
                    <p class="text-[10px] text-slate-400 mt-1">Filled with the current date/time. You can change it manually.</p>
                </div>

                <div>
                    <label class="block text-[10px] font-extrabold text-slate-600 uppercase tracking-wider mb-1.5">Sister's Initials</label>
                    <div class="w-full px-3.5 py-3 bg-slate-100 border border-slate-200 rounded-xl text-xs font-extrabold text-slate-700"
                         x-text="getInitials(currentUserName)"></div>
                    <p class="text-[10px] text-slate-400 mt-1">Taken automatically from the logged-in nurse.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-extrabold text-slate-600 uppercase tracking-wider mb-1.5">Quantity Given *</label>
                    <div class="flex items-center gap-2">
                        <input
                            type="number"
                            min="1"
                            step="1"
                            x-model.number="newDailyCount.qty_given"
                            required
                            class="flex-1 px-3.5 py-3 bg-white border border-slate-200 rounded-xl text-xs font-extrabold outline-none focus:border-indigo-500"
                        >
                        <span class="text-xs font-bold text-slate-400" x-text="selectedConsumable.unit || 'Unit'"></span>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-extrabold text-slate-600 uppercase tracking-wider mb-1.5">Balance</label>
                    <div
                        class="w-full px-3.5 py-3 rounded-xl border text-xs font-extrabold"
                        :class="dailyCountCalculatedBalance < 0 ? 'bg-red-50 border-red-200 text-red-700' : 'bg-emerald-50 border-emerald-200 text-emerald-700'"
                        x-text="`${Math.max(0, dailyCountCalculatedBalance)} ${selectedConsumable.unit || ''}`"
                    ></div>
                    <p class="text-[10px] text-slate-400 mt-1">Available stock after this daily count.</p>
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-extrabold text-slate-600 uppercase tracking-wider mb-1.5">Remarks</label>
                <textarea
                    x-model="newDailyCount.remarks"
                    rows="3"
                    class="w-full px-3.5 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-indigo-500"
                    placeholder="Enter the end-of-day remarks..."
                ></textarea>
            </div>

            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-800">
                <div class="font-extrabold">Stock will be reduced immediately.</div>
                <div class="text-[11px] mt-1">The server checks the real available balance before saving the count, so stock cannot become negative.</div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                <button type="button" @click="showDailyCountModal = false" class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600">Cancel</button>
                <button
                    type="submit"
                    :disabled="dailyCountCalculatedBalance < 0"
                    class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-bold"
                >
                    <i class="fa-solid fa-floppy-disk mr-1"></i>
                    Save Daily Count
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================================================================ -->
<!-- NEW STOCK ADJUSTMENT MODAL -->
<!-- ================================================================ -->
<div x-show="showAdjustmentModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
    <div @click.outside="showAdjustmentModal = false" class="w-full max-w-xl bg-white rounded-3xl shadow-2xl overflow-hidden">
        <div class="p-6 bg-amber-50 border-b border-amber-100 flex items-center justify-between">
            <div>
                <div class="text-[10px] font-extrabold text-amber-600 uppercase tracking-wider">Stock Control</div>
                <h3 class="text-lg font-extrabold text-slate-900 mt-1">New Adjustment</h3>
                <p class="text-xs text-slate-500 mt-1" x-text="selectedConsumable.name || 'Selected medicine'"></p>
            </div>
            <button type="button" @click="showAdjustmentModal = false" class="w-9 h-9 rounded-xl hover:bg-amber-100 text-slate-500">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form @submit.prevent="submitAdjustment()" class="p-6 space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-[10px] font-extrabold text-slate-600 uppercase tracking-wider">Date &amp; Time *</label>
                        <span class="text-[9px] font-extrabold text-emerald-600 uppercase">● Live</span>
                    </div>
                    <input type="datetime-local" x-model="newAdjustment.date" required class="w-full px-3.5 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold outline-none focus:border-amber-500">
                    <p class="text-[10px] text-slate-400 mt-1">Filled automatically. You can change it manually.</p>
                </div>

                <div>
                    <label class="block text-[10px] font-extrabold text-slate-600 uppercase tracking-wider mb-1.5">Medicine</label>
                    <div class="w-full px-3.5 py-3 bg-slate-100 border border-slate-200 rounded-xl text-xs font-extrabold text-slate-700" x-text="selectedConsumable.name || 'No medicine selected'"></div>
                    <p class="text-[10px] text-slate-400 mt-1">Adjustment applies to the selected medicine.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-extrabold text-slate-600 uppercase tracking-wider mb-1.5">Adjustment Type *</label>
                    <select x-model="newAdjustment.type" required class="w-full px-3.5 py-3 bg-white border border-slate-200 rounded-xl text-xs font-semibold outline-none focus:border-amber-500">
                        <option value="increase">Increase</option>
                        <option value="decrease">Decrease</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-extrabold text-slate-600 uppercase tracking-wider mb-1.5">Quantity *</label>
                    <div class="flex items-center gap-2">
                        <input type="number" min="1" step="1" x-model.number="newAdjustment.quantity" required class="flex-1 px-3.5 py-3 bg-white border border-slate-200 rounded-xl text-xs font-extrabold outline-none focus:border-amber-500">
                        <span class="text-xs font-bold text-slate-400" x-text="selectedConsumable.unit || 'Unit'"></span>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-extrabold text-slate-600 uppercase tracking-wider mb-1.5">Reason *</label>
                <textarea x-model="newAdjustment.reason" required rows="4" class="w-full px-3.5 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-amber-500" placeholder="Enter the reason for this stock adjustment..."></textarea>
            </div>

            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-800">
                <div class="font-extrabold">Stock balance will be updated immediately.</div>
                <div class="text-[11px] mt-1">The selected medicine and adjustment quantity will be recorded in the stock adjustment log.</div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                <button type="button" @click="showAdjustmentModal = false" class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600">Cancel</button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold">
                    <i class="fa-solid fa-floppy-disk mr-1"></i>
                    Save Adjustment
                </button>
            </div>
        </form>
    </div>
</div>


<!-- ================================================================ -->
<!-- ADD CONSUMABLE MODAL -->
<!-- ================================================================ -->
<div x-show="showAddConsumableModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div @click.outside="showAddConsumableModal = false" class="w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-extrabold text-slate-900">Add Consumable</h3>
                <p class="text-xs text-slate-500 mt-1">Register a new surgical consumable.</p>
            </div>
            <button @click="showAddConsumableModal = false" class="w-9 h-9 rounded-xl hover:bg-slate-100 text-slate-500"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form @submit.prevent="submitNewConsumable()" class="p-6 space-y-4">
            <div>
                <label class="block text-[10px] font-extrabold text-slate-600 uppercase tracking-wider mb-1.5">Item Name *</label>
                <input type="text" x-model="newConsumable.name" required class="w-full px-3.5 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold outline-none focus:border-blue-500" placeholder="e.g. SURGICAL GLOVES 7.5">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-extrabold text-slate-600 uppercase tracking-wider mb-1.5">Opening Balance *</label>
                    <input type="number" min="0" x-model="newConsumable.balance" required class="w-full px-3.5 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-[10px] font-extrabold text-slate-600 uppercase tracking-wider mb-1.5">Unit *</label>
                    <input type="text" x-model="newConsumable.unit" required class="w-full px-3.5 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold outline-none focus:border-blue-500" placeholder="Box">
                </div>
            </div>
            <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                <button type="button" @click="showAddConsumableModal = false" class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600">Cancel</button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold">Add Item</button>
            </div>
        </form>
    </div>
</div>

<!-- ================================================================ -->
<!-- TOASTS -->
<!-- ================================================================ -->
<div class="fixed right-6 bottom-6 z-[100] space-y-2 w-[360px] max-w-[calc(100vw-48px)]">
    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="toast.visible" x-transition class="bg-white border rounded-2xl shadow-xl p-4 flex items-start gap-3" :class="toast.type === 'error' ? 'border-red-200' : 'border-emerald-200'">
            <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0" :class="toast.type === 'error' ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600'">
                <i :class="toast.type === 'error' ? 'fa-solid fa-circle-exclamation' : 'fa-solid fa-circle-check'"></i>
            </div>
            <div class="text-xs font-semibold text-slate-700 pt-1" x-text="toast.message"></div>
        </div>
    </template>
</div>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
function surgicalConsumableApp() {
    return {
        csrfToken: '',
        currentRole: @json($activeRole),
        currentUserId: @json($currentUserId),
        currentUserName: @json($currentUserName),

        sidebarSearch: '',
        logSearch: '',
        logDateFilter: '',
        activeTab: 'requisitions',

        showAddOrderModal: false,
        showAddConsumableModal: false,
        showMsApprovalModal: false,
        showNurseReceiveModal: false,
        showRejectModal: false,
        showDailyCountModal: false,
        showAdjustmentModal: false,

        consumables: [],
        requisitions: [],
        adjustments: [],
        dailyCounts: [],
        selectedConsumable: { id: null, name: '', balance: 0, unit: 'Box' },
        toasts: [],

        newOrder: {
            date: '',
            items: []
        },

        newConsumable: {
            name: '',
            balance: 0,
            unit: 'Box'
        },

        msModalOrder: {
            id: null,
            req_no: '',
            date: '',
            requested_by: '',
            items: [],
            remark: ''
        },

        receiveModalOrder: {
            id: null,
            req_no: '',
            items: []
        },

        rejectModalOrder: {
            id: null,
            req_no: '',
            reason: ''
        },

        newDailyCount: {
            date: '',
            qty_given: 1,
            remarks: ''
        },

        newAdjustment: {
            date: '',
            type: 'increase',
            quantity: 1,
            reason: ''
        },

        init() {
            this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            this.currentRole = String(this.currentRole || 'nurse').toLowerCase();

            this.fetchConsumables();
            this.fetchRequisitions();
            this.fetchAdjustments();
            this.fetchDailyCounts();

            // Keep the requisition list fresh for nurse/MS screens.
            if (this.currentRole === 'nurse' || this.currentRole === 'ms' || this.currentRole === 'admin' || this.currentRole === 'pharmacist') {
                setInterval(() => {
                    if (!this.isAnyModalOpen()) {
                        this.fetchRequisitions();
                        this.fetchConsumables();
                        this.fetchDailyCounts();
                    }
                }, 15000);
            }
        },

        isAnyModalOpen() {
    return this.showAddOrderModal ||
        this.showAddConsumableModal ||
        this.showMsApprovalModal ||
        this.showNurseReceiveModal ||
        this.showRejectModal ||
        this.showDailyCountModal ||
        this.showAdjustmentModal;
    },

        getLiveDateTime() {
            const now = new Date();
            const y = now.getFullYear();
            const m = String(now.getMonth() + 1).padStart(2, '0');
            const d = String(now.getDate()).padStart(2, '0');
            const h = String(now.getHours()).padStart(2, '0');
            const min = String(now.getMinutes()).padStart(2, '0');
            return `${y}-${m}-${d}T${h}:${min}`;
        },

        isLowStock(item) {
            const n = Number(item?.balance ?? 0);
            return !Number.isNaN(n) && n <= 10;
        },

        getOrderStatus(log) {
            const status = String(log?.status || log?.ms_approval_status || 'pending').toLowerCase();

            if (status === 'rejected') return 'rejected';
            if (status === 'received' || status === 'completed') return 'received';
            if (status === 'approved') return 'approved';
            return 'pending';
        },

        get filteredConsumables() {
            const q = this.sidebarSearch.toLowerCase().trim();
            if (!q) return this.consumables;
            return this.consumables.filter(item => String(item.name).toLowerCase().includes(q));
        },

        get filteredRequisitions() {
            const selectedName = String(this.selectedConsumable?.name || '').toLowerCase();
            const search = this.logSearch.toLowerCase().trim();

            return this.requisitions
                .filter(log => {
                    const itemMatch = !selectedName ||
                        String(log.supply_name || '').toLowerCase().includes(selectedName) ||
                        (log.items || []).some(item => String(item.name || '').toLowerCase().includes(selectedName));

                    const searchMatch = !search ||
                        String(log.req_no || '').toLowerCase().includes(search) ||
                        String(log.requested_by || '').toLowerCase().includes(search) ||
                        String(log.approved_by || '').toLowerCase().includes(search) ||
                        String(log.received_by || '').toLowerCase().includes(search) ||
                        String(log.supply_name || '').toLowerCase().includes(search) ||
                        String(log.date || '').toLowerCase().includes(search);

                    const dateMatch = !this.logDateFilter || String(log.date || '').slice(0, 10) === this.logDateFilter;

                    return itemMatch && searchMatch && dateMatch;
                })
                .sort((a, b) => {
                    return String(b.date || '').localeCompare(String(a.date || '')) || Number(b.id) - Number(a.id);
                });
        },

        get filteredAdjustments() {
            const selectedName = String(this.selectedConsumable?.name || '').toLowerCase();
            const search = this.logSearch.toLowerCase().trim();

            return this.adjustments.filter(adj => {
                const item = String(adj.item_name || '').toLowerCase();
                const matchesItem = !selectedName || item.includes(selectedName) || selectedName.includes(item);
                const matchesSearch = !search ||
                    item.includes(search) ||
                    String(adj.type || '').toLowerCase().includes(search) ||
                    String(adj.reason || '').toLowerCase().includes(search) ||
                    String(adj.adjusted_by || '').toLowerCase().includes(search);
                const matchesDate = !this.logDateFilter || String(adj.date || '').slice(0, 10) === this.logDateFilter;
                return matchesItem && matchesSearch && matchesDate;
            });
        },

        selectConsumable(item) {
            this.selectedConsumable = { ...item };
        },

        openAddOrderModal() {
            if (!this.currentUserName) {
                this.showToast('The logged-in user could not be identified. Please check the main login integration.', 'error');
                return;
            }

            this.newOrder.date = this.getLiveDateTime();

            const item = this.selectedConsumable?.name
                ? this.selectedConsumable
                : (this.consumables[0] || null);

            this.newOrder.items = item ? [{
                name: item.name,
                requested_qty_num: 1,
                unit: item.unit || 'Box',
                balance: `${item.balance ?? 0} ${item.unit || 'Box'}`
            }] : [];

            this.showAddOrderModal = true;
        },

        addOrderItemRow() {
            const item = this.consumables[0];
            if (!item) return;

            this.newOrder.items.push({
                name: item.name,
                requested_qty_num: 1,
                unit: item.unit || 'Box',
                balance: `${item.balance ?? 0} ${item.unit || 'Box'}`
            });
        },

        removeOrderItemRow(index) {
            if (this.newOrder.items.length <= 1) return;
            this.newOrder.items.splice(index, 1);
        },

        onOrderItemChange(item) {
            const match = this.consumables.find(c => c.name === item.name);
            if (!match) return;

            item.unit = match.unit || 'Box';
            item.balance = `${match.balance ?? 0} ${item.unit}`;
        },

        async submitNewOrder() {
            if (!this.newOrder.items.length) {
                this.showToast('Please add at least one item.', 'error');
                return;
            }

            if (!this.currentUserId || !this.currentUserName) {
                this.showToast('No authenticated nurse was found.', 'error');
                return;
            }

            const payload = {
                date: this.newOrder.date,
                items: this.newOrder.items.map(item => ({
                    name: item.name,
                    requested_qty_num: Number(item.requested_qty_num),
                    unit: item.unit || 'Box'
                }))
            };

            try {
                const res = await fetch('/api/surgical-consumable-2/requisitions', {
                    method: 'POST',
                    headers: this.jsonHeaders(),
                    body: JSON.stringify(payload)
                });

                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    throw new Error(data.message || 'Unable to create requisition.');
                }

                this.showAddOrderModal = false;
                this.showToast('Order submitted successfully. Waiting for MS approval.');
                await this.fetchRequisitions();
            } catch (error) {
                console.error(error);
                this.showToast(error.message || 'Unable to create requisition.', 'error');
            }
        },

        openMsApprovalModal(log) {
            this.msModalOrder = {
                id: log.id,
                req_no: log.req_no,
                date: log.date,
                requested_by: log.requested_by || '—',
                items: JSON.parse(JSON.stringify(log.items || [])),
                remark: ''
            };

            if (!this.msModalOrder.items.length) {
                this.msModalOrder.items = [{
                    id: log.id,
                    name: log.supply_name,
                    requested_qty_num: Number.parseInt(log.requested_qty) || 1,
                    unit: 'Box',
                    balance: log.balance || '0 Box'
                }];
            }

            this.showMsApprovalModal = true;
        },

        removeMsItemRow(index) {
            if (this.msModalOrder.items.length <= 1) {
                this.showToast('At least one item must remain. Reject the request instead if no item should be approved.', 'error');
                return;
            }
            this.msModalOrder.items.splice(index, 1);
        },

        async submitMsApproval() {
            if (!this.currentUserId || !this.currentUserName) {
                this.showToast('No authenticated MS was found.', 'error');
                return;
            }

            const payload = {
                items: this.msModalOrder.items.map(item => ({
                    id: item.id,
                    name: item.name,
                    requested_qty_num: Number(item.requested_qty_num),
                    unit: item.unit || 'Box'
                })),
                remark: this.msModalOrder.remark
            };

            try {
                const res = await fetch(`/api/surgical-consumable-2/requisitions/${this.msModalOrder.id}/approve`, {
                    method: 'PUT',
                    headers: this.jsonHeaders(),
                    body: JSON.stringify(payload)
                });

                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    throw new Error(data.message || 'Unable to approve requisition.');
                }

                this.showMsApprovalModal = false;
                this.showToast(`Order ${this.msModalOrder.req_no} approved by ${this.currentUserName}.`);
                await this.fetchRequisitions();
            } catch (error) {
                console.error(error);
                this.showToast(error.message || 'Unable to approve requisition.', 'error');
            }
        },

        openRejectModalFromMsModal() {
            this.rejectModalOrder = {
                id: this.msModalOrder.id,
                req_no: this.msModalOrder.req_no,
                reason: ''
            };
            this.showMsApprovalModal = false;
            this.showRejectModal = true;
        },

        async submitRejectOrder() {
            try {
                const res = await fetch(`/api/surgical-consumable-2/requisitions/${this.rejectModalOrder.id}/reject`, {
                    method: 'PUT',
                    headers: this.jsonHeaders(),
                    body: JSON.stringify({ reason: this.rejectModalOrder.reason })
                });

                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    throw new Error(data.message || 'Unable to reject requisition.');
                }

                this.showRejectModal = false;
                this.showToast('Requisition rejected.');
                await this.fetchRequisitions();
            } catch (error) {
                console.error(error);
                this.showToast(error.message || 'Unable to reject requisition.', 'error');
            }
        },

        async submitInlineReceive(log) {
            if (this.getOrderStatus(log) !== 'approved') {
                this.showToast('Only MS-approved requests can be received.', 'error');
                return;
            }

            if (!this.currentUserId || !this.currentUserName) {
                this.showToast('No authenticated receiving nurse was found.', 'error');
                return;
            }

            const item = log.items?.[0];
            const receivedQty = Number(log.receive_qty_input);
            const approvedQty = Number(item?.requested_qty_num || 0);

            if (!Number.isInteger(receivedQty) || receivedQty < 1) {
                this.showToast('Enter a valid received quantity.', 'error');
                return;
            }

            if (receivedQty > approvedQty) {
                this.showToast('Received quantity cannot exceed the MS-approved quantity.', 'error');
                return;
            }

            try {
                const res = await fetch(`/api/surgical-consumable-2/requisitions/${log.id}/receive`, {
                    method: 'PUT',
                    headers: this.jsonHeaders(),
                    body: JSON.stringify({
                        items: [{
                            id: item.id,
                            received_qty_num: receivedQty
                        }]
                    })
                });

                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    throw new Error(data.message || 'Unable to record received quantity.');
                }

                this.showToast(`Received quantity recorded by ${this.currentUserName}.`);
                await this.fetchRequisitions();
                await this.fetchConsumables();
            } catch (error) {
                console.error(error);
                this.showToast(error.message || 'Unable to record received quantity.', 'error');
            }
        },

        openNurseReceiveModal(log) {
            if (!this.currentUserId || !this.currentUserName) {
                this.showToast('No authenticated receiving nurse was found.', 'error');
                return;
            }

            const items = JSON.parse(JSON.stringify(log.items || []));

            items.forEach(item => {
                item.received_qty_num = Number(item.received_qty_num || 0);
            });

            this.receiveModalOrder = {
                id: log.id,
                req_no: log.req_no,
                items
            };

            this.showNurseReceiveModal = true;
        },

        async submitNurseReceive() {
            for (const item of this.receiveModalOrder.items) {
                const received = Number(item.received_qty_num);
                const approved = Number(item.requested_qty_num);

                if (!Number.isInteger(received) || received < 1) {
                    this.showToast('Enter a valid received quantity for every item.', 'error');
                    return;
                }

                if (received > approved) {
                    this.showToast('Received quantity cannot exceed the MS-approved quantity.', 'error');
                    return;
                }
            }

            try {
                const payload = {
                    items: this.receiveModalOrder.items.map(item => ({
                        id: item.id,
                        received_qty_num: Number(item.received_qty_num)
                    }))
                };

                const res = await fetch(`/api/surgical-consumable-2/requisitions/${this.receiveModalOrder.id}/receive`, {
                    method: 'PUT',
                    headers: this.jsonHeaders(),
                    body: JSON.stringify(payload)
                });

                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    throw new Error(data.message || 'Unable to record received quantity.');
                }

                this.showNurseReceiveModal = false;
                this.showToast(`Received quantity recorded by ${this.currentUserName}.`);
                await this.fetchRequisitions();
                await this.fetchConsumables();
            } catch (error) {
                console.error(error);
                this.showToast(error.message || 'Unable to record received quantity.', 'error');
            }
        },

        async deleteRequisition(id) {
            const record = this.requisitions.find(r => Number(r.id) === Number(id));

            if (!record) return;

            if (this.getOrderStatus(record) !== 'pending') {
                this.showToast('Only pending requests can be deleted.', 'error');
                return;
            }

            if (!confirm('Delete this pending requisition?')) return;

            try {
                const res = await fetch(`/api/surgical-consumable-2/requisitions/${id}`, {
                    method: 'DELETE',
                    headers: this.jsonHeaders()
                });

                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    throw new Error(data.message || 'Unable to delete requisition.');
                }

                this.showToast('Pending requisition deleted.');
                await this.fetchRequisitions();
            } catch (error) {
                console.error(error);
                this.showToast(error.message || 'Unable to delete requisition.', 'error');
            }
        },

        viewRejection(log) {
            alert(`Requisition ${log.req_no}\n\nReason: ${log.rejection_reason || 'No reason recorded.'}`);
        },

        openAddConsumableModal() {
            this.newConsumable = { name: '', balance: 0, unit: 'Box' };
            this.showAddConsumableModal = true;
        },

        async submitNewConsumable() {
            try {
                const res = await fetch('/api/surgical-consumable-2/consumables', {
                    method: 'POST',
                    headers: this.jsonHeaders(),
                    body: JSON.stringify({
                        name: this.newConsumable.name.trim().toUpperCase(),
                        balance: Number(this.newConsumable.balance),
                        unit: this.newConsumable.unit.trim()
                    })
                });

                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    throw new Error(data.message || 'Unable to add consumable.');
                }

                this.showAddConsumableModal = false;
                this.showToast('Consumable added successfully.');
                await this.fetchConsumables();
            } catch (error) {
                console.error(error);
                this.showToast(error.message || 'Unable to add consumable.', 'error');
            }
        },

        openAdjustmentModal() {
            if (!this.selectedConsumable?.name) {
                this.showToast('Please select a surgical consumable first.', 'error');
                return;
            }

            if (!this.currentUserId || !this.currentUserName) {
                this.showToast('No authenticated user was found.', 'error');
                return;
            }

            this.newAdjustment = {
                date: this.getLiveDateTime(),
                type: 'increase',
                quantity: 1,
                reason: ''
            };

            this.showAdjustmentModal = true;
        },

        async submitAdjustment() {
            const quantity = Number(this.newAdjustment.quantity);

            if (!Number.isInteger(quantity) || quantity < 1) {
                this.showToast('Enter a valid adjustment quantity.', 'error');
                return;
            }

            if (!['increase', 'decrease'].includes(this.newAdjustment.type)) {
                this.showToast('Select Increase or Decrease.', 'error');
                return;
            }

            if (this.newAdjustment.type === 'decrease' && quantity > Number(this.selectedConsumable?.balance ?? 0)) {
                this.showToast('The decrease quantity cannot be greater than the available balance.', 'error');
                return;
            }

            if (!this.newAdjustment.reason || !this.newAdjustment.reason.trim()) {
                this.showToast('Please enter a reason for the adjustment.', 'error');
                return;
            }

            if (!this.selectedConsumable?.name) {
                this.showToast('Please select a surgical consumable first.', 'error');
                return;
            }

            try {
                const res = await fetch('/api/surgical-consumable-2/adjustments', {
                    method: 'POST',
                    headers: this.jsonHeaders(),
                    body: JSON.stringify({
                        date: this.newAdjustment.date,
                        item_name: this.selectedConsumable.name,
                        type: this.newAdjustment.type,
                        quantity: quantity,
                        reason: this.newAdjustment.reason.trim()
                    })
                });

                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    const validation = data.errors
                        ? Object.values(data.errors).flat().join(' ')
                        : '';
                    throw new Error(validation || data.message || 'Unable to save stock adjustment.');
                }

                this.showAdjustmentModal = false;
                this.showToast('Stock adjustment recorded successfully.');
                await this.fetchConsumables();
                await this.fetchAdjustments();
            } catch (error) {
                console.error('submitAdjustment', error);
                this.showToast(error.message || 'Unable to save stock adjustment.', 'error');
            }
        },

        async fetchConsumables() {
            try {
                const res = await fetch('/api/surgical-consumable-2/consumables', {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    throw new Error(
                        data.message || 'Unable to load surgical consumables.'
                    );
                }

                this.consumables = Array.isArray(data) ? data : [];

                if (this.selectedConsumable.id) {
                    const current = this.consumables.find(
                        item => Number(item.id) === Number(this.selectedConsumable.id)
                    );

                    if (current) {
                        this.selectedConsumable = { ...current };
                        return;
                    }
                }

                if (this.consumables.length) {
                    this.selectedConsumable = { ...this.consumables[0] };
                }

            } catch (error) {
                console.error('fetchConsumables', error);

                this.showToast(
                    error.message || 'Unable to load surgical consumables.',
                    'error'
                );
            }
        },

        async fetchRequisitions() {
            try {
                const res = await fetch('/api/surgical-consumable-2/requisitions', {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    throw new Error(
                        data.message || 'Unable to load requisitions.'
                    );
                }

                this.requisitions = Array.isArray(data)
                    ? data.map(record => ({
                        ...record,
                        items: Array.isArray(record.items)
                            ? record.items
                            : [],
                        requested_qty: record.requested_qty || '',
                        received_qty: record.received_qty || ''
                    }))
                    : [];

            } catch (error) {
                console.error('fetchRequisitions', error);

                this.showToast(
                    error.message || 'Unable to load requisitions.',
                    'error'
                );
            }
        },

        get dailyCountCalculatedBalance() {
            const current = Number(this.selectedConsumable?.balance ?? 0);
            const given = Number(this.newDailyCount?.qty_given ?? 0);
            if (!Number.isFinite(current) || !Number.isFinite(given)) return 0;
            return current - given;
        },

        get filteredDailyCounts() {
            const selectedName = String(this.selectedConsumable?.name || '').toLowerCase();
            const search = this.logSearch.toLowerCase().trim();

            return this.dailyCounts.filter(record => {
                const itemName = String(record.item_name || '').toLowerCase();
                const matchesItem = !selectedName || itemName === selectedName;
                const matchesSearch = !search ||
                    itemName.includes(search) ||
                    String(record.sisters_initials || '').toLowerCase().includes(search) ||
                    String(record.remarks || '').toLowerCase().includes(search);
                const matchesDate = !this.logDateFilter ||
                    String(record.date || '').slice(0, 10) === this.logDateFilter;

                return matchesItem && matchesSearch && matchesDate;
            });
        },

        openDailyCountModal() {
            if (!this.selectedConsumable?.name) {
                this.showToast('Please select a surgical item first.', 'error');
                return;
            }

            if (!this.currentUserId || !this.currentUserName) {
                this.showToast('No authenticated nurse was found.', 'error');
                return;
            }

            this.newDailyCount = {
                date: this.getLiveDateTime(),
                qty_given: 1,
                remarks: ''
            };
            this.showDailyCountModal = true;
        },

        async fetchDailyCounts() {
            try {
                const res = await fetch(
                    '/api/surgical-consumable-2/daily-counts',
                    {
                        headers: {
                            'Accept': 'application/json'
                        }
                    }
                );

                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    throw new Error(
                        data.message || 'Unable to load daily counts.'
                    );
                }

                this.dailyCounts = Array.isArray(data) ? data : [];

            } catch (error) {
                console.error('fetchDailyCounts', error);

                this.showToast(
                    error.message || 'Unable to load daily counts.',
                    'error'
                );
            }
        },

        async submitDailyCount() {
            const qty = Number(this.newDailyCount.qty_given);
            const balance = Number(this.selectedConsumable?.balance ?? 0);

            if (!Number.isInteger(qty) || qty < 1) {
                this.showToast('Enter a valid quantity given.', 'error');
                return;
            }

            if (qty > balance) {
                this.showToast(`Quantity given cannot exceed the available balance of ${balance}.`, 'error');
                return;
            }

            try {
                const res = await fetch('/api/surgical-consumable-2/daily-counts', {
                    method: 'POST',
                    headers: this.jsonHeaders(),
                    body: JSON.stringify({
                        date: this.newDailyCount.date,
                        item_name: this.selectedConsumable.name,
                        qty_given: qty,
                        sisters_initials: this.currentUserName,
                        remarks: this.newDailyCount.remarks || ''
                    })
                });

                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    throw new Error(data.message || 'Unable to save daily count.');
                }

                this.showDailyCountModal = false;
                this.showToast('Daily count recorded and available stock reduced.');
                await this.fetchConsumables();
                await this.fetchDailyCounts();
            } catch (error) {
                console.error(error);
                this.showToast(error.message || 'Unable to save daily count.', 'error');
            }
        },

        async fetchAdjustments() {
            try {
                const res = await fetch(
                    '/api/surgical-consumable-2/adjustments',
                    {
                        headers: {
                            'Accept': 'application/json'
                        }
                    }
                );

                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    throw new Error(
                        data.message || 'Unable to load stock adjustments.'
                    );
                }

                this.adjustments = Array.isArray(data) ? data : [];

            } catch (error) {
                console.error('fetchAdjustments', error);

                this.showToast(
                    error.message || 'Unable to load stock adjustments.',
                    'error'
                );
            }
        },

        jsonHeaders() {
            return {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
            };
        },

        formatDateTime(value) {
            if (!value) return '—';

            const parsed = new Date(String(value).replace(' ', 'T'));
            if (Number.isNaN(parsed.getTime())) return value;

            return parsed.toLocaleString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        getInitials(name) {
            if (!name) return 'U';
            return String(name)
                .trim()
                .split(/\s+/)
                .map(part => part[0])
                .join('')
                .slice(0, 2)
                .toUpperCase();
        },

        clearFilters() {
            this.logSearch = '';
            this.logDateFilter = '';
        },

        showToast(message, type = 'success') {
            const id = Date.now() + Math.random();
            this.toasts.push({ id, message, type, visible: true });

            setTimeout(() => {
                const toast = this.toasts.find(item => item.id === id);
                if (toast) toast.visible = false;

                setTimeout(() => {
                    this.toasts = this.toasts.filter(item => item.id !== id);
                }, 300);
            }, 3500);
        }
    };
}
</script>
</body>
</html>
