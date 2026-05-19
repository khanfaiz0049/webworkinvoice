@forelse($customers as $customer)
    <tr class="group hover:bg-blue-50/30 transition-colors">
        <td class="px-10 py-5">
            <div class="flex items-center gap-2">
                <span class="font-bold text-slate-900 uppercase tracking-tight italic">{{ $customer->name }}</span>
                @if($customer->reference_name)
                    <span class="px-2.5 py-0.5 rounded-lg bg-slate-100 text-[9px] font-black text-slate-500 uppercase tracking-wider" title="Reference Name">Ref: {{ $customer->reference_name }}</span>
                @endif
            </div>
            @if($customer->company_name)
                <div class="text-xs text-slate-400 font-bold uppercase tracking-wider mt-1">{{ $customer->company_name }}</div>
            @endif
        </td>
        <td class="px-10 py-5">
            <span class="text-xs font-black text-[#0055a4] uppercase tracking-wider">{{ $customer->gst_number ?? 'N/A' }}</span>
        </td>
        <td class="px-10 py-5">
            <div class="text-xs text-slate-600 font-bold">{{ $customer->email ?? 'N/A' }}</div>
            @if($customer->phone)
                <div class="text-[10px] text-slate-400 font-bold mt-1">{{ $customer->phone }}</div>
            @endif
        </td>
        <td class="px-10 py-5 text-right">
            <div class="flex items-center justify-end gap-2">
                <!-- Edit Icon (Native SVG) -->
                <a href="{{ route('customers.edit', $customer) }}" class="p-2 text-slate-400 hover:text-[#0055a4] hover:bg-slate-50 rounded-xl transition-all duration-200" title="Edit Customer">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                        <path d="M12 20h9"></path>
                        <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                    </svg>
                </a>
                
                <!-- Delete Icon (Native SVG) -->
                <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this customer?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-2 text-slate-400 hover:text-[#d32d27] hover:bg-red-50 rounded-xl transition-all duration-200" title="Delete Customer">
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
        <td colspan="4" class="px-10 py-20 text-center">
            <div class="flex flex-col items-center gap-4">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center border border-slate-100">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-10 h-10 text-slate-200">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" x2="16.65" y1="21" y2="16.65"></line>
                    </svg>
                </div>
                @if(request('search'))
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">No customers match your search</p>
                @else
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">No customers found</p>
                    <a href="{{ route('customers.create') }}" class="text-[#0055a4] font-bold text-xs underline uppercase tracking-widest">Add your first customer</a>
                @endif
            </div>
        </td>
    </tr>
@endforelse
