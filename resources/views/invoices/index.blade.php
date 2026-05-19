<x-app-layout>
    <x-slot name="header">
        Billing History
    </x-slot>

    <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-10 py-8 border-b border-slate-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-slate-50/50">
            <div>
                <h3 class="font-black text-2xl uppercase tracking-tighter italic text-slate-900">Invoices</h3>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Track and manage your generated bills</p>
            </div>
            <div class="flex flex-wrap items-center justify-end gap-4 w-full sm:w-auto">
                <form method="GET" action="{{ route('invoices.index') }}" class="flex items-center gap-2 bg-white border border-slate-200 hover:border-slate-300 rounded-xl px-3 py-1.5 shadow-sm transition-colors">
                    <label for="per_page" class="text-[10px] font-black uppercase tracking-widest text-slate-400">Show:</label>
                    <select name="per_page" id="per_page" onchange="this.form.submit()" class="text-xs font-bold text-slate-700 bg-transparent border-none p-0 pr-8 focus:ring-0 cursor-pointer">
                        <option value="5" {{ $perPage == 5 ? 'selected' : '' }}>5</option>
                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                        <option value="all" {{ $perPage === 'all' ? 'selected' : '' }}>All</option>
                    </select>
                </form>

                <a href="{{ route('invoices.create') }}" class="bg-[#d32d27] hover:bg-[#b21f24] text-white font-black py-3 px-8 rounded-xl transition-all duration-200 shadow-lg shadow-red-500/20 active:scale-95 text-xs uppercase tracking-widest flex items-center gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i> Create Invoice
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] bg-white">
                        <th class="px-6 py-6">Invoice #</th>
                        <th class="px-6 py-6">Customer</th>
                        <th class="px-6 py-6">Date</th>
                        <th class="px-6 py-6">Amount</th>
                        <th class="px-6 py-6">Status</th>
                        <th class="px-6 py-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($invoices as $invoice)
                        <tr class="group hover:bg-blue-50/30 transition-colors">
                            <td class="px-6 py-6 font-bold text-slate-900 uppercase tracking-tight italic">{{ $invoice->invoice_number }}</td>
                            <td class="px-6 py-6">
                                <div class="text-sm font-bold text-slate-900">{{ $invoice->customer?->name ?? 'Deleted Customer' }}</div>
                                <div class="text-[10px] text-slate-400 font-black uppercase tracking-widest">{{ $invoice->company?->name ?? 'Deleted Company' }}</div>
                            </td>
                             <td class="px-6 py-6 text-sm text-slate-500 font-medium">
                                 <div>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M, Y') }}</div>
                                 @if($invoice->renewal_date)
                                     <div class="text-[10px] text-amber-600 font-black uppercase tracking-wider mt-1 flex items-center gap-1">
                                         <i data-lucide="refresh-cw" class="w-3 h-3 inline"></i>
                                         Renew: {{ \Carbon\Carbon::parse($invoice->renewal_date)->format('d M, Y') }}
                                     </div>
                                 @endif
                             </td>
                            <td class="px-6 py-6 font-black text-slate-900 italic">₹{{ number_format($invoice->grand_total, 2) }}</td>
                            <td class="px-6 py-6">
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest {{ $invoice->status == 'paid' ? 'bg-green-50 text-green-600' : 'bg-amber-50 text-amber-600' }}">
                                    {{ $invoice->status }}
                                </span>
                            </td>
                            <td class="px-6 py-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" class="inline invoice-undo-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="confirmInvoiceUndo(this)" class="p-2 text-slate-400 hover:text-[#d32d27] transition-colors shadow-sm bg-white rounded-lg border border-slate-100 flex items-center justify-center" title="Undo / Delete Invoice">
                                            <i data-lucide="rotate-ccw" class="w-5 h-5"></i>
                                        </button>
                                    </form>
                                    <a href="{{ route('invoices.show', $invoice) }}" class="p-2 text-slate-400 hover:text-[#0055a4] transition-colors shadow-sm bg-white rounded-lg border border-slate-100 flex items-center justify-center"><i data-lucide="eye" class="w-5 h-5"></i></a>
                                    <a href="{{ route('invoices.edit', $invoice) }}" class="p-2 text-slate-400 hover:text-amber-500 transition-colors shadow-sm bg-white rounded-lg border border-slate-100 flex items-center justify-center"><i data-lucide="edit-3" class="w-5 h-5"></i></a>
                                    <a href="{{ route('invoices.download', $invoice) }}" class="p-2 text-slate-400 hover:text-[#d32d27] transition-colors shadow-sm bg-white rounded-lg border border-slate-100 flex items-center justify-center"><i data-lucide="download" class="w-5 h-5"></i></a>
                                </div>
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
        @if($invoices instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $invoices->hasPages())
            <div class="px-10 py-6 border-t border-slate-100 bg-slate-50/30">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        function confirmInvoiceUndo(button) {
            Swal.fire({
                title: 'Undo Invoice?',
                text: "This will delete the invoice and any payments recorded against it!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d32d27',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, undo it!',
                cancelButtonText: 'Cancel',
                background: '#ffffff',
                color: '#0f172a',
                customClass: {
                    popup: 'rounded-[2rem] p-6 shadow-2xl border border-slate-100 font-sans',
                    title: 'text-2xl font-black uppercase tracking-tight italic text-[#d32d27]',
                    htmlContainer: 'text-sm font-medium text-slate-500 mt-2',
                    confirmButton: 'bg-[#d32d27] hover:bg-[#b21f24] text-white font-bold py-3 px-6 rounded-xl transition-all duration-200 active:scale-95 text-xs uppercase tracking-widest outline-none border-none mr-2',
                    cancelButton: 'bg-slate-500 hover:bg-slate-600 text-white font-bold py-3 px-6 rounded-xl transition-all duration-200 active:scale-95 text-xs uppercase tracking-widest outline-none border-none'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    button.closest('form').submit();
                }
            });
        }
    </script>
    @endpush
</x-app-layout>
