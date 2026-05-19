<x-app-layout>
    <x-slot name="header">
        Create Invoice
    </x-slot>

    <!-- Include Quill CSS/JS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

    <div class="max-w-6xl mx-auto" x-data='{
        items: [{ description: "", amount: 0, gst: 18, hsn: "" }],
        customers: @json($customers),
        selectedCustomerId: "",
        selectedCustomerName: "",
        gstDisabled: false,
        gstType: "intra_state",
        isIntraState: true,
        isInterState: false,
        searchQuery: "",
        searchOpen: false,
        searchLoading: false,
        searchResults: [],
        init() {
            this.$watch("selectedCustomerId", (id) => {
                const customer = this.customers.find(c => String(c.id) === String(id));
                this.gstType    = customer ? customer.gst_type : "intra_state";
                this.isIntraState = this.gstType === "intra_state";
                this.isInterState = this.gstType === "inter_state";
            });
            this.searchResults = this.customers.slice(0, 10);
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
            if (!this.customers.some(c => String(c.id) === String(customer.id))) {
                this.customers.push(customer);
            }
        },
        addItem() {
            this.items.push({ description: "", amount: 0, gst: 18, hsn: "" });
        },
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },
        calculateSubtotal() {
            return this.items.reduce((total, item) => total + parseFloat(item.amount || 0), 0);
        },
        calculateTotalGst() {
            if (this.gstDisabled) return 0;
            return this.calculateSubtotal() * 0.18;
        },
        calculateCgst() {
            return this.isInterState ? this.calculateTotalGst() / 2 : 0;
        },
        calculateSgst() {
            return this.isInterState ? this.calculateTotalGst() / 2 : 0;
        },
        calculateIgst() {
            return this.isIntraState ? this.calculateTotalGst() : 0;
        },
        calculateGrandTotal() {
            return this.calculateSubtotal() + this.calculateTotalGst();
        }
    }'>
        <form action="{{ route('invoices.store') }}" method="POST" class="space-y-8">
            @csrf
            
            <!-- Header Section -->
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100">
                <div class="px-10 py-8 border-b border-slate-50 bg-slate-50/50 rounded-t-[2.5rem] flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div>
                        <h3 class="font-black text-2xl uppercase tracking-tighter italic text-slate-900">New GST Invoice</h3>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Professional billing with automated tax calculation</p>
                    </div>
                    
                    <!-- Company Switcher inside Card -->
                    <div class="relative" x-data="{ open: false }">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Billing From</p>
                        <button type="button" @click="open = !open" class="flex items-center gap-4 px-6 py-3 rounded-2xl bg-white border border-slate-200 text-slate-900 shadow-sm group hover:border-[#0055a4] transition-all min-w-[240px] justify-between">
                            <div class="flex items-center gap-3">
                                <i data-lucide="building" class="w-4 h-4 text-[#0055a4]"></i>
                                <span class="text-sm font-black uppercase italic tracking-tight">
                                    {{ $activeCompany ? $activeCompany->name : 'Select Company' }}
                                </span>
                            </div>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 group-hover:text-[#0055a4]"></i>
                        </button>

                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-3 w-72 bg-white rounded-2xl shadow-2xl border border-slate-100 py-3 overflow-hidden z-50">
                            <div class="px-6 py-2 mb-2 border-b border-slate-50">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Switch Billing Company</p>
                            </div>
                            <div class="max-h-60 overflow-y-auto">
                                @foreach($companies as $company)
                                    <button type="button" 
                                        @click="
                                            const form = document.createElement('form');
                                            form.method = 'POST';
                                            form.action = '{{ route('companies.switch') }}';
                                            const csrf = document.createElement('input');
                                            csrf.type = 'hidden';
                                            csrf.name = '_token';
                                            csrf.value = '{{ csrf_token() }}';
                                            const cid = document.createElement('input');
                                            cid.type = 'hidden';
                                            cid.name = 'company_id';
                                            cid.value = '{{ $company->id }}';
                                            const redirect = document.createElement('input');
                                            redirect.type = 'hidden';
                                            redirect.name = 'redirect_to';
                                            redirect.value = '{{ url()->current() }}';
                                            form.appendChild(csrf);
                                            form.appendChild(cid);
                                            form.appendChild(redirect);
                                            document.body.appendChild(form);
                                            form.submit();
                                        "
                                        class="w-full flex items-center justify-between px-6 py-3 text-xs font-bold transition-colors {{ $activeCompany && $activeCompany->id == $company->id ? 'text-[#0055a4] bg-blue-50' : 'text-slate-600 hover:bg-slate-50' }}">
                                        <span>{{ $company->name }}</span>
                                        @if($activeCompany && $activeCompany->id == $company->id)
                                            <i data-lucide="check" class="w-4 h-4"></i>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-10">
                    <div class="mb-8 bg-gradient-to-br from-white to-slate-50 border border-slate-200 rounded-[2rem] p-6 shadow-sm flex flex-col lg:flex-row lg:items-center justify-between gap-8">
                        <!-- Customer Selection (Left side) -->
                        <div class="flex-grow space-y-2 relative">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Customer / Client</label>
                            <div class="relative">
                                <!-- Search Input Trigger -->
                                <button type="button" @click="searchOpen = !searchOpen; if(searchOpen) { $nextTick(() => $refs.searchInput.focus()); searchCustomers(); }" 
                                    class="w-full bg-white border border-slate-200 rounded-2xl px-5 py-4 flex items-center justify-between focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] hover:border-[#0055a4] transition-all font-bold text-slate-900 text-left shadow-sm">
                                    <span x-text="selectedCustomerName || 'Select client...'" class="block truncate text-slate-500" :class="selectedCustomerName ? 'text-slate-900 font-bold' : ''"></span>
                                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="searchOpen ? 'rotate-180 text-[#0055a4]' : ''"></i>
                                </button>

                                <input type="hidden" name="customer_id" :value="selectedCustomerId" required>

                                <!-- Dropdown panel -->
                                <div x-show="searchOpen" x-transition 
                                    @click.away="searchOpen = false" 
                                    style="display: none;" 
                                    class="absolute left-0 right-0 mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl p-3 space-y-3 z-50">
                                    
                                    <!-- Search field -->
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

                                    <!-- Search results list -->
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
                            <!-- GST Type Badge -->
                            <div class="flex items-center gap-2 px-1 mt-1" x-show="selectedCustomerId !== ''">
                                <span class="text-[10px] font-black uppercase tracking-widest"
                                    :class="gstType === 'intra_state' ? 'text-violet-600' : 'text-emerald-600'"
                                    x-text="gstType === 'intra_state' ? 'Intra State - IGST applies' : 'Inter State - CGST + SGST applies'"
                                ></span>
                            </div>
                        </div>

                        <!-- GST Preference Switcher (Right side) -->
                        <div class="flex-shrink-0 flex flex-col items-center lg:items-end justify-center space-y-2 lg:border-l lg:border-slate-200 lg:pl-8 pt-4 lg:pt-0">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">GST Preference</label>
                            <button type="button"
                                @click="gstDisabled = !gstDisabled"
                                :aria-pressed="gstDisabled.toString()"
                                class="flex items-center rounded-full border border-slate-200 bg-white p-1 shadow-sm transition hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0055a4]/30">
                                <span :class="gstDisabled ? 'bg-slate-100 text-slate-400' : 'bg-emerald-50 text-emerald-600 shadow-sm'"
                                    class="rounded-full px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.2em] transition">GST</span>
                                <span :class="gstDisabled ? 'translate-x-0 bg-[#d32d27]' : 'translate-x-0 bg-[#0055a4]'"
                                    class="mx-1 flex h-7 w-7 items-center justify-center rounded-full text-white shadow-sm transition">
                                    <i data-lucide="power" class="h-3.5 w-3.5"></i>
                                </span>
                                <span :class="gstDisabled ? 'bg-amber-50 text-amber-700 shadow-sm' : 'bg-slate-100 text-slate-400'"
                                    class="rounded-full px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.2em] transition">No GST</span>
                            </button>
                            <span x-text="gstDisabled ? 'GST Disabled' : 'GST Enabled'"
                                :class="gstDisabled ? 'text-[#d32d27]' : 'text-[#0055a4]'"
                                class="text-[10px] font-black uppercase tracking-[0.24em] mt-1 block"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Invoice Number</label>
                            <input type="text" name="invoice_number" value="{{ $activeCompany ? $activeCompany->invoice_starting_number : '' }}" readonly required class="w-full bg-slate-100 border-slate-200 rounded-2xl px-5 py-4 cursor-not-allowed focus:ring-0 transition-all font-bold text-slate-500">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Invoice Date</label>
                            <input type="date" name="invoice_date" value="{{ date('Y-m-d') }}" required class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Invoice Renewals</label>
                            <input type="date" name="renewal_date" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                        </div>
                        <div class="space-y-2 relative" x-data="{ 
                            open: false, 
                            options: ['Domain', 'Hosting', 'AMC Yearly', 'AMC Monthly', 'SEO Monthly', 'Digital Marketing Monthly', 'GSUIT Yearly', 'ZOHO Yearly'],
                            selected: []
                        }">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Renewal Type of Service</label>
                            <div class="relative">
                                <button type="button" @click="open = !open" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 flex items-center justify-between focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900 text-left">
                                    <span x-text="selected.length ? selected.join(', ') : 'Select services...'" class="block truncate text-slate-500" :class="selected.length ? 'text-slate-900' : ''"></span>
                                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400"></i>
                                </button>
                                <div x-show="open" x-transition @click.away="open = false" style="display: none;" class="absolute z-50 w-full mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl max-h-60 overflow-y-auto p-2">
                                    <template x-for="option in options" :key="option">
                                        <label class="flex items-center px-4 py-3 hover:bg-slate-50 rounded-xl cursor-pointer transition-colors">
                                            <input type="checkbox" :value="option" x-model="selected" name="renewal_text[]" class="rounded border-slate-300 text-[#0055a4] focus:ring-[#0055a4] w-4 h-4 mr-3">
                                            <span x-text="option" class="text-sm font-bold text-slate-700"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Section -->
            <div class="bg-white rounded-[1.5rem] shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-8 py-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-base uppercase tracking-wider text-slate-800">Invoice Items</h3>
                        <p class="text-[10px] font-medium text-slate-500 uppercase tracking-widest mt-0.5">List of services or products</p>
                    </div>
                </div>
                <div class="p-8">
                    <div class="space-y-10">
                        <!-- Items Template -->
                        <template x-for="(item, index) in items" :key="index">
                            <div class="border border-slate-100 rounded-2xl p-8 bg-white hover:border-slate-200 transition-all shadow-sm">
                                <div class="flex flex-col lg:flex-row gap-8 items-start">
                                    
                                    <!-- Description Area -->
                                    <div class="flex-grow w-full">
                                        <div class="flex items-center justify-between mb-2">
                                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Description & Services</label>
                                            <span class="text-[10px] font-bold text-slate-300" x-text="'Item #' + (index + 1)"></span>
                                        </div>
                                        <div class="border border-slate-200 rounded-xl overflow-hidden focus-within:ring-1 focus-within:ring-[#0055a4] transition-all">
                                            <div :id="'editor-' + index" x-init="
                                                $nextTick(() => {
                                                    const quill = new Quill('#editor-' + index, {
                                                        theme: 'snow',
                                                        placeholder: 'Enter service description...',
                                                        modules: { toolbar: [['bold', 'italic', 'underline'], [{ 'list': 'ordered'}, { 'list': 'bullet' }]] }
                                                    });
                                                    quill.on('text-change', () => {
                                                        item.description = quill.root.innerHTML;
                                                    });
                                                    quill.root.innerHTML = item.description;
                                                })
                                            " class="min-h-[200px] text-slate-700 text-sm"></div>
                                        </div>
                                        <input type="hidden" :name="'items['+index+'][description]'" x-model="item.description">
                                    </div>

                                    <!-- Amount & Actions -->
                                    <div class="w-full lg:w-64 flex-shrink-0 flex flex-col gap-5">
                                        <div class="space-y-2">
                                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block text-right lg:text-left">HSN/SAC Code</label>
                                            <input type="text" :name="'items['+index+'][hsn_sac]'" x-model="item.hsn"
                                                class="w-full bg-slate-50 border-slate-200 rounded-xl px-4 py-3 focus:bg-white focus:ring-1 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900 text-sm"
                                                placeholder="e.g. 9983">
                                        </div>

                                        <div class="space-y-2">
                                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block text-right lg:text-left">Amount</label>
                                            <div class="relative group">
                                                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 font-bold text-sm select-none">Rs.</div>
                                                <input type="number" :name="'items['+index+'][price]'" x-model="item.amount" step="0.01"
                                                    class="w-full bg-slate-50 border-slate-200 rounded-xl pl-9 pr-4 py-3.5 focus:bg-white focus:ring-1 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900 text-right text-lg"
                                                    placeholder="0.00" required>
                                            </div>
                                        </div>

                                        <div class="flex justify-end">
                                            <button type="button" @click="removeItem(index)"
                                                class="flex items-center gap-2 px-4 py-2 rounded-lg text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-red-600 hover:bg-red-50 transition-all border border-transparent hover:border-red-100">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><path d="M3 6h18"></path><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path></svg>
                                                <span>Remove Item</span>
                                            </button>
                                        </div>

                                        <input type="hidden" :name="'items['+index+'][gst_percentage]'" x-model="item.gst">
                                    </div>
                                </div>
                                <input type="hidden" :name="'items['+index+'][quantity]'" value="1">
                                <input type="hidden" :name="'items['+index+'][discount]'" value="0">
                            </div>
                        </template>
                    </div>
                    
                    <div class="mt-8 pt-8 border-t border-slate-100 flex flex-col md:flex-row justify-between items-start gap-8">
                        <button type="button" @click="addItem()" class="text-xs font-black uppercase tracking-widest text-[#0055a4] flex items-center gap-2 bg-blue-50 px-8 py-4 rounded-2xl border border-blue-100 hover:bg-white active:scale-95 transition-all shadow-sm">
                            <i data-lucide="plus-circle" class="w-5 h-5"></i> Add Another Item
                        </button>
                        
                        <div class="w-full md:w-80 space-y-4 bg-slate-50/50 p-8 rounded-[2.5rem] border border-slate-100">
                            <!-- Hidden inputs to pass GST breakdown to controller -->
                            <input type="hidden" name="gst_enabled" :value="gstDisabled ? 0 : 1">
                            <input type="hidden" name="cgst" :value="calculateCgst().toFixed(2)">
                            <input type="hidden" name="sgst" :value="calculateSgst().toFixed(2)">
                            <input type="hidden" name="igst" :value="calculateIgst().toFixed(2)">
                            <input type="hidden" name="total_gst" :value="calculateTotalGst().toFixed(2)">
                            <input type="hidden" name="grand_total" :value="calculateGrandTotal().toFixed(2)">
                            <input type="hidden" name="subtotal" :value="calculateSubtotal().toFixed(2)">
                            <input type="hidden" name="gst_type" :value="gstType">

                            <div class="flex justify-between text-xs font-bold text-slate-400 uppercase tracking-widest">
                                <span>Subtotal</span>
                                <span class="text-slate-900 font-black" x-text="'Rs. ' + calculateSubtotal().toFixed(2)"></span>
                            </div>

                            <div x-show="gstDisabled" class="print:hidden rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-[11px] font-bold uppercase tracking-widest text-amber-700">
                                GST disabled for this invoice
                            </div>

                            <!-- Intra State: IGST -->
                            <div x-show="!gstDisabled && isIntraState" class="flex justify-between text-xs font-bold text-slate-400 uppercase tracking-widest">
                                <span>IGST (18%)</span>
                                <span class="text-slate-900 font-black" x-text="'Rs. ' + calculateIgst().toFixed(2)"></span>
                            </div>

                            <!-- Inter State: CGST + SGST -->
                            <div x-show="!gstDisabled && isInterState" class="space-y-3">
                                <div class="flex justify-between text-xs font-bold text-slate-400 uppercase tracking-widest">
                                    <span>CGST (9%)</span>
                                    <span class="text-slate-900 font-black" x-text="'Rs. ' + calculateCgst().toFixed(2)"></span>
                                </div>
                                <div class="flex justify-between text-xs font-bold text-slate-400 uppercase tracking-widest">
                                    <span>SGST (9%)</span>
                                    <span class="text-slate-900 font-black" x-text="'Rs. ' + calculateSgst().toFixed(2)"></span>
                                </div>
                            </div>

                            <div class="flex justify-between pt-4 border-t border-slate-200">
                                <span class="text-xs font-black text-slate-900 uppercase tracking-widest">Grand Total</span>
                                <span class="text-2xl font-black text-[#d32d27] italic tracking-tighter" x-text="'Rs. ' + calculateGrandTotal().toFixed(2)"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between pb-20">
                <a href="{{ route('invoices.index') }}" class="text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-900 transition-colors">Discard</a>
                <button type="submit" class="bg-[#d32d27] hover:bg-[#b21f24] text-white font-black py-5 px-16 rounded-[2.5rem] transition-all duration-200 shadow-2xl shadow-red-500/40 active:scale-95 text-sm uppercase tracking-widest">
                    Generate GST Invoice
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
