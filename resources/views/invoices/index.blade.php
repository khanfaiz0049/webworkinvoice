<x-app-layout>
    <x-slot name="header">
        Billing History
    </x-slot>

    <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-10 py-8 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="font-black text-2xl uppercase tracking-tighter italic text-slate-900">Invoices</h3>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Track and manage your generated bills</p>
            </div>
            <a href="{{ route('invoices.create') }}" class="bg-[#d32d27] hover:bg-[#b21f24] text-white font-black py-3 px-8 rounded-xl transition-all duration-200 shadow-lg shadow-red-500/20 active:scale-95 text-xs uppercase tracking-widest flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i> Create Invoice
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] bg-white">
                        <th class="px-10 py-6">Invoice #</th>
                        <th class="px-10 py-6">Customer</th>
                        <th class="px-10 py-6">Date</th>
                        <th class="px-10 py-6">Amount</th>
                        <th class="px-10 py-6">Status</th>
                        <th class="px-10 py-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($invoices as $invoice)
                        <tr class="group hover:bg-blue-50/30 transition-colors">
                            <td class="px-10 py-6 font-bold text-slate-900 uppercase tracking-tight italic">{{ $invoice->invoice_number }}</td>
                            <td class="px-10 py-6">
                                <div class="text-sm font-bold text-slate-900">{{ $invoice->customer->name }}</div>
                                <div class="text-[10px] text-slate-400 font-black uppercase tracking-widest">{{ $invoice->company->name }}</div>
                            </td>
                            <td class="px-10 py-6 text-sm text-slate-500 font-medium">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M, Y') }}</td>
                            <td class="px-10 py-6 font-black text-slate-900 italic">₹{{ number_format($invoice->grand_total, 2) }}</td>
                            <td class="px-10 py-6">
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest {{ $invoice->status == 'paid' ? 'bg-green-50 text-green-600' : 'bg-amber-50 text-amber-600' }}">
                                    {{ $invoice->status }}
                                </span>
                            </td>
                            <td class="px-10 py-6 text-right flex items-center justify-end gap-2">
                                <a href="{{ route('invoices.show', $invoice) }}" class="p-2 text-slate-400 hover:text-[#0055a4] transition-colors shadow-sm bg-white rounded-lg border border-slate-100"><i data-lucide="eye" class="w-5 h-5"></i></a>
                                <a href="{{ route('invoices.edit', $invoice) }}" class="p-2 text-slate-400 hover:text-amber-500 transition-colors shadow-sm bg-white rounded-lg border border-slate-100"><i data-lucide="edit-3" class="w-5 h-5"></i></a>
                                <a href="{{ route('invoices.download', $invoice) }}" class="p-2 text-slate-400 hover:text-[#d32d27] transition-colors shadow-sm bg-white rounded-lg border border-slate-100"><i data-lucide="download" class="w-5 h-5"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-10 py-20 text-center">
                                <div class="flex flex-col items-center gap-4">
                                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center border border-slate-100">
                                        <i data-lucide="file-text" class="w-10 h-10 text-slate-200"></i>
                                    </div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">No invoices generated yet</p>
                                    <a href="{{ route('invoices.create') }}" class="text-[#0055a4] font-bold text-xs underline uppercase tracking-widest">Create your first invoice</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
