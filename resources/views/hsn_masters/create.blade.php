<x-app-layout>
    <x-slot name="header">
        Add HSN Master
    </x-slot>

    <div class="max-w-4xl mx-auto" x-data="{
        rows: [{ service_name: '', hsn_code: '' }],
        addRow() {
            this.rows.push({ service_name: '', hsn_code: '' });
        },
        removeRow(index) {
            if (this.rows.length > 1) {
                this.rows.splice(index, 1);
            }
        }
    }">
        <form action="{{ route('hsn-masters.store') }}" method="POST" class="space-y-8">
            @csrf

            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-4 sm:px-6 lg:px-10 py-8 border-b border-slate-50 bg-slate-50/50">
                    <h3 class="font-black text-2xl uppercase tracking-tighter italic text-slate-900">Add HSN Records</h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Add one or multiple HSN/SAC codes in bulk</p>
                </div>

                <div class="p-4 sm:p-6 lg:p-10 space-y-6">
                    <template x-for="(row, index) in rows" :key="index">
                        <div class="flex items-center gap-4 sm:gap-6 p-4 sm:p-6 rounded-2xl bg-slate-50/50 border border-slate-100 hover:border-slate-200 transition-all">
                            <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Service Name</label>
                                    <input type="text" :name="'items['+index+'][service_name]'" x-model="row.service_name" required class="w-full bg-white border border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">HSN / SAC Code</label>
                                    <input type="text" :name="'items['+index+'][hsn_code]'" x-model="row.hsn_code" required class="w-full bg-white border border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                                </div>
                            </div>
                            
                            <div class="pt-6 shrink-0" x-show="rows.length > 1">
                                <button type="button" @click="removeRow(index)" class="p-4 rounded-2xl text-slate-400 hover:text-red-600 hover:bg-red-50 transition-all border border-transparent hover:border-red-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                                        <path d="M3 6h18"></path>
                                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>

                    <div class="pt-4">
                        <button type="button" @click="addRow()" class="text-xs font-black uppercase tracking-widest text-[#0055a4] flex items-center gap-2 bg-blue-50 px-8 py-4 rounded-2xl border border-blue-100 hover:bg-white active:scale-95 transition-all shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            Add Another Row
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between pb-20">
                <a href="{{ route('hsn-masters.index') }}" class="text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-900 transition-colors">Cancel</a>
                <button type="submit" class="bg-[#d32d27] hover:bg-[#b21f24] text-white font-black py-5 px-16 rounded-[2.5rem] transition-all duration-200 shadow-2xl shadow-red-500/40 active:scale-95 text-sm uppercase tracking-widest">
                    Save HSN Records
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
