<x-app-layout>
    <x-slot name="header">
        Edit Renewal
    </x-slot>

    <div class="max-w-4xl mx-auto" x-data='{
        customers: @json($customers),
        selectedCustomerId: "{{ old('customer_id', $renewal->customer_id) }}",
        selectedCustomerName: "",
        searchQuery: "",
        searchOpen: false,
        searchLoading: false,
        searchResults: [],

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
        }
    }'>
        <form action="{{ route('renewals.update', $renewal) }}" method="POST" class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
            @csrf
            @method('PUT')
            <div class="px-4 sm:px-6 lg:px-10 py-8 border-b border-slate-50 bg-slate-50/50">
                <h3 class="font-black text-2xl uppercase tracking-tighter italic text-slate-900">Renewal Details</h3>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Update recurring service or domain renewals</p>
            </div>

            <div class="p-4 sm:p-6 lg:p-10 space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Service/Product Name</label>
                        <input type="text" name="name" value="{{ old('name', $renewal->name) }}" required class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900 placeholder-slate-300" placeholder="e.g. Domain Renewal">
                    </div>
                    
                    {{-- Customer Selection Dropdown --}}
                    <div class="space-y-2 relative">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Customer</label>
                        <div class="relative">
                            <!-- Dropdown Trigger Button -->
                            <button type="button" @click="searchOpen = !searchOpen; if(searchOpen) { $nextTick(() => $refs.searchInput.focus()); searchCustomers(); }" 
                                class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 flex items-center justify-between focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] hover:border-[#0055a4] transition-all font-bold text-slate-900 text-left">
                                <span x-text="selectedCustomerName || 'Choose a client...'" class="block truncate" :class="selectedCustomerName ? 'text-slate-900' : 'text-slate-400'"></span>
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

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Due Date</label>
                        <input type="date" name="due_date" value="{{ old('due_date', $renewal->due_date) }}" required class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Frequency</label>
                        <select name="frequency" required class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                            <option value="Monthly" {{ old('frequency', $renewal->frequency) === 'Monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="Quarterly" {{ old('frequency', $renewal->frequency) === 'Quarterly' ? 'selected' : '' }}>Quarterly</option>
                            <option value="Half Yearly" {{ old('frequency', $renewal->frequency) === 'Half Yearly' ? 'selected' : '' }}>Half Yearly</option>
                            <option value="Yearly" {{ old('frequency', $renewal->frequency) === 'Yearly' ? 'selected' : '' }}>Yearly</option>
                            <option value="Custom" {{ old('frequency', $renewal->frequency) === 'Custom' ? 'selected' : '' }}>Custom</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Initial Status</label>
                        <select name="status" required class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                            <option value="active" {{ old('status', $renewal->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="paused" {{ old('status', $renewal->status) === 'paused' ? 'selected' : '' }}>Paused</option>
                            <option value="expired" {{ old('status', $renewal->status) === 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="px-4 sm:px-6 lg:px-10 py-8 bg-slate-50/50 border-t border-slate-50 flex items-center justify-between">
                <a href="{{ route('renewals.index') }}" class="text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-900 transition-colors">Cancel</a>
                <button type="submit" class="bg-[#d32d27] hover:bg-[#b21f24] text-white font-black py-4 px-12 rounded-2xl transition-all duration-200 shadow-xl shadow-red-500/20 active:scale-95 text-xs uppercase tracking-widest">
                    Update Renewal
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
