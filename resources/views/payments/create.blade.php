<x-app-layout>
    <x-slot name="header">
        Record Payment
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <form action="{{ route('payments.store') }}" method="POST" class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
            @csrf
            <div class="px-10 py-8 border-b border-slate-50 bg-slate-50/50">
                <h3 class="font-black text-2xl uppercase tracking-tighter italic text-slate-900">Record Payment</h3>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Log a collection from your customer</p>
            </div>

            <div class="p-10 space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Customer</label>
                        <select name="customer_id" required class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                            <option value="">Choose a client...</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Link to Invoice (Optional)</label>
                        <select name="invoice_id" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                            <option value="">General Payment</option>
                            @foreach($invoices as $invoice)
                                <option value="{{ $invoice->id }}">{{ $invoice->invoice_number }} (Bal: ₹{{ number_format($invoice->outstanding_amount, 2) }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Received In (Account)</label>
                        <select name="received_in" required class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                            <option value="">Select Account...</option>
                            <option value="WEBWORK">WEBWORK</option>
                            <option value="SYAMSUNDAR GUPTA">SYAMSUNDAR GUPTA</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Payment Amount</label>
                        <input type="number" name="amount" required step="0.01" min="0.01" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900" placeholder="0.00">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Payment Date</label>
                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Payment Method</label>
                        <select name="payment_method" required class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                            <option value="Cash">Cash</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="UPI">UPI</option>
                            <option value="Card">Card</option>
                            <option value="Cheque">Cheque</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Transaction ID / Ref #</label>
                        <input type="text" name="transaction_id" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900" placeholder="Optional">
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Internal Notes</label>
                    <textarea name="reference_notes" rows="3" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900" placeholder="Add any payment details..."></textarea>
                </div>
            </div>

            <div class="px-10 py-8 bg-slate-50/50 border-t border-slate-50 flex items-center justify-between">
                <a href="{{ route('payments.index') }}" class="text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-900 transition-colors">Cancel</a>
                <button type="submit" class="bg-[#d32d27] hover:bg-[#b21f24] text-white font-black py-4 px-12 rounded-2xl transition-all duration-200 shadow-xl shadow-red-500/20 active:scale-95 text-xs uppercase tracking-widest">
                    Record Payment
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
