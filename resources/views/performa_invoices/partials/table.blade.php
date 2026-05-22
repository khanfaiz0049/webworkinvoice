@forelse($invoices as $invoice)
    <tr class="group hover:bg-blue-50/30 transition-colors">
        <td class="px-4 sm:px-6 py-6 font-bold text-slate-900 uppercase tracking-tight italic">{{ $invoice->invoice_number }}</td>
        <td class="px-4 sm:px-6 py-6">
            <div class="text-sm font-bold text-slate-900">{{ $invoice->customer?->name ?? 'Deleted Customer' }}</div>
            <div class="text-[10px] text-slate-400 font-black uppercase tracking-widest">{{ $invoice->company?->name ?? 'Deleted Company' }}</div>
        </td>
         <td class="px-4 sm:px-6 py-6 text-sm text-slate-500 font-medium">
             <div>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M, Y') }}</div>
             @if($invoice->renewal_date)
                 <div class="text-[10px] text-amber-600 font-black uppercase tracking-wider mt-1 flex items-center gap-1">
                     <i data-lucide="refresh-cw" class="w-3 h-3 inline"></i>
                     Renew: {{ \Carbon\Carbon::parse($invoice->renewal_date)->format('d M, Y') }}
                 </div>
             @endif
         </td>
        <td class="px-4 sm:px-6 py-6 font-black text-slate-900 italic">₹{{ number_format($invoice->grand_total, 0) }}</td>

        {{-- Invoice # column: shows converted invoice number OR next available --}}
        <td class="px-4 sm:px-6 py-6">
            @if($invoice->status === 'converted')
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-green-50 text-green-700 border border-green-100">
                    <i data-lucide="check-circle-2" class="w-3 h-3"></i> Converted
                </span>
            @else
                @if($nextInvoiceNumber)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-blue-50 text-blue-700 border border-blue-100">
                        <i data-lucide="file-check" class="w-3 h-3"></i> INV #{{ $nextInvoiceNumber }}
                    </span>
                @else
                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-amber-50 text-amber-600">Pending</span>
                @endif
            @endif
        </td>

        <td class="px-4 sm:px-6 py-6 text-right">
            <div class="flex items-center justify-end gap-2">
                {{-- Delete / Undo --}}
                <form action="{{ route('performa-invoices.destroy', $invoice) }}" method="POST" class="inline performa-invoice-undo-form">
                    @csrf
                    @method('DELETE')
                    <button type="button" onclick="confirmPerformaInvoiceUndo(this)" class="p-2 text-slate-400 hover:text-[#d32d27] transition-colors shadow-sm bg-white rounded-lg border border-slate-100 flex items-center justify-center" title="Undo / Delete Proforma Invoice">
                        <i data-lucide="rotate-ccw" class="w-5 h-5"></i>
                    </button>
                </form>

                {{-- View --}}
                <a href="{{ route('performa-invoices.show', $invoice) }}" class="p-2 text-slate-400 hover:text-[#0055a4] transition-colors shadow-sm bg-white rounded-lg border border-slate-100 flex items-center justify-center"><i data-lucide="eye" class="w-5 h-5"></i></a>

                {{-- Edit --}}
                <a href="{{ route('performa-invoices.edit', $invoice) }}" class="p-2 text-slate-400 hover:text-amber-500 transition-colors shadow-sm bg-white rounded-lg border border-slate-100 flex items-center justify-center"><i data-lucide="edit-3" class="w-5 h-5"></i></a>

                {{-- Download PDF --}}
                <a href="{{ route('performa-invoices.download', $invoice) }}" class="p-2 text-slate-400 hover:text-[#d32d27] transition-colors shadow-sm bg-white rounded-lg border border-slate-100 flex items-center justify-center"><i data-lucide="download" class="w-5 h-5"></i></a>

                {{-- Convert to Invoice (only if not already converted) --}}
                @if($invoice->status !== 'converted')
                    <form action="{{ route('performa-invoices.convert', $invoice) }}" method="POST" class="inline">
                        @csrf
                        <button type="button"
                            onclick="confirmConvertToInvoice(this)"
                            class="flex items-center gap-1.5 px-3 py-2 bg-[#0055a4] hover:bg-[#003d7a] text-white rounded-lg text-[10px] font-black uppercase tracking-widest transition-all shadow-sm border border-blue-700/20 active:scale-95"
                            title="Convert to Invoice #{{ $nextInvoiceNumber }}">
                            <i data-lucide="file-plus-2" class="w-4 h-4"></i>
                            Convert
                        </button>
                    </form>
                @endif
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
                @if(request('search'))
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">No performa invoices match your search</p>
                @else
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">No performa invoices generated yet</p>
                    <a href="{{ route('performa-invoices.create') }}" class="text-[#0055a4] font-bold text-xs underline uppercase tracking-widest">Create your first proforma invoice</a>
                @endif
            </div>
        </td>
    </tr>
@endforelse
