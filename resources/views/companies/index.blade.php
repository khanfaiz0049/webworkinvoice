<x-app-layout>
    <x-slot name="header">
        Companies
    </x-slot>

    <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-10 py-8 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="font-black text-2xl uppercase tracking-tighter italic text-slate-900">Manage Companies</h3>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">List of all your registered businesses</p>
            </div>
            <a href="{{ route('companies.create') }}" class="bg-[#d32d27] hover:bg-[#b21f24] text-white font-black py-3 px-8 rounded-xl transition-all duration-200 shadow-lg shadow-red-500/20 active:scale-95 text-xs uppercase tracking-widest flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i> Add Company
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] bg-white">
                        <th class="px-10 py-6">Company Name</th>
                        <th class="px-10 py-6">Email</th>
                        <th class="px-10 py-6">Phone</th>
                        <th class="px-10 py-6">GSTIN</th>
                        <th class="px-10 py-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($companies as $company)
                        <tr class="group hover:bg-blue-50/30 transition-colors">
                            <td class="px-10 py-6 font-bold text-slate-900 uppercase tracking-tight italic">{{ $company->name }}</td>
                            <td class="px-10 py-6 text-sm text-slate-500 font-medium">{{ $company->email }}</td>
                            <td class="px-10 py-6 text-sm text-slate-500 font-medium">{{ $company->phone }}</td>
                            <td class="px-10 py-6 text-xs font-black text-[#0055a4] uppercase tracking-widest">{{ $company->gst_number ?? 'N/A' }}</td>
                            <td class="px-10 py-6 text-right flex items-center justify-end gap-2">
                                <a href="{{ route('companies.edit', $company) }}" class="p-2 text-slate-400 hover:text-[#0055a4] transition-colors"><i data-lucide="edit-3" class="w-5 h-5"></i></a>
                                <form action="{{ route('companies.destroy', $company) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this company?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-slate-400 hover:text-[#d32d27] transition-colors"><i data-lucide="trash-2" class="w-5 h-5"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-10 py-20 text-center">
                                <div class="flex flex-col items-center gap-4">
                                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center border border-slate-100">
                                        <i data-lucide="building-2" class="w-10 h-10 text-slate-200"></i>
                                    </div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">No companies found</p>
                                    <a href="{{ route('companies.create') }}" class="text-[#0055a4] font-bold text-xs underline uppercase tracking-widest">Create your first company</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
