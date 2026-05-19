<x-app-layout>
    <x-slot name="header">
        Edit Invoice #{{ $invoice->invoice_number }}
    </x-slot>

    <!-- Include Quill CSS/JS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

    <div class="max-w-6xl mx-auto" x-data='{
        items: @json($items),
        customers: @json($customers),
        selectedCustomerId: "{{ $invoice->customer_id }}",
        gstDisabled: {{ $invoice->gst_enabled ? 'false' : 'true' }},
        gstType: "{{ $invoice->customer->gst_type ?? "intra_state" }}",
        isIntraState: {{ ($invoice->customer->gst_type ?? "intra_state") === "intra_state" ? "true" : "false" }},
        isInterState: {{ ($invoice->customer->gst_type ?? "intra_state") === "inter_state" ? "true" : "false" }},
        init() {
            this.$watch("selectedCustomerId", (id) => {
                const customer = this.customers.find(c => String(c.id) === String(id));
                this.gstType    = customer ? customer.gst_type : "intra_state";
                this.isIntraState = this.gstType === "intra_state";
                this.isInterState = this.gstType === "inter_state";
            });
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
        <form action="{{ route('invoices.update', $invoice) }}" method="POST" class="space-y-8">
            @csrf
            @method('PUT')
            
            <!-- Header Section -->
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100">
                <div class="px-10 py-8 border-b border-slate-50 bg-slate-50/50 rounded-t-[2.5rem] flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div>
                        <h3 class="font-black text-2xl uppercase tracking-tighter italic text-slate-900">Edit Invoice</h3>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Modify details and recalculate totals</p>
                    </div>
                    
                    <div class="bg-white border border-slate-200 rounded-[2rem] px-6 py-3 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-blue-50 rounded-full flex items-center justify-center">
                                <i data-lucide="building-2" class="w-5 h-5 text-[#0055a4]"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">Billing From</p>
                                <p class="text-xs font-black text-slate-900 mt-1 uppercase">{{ $activeCompany?->name ?? 'No Company Selected' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-10">
                    <div class="mb-8 flex justify-end">
                        <div class="w-full max-w-[430px] rounded-[1.75rem] border border-slate-200 bg-gradient-to-br from-white to-slate-50 px-5 py-4 shadow-sm">
                            <div class="flex items-center justify-between gap-4">
                                <div class="pr-2">
                                    <p class="text-xs font-black uppercase tracking-widest text-slate-900">GST Preference</p>
                                    <p class="mt-1 text-[11px] font-bold leading-5 text-slate-400">Keep GST on by default, or switch to remove GST from this invoice.</p>
                                </div>
                                <button type="button"
                                    @click="gstDisabled = !gstDisabled"
                                    :aria-pressed="gstDisabled.toString()"
                                    class="flex flex-shrink-0 items-center rounded-full border border-slate-200 bg-white p-1 shadow-sm transition hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0055a4]/30">
                                    <span :class="gstDisabled ? 'bg-slate-100 text-slate-400' : 'bg-emerald-50 text-emerald-600 shadow-sm'"
                                        class="rounded-full px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.2em] transition">GST</span>
                                    <span :class="gstDisabled ? 'translate-x-0 bg-[#d32d27]' : 'translate-x-0 bg-[#0055a4]'"
                                        class="mx-1 flex h-7 w-7 items-center justify-center rounded-full text-white shadow-sm transition">
                                        <i data-lucide="power" class="h-3.5 w-3.5"></i>
                                    </span>
                                    <span :class="gstDisabled ? 'bg-amber-50 text-amber-700 shadow-sm' : 'bg-slate-100 text-slate-400'"
                                        class="rounded-full px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.2em] transition">No GST</span>
                                </button>
                            </div>
                            <div class="mt-3 flex justify-end">
                                <span x-text="gstDisabled ? 'GST Disabled' : 'GST Enabled'"
                                    :class="gstDisabled ? 'text-[#d32d27]' : 'text-[#0055a4]'"
                                    class="text-[10px] font-black uppercase tracking-[0.24em]"></span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Customer</label>
                            <select name="customer_id" x-model="selectedCustomerId" required class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                                <option value="">Select client...</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->company_name ?: $customer->name }}</option>
                                @endforeach
                            </select>
                            <!-- GST Type Badge -->
                            <div class="flex items-center gap-2 px-1 mt-1" x-show="selectedCustomerId !== ''">
                                <span class="text-[10px] font-black uppercase tracking-widest"
                                    :class="gstType === 'intra_state' ? 'text-violet-600' : 'text-emerald-600'"
                                    x-text="gstType === 'intra_state' ? 'Intra State - IGST applies' : 'Inter State - CGST + SGST applies'"
                                ></span>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Invoice Number</label>
                            <input type="text" name="invoice_number" value="{{ $invoice->invoice_number }}" readonly required class="w-full bg-slate-100 border-slate-200 rounded-2xl px-5 py-4 cursor-not-allowed focus:ring-0 transition-all font-bold text-slate-500 uppercase">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Invoice Date</label>
                            <input type="date" name="invoice_date" value="{{ $invoice->invoice_date }}" required class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Invoice Renewals</label>
                            <input type="date" name="renewal_date" value="{{ $invoice->renewal_date }}" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                        </div>
                        <div class="space-y-2 relative" x-data="{ 
                            open: false, 
                            options: ['Domain', 'Hosting', 'AMC Yearly', 'AMC Monthly', 'SEO Monthly', 'Digital Marketing Monthly', 'GSUIT Yearly', 'ZOHO Yearly'],
                            selected: '{{ $invoice->renewal_text }}' ? '{{ $invoice->renewal_text }}'.split(',').map(s => s.trim()) : []
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
                        <p class="text-[10px] font-medium text-slate-500 uppercase tracking-widest mt-0.5">Edit services or products</p>
                    </div>
                </div>
                <div class="p-8">
                    <div class="space-y-10">
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
                                            " class="min-h-[100px] text-slate-700 text-sm"></div>
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

            <div class="flex justify-end gap-4">
                <a href="{{ route('invoices.index') }}" class="px-10 py-5 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-all">Discard</a>
                <button type="submit" class="bg-[#0055a4] hover:bg-[#004482] text-white font-black py-5 px-12 rounded-[2rem] transition-all duration-300 shadow-xl shadow-blue-500/20 active:scale-95 text-xs uppercase tracking-widest flex items-center gap-3">
                    <i data-lucide="save" class="w-5 h-5"></i> Update Invoice
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
