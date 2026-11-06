<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Injection Antibiotics Inventory - Ward Inventory System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-[#F8FAFC] text-slate-800 h-screen overflow-hidden flex"
    x-data="{
        searchQuery: '',
        searchDate: '',
        openAddMedicineModal: {{ $errors->has('name') ? 'true' : 'false' }},
        openAddOrderModal: {{ ($errors->has('required_quantity') || $errors->has('balance') || $errors->has('request_signature') || $errors->has('date')) && !old('_method') ? 'true' : 'false' }},
        openEditOrderModal: false,
        editOrder: {
            id: null,
            date: '',
            medicine_id: '',
            balance: 0,
            required_quantity: '',
            request_signature: '',
            received_quantity: '',
            received_signature: ''
        },
        populateEdit(order) {
            this.editOrder = {
                id: order.id,
                date: order.date ? order.date.substring(0, 10) : '',
                medicine_id: order.medicine_id,
                balance: order.balance !== undefined ? order.balance : 0,
                required_quantity: order.required_quantity,
                request_signature: order.request_signature || '',
                received_quantity: order.received_quantity !== null ? order.received_quantity : '',
                received_signature: order.received_signature || ''
            };
            this.openEditOrderModal = true;
        }
    }">

    {{-- Flash Notifications --}}
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)"
        x-transition.opacity.duration.500ms
        class="fixed top-5 right-5 z-50 bg-green-500 text-white px-6 py-3 rounded-xl shadow-lg font-semibold flex items-center gap-3">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
        x-transition.opacity.duration.500ms
        class="fixed top-5 right-5 z-50 bg-red-500 text-white px-6 py-3 rounded-xl shadow-lg font-semibold flex items-center gap-3 max-w-md">
        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)"
        x-transition.opacity.duration.500ms
        class="fixed top-5 right-5 z-50 bg-red-500 text-white px-6 py-3 rounded-xl shadow-lg font-semibold flex flex-col gap-1 max-w-md">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="font-bold">Please check the form:</span>
        </div>
        <ul class="list-disc list-inside text-xs font-normal mt-1">
            @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Left Sidebar -->
    <div class="w-[320px] bg-white border-r border-slate-200 flex flex-col h-full shadow-[2px_0_10px_rgba(0,0,0,0.02)] z-10">
        <!-- Sidebar Header -->
        <div class="p-6 pb-4 border-b border-transparent">
            <div class="flex items-center gap-3 mb-6">
                <div class="text-[#3B82F6]">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-[#1E3A8A] tracking-tight leading-tight">Ward Inventory</h1>
                    <p class="text-[10px] font-bold text-slate-400 tracking-[0.15em] uppercase mt-0.5">Injection Antibiotics</p>
                </div>
            </div>

            <!-- Search -->
            <div class="relative mb-5">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" id="sidebarSearchInput"
                    class="bg-slate-50/80 border border-slate-200/80 text-slate-700 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 outline-none transition-all placeholder:text-slate-400"
                    placeholder="Search medicines...">
            </div>

            <!-- Add Medicine Button -->
            <button @click="openAddMedicineModal = true"
                class="w-full bg-[#3B82F6] hover:bg-blue-700 text-white font-semibold rounded-xl text-sm px-5 py-3 transition-all duration-200 flex items-center justify-center gap-2 shadow-[0_2px_10px_rgba(59,130,246,0.3)] hover:shadow-[0_4px_15px_rgba(59,130,246,0.4)] active:scale-[0.98] mb-5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Medicine
            </button>
        </div>

        <!-- Medicine List -->
        <div class="flex-1 overflow-y-auto no-scrollbar px-4 pb-6 space-y-1.5" id="sidebarMedicineList">
            @forelse($medicines as $med)
            @php
                $isActive = $selectedMedicine && $selectedMedicine->id === $med->id;
                $latestReq = $med->antibioticRequisitions->sortByDesc('id')->first();
                $medBalance = $latestReq ? (int) $latestReq->balance : 0;
                $badgeClass = $medBalance > 0 ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600';
            @endphp
            <a href="{{ route('injection.antibiotics', ['medicine' => $med->id]) }}"
                class="medicine-list-item block w-full text-left rounded-xl transition-all duration-200 {{ $isActive ? 'bg-[#EFF6FF] shadow-[inset_0_1px_3px_rgba(0,0,0,0.02)] border border-blue-100/50' : 'hover:bg-slate-50 border border-transparent' }} group relative overflow-hidden">
                @if($isActive)
                <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-[#3B82F6] rounded-l-xl"></div>
                @endif
                <div class="p-4 pl-5">
                    <div class="medicine-name font-semibold text-[14.5px] text-slate-800 mb-2 {{ $isActive ? 'text-[#1E3A8A]' : 'group-hover:text-blue-700 transition-colors' }}">
                        {{ $med->name }}
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-400">Balance:</span>
                        <span class="text-[11px] font-bold px-2.5 py-1 rounded-full {{ $badgeClass }}">
                            {{ $medBalance }} Unit(s)
                        </span>
                    </div>
                </div>
            </a>
            @empty
            <div class="text-center py-10 px-4 text-slate-400">
                <i class="fa-solid fa-shield-virus text-3xl mb-3 text-slate-300"></i>
                <p class="text-sm font-semibold">No injection antibiotics yet.</p>
                <p class="text-xs mt-1">Click "+ Add Medicine" to create one.</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Right Content Area -->
    <div class="flex-1 flex flex-col h-full bg-[#F8FAFC] overflow-hidden">
        <!-- Top Header / User Profile -->
        <div class="w-full bg-white border-b border-slate-200 px-8 py-3 flex shadow-sm">
            <div class="ms-auto w-full flex justify-start sm:justify-start items-center gap-6">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#1894f2] text-white shadow-md">
                        <i class="fa-solid fa-plus text-lg"></i>
                    </div>
                    <div class="hidden sm:block">
                        <span class="block text-base font-bold text-slate-800">Hospital Inventory Management System</span>
                        <span class="block text-[10px] font-medium uppercase tracking-wider text-[#7b838f]">Hospital Management</span>
                    </div>
                </a>

                <div class="h-6 w-px bg-slate-200 hidden sm:block"></div>

                <a href="{{ route('home') }}"
                    class="hidden sm:inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-[#1894f2] bg-slate-50 hover:bg-blue-50 border border-slate-200 hover:border-blue-200 px-3.5 py-2 rounded-xl transition-all shadow-sm active:scale-95">
                    <i class="fa-solid fa-arrow-left text-[11px]"></i>
                    Back to Home
                </a>
            </div>

            <div class="ms-auto justify-content-end w-full flex justify-end">
                <div x-data="{ open: false }" class="relative dropdown">
                    <button @click="open = !open" @click.away="open = false"
                        class="dropdown-toggle flex items-center gap-3 focus:outline-none hover:bg-slate-50 p-1.5 pr-2 rounded-xl transition-colors border border-transparent hover:border-slate-200">
                        <div class="text-right hidden sm:block">
                            <div class="text-sm font-bold text-slate-700 leading-tight">{{ auth()->user()->name ?? 'User' }}</div>
                            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ $displayRole }}</div>
                        </div>
                        <div class="w-9 h-9 rounded-full bg-blue-100 text-[#3B82F6] flex items-center justify-center font-bold text-sm border border-blue-200 shadow-sm">
                            {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                        </div>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <div x-show="open" x-cloak
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="dropdown-menu absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-slate-100 py-2 z-50">
                        <div class="px-4 py-3 border-b border-slate-50 mb-1">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-0.5">Signed in as</p>
                            <p class="text-sm font-semibold text-slate-700 truncate">{{ auth()->user()->email ?? 'user@example.com' }}</p>
                        </div>
                        <form action="{{ route('logout') }}" method="POST" class="w-full m-0 p-0">
                            @csrf
                            <button type="submit"
                                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-semibold transition-colors flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Scrollable Area -->
        <div class="flex-1 overflow-y-auto p-8 pt-6 max-w-7xl mx-auto w-full">

            <!-- Horizontal Module Tabs -->
            <div class="flex flex-row flex-wrap items-center gap-2.5 bg-white rounded-2xl p-2.5 mb-6 shadow-[0_2px_10px_rgba(0,0,0,0.02)] border border-slate-200/80">
                <a href="{{ route('injection.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold transition-all duration-200 bg-slate-50 hover:bg-blue-50 text-slate-600 hover:text-[#3B82F6] border border-slate-200/70 hover:border-blue-200">
                    <i class="fa-solid fa-syringe text-[11px] text-slate-400"></i>
                    <span>Injection</span>
                </a>
                <a href="{{ route('injection.antibiotics') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold transition-all duration-200 bg-[#3B82F6] text-white shadow-sm">
                    <i class="fa-solid fa-shield-virus text-[11px] text-white"></i>
                    <span>Injection Antibiotics</span>
                </a>
            </div>

            <!-- Hero Banner -->
            <div class="bg-gradient-to-r from-[#3B82F6] to-[#4F46E5] rounded-2xl p-7 mb-6 text-white shadow-[0_10px_25px_-5px_rgba(59,130,246,0.4)] flex justify-between items-center relative overflow-hidden">
                <div class="relative z-10">
                    <h2 class="text-[28px] font-bold mb-1.5 tracking-tight">
                        {{ $selectedMedicine ? $selectedMedicine->name : 'No Medicine Selected' }}
                    </h2>
                    <p class="text-blue-100/90 font-medium tracking-wide text-[13px]">Recorded Balance & Requisitions</p>
                </div>
                <div class="bg-white rounded-xl px-7 py-3 text-center shadow-[0_4px_20px_rgba(0,0,0,0.1)] relative z-10 transform transition-transform hover:scale-105 duration-300">
                    <div class="text-[32px] font-extrabold tracking-tight leading-none mb-1 text-[#16A34A]">
                        {{ $latestBalance }} <span class="text-lg font-bold">Unit(s)</span>
                    </div>
                    <div class="text-[10px] font-bold tracking-[0.2em] uppercase mt-1 text-[#65A30D]">Recorded Balance</div>
                </div>
            </div>

            <!-- Action / Filter Bar -->
            <div class="flex items-center justify-between bg-white rounded-[14px] p-2 shadow-[0_2px_10px_rgba(0,0,0,0.02)] border border-slate-100/80 mb-6">
                <!-- Title -->
                <div class="flex items-center px-3">
                    <span class="text-[13px] font-bold text-slate-800">
                        Injection Antibiotic Orders ({{ $requisitions->count() }})
                    </span>
                </div>

                <!-- Filters -->
                <div class="flex items-center space-x-2.5 px-2">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" x-model="searchQuery"
                            class="bg-[#F8FAFC] border border-slate-100 text-slate-700 text-[13px] font-medium rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-blue-200 block w-52 pl-10 p-2.5 outline-none transition-all placeholder:text-slate-400"
                            placeholder="Search records...">
                    </div>

                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <input type="date" x-model="searchDate"
                            class="bg-[#F8FAFC] border border-slate-100 text-slate-600 text-[13px] font-medium rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-blue-200 block w-36 pl-10 p-2.5 outline-none transition-all placeholder:text-slate-400">
                    </div>

                    <button @click="searchQuery = ''; searchDate = ''"
                        class="flex items-center gap-1.5 text-slate-500 hover:text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 px-3.5 py-2.5 rounded-xl text-[13px] font-semibold transition-all shadow-sm active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Clear
                    </button>
                </div>
            </div>

            <!-- Table Container -->
            <div class="bg-white rounded-[16px] shadow-[0_2px_15px_rgba(0,0,0,0.03)] border border-slate-100/80 overflow-hidden mb-12">
                <!-- Header -->
                <div class="p-6 pb-5 flex items-center justify-between bg-white border-b border-slate-100/50">
                    <h3 class="text-[16px] font-bold text-slate-800">Injection Antibiotics Requisitions Log</h3>
                    @if($selectedMedicine)
                    <button @click="openAddOrderModal = true"
                        class="bg-[#3B82F6] hover:bg-[#2563EB] text-white font-semibold rounded-xl text-[13px] px-4 py-2.5 transition-all shadow-sm flex items-center gap-2 active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Order
                    </button>
                    @endif
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-[13px] text-left text-slate-600">
                        <thead class="text-[11px] text-slate-400 uppercase font-bold tracking-wider border-b border-slate-100/80 bg-slate-50/50">
                            <tr>
                                <th scope="col" class="px-5 py-4">Date</th>
                                <th scope="col" class="px-5 py-4">Name of Drug</th>
                                <th scope="col" class="px-5 py-4">Balance</th>
                                <th scope="col" class="px-5 py-4">Required Quantity</th>
                                <th scope="col" class="px-5 py-4">Confirm Signature for Request</th>
                                <th scope="col" class="px-5 py-4">Received Quantity</th>
                                <th scope="col" class="px-5 py-4">Confirm Signature They Received</th>
                                <th scope="col" class="px-5 py-4 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($requisitions as $req)
                            @php
                                $rowSearch = strtolower($req->medicine->name . ' ' . $req->request_signature . ' ' . $req->received_signature);
                                $rowDate = $req->date ? \Carbon\Carbon::parse($req->date)->format('Y-m-d') : '';
                            @endphp
                            <tr x-show="(searchQuery === '' || '{{ $rowSearch }}'.includes(searchQuery.toLowerCase())) && (searchDate === '' || '{{ $rowDate }}' === searchDate)"
                                class="hover:bg-slate-50/60 transition-colors">
                                <!-- 1. Date -->
                                <td class="px-5 py-4 font-semibold text-slate-700 whitespace-nowrap">
                                    {{ $req->date ? \Carbon\Carbon::parse($req->date)->format('M d, Y') : 'N/A' }}
                                </td>

                                <!-- 2. Name of Drug -->
                                <td class="px-5 py-4 font-semibold text-[#1E3A8A]">
                                    {{ $req->medicine->name ?? 'N/A' }}
                                </td>

                                <!-- 3. Balance -->
                                <td class="px-5 py-4 font-bold text-slate-700">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-slate-100 font-bold text-xs">
                                        {{ $req->balance }}
                                    </span>
                                </td>

                                <!-- 4. Required Quantity -->
                                <td class="px-5 py-4 font-bold text-slate-800">
                                    {{ $req->required_quantity }} <span class="text-xs font-normal text-slate-500">Unit(s)</span>
                                </td>

                                <!-- 5. Confirm Signature for Request -->
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-100 text-slate-700 font-medium text-xs">
                                        <i class="fa-solid fa-signature text-slate-400"></i>
                                        {{ $req->request_signature }}
                                    </span>
                                </td>

                                <!-- 6. Received Quantity -->
                                <td class="px-5 py-4 font-bold text-slate-800">
                                    @if($req->received_quantity !== null)
                                    {{ $req->received_quantity }} <span class="text-xs font-normal text-slate-500">Unit(s)</span>
                                    @else
                                    <span class="text-slate-400">&mdash;</span>
                                    @endif
                                </td>

                                <!-- 7. Confirm Signature They Received -->
                                <td class="px-5 py-4">
                                    @if($req->received_signature)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-blue-50 text-blue-800 font-medium text-xs">
                                        <i class="fa-solid fa-signature text-blue-400"></i>
                                        {{ $req->received_signature }}
                                    </span>
                                    @else
                                    <span class="text-slate-400">&mdash;</span>
                                    @endif
                                </td>

                                <!-- 8. Action -->
                                <td class="px-5 py-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-2">
                                        <!-- Edit Button -->
                                        <button type="button"
                                            @click="populateEdit(@js($req))"
                                            class="p-1.5 text-slate-400 hover:text-blue-600 transition rounded-lg hover:bg-blue-50"
                                            title="Edit Order">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                            </svg>
                                        </button>

                                        <!-- Delete Button -->
                                        <form action="{{ route('injection.antibiotics.orders.destroy', $req->id) }}" method="POST" class="inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                class="p-1.5 text-slate-400 hover:text-red-600 transition rounded-lg hover:bg-red-50 delete-btn"
                                                title="Delete Order">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fa-solid fa-folder-open text-3xl mb-3 text-slate-300"></i>
                                        <p class="text-sm font-semibold">No antibiotic requisitions recorded yet.</p>
                                        @if($selectedMedicine)
                                        <p class="text-xs text-slate-400 mt-1">Click "+ Add Order" above to record a new requisition.</p>
                                        @else
                                        <p class="text-xs text-slate-400 mt-1">Please select or add a medicine first.</p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal: Add Medicine -->
    <div x-show="openAddMedicineModal" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden transform transition-all"
            @click.away="openAddMedicineModal = false">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#3B82F6] flex items-center justify-center">
                        <i class="fa-solid fa-shield-virus text-sm"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Add Injection Antibiotic</h3>
                </div>
                <button type="button" @click="openAddMedicineModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form action="{{ route('injection.antibiotics.medicines.store') }}" method="POST">
                @csrf
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                            Drug Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" required value="{{ old('name') }}"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-blue-100 focus:border-blue-400 outline-none"
                            placeholder="e.g. MEROPENEM 1G INJECTION">
                    </div>
                </div>
                <div class="p-6 pt-3 bg-slate-50/50 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="openAddMedicineModal = false"
                        class="px-4 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 text-xs font-semibold text-white bg-[#3B82F6] hover:bg-blue-700 rounded-xl shadow-sm transition">
                        Save Medicine
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Add Order -->
    <div x-show="openAddOrderModal" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-100 overflow-hidden transform transition-all"
            @click.away="openAddOrderModal = false">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#3B82F6] flex items-center justify-center">
                        <i class="fa-solid fa-file-circle-plus text-sm"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Add Antibiotic Requisition Order</h3>
                </div>
                <button type="button" @click="openAddOrderModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form action="{{ route('injection.antibiotics.orders.store') }}" method="POST">
                @csrf
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                                Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="date" required value="{{ old('date', date('Y-m-d')) }}"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-blue-100 focus:border-blue-400 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                                Name of Drug <span class="text-red-500">*</span>
                            </label>
                            <select name="medicine_id" required
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-blue-100 focus:border-blue-400 outline-none">
                                @foreach($medicines as $med)
                                <option value="{{ $med->id }}" {{ $selectedMedicine && $selectedMedicine->id === $med->id ? 'selected' : '' }}>
                                    {{ $med->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                                Balance <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="balance" min="0" required value="{{ old('balance', $latestBalance ?? 0) }}"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-blue-100 focus:border-blue-400 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                                Required Quantity <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="required_quantity" min="1" required value="{{ old('required_quantity') }}"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-blue-100 focus:border-blue-400 outline-none"
                                placeholder="e.g. 5">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                            Confirm Signature for Request <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="request_signature" required value="{{ old('request_signature', auth()->user()->name ?? '') }}"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-blue-100 focus:border-blue-400 outline-none"
                            placeholder="Name or initials of requesting nurse/officer">
                    </div>

                    <div class="border-t border-slate-100 pt-3">
                        <p class="text-xs text-slate-400 mb-3">Optional Initial Receiving Data (can be filled when received):</p>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                                    Received Quantity
                                </label>
                                <input type="number" name="received_quantity" min="0" value="{{ old('received_quantity') }}"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-blue-100 focus:border-blue-400 outline-none"
                                    placeholder="Optional">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                                    Confirm Signature Received
                                </label>
                                <input type="text" name="received_signature" value="{{ old('received_signature') }}"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-blue-100 focus:border-blue-400 outline-none"
                                    placeholder="Optional">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-6 pt-3 bg-slate-50/50 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="openAddOrderModal = false"
                        class="px-4 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 text-xs font-semibold text-white bg-[#3B82F6] hover:bg-blue-700 rounded-xl shadow-sm transition">
                        Save Order
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Edit Order -->
    <div x-show="openEditOrderModal" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-100 overflow-hidden transform transition-all"
            @click.away="openEditOrderModal = false">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#3B82F6] flex items-center justify-center">
                        <i class="fa-solid fa-pen-to-square text-sm"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Edit Antibiotic Requisition Order</h3>
                </div>
                <button type="button" @click="openEditOrderModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form :action="'{{ url('/injection/antibiotics/orders') }}/' + editOrder.id" method="POST">
                @csrf
                @method('PUT')
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                                Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="date" required x-model="editOrder.date"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-blue-100 focus:border-blue-400 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                                Name of Drug <span class="text-red-500">*</span>
                            </label>
                            <select name="medicine_id" required x-model="editOrder.medicine_id"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-blue-100 focus:border-blue-400 outline-none">
                                @foreach($medicines as $med)
                                <option value="{{ $med->id }}">{{ $med->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                                Balance <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="balance" min="0" required x-model="editOrder.balance"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-blue-100 focus:border-blue-400 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                                Required Quantity <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="required_quantity" min="1" required x-model="editOrder.required_quantity"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-blue-100 focus:border-blue-400 outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                            Confirm Signature for Request <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="request_signature" required x-model="editOrder.request_signature"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-blue-100 focus:border-blue-400 outline-none">
                    </div>

                    <div class="border-t border-slate-100 pt-3">
                        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Receiving Record</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                                    Received Quantity
                                </label>
                                <input type="number" name="received_quantity" min="0" x-model="editOrder.received_quantity"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-blue-100 focus:border-blue-400 outline-none"
                                    placeholder="Optional">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                                    Confirm Signature They Received
                                </label>
                                <input type="text" name="received_signature" x-model="editOrder.received_signature"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-blue-100 focus:border-blue-400 outline-none"
                                    placeholder="Optional">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-6 pt-3 bg-slate-50/50 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="openEditOrderModal = false"
                        class="px-4 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 text-xs font-semibold text-white bg-[#3B82F6] hover:bg-blue-700 rounded-xl shadow-sm transition">
                        Update Order
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation & Sidebar Search Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Sidebar Search
            const sidebarSearchInput = document.getElementById('sidebarSearchInput');
            const medicineListItems = document.querySelectorAll('.medicine-list-item');

            if (sidebarSearchInput) {
                sidebarSearchInput.addEventListener('input', function () {
                    const searchTerm = this.value.toLowerCase().trim();
                    medicineListItems.forEach(item => {
                        const nameEl = item.querySelector('.medicine-name');
                        if (nameEl) {
                            const nameText = nameEl.textContent.toLowerCase().trim();
                            item.style.display = nameText.includes(searchTerm) ? '' : 'none';
                        }
                    });
                });
            }

            // Delete Order SweetAlert confirmation
            const deleteButtons = document.querySelectorAll('.delete-btn');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    const form = this.closest('.delete-form');
                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'Do you want to delete this requisition order? It will be safely soft deleted.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#EF4444',
                        cancelButtonColor: '#6B7280',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
</body>

</html>
