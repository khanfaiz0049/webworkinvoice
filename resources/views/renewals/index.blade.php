<x-app-layout>
    <x-slot name="header">
        Renewals
    </x-slot>

    <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-10 py-8 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="font-black text-2xl uppercase tracking-tighter italic text-slate-900">Subscription Renewals</h3>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Track and manage upcoming service renewals</p>
            </div>
            <a href="{{ route('renewals.create') }}" class="bg-[#d32d27] hover:bg-[#b21f24] text-white font-black py-3 px-8 rounded-xl transition-all duration-200 shadow-lg shadow-red-500/20 active:scale-95 text-xs uppercase tracking-widest flex items-center gap-2">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i> Add Renewal
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] bg-white">
                        <th class="px-10 py-6">Service Name</th>
                        <th class="px-10 py-6">Customer</th>
                        <th class="px-10 py-6">Due Date</th>
                        <th class="px-10 py-6">Frequency</th>
                        <th class="px-10 py-6">Status</th>
                        <th class="px-10 py-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($renewals as $renewal)
                        <tr class="group hover:bg-red-50/30 transition-colors">
                            <td class="px-10 py-6 font-bold text-slate-900 uppercase tracking-tight italic">{{ $renewal->name }}</td>
                            <td class="px-10 py-6">
                                <div class="text-sm font-bold text-slate-600 italic">{{ $renewal->customer?->name ?? 'Deleted Customer' }}</div>
                                <div class="text-[10px] text-slate-400 font-black uppercase tracking-widest">{{ $renewal->company?->name ?? 'Deleted Company' }}</div>
                            </td>
                            <td class="px-10 py-6 text-sm {{ \Carbon\Carbon::parse($renewal->due_date)->isPast() ? 'text-red-500 font-black' : 'text-slate-500 font-medium' }}">
                                {{ \Carbon\Carbon::parse($renewal->due_date)->format('d M, Y') }}
                                @if(\Carbon\Carbon::parse($renewal->due_date)->isPast())
                                    <span class="block text-[8px] uppercase tracking-widest">Overdue</span>
                                @endif
                            </td>
                            <td class="px-10 py-6">
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-blue-50 text-[#0055a4]">
                                    {{ $renewal->frequency }}
                                </span>
                            </td>
                            <td class="px-10 py-6">
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest {{ $renewal->status == 'active' ? 'bg-green-50 text-green-600' : 'bg-slate-100 text-slate-400' }}">
                                    {{ $renewal->status }}
                                </span>
                            </td>
                            <td class="px-10 py-6 text-right flex items-center justify-end gap-2">
                                <a href="{{ route('renewals.edit', $renewal) }}" class="p-2 text-slate-400 hover:text-[#0055a4] transition-colors"><i data-lucide="edit-3" class="w-5 h-5"></i></a>
                                <form action="{{ route('renewals.destroy', $renewal) }}" method="POST" onsubmit="return confirm('Remove this renewal?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-slate-400 hover:text-[#d32d27] transition-colors"><i data-lucide="trash-2" class="w-5 h-5"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-10 py-20 text-center">
                                <div class="flex flex-col items-center gap-4">
                                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center border border-slate-100">
                                        <i data-lucide="refresh-cw" class="w-10 h-10 text-slate-200"></i>
                                    </div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">No active renewals found</p>
                                    <a href="{{ route('renewals.create') }}" class="text-[#d32d27] font-bold text-xs underline uppercase tracking-widest">Schedule your first renewal</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
