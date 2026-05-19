@forelse($hsns as $hsn)
    <tr class="group hover:bg-blue-50/30 transition-colors">
        <td class="px-4 sm:px-6 lg:px-10 py-5">
            <div class="font-bold text-slate-900 uppercase tracking-tight italic">{{ $hsn->service_name }}</div>
        </td>
        <td class="px-4 sm:px-6 lg:px-10 py-5">
            <span class="text-xs font-black text-[#0055a4] uppercase tracking-wider">{{ $hsn->hsn_code }}</span>
        </td>
        <td class="px-4 sm:px-6 lg:px-10 py-5 text-right">
            <div class="flex items-center justify-end gap-2">
                <!-- Edit Icon (Native SVG) -->
                <a href="{{ route('hsn-masters.edit', $hsn) }}" class="p-2 text-slate-400 hover:text-[#0055a4] hover:bg-slate-50 rounded-xl transition-all duration-200" title="Edit HSN">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                        <path d="M12 20h9"></path>
                        <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                    </svg>
                </a>
                
                <!-- Delete Icon (Native SVG) -->
                <form action="{{ route('hsn-masters.destroy', $hsn) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this HSN record?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-2 text-slate-400 hover:text-[#d32d27] hover:bg-red-50 rounded-xl transition-all duration-200" title="Delete HSN">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                            <path d="M3 6h18"></path>
                            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                            <line x1="10" x2="10" y1="11" y2="17"></line>
                            <line x1="14" x2="14" y1="11" y2="17"></line>
                        </svg>
                    </button>
                </form>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="3" class="px-4 sm:px-6 lg:px-10 py-20 text-center">
            <div class="flex flex-col items-center gap-4">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center border border-slate-100">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-10 h-10 text-slate-200">
                        <line x1="4" x2="20" y1="9" y2="9"></line>
                        <line x1="4" x2="20" y1="15" y2="15"></line>
                        <line x1="10" x2="8" y1="3" y2="21"></line>
                        <line x1="16" x2="14" y1="3" y2="21"></line>
                    </svg>
                </div>
                @if(request('search'))
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">No HSN records match your search</p>
                @else
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">No HSN records found</p>
                    <a href="{{ route('hsn-masters.create') }}" class="text-[#0055a4] font-bold text-xs underline uppercase tracking-widest">Add your first HSN</a>
                @endif
            </div>
        </td>
    </tr>
@endforelse
