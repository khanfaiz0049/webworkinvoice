<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-50 text-slate-900">
        <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden">
            <!-- Sidebar -->
            <aside class="fixed inset-y-0 left-0 z-50 w-72 bg-white border-r border-slate-200 transition-transform duration-300 transform lg:translate-x-0 lg:static lg:inset-0 shadow-sm"
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
                
                <div class="flex flex-col h-full">
                    <!-- Sidebar Header -->
                    <div class="flex items-center gap-3 px-8 h-20 border-b border-slate-100">
                        <a href="{{ route('dashboard') }}" class="flex items-center">
                            <x-application-logo class="h-8 w-auto" />
                        </a>
                    </div>

                    <!-- Sidebar Links -->
                    <nav class="flex-1 px-4 py-8 space-y-1.5 overflow-y-auto">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-[#0055a4] font-bold shadow-sm border-l-4 border-[#0055a4]' : 'text-slate-600 hover:bg-slate-50 hover:text-[#0055a4]' }}">
                            <i data-lucide="layout-grid" class="w-5 h-5"></i>
                            <span class="uppercase tracking-widest text-xs font-black">Dashboard</span>
                        </a>

                        <div class="pt-8 pb-3 px-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Management</div>
                        
                        <a href="{{ route('companies.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('companies.*') ? 'bg-blue-50 text-[#0055a4] font-bold shadow-sm border-l-4 border-[#0055a4]' : 'text-slate-600 hover:bg-slate-50 hover:text-[#0055a4]' }}">
                            <i data-lucide="building-2" class="w-5 h-5"></i>
                            <span class="uppercase tracking-widest text-xs font-black">Companies</span>
                        </a>
                        <a href="{{ route('customers.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('customers.*') ? 'bg-blue-50 text-[#0055a4] font-bold shadow-sm border-l-4 border-[#0055a4]' : 'text-slate-600 hover:bg-slate-50 hover:text-[#0055a4]' }}">
                            <i data-lucide="users" class="w-5 h-5"></i>
                            <span class="uppercase tracking-widest text-xs font-black">Customers</span>
                        </a>
                        <a href="{{ route('invoices.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('invoices.*') ? 'bg-blue-50 text-[#0055a4] font-bold shadow-sm border-l-4 border-[#0055a4]' : 'text-slate-600 hover:bg-slate-50 hover:text-[#0055a4]' }}">
                            <i data-lucide="file-text" class="w-5 h-5"></i>
                            <span class="uppercase tracking-widest text-xs font-black">Invoices</span>
                        </a>
                        <a href="{{ route('payments.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('payments.*') ? 'bg-blue-50 text-[#0055a4] font-bold shadow-sm border-l-4 border-[#0055a4]' : 'text-slate-600 hover:bg-slate-50 hover:text-[#0055a4]' }}">
                            <i data-lucide="credit-card" class="w-5 h-5"></i>
                            <span class="uppercase tracking-widest text-xs font-black">Payments</span>
                        </a>
                        <a href="{{ route('renewals.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('renewals.*') ? 'bg-red-50 text-[#d32d27] font-bold shadow-sm border-l-4 border-[#d32d27]' : 'text-slate-600 hover:bg-red-50 hover:text-[#d32d27]' }}">
                            <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                            <span class="uppercase tracking-widest text-xs font-black">Renewals</span>
                        </a>
                        <a href="{{ route('expenses.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('expenses.*') ? 'bg-blue-50 text-[#0055a4] font-bold shadow-sm border-l-4 border-[#0055a4]' : 'text-slate-600 hover:bg-slate-50 hover:text-[#0055a4]' }}">
                            <i data-lucide="wallet" class="w-5 h-5"></i>
                            <span class="uppercase tracking-widest text-xs font-black">Expenses</span>
                        </a>

                        <div class="pt-8 pb-3 px-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Reports</div>
                        
                        <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 text-slate-600 hover:bg-slate-50 hover:text-[#0055a4]">
                            <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                            <span class="uppercase tracking-widest text-xs font-black">Revenue</span>
                        </a>
                        <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 text-slate-600 hover:bg-slate-50 hover:text-[#0055a4]">
                            <i data-lucide="pie-chart" class="w-5 h-5"></i>
                            <span class="uppercase tracking-widest text-xs font-black">GST Reports</span>
                        </a>
                    </nav>

                    <!-- Sidebar Footer -->
                    <div class="p-6 border-t border-slate-100">
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 text-slate-600 hover:bg-slate-100">
                            <i data-lucide="settings-2" class="w-5 h-5"></i>
                            <span class="uppercase tracking-widest text-xs font-black">Settings</span>
                        </a>
                    </div>
                </div>
            </aside>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
                <!-- Top Navigation -->
                <header class="h-20 bg-white/80 backdrop-blur-md border-b border-slate-200 sticky top-0 z-40 flex items-center justify-between px-8">
                    <div class="flex items-center gap-6">
                        <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg hover:bg-slate-100 lg:hidden text-slate-600">
                            <i data-lucide="align-left" class="w-6 h-6"></i>
                        </button>
                        @isset($header)
                            <div class="text-xl font-black text-slate-900 uppercase tracking-tighter italic">
                                {{ $header }}
                            </div>
                        @endisset
                    </div>

                    <div class="flex items-center gap-6">
                        <!-- Company Selector (Commented out as per user request, moved to specific forms) -->
                        {{-- 
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-3 px-4 py-2 rounded-xl bg-slate-100 border border-slate-200 text-slate-900 shadow-inner group cursor-pointer hover:bg-white hover:border-[#0055a4] transition-all">
                                <i data-lucide="building" class="w-4 h-4 text-[#0055a4]"></i>
                                <span class="text-xs font-black uppercase tracking-widest">
                                    {{ $activeCompany ? $activeCompany->name : 'Select Company' }}
                                </span>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 group-hover:text-[#0055a4]"></i>
                            </button>

                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-3 w-64 bg-white rounded-2xl shadow-2xl border border-slate-100 py-3 overflow-hidden z-50">
                                <div class="px-6 py-2 mb-2">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Switch Company</p>
                                </div>
                                <div class="max-h-60 overflow-y-auto">
                                    @foreach($companies as $company)
                                        <form method="POST" action="{{ route('companies.switch') }}">
                                            @csrf
                                            <input type="hidden" name="company_id" value="{{ $company->id }}">
                                            <button type="submit" class="w-full flex items-center justify-between px-6 py-2.5 text-xs font-bold transition-colors {{ $activeCompany && $activeCompany->id == $company->id ? 'text-[#0055a4] bg-blue-50' : 'text-slate-600 hover:bg-slate-50' }}">
                                                <span>{{ $company->name }}</span>
                                                @if($activeCompany && $activeCompany->id == $company->id)
                                                    <i data-lucide="check" class="w-4 h-4"></i>
                                                @endif
                                            </button>
                                        </form>
                                    @endforeach
                                </div>
                                <div class="border-t border-slate-50 my-2"></div>
                                <a href="{{ route('companies.create') }}" class="flex items-center gap-3 px-6 py-2.5 text-xs font-bold text-[#d32d27] hover:bg-red-50 transition-colors">
                                    <i data-lucide="plus-circle" class="w-4 h-4"></i> Add New Company
                                </a>
                            </div>
                        </div>
                        --}}

                        <!-- User Menu -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-3 p-1.5 rounded-2xl bg-white border border-slate-200 hover:shadow-md transition-all active:scale-95">
                                <div class="w-9 h-9 rounded-xl bg-[#d32d27] flex items-center justify-center text-white font-black text-sm shadow-lg shadow-red-500/20">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </div>
                                <span class="hidden sm:block text-xs font-black uppercase tracking-widest pr-2 text-slate-700">{{ Auth::user()->name }}</span>
                            </button>

                            <div x-show="open" 
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                @click.away="open = false" 
                                class="absolute right-0 mt-3 w-56 bg-white rounded-2xl shadow-2xl border border-slate-100 py-3 overflow-hidden">
                                <div class="px-6 py-2 mb-2">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Logged in as</p>
                                    <p class="text-xs font-bold text-slate-900 truncate">{{ Auth::user()->email }}</p>
                                </div>
                                <div class="border-t border-slate-50 my-2"></div>
                                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-6 py-2.5 text-xs font-bold text-slate-600 hover:bg-blue-50 hover:text-[#0055a4] transition-colors">
                                    <i data-lucide="user" class="w-4 h-4"></i> Profile
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-3 px-6 py-2.5 text-xs font-bold text-red-600 hover:bg-red-50 transition-colors text-left">
                                        <i data-lucide="log-out" class="w-4 h-4"></i> Log Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 overflow-y-auto p-8">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @stack('scripts')

        <!-- SweetAlert2 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        @if (session('success') || session('error') || session('status'))
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    @if (session('success'))
                        Swal.fire({
                            title: 'Success!',
                            text: "{{ session('success') }}",
                            icon: 'success',
                            confirmButtonText: 'Great',
                            confirmButtonColor: '#0055a4',
                            background: '#ffffff',
                            color: '#0f172a',
                            customClass: {
                                popup: 'rounded-[2rem] p-6 shadow-2xl border border-slate-100 font-sans',
                                title: 'text-2xl font-black uppercase tracking-tight italic text-[#0055a4]',
                                htmlContainer: 'text-sm font-medium text-slate-500 mt-2',
                                confirmButton: 'bg-[#0055a4] hover:bg-[#004482] text-white font-bold py-3.5 px-8 rounded-xl transition-all duration-200 shadow-lg active:scale-95 text-xs uppercase tracking-widest outline-none border-none focus:ring-2 focus:ring-[#0055a4]/20'
                            }
                        });
                    @endif

                    @if (session('status'))
                        Swal.fire({
                            title: 'Status Update',
                            text: "{{ session('status') }}",
                            icon: 'info',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#0055a4',
                            background: '#ffffff',
                            color: '#0f172a',
                            customClass: {
                                popup: 'rounded-[2rem] p-6 shadow-2xl border border-slate-100 font-sans',
                                title: 'text-2xl font-black uppercase tracking-tight italic text-[#0055a4]',
                                htmlContainer: 'text-sm font-medium text-slate-500 mt-2',
                                confirmButton: 'bg-[#0055a4] hover:bg-[#004482] text-white font-bold py-3.5 px-8 rounded-xl transition-all duration-200 shadow-lg active:scale-95 text-xs uppercase tracking-widest outline-none border-none focus:ring-2 focus:ring-[#0055a4]/20'
                            }
                        });
                    @endif

                    @if (session('error'))
                        Swal.fire({
                            title: 'Error!',
                            text: "{{ session('error') }}",
                            icon: 'error',
                            confirmButtonText: 'Dismiss',
                            confirmButtonColor: '#d32d27',
                            background: '#ffffff',
                            color: '#0f172a',
                            customClass: {
                                popup: 'rounded-[2rem] p-6 shadow-2xl border border-slate-100 font-sans',
                                title: 'text-2xl font-black uppercase tracking-tight italic text-[#d32d27]',
                                htmlContainer: 'text-sm font-medium text-slate-500 mt-2',
                                confirmButton: 'bg-[#d32d27] hover:bg-[#b21f24] text-white font-bold py-3.5 px-8 rounded-xl transition-all duration-200 shadow-lg active:scale-95 text-xs uppercase tracking-widest outline-none border-none focus:ring-2 focus:ring-[#d32d27]/20'
                            }
                        });
                    @endif
                });
            </script>
        @endif
    </body>
</html>
