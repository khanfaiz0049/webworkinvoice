@forelse($payments as $payment)
    <tr class="group hover:bg-blue-50/30 transition-colors">
        <td class="px-4 sm:px-6 lg:px-10 py-6 text-sm text-slate-500 font-bold uppercase tracking-tight italic">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M, Y') }}</td>
        <td class="px-4 sm:px-6 lg:px-10 py-6">
            <div class="font-bold text-slate-900 uppercase tracking-tight italic">{{ $payment->customer?->name ?? 'Deleted Customer' }}</div>
            <div class="text-[10px] text-slate-400 font-black uppercase tracking-widest">{{ $payment->invoice?->company?->name ?? 'N/A' }}</div>
        </td>
        <td class="px-4 sm:px-6 lg:px-10 py-6 text-xs font-black text-[#0055a4] uppercase tracking-widest">{{ $payment->invoice ? $payment->invoice->invoice_number : 'General' }}</td>
        <td class="px-4 sm:px-6 lg:px-10 py-6">
            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-slate-100 text-slate-600">
                {{ $payment->payment_method }}
            </span>
            @if($payment->received_in)
            <div class="mt-2 text-[10px] text-slate-400 font-black uppercase tracking-widest">
                In: {{ $payment->received_in }}
            </div>
            @endif
        </td>
        <td class="px-4 sm:px-6 lg:px-10 py-6 font-black text-slate-900 italic">₹{{ number_format($payment->amount, 2) }}</td>
        <td class="px-4 sm:px-6 lg:px-10 py-6 text-right">
            <div class="flex items-center justify-end gap-2">
                <form action="{{ route('payments.destroy', $payment) }}" method="POST" class="inline payment-undo-form">
                    @csrf
                    @method('DELETE')
                    <button type="button" onclick="confirmPaymentUndo(this)" class="p-2 text-slate-400 hover:text-[#d32d27] transition-colors shadow-sm bg-white rounded-lg border border-slate-100 flex items-center justify-center" title="Undo / Delete Payment">
                        <i data-lucide="rotate-ccw" class="w-5 h-5"></i>
                    </button>
                </form>
                @if($payment->invoice)
                    <a href="{{ route('invoices.show', $payment->invoice) }}" class="p-2 text-slate-400 hover:text-[#0055a4] transition-colors shadow-sm bg-white rounded-lg border border-slate-100 flex items-center justify-center" title="View Invoice"><i data-lucide="eye" class="w-5 h-5"></i></a>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="px-4 sm:px-6 lg:px-10 py-20 text-center">
            <div class="flex flex-col items-center gap-4">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center border border-slate-100">
                    <i data-lucide="credit-card" class="w-10 h-10 text-slate-200"></i>
                </div>
                @if(request('search'))
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">No payments match your search</p>
                @else
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">No payments recorded yet</p>
                    <a href="{{ route('payments.create') }}" class="text-[#0055a4] font-bold text-xs underline uppercase tracking-widest">Record your first payment</a>
                @endif
            </div>
        </td>
    </tr>
@endforelse
