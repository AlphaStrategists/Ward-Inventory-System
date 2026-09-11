<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ward Inventory System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-[#F8FAFC] text-slate-800 h-screen overflow-hidden flex" x-data="{ tab: 'pharmacy' }">

    <!-- Left Sidebar -->
    <div class="w-[320px] bg-white border-r border-slate-200 flex flex-col h-full shadow-[2px_0_10px_rgba(0,0,0,0.02)] z-10">
        <!-- Sidebar Header -->
        <div class="p-6 pb-4 border-b border-transparent">
            <div class="flex items-center gap-3 mb-6">
                <!-- Icon -->
                <div class="text-[#3B82F6]">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-[#1E3A8A] tracking-tight leading-tight">Ward Inventory</h1>
                    <p class="text-[10px] font-bold text-slate-400 tracking-[0.15em] uppercase mt-0.5">Antibiotic</p>
                </div>
            </div>

            <!-- Search -->
            <div class="relative mb-5">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" class="bg-slate-50/80 border border-slate-200/80 text-slate-700 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 outline-none transition-all placeholder:text-slate-400" placeholder="Search medicines...">
            </div>

            <!-- Add Button -->
            <button class="w-full bg-[#3B82F6] hover:bg-blue-700 text-white font-semibold rounded-xl text-sm px-5 py-3 transition-all duration-200 flex items-center justify-center gap-2 shadow-[0_2px_10px_rgba(59,130,246,0.3)] hover:shadow-[0_4px_15px_rgba(59,130,246,0.4)] active:scale-[0.98]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Add Medicine
            </button>
        </div>

        <!-- Medicine List -->
        <div class="flex-1 overflow-y-auto no-scrollbar px-4 pb-6 space-y-1.5">
            @foreach($medicines as $medicine)
                @php
                    $isActive = $medicine->id === $selectedMedicine->id;
                    $badgeClass = match($medicine->stock_status) {
                        'sufficient' => 'bg-[#DCFCE7] text-[#15803D]',
                        'low' => 'bg-[#FEE2E2] text-[#B91C1C]',
                        'warning' => 'bg-[#FEF9C3] text-[#A16207]',
                        default => 'bg-slate-100 text-slate-700',
                    };
                @endphp
                <a href="{{ route('medicines.details', ['id' => $medicine->id]) }}" class="block w-full text-left rounded-xl transition-all duration-200 {{ $isActive ? 'bg-[#EFF6FF] shadow-[inset_0_1px_3px_rgba(0,0,0,0.02)] border border-blue-100/50' : 'hover:bg-slate-50 border border-transparent' }} group relative overflow-hidden">
                    @if($isActive)
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-[#3B82F6] rounded-l-xl"></div>
                    @endif
                    <div class="p-4 pl-5">
                        <div class="font-semibold text-[14.5px] text-slate-800 mb-2.5 {{ $isActive ? 'text-[#1E3A8A]' : 'group-hover:text-blue-700 transition-colors' }}">{{ $medicine->name }}</div>
                        <div class="flex items-center justify-between">
                            <div class="flex gap-2.5 text-slate-400">
                                <svg class="w-4 h-4 {{ $isActive ? 'text-[#60A5FA]' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            </div>
                            <span class="text-[11px] font-bold px-2.5 py-1 rounded-full {{ $badgeClass }}">
                                {{ $medicine->stock }} {{ $medicine->unit }}
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Right Content Area -->
    <div class="flex-1 flex flex-col h-full bg-[#F8FAFC]">
        <!-- Main Content Scrollable Area -->
        <div class="flex-1 overflow-y-auto p-8 pt-7 max-w-7xl mx-auto w-full">
            
            <!-- Hero Banner -->
            <div class="bg-gradient-to-r from-[#3B82F6] to-[#4F46E5] rounded-2xl p-7 mb-6 text-white shadow-[0_10px_25px_-5px_rgba(59,130,246,0.4)] flex justify-between items-center relative overflow-hidden">
                <div class="relative z-10">
                    <h2 class="text-[28px] font-bold mb-1.5 tracking-tight">{{ $selectedMedicine->name }}</h2>
                    <p class="text-blue-100/90 font-medium tracking-wide text-[13px]">Current Ward Stock Balance</p>
                </div>
                <div class="bg-white rounded-xl px-7 py-3 text-center shadow-[0_4px_20px_rgba(0,0,0,0.1)] relative z-10 transform transition-transform hover:scale-105 duration-300">
                    <div class="text-[32px] font-extrabold tracking-tight leading-none mb-1 {{ $selectedMedicine->stock_status === 'low' ? 'text-[#DC2626]' : 'text-[#16A34A]' }}">
                        {{ $selectedMedicine->stock }} <span class="text-lg font-bold">{{ $selectedMedicine->unit }}</span>
                    </div>
                    <div class="text-[10px] font-bold text-[#65A30D] tracking-[0.2em] uppercase mt-1">Available</div>
                </div>
            </div>

            <!-- Action / Filter Bar -->
            <div class="flex items-center justify-between bg-white rounded-[14px] p-1.5 shadow-[0_2px_10px_rgba(0,0,0,0.02)] border border-slate-100/80 mb-6">
                <!-- Tabs -->
                <div class="flex items-center p-1 relative z-0">
                    <button @click="tab = 'pharmacy'" 
                            :class="tab === 'pharmacy' ? 'bg-[#F8FAFC] text-slate-800 font-bold shadow-sm' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50 font-semibold'"
                            class="px-5 py-2.5 rounded-[10px] text-[13px] transition-all duration-200">
                        Pharmacy Orders ({{ $pharmacyOrders->count() }})
                    </button>
                    <button @click="tab = 'patient'" 
                            :class="tab === 'patient' ? 'bg-[#F8FAFC] text-slate-800 font-bold shadow-sm' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50 font-semibold'"
                            class="px-5 py-2.5 rounded-[10px] text-[13px] transition-all duration-200 ml-1">
                        Patient Administrations ({{ $patientAdministrations->count() }})
                    </button>
                </div>

                <!-- Filters -->
                <div class="flex items-center space-x-2.5 px-2">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input type="text" class="bg-[#F8FAFC] border border-slate-100 text-slate-700 text-[13px] font-medium rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-blue-200 block w-52 pl-10 p-2.5 outline-none transition-all placeholder:text-slate-400" placeholder="Search records...">
                    </div>
                    
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <input type="text" class="bg-[#F8FAFC] border border-slate-100 text-slate-600 text-[13px] font-medium rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-blue-200 block w-36 pl-10 p-2.5 outline-none transition-all placeholder:text-slate-400" placeholder="mm/dd/yyyy">
                    </div>

                    <button class="flex items-center gap-1.5 text-slate-500 hover:text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 px-3.5 py-2.5 rounded-xl text-[13px] font-semibold transition-all shadow-sm active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        Clear
                    </button>
                </div>
            </div>

            <!-- Table Container -->
            <div class="bg-white rounded-[16px] shadow-[0_2px_15px_rgba(0,0,0,0.03)] border border-slate-100/80 overflow-hidden">
                
                <!-- Pharmacy Tab Content -->
                <div x-show="tab === 'pharmacy'" x-cloak x-transition.opacity.duration.300ms>
                    <!-- Header -->
                    <div class="p-6 pb-5 flex items-center justify-between bg-white border-b border-slate-100/50">
                        <h3 class="text-[16px] font-bold text-slate-800">Pharmacy Requisitions Log</h3>
                        <button class="bg-[#3B82F6] hover:bg-[#2563EB] text-white font-semibold rounded-xl text-[13px] px-4 py-2.5 transition-all shadow-sm flex items-center gap-2 active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            Add Order
                        </button>
                    </div>
                    
                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-[13px] text-left text-slate-600">
                            <thead class="text-[11px] text-slate-400 uppercase font-bold tracking-wider border-b border-slate-100/80">
                                <tr>
                                    <th scope="col" class="px-6 py-4">Date</th>
                                    <th scope="col" class="px-6 py-4">Req. No.</th>
                                    <th scope="col" class="px-6 py-4">Qty Requested</th>
                                    <th scope="col" class="px-6 py-4">Requested By</th>
                                    <th scope="col" class="px-6 py-4">MS Approval</th>
                                    <th scope="col" class="px-6 py-4">Qty Received</th>
                                    <th scope="col" class="px-6 py-4">Issuing Officer</th>
                                    <th scope="col" class="px-6 py-4">Receiving Officer</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($pharmacyOrders as $order)
                                <tr class="hover:bg-slate-50/60 transition-colors group">
                                    <td class="px-6 py-4 font-semibold text-slate-700">{{ $order->date }}</td>
                                    <td class="px-6 py-4">
                                        <span class="text-[#3B82F6] font-semibold bg-[#EFF6FF] px-2.5 py-1 rounded-md">{{ $order->req_no }}</span>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-slate-800">{{ $order->qty_requested }}</td>
                                    <td class="px-6 py-4 font-semibold text-slate-700">{{ $order->requested_by }}</td>
                                    <td class="px-6 py-4">
                                        <span class="bg-[#DCFCE7] text-[#16A34A] font-bold px-3 py-1.5 rounded-full text-[11px]">
                                            {{ $order->ms_approval }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-slate-800">{{ $order->qty_received }}</td>
                                    <td class="px-6 py-4 text-slate-500 font-medium">{{ $order->issuing_officer }}</td>
                                    <td class="px-6 py-4 font-semibold text-slate-700">{{ $order->receiving_officer }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Patient Tab Content -->
                <div x-show="tab === 'patient'" x-cloak x-transition.opacity.duration.300ms>
                    <!-- Header -->
                    <div class="p-6 pb-5 flex items-center justify-between bg-white border-b border-slate-100/50">
                        <h3 class="text-[16px] font-bold text-slate-800">Patient Dispensations & Admin Log</h3>
                        <button class="bg-[#3B82F6] hover:bg-[#2563EB] text-white font-semibold rounded-xl text-[13px] px-4 py-2.5 transition-all shadow-sm flex items-center gap-2 active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            Add Administration
                        </button>
                    </div>
                    
                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-[13px] text-left text-slate-600">
                            <thead class="text-[11px] text-slate-400 uppercase font-bold tracking-wider border-b border-slate-100/80">
                                <tr>
                                    <th scope="col" class="px-6 py-4">Date</th>
                                    <th scope="col" class="px-6 py-4">B.H.T No.</th>
                                    <th scope="col" class="px-6 py-4">Qty Given</th>
                                    <th scope="col" class="px-6 py-4">Balance</th>
                                    <th scope="col" class="px-6 py-4">Sister Initials</th>
                                    <th scope="col" class="px-6 py-4">Remark</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($patientAdministrations as $admin)
                                <tr class="hover:bg-slate-50/60 transition-colors group">
                                    <td class="px-6 py-4 font-semibold text-slate-700">{{ $admin->date }}</td>
                                    <td class="px-6 py-4">
                                        <span class="text-[#3B82F6] font-semibold bg-[#EFF6FF] px-2.5 py-1 rounded-md">{{ $admin->bht_no }}</span>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-slate-800">{{ $admin->qty_given }}</td>
                                    <td class="px-6 py-4 font-bold {{ (int) $admin->balance < 50 ? 'text-[#DC2626]' : 'text-[#16A34A]' }}">
                                        {{ $admin->balance }}
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-slate-700">{{ $admin->sister_initials }}</td>
                                    <td class="px-6 py-4 text-slate-500 font-medium">{{ $admin->remark }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

</body>
</html>
