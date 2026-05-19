<x-app-layout>
    <x-slot name="header">
        Edit HSN Master
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <form action="{{ route('hsn-masters.update', $hsnMaster) }}" method="POST" class="space-y-8">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-4 sm:px-6 lg:px-10 py-8 border-b border-slate-50 bg-slate-50/50">
                    <h3 class="font-black text-2xl uppercase tracking-tighter italic text-slate-900">Edit HSN Record</h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Modify service name and HSN/SAC code details</p>
                </div>

                <div class="p-4 sm:p-6 lg:p-10 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Service Name</label>
                            <input type="text" name="service_name" value="{{ old('service_name', $hsnMaster->service_name) }}" required class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">HSN / SAC Code</label>
                            <input type="text" name="hsn_code" value="{{ old('hsn_code', $hsnMaster->hsn_code) }}" required class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between pb-20">
                <a href="{{ route('hsn-masters.index') }}" class="text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-900 transition-colors">Cancel</a>
                <button type="submit" class="bg-[#d32d27] hover:bg-[#b21f24] text-white font-black py-5 px-16 rounded-[2.5rem] transition-all duration-200 shadow-2xl shadow-red-500/40 active:scale-95 text-sm uppercase tracking-widest">
                    Update HSN Record
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
