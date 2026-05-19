<x-app-layout>
    <x-slot name="header">
        Schedule Renewal
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <form action="{{ route('renewals.store') }}" method="POST" class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
            @csrf
            <div class="px-4 sm:px-6 lg:px-10 py-8 border-b border-slate-50 bg-slate-50/50">
                <h3 class="font-black text-2xl uppercase tracking-tighter italic text-slate-900">Renewal Details</h3>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Setup recurring service or domain renewals</p>
            </div>

            <div class="p-4 sm:p-6 lg:p-10 space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Service/Product Name</label>
                        <input type="text" name="name" required class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900 placeholder-slate-300" placeholder="e.g. Domain Renewal">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Customer</label>
                        <select name="customer_id" required class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                            <option value="">Choose a client...</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Due Date</label>
                        <input type="date" name="due_date" required class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Frequency</label>
                        <select name="frequency" required class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                            <option value="Monthly">Monthly</option>
                            <option value="Quarterly">Quarterly</option>
                            <option value="Half Yearly">Half Yearly</option>
                            <option value="Yearly" selected>Yearly</option>
                            <option value="Custom">Custom</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Initial Status</label>
                        <select name="status" required class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                            <option value="active">Active</option>
                            <option value="paused">Paused</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="px-4 sm:px-6 lg:px-10 py-8 bg-slate-50/50 border-t border-slate-50 flex items-center justify-between">
                <a href="{{ route('renewals.index') }}" class="text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-900 transition-colors">Cancel</a>
                <button type="submit" class="bg-[#d32d27] hover:bg-[#b21f24] text-white font-black py-4 px-12 rounded-2xl transition-all duration-200 shadow-xl shadow-red-500/20 active:scale-95 text-xs uppercase tracking-widest">
                    Schedule Renewal
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
