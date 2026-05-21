<x-app-layout>
    <x-slot name="header">New Project</x-slot>

    <div class="max-w-4xl mx-auto" x-data='{
        customers: @json($customers),
        selectedCustomerId: "{{ old('customer_id') }}",
        selectedCustomerName: "",
        searchQuery: "",
        searchOpen: false,
        searchLoading: false,
        searchResults: [],
        
        selectedServices: {{ json_encode(old('services', [])) }},
        servicesList: {{ json_encode(\App\Models\Project::getServicesList()) }},
        servicesOpen: false,

        init() {
            this.searchResults = this.customers.slice(0, 10);
            if (this.selectedCustomerId) {
                const customer = this.customers.find(c => String(c.id) === String(this.selectedCustomerId));
                if (customer) {
                    this.selectedCustomerName = customer.company_name ? `${customer.company_name} (${customer.name})` : customer.name;
                }
            }
        },
        async searchCustomers() {
            if (!this.searchQuery.trim()) {
                this.searchResults = this.customers.slice(0, 10);
                return;
            }
            this.searchLoading = true;
            try {
                const url = new URL("{{ route('api.customers.search') }}");
                url.searchParams.append("q", this.searchQuery);
                const response = await fetch(url);
                if (response.ok) {
                    this.searchResults = await response.json();
                }
            } catch (error) {
                console.error("Error searching customers:", error);
            } finally {
                this.searchLoading = false;
            }
        },
        selectCustomer(customer) {
            this.selectedCustomerId = customer.id;
            this.selectedCustomerName = customer.company_name ? `${customer.company_name} (${customer.name})` : customer.name;
            this.searchOpen = false;
            this.searchQuery = "";
        },
        toggleService(s) {
            const i = this.selectedServices.indexOf(s);
            i > -1 ? this.selectedServices.splice(i, 1) : this.selectedServices.push(s);
        },
        startDate: "{{ old('start_date', date('Y-m-d')) }}",
        renewalPeriod: "{{ old('renewal_period', '1_month') }}",
        getRenewalDate() {
            if (!this.startDate || !this.renewalPeriod) return "";
            try {
                const date = new Date(this.startDate);
                if (isNaN(date.getTime())) return "";
                
                if (this.renewalPeriod === "1_month") {
                    date.setMonth(date.getMonth() + 1);
                } else if (this.renewalPeriod === "3_months") {
                    date.setMonth(date.getMonth() + 3);
                } else if (this.renewalPeriod === "6_months") {
                    date.setMonth(date.getMonth() + 6);
                } else if (this.renewalPeriod === "yearly") {
                    date.setFullYear(date.getFullYear() + 1);
                }
                
                const yyyy = date.getFullYear();
                const mm = String(date.getMonth() + 1).padStart(2, "0");
                const dd = String(date.getDate()).padStart(2, "0");
                return `${yyyy}-${mm}-${dd}`;
            } catch (e) {
                return "";
            }
        }
    }'>
        <form action="{{ route('projects.store') }}" method="POST" class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
            @csrf
            <div class="px-4 sm:px-6 lg:px-10 py-8 border-b border-slate-50 bg-slate-50/50">
                <h3 class="font-black text-2xl uppercase tracking-tighter italic text-slate-900">Project Details</h3>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Track client projects with service renewals</p>
            </div>

            <div class="p-4 sm:p-6 lg:p-10 space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    {{-- Project Name --}}
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Project Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900 placeholder-slate-300" placeholder="e.g. Website Development">
                    </div>

                    {{-- Customer Selection Dropdown --}}
                    <div class="space-y-2 relative">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Select Client</label>
                        <div class="relative">
                            <!-- Dropdown Trigger Button -->
                            <button type="button" @click="searchOpen = !searchOpen; if(searchOpen) { $nextTick(() => $refs.searchInput.focus()); searchCustomers(); }" 
                                class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 flex items-center justify-between focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] hover:border-[#0055a4] transition-all font-bold text-slate-900 text-left">
                                <span x-text="selectedCustomerName || 'Select client...'" class="block truncate" :class="selectedCustomerName ? 'text-slate-900' : 'text-slate-400'"></span>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="searchOpen ? 'rotate-180 text-[#0055a4]' : ''"></i>
                            </button>

                            <input type="hidden" name="customer_id" :value="selectedCustomerId" required>

                            <!-- Dropdown Search Panel -->
                            <div x-show="searchOpen" x-transition 
                                @click.away="searchOpen = false" 
                                style="display: none;" 
                                class="absolute left-0 right-0 mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl p-3 space-y-3 z-50">
                                
                                <!-- Search Input -->
                                <div class="relative">
                                    <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                                    <input type="text" x-ref="searchInput" x-model="searchQuery" @input.debounce.300ms="searchCustomers()" 
                                        class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-11 pr-4 py-3 focus:bg-white focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900 text-sm placeholder:text-slate-400" 
                                        placeholder="Search customer by name, company, email, phone...">
                                    <div x-show="searchLoading" class="absolute right-4 top-1/2 -translate-y-1/2 flex items-center">
                                        <svg class="animate-spin h-4 w-4 text-[#0055a4]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                </div>

                                <!-- Search Results List -->
                                <div class="max-h-60 overflow-y-auto space-y-1 pr-1">
                                    <template x-for="customer in searchResults" :key="customer.id">
                                        <button type="button" @click="selectCustomer(customer)" 
                                            class="w-full flex items-center justify-between px-4 py-3 hover:bg-slate-50 active:bg-blue-50/50 rounded-xl text-left transition-all group">
                                            <div>
                                                <div class="font-bold text-slate-900 text-sm group-hover:text-[#0055a4] transition-colors" x-text="customer.company_name || customer.name"></div>
                                                <div class="text-[10px] text-slate-400 uppercase tracking-widest mt-0.5" x-text="(customer.company_name ? customer.name + ' • ' : '') + customer.state"></div>
                                            </div>
                                            <span class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest bg-slate-100 text-slate-500 group-hover:bg-[#0055a4]/10 group-hover:text-[#0055a4] transition-all" 
                                                x-text="customer.gst_type === 'intra_state' ? 'Intra State' : 'Inter State'"></span>
                                        </button>
                                    </template>

                                    <div x-show="!searchLoading && searchResults.length === 0" class="py-6 text-center text-slate-400 text-xs font-bold uppercase tracking-widest">
                                        No customers found
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Services multi-select --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Services <span class="text-slate-300">(select multiple)</span></label>
                    <div class="relative">
                        <button type="button" @click="servicesOpen = !servicesOpen" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 text-left focus:ring-2 focus:ring-[#0055a4] transition-all font-bold text-slate-900 flex items-center justify-between cursor-pointer hover:border-[#0055a4]">
                            <span x-show="selectedServices.length === 0" class="text-slate-300">Choose services...</span>
                            <span x-show="selectedServices.length > 0" class="flex flex-wrap gap-1.5">
                                <template x-for="s in selectedServices" :key="s">
                                    <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-blue-50 text-[#0055a4] border border-blue-100" x-text="s"></span>
                                </template>
                            </span>
                            <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 shrink-0 transition-transform" :class="servicesOpen && 'rotate-180'"></i>
                        </button>
                        <div x-show="servicesOpen" @click.away="servicesOpen = false" x-transition class="absolute z-50 w-full mt-2 bg-white rounded-2xl shadow-2xl border border-slate-100 py-2 overflow-hidden">
                            <template x-for="service in servicesList" :key="service">
                                <button type="button" @click="toggleService(service)" class="w-full flex items-center gap-3 px-5 py-3 text-left transition-all hover:bg-blue-50/50" :class="selectedServices.includes(service) ? 'bg-blue-50/30' : ''">
                                    <span class="w-5 h-5 rounded-lg border-2 flex items-center justify-center shrink-0 transition-all" :class="selectedServices.includes(service) ? 'border-[#0055a4] bg-[#0055a4]' : 'border-slate-300'">
                                        <svg x-show="selectedServices.includes(service)" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </span>
                                    <span class="text-sm font-bold text-slate-700" x-text="service"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                    <template x-for="s in selectedServices" :key="s">
                        <input type="hidden" name="services[]" :value="s">
                    </template>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Amount (₹)</label>
                        <input type="number" name="amount" value="{{ old('amount') }}" required step="0.01" min="0" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900 placeholder-slate-300" placeholder="0.00">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Status</label>
                        <select name="status" required class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                            <option value="open" {{ old('status','open')==='open' ? 'selected' : '' }}>Open</option>
                            <option value="closed" {{ old('status')==='closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Start Date</label>
                        <input type="date" name="start_date" x-model="startDate" required class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Renewal Period</label>
                        <select name="renewal_period" x-model="renewalPeriod" required class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                            @foreach(\App\Models\Project::RENEWAL_PERIODS as $value => $label)
                                <option value="{{ $value }}" {{ old('renewal_period')===$value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Next Renewal Date</label>
                        <input type="date" name="renewal_date" :value="getRenewalDate()" readonly class="w-full bg-slate-100 border-slate-200 rounded-2xl px-5 py-4 cursor-not-allowed focus:ring-0 font-bold text-slate-500">
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Notes <span class="text-slate-300">(optional)</span></label>
                    <textarea name="notes" rows="3" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900 placeholder-slate-300 resize-none" placeholder="Any additional notes...">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="px-4 sm:px-6 lg:px-10 py-8 bg-slate-50/50 border-t border-slate-50 flex items-center justify-between">
                <a href="{{ route('projects.index') }}" class="text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-900 transition-colors">Cancel</a>
                <button type="submit" class="bg-[#0055a4] hover:bg-[#004482] text-white font-black py-4 px-12 rounded-2xl transition-all duration-200 shadow-xl shadow-blue-500/20 active:scale-95 text-xs uppercase tracking-widest">Create Project</button>
            </div>
        </form>
    </div>
</x-app-layout>
