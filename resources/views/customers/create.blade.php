<x-app-layout>
    <x-slot name="header">
        Add New Customer
    </x-slot>

    <div class="max-w-5xl mx-auto pb-20">
        <form action="{{ route('customers.store') }}" method="POST" class="space-y-8">
            @csrf
            
            <!-- Basic Info -->
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-10 py-8 border-b border-slate-50 bg-slate-50/50">
                    <h3 class="font-black text-2xl uppercase tracking-tighter italic text-slate-900">Customer Identity</h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Personal and business profile</p>
                </div>
                <div class="p-10 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Full Name</label>
                            <input type="text" name="name" required class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Reference Name</label>
                            <input type="text" name="reference_name" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Customer Company Name</label>
                            <input type="text" name="company_name" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">GST Number</label>
                            <input type="text" name="gst_number" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">PAN Number</label>
                            <input type="text" name="pan_number" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Section -->
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-10 py-8 border-b border-slate-50 bg-slate-50/50">
                    <h3 class="font-black text-2xl uppercase tracking-tighter italic text-slate-900">Contact Channels</h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Email, Phone, and WhatsApp</p>
                </div>
                <div class="p-10 grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Email</label>
                        <input type="email" name="email" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Phone</label>
                        <input type="text" name="phone" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">WhatsApp</label>
                        <input type="text" name="whatsapp" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                    </div>
                </div>
            </div>

            <!-- Addresses -->
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-10 py-8 border-b border-slate-50 bg-slate-50/50">
                    <h3 class="font-black text-2xl uppercase tracking-tighter italic text-slate-900">Address Details</h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Billing and Shipping locations</p>
                </div>
                <div class="p-10 space-y-8">
                    <div class="grid grid-cols-1 gap-8">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Billing Address</label>
                            <textarea name="billing_address" rows="3" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900"></textarea>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">State</label>
                            <input type="text" name="state" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Status</label>
                            <select name="status" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financials -->
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-10 py-8 border-b border-slate-50 bg-slate-50/50">
                    <h3 class="font-black text-2xl uppercase tracking-tighter italic text-slate-900">Financials & Tags</h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Balances and categorization</p>
                </div>
                <div class="p-10 grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">GST Type</label>
                        <select name="gst_type" id="gst_type" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                            <option value="">Select GST Type</option>
                            <option value="inter_state" {{ old('gst_type') == 'inter_state' ? 'selected' : '' }}>Inter State</option>
                            <option value="intra_state" {{ old('gst_type') == 'intra_state' ? 'selected' : '' }}>Intra State</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Opening Balance (₹)</label>
                        <input type="number" name="opening_balance" step="0.01" value="0.00" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Credit Limit (₹)</label>
                        <input type="number" name="credit_limit" step="0.01" value="0.00" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('customers.index') }}" class="text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-900 transition-colors">Back to list</a>
                <button type="submit" class="bg-[#d32d27] hover:bg-[#b21f24] text-white font-black py-5 px-16 rounded-[2rem] transition-all duration-200 shadow-2xl shadow-red-500/30 active:scale-95 text-sm uppercase tracking-widest">
                    Create Customer
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
