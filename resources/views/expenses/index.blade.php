<x-app-layout>
    <x-slot name="header">
        Expenses
    </x-slot>

    <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-10 py-8 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="font-black text-2xl uppercase tracking-tighter italic text-slate-900">Expense Tracker</h3>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Manage and monitor your business spends</p>
            </div>
            <a href="{{ route('expenses.create') }}" class="bg-[#d32d27] hover:bg-[#b21f24] text-white font-black py-3 px-8 rounded-xl transition-all duration-200 shadow-lg shadow-red-500/20 active:scale-95 text-xs uppercase tracking-widest flex items-center gap-2">
                <i data-lucide="wallet" class="w-4 h-4"></i> Log Expense
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] bg-white">
                        <th class="px-10 py-6">Date</th>
                        <th class="px-10 py-6">Expense Name</th>
                        <th class="px-10 py-6">Category</th>
                        <th class="px-10 py-6">Method</th>
                        <th class="px-10 py-6">Amount</th>
                        <th class="px-10 py-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($expenses as $expense)
                        <tr class="group hover:bg-red-50/30 transition-colors">
                            <td class="px-10 py-6 text-sm text-slate-500 font-bold uppercase tracking-tight italic">{{ \Carbon\Carbon::parse($expense->date)->format('d M, Y') }}</td>
                            <td class="px-10 py-6">
                                <div class="font-bold text-slate-900 uppercase tracking-tight italic">{{ $expense->name }}</div>
                                <div class="text-[10px] text-slate-400 font-black uppercase tracking-widest">{{ $expense->company->name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-10 py-6">
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-slate-100 text-slate-600">
                                    {{ $expense->category }}
                                </span>
                            </td>
                            <td class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $expense->payment_method }}</td>
                            <td class="px-10 py-6 font-black text-red-600 italic">₹{{ number_format($expense->amount, 2) }}</td>
                            <td class="px-10 py-6 text-right flex items-center justify-end gap-2">
                                <form action="{{ route('expenses.destroy', $expense) }}" method="POST" onsubmit="return confirm('Remove this expense?')">
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
                                        <i data-lucide="wallet" class="w-10 h-10 text-slate-200"></i>
                                    </div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">No expenses recorded yet</p>
                                    <a href="{{ route('expenses.create') }}" class="text-[#d32d27] font-bold text-xs underline uppercase tracking-widest">Log your first expense</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
