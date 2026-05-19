<x-app-layout>
    <x-slot name="header">
        Log Expense
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <form action="{{ route('expenses.store') }}" method="POST" class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
            @csrf
            <div class="px-4 sm:px-6 lg:px-10 py-8 border-b border-slate-50 bg-slate-50/50">
                <h3 class="font-black text-2xl uppercase tracking-tighter italic text-slate-900">Expense Details</h3>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Record a new business expenditure</p>
            </div>

            <div class="p-4 sm:p-6 lg:p-10 space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Expense Name</label>
                        <input type="text" name="name" required class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900 placeholder-slate-300" placeholder="e.g. Office Rent">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Category</label>
                        <select name="category" required class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                            <option value="Rent">Rent</option>
                            <option value="Salary">Salary</option>
                            <option value="Utility">Utility (Electricity/Water)</option>
                            <option value="Marketing">Marketing</option>
                            <option value="Software">Software/SaaS</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Amount</label>
                        <input type="number" name="amount" required step="0.01" min="0.01" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900" placeholder="0.00">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Date</label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Payment Method</label>
                        <select name="payment_method" required class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                            <option value="Cash">Cash</option>
                            <option value="Bank">Bank Transfer</option>
                            <option value="UPI">UPI</option>
                            <option value="Card">Card</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Notes</label>
                    <textarea name="notes" rows="3" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900" placeholder="Add expense details..."></textarea>
                </div>
            </div>

            <div class="px-4 sm:px-6 lg:px-10 py-8 bg-slate-50/50 border-t border-slate-50 flex items-center justify-between">
                <a href="{{ route('expenses.index') }}" class="text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-900 transition-colors">Cancel</a>
                <button type="submit" class="bg-[#d32d27] hover:bg-[#b21f24] text-white font-black py-4 px-12 rounded-2xl transition-all duration-200 shadow-xl shadow-red-500/20 active:scale-95 text-xs uppercase tracking-widest">
                    Log Expense
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
