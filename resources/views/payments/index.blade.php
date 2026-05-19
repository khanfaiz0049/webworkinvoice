<x-app-layout>
    <x-slot name="header">
        Payments
    </x-slot>

    <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-10 py-8 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="font-black text-2xl uppercase tracking-tighter italic text-slate-900">Payment History</h3>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Track all your collections and customer payments</p>
            </div>
            <a href="{{ route('payments.create') }}" class="bg-[#d32d27] hover:bg-[#b21f24] text-white font-black py-3 px-8 rounded-xl transition-all duration-200 shadow-lg shadow-red-500/20 active:scale-95 text-xs uppercase tracking-widest flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i> Record Payment
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] bg-white">
                        <th class="px-10 py-6">Date</th>
                        <th class="px-10 py-6">Customer</th>
                        <th class="px-10 py-6">Invoice #</th>
                        <th class="px-10 py-6">Method</th>
                        <th class="px-10 py-6">Amount</th>
                        <th class="px-10 py-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($payments as $payment)
                        <tr class="group hover:bg-blue-50/30 transition-colors">
                            <td class="px-10 py-6 text-sm text-slate-500 font-bold uppercase tracking-tight italic">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M, Y') }}</td>
                            <td class="px-10 py-6">
                                <div class="font-bold text-slate-900 uppercase tracking-tight italic">{{ $payment->customer?->name ?? 'Deleted Customer' }}</div>
                                <div class="text-[10px] text-slate-400 font-black uppercase tracking-widest">{{ $payment->invoice?->company?->name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-10 py-6 text-xs font-black text-[#0055a4] uppercase tracking-widest">{{ $payment->invoice ? $payment->invoice->invoice_number : 'General' }}</td>
                            <td class="px-10 py-6">
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-slate-100 text-slate-600">
                                    {{ $payment->payment_method }}
                                </span>
                                @if($payment->received_in)
                                <div class="mt-2 text-[10px] text-slate-400 font-black uppercase tracking-widest">
                                    In: {{ $payment->received_in }}
                                </div>
                                @endif
                            </td>
                            <td class="px-10 py-6 font-black text-slate-900 italic">₹{{ number_format($payment->amount, 2) }}</td>
                            <td class="px-10 py-6 text-right">
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
                            <td colspan="6" class="px-10 py-20 text-center">
                                <div class="flex flex-col items-center gap-4">
                                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center border border-slate-100">
                                        <i data-lucide="credit-card" class="w-10 h-10 text-slate-200"></i>
                                    </div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">No payments recorded yet</p>
                                    <a href="{{ route('payments.create') }}" class="text-[#0055a4] font-bold text-xs underline uppercase tracking-widest">Record your first payment</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
        function confirmPaymentUndo(button) {
            Swal.fire({
                title: 'Undo Payment?',
                text: "This will delete the payment and adjust the linked invoice's amounts!",
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
