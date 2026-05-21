<x-app-layout>
    <x-slot name="header">
        Billing History (Proforma)
    </x-slot>

    <div class="bg-white rounded-2xl sm:rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden w-full">
        <div class="px-4 sm:px-6 lg:px-10 py-6 sm:py-8 border-b border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50/50 w-full">
            <div class="w-full md:w-auto">
                <h3 class="font-black text-2xl uppercase tracking-tighter italic text-slate-900">Proforma Invoices</h3>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Track and manage your generated proforma bills</p>
            </div>

            <div class="flex items-center gap-4 flex-1 md:max-w-md w-full">
                <form action="{{ route('performa-invoices.index') }}" method="GET" class="relative w-full" onsubmit="event.preventDefault();">
                    <input type="text" name="search" value="{{ request('search') }}" autocomplete="off" placeholder="Search by performa #, customer, company..." class="w-full bg-white border border-slate-200 rounded-2xl pl-12 pr-10 py-3.5 text-xs font-bold text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all">
                    <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </div>
                    <button type="button" id="clear-search" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors {{ request('search') ? '' : 'hidden' }}">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-4 w-full md:w-auto">
                <form method="GET" action="{{ route('performa-invoices.index') }}" class="flex items-center justify-between sm:justify-start gap-2 bg-white border border-slate-200 hover:border-slate-300 rounded-xl px-3 py-1.5 shadow-sm transition-colors w-full sm:w-auto">
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

                <a href="{{ route('performa-invoices.create') }}" class="w-full sm:w-auto bg-[#d32d27] hover:bg-[#b21f24] text-white font-black py-3 px-8 rounded-xl transition-all duration-200 shadow-lg shadow-red-500/20 active:scale-95 text-xs uppercase tracking-widest flex items-center justify-center gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i> Create Proforma Invoice
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] bg-white">
                        <th class="px-4 sm:px-6 py-6">Proforma #</th>
                        <th class="px-4 sm:px-6 py-6">Customer</th>
                        <th class="px-4 sm:px-6 py-6">Date</th>
                        <th class="px-4 sm:px-6 py-6">Amount</th>
                        <th class="px-4 sm:px-6 py-6">Invoice #</th>
                        <th class="px-4 sm:px-6 py-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="performa-invoices-table-body" class="divide-y divide-slate-50">
                    @include('performa_invoices.partials.table')
                </tbody>
            </table>
        </div>
        @if($invoices instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $invoices->hasPages())
            <div class="px-4 sm:px-6 lg:px-10 py-6 border-t border-slate-100 bg-slate-50/30">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        function confirmProformaInvoiceUndo(button) {
            Swal.fire({
                title: 'Undo Proforma Invoice?',
                text: "This will permanently delete this proforma invoice!",
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

        function confirmConvertToInvoice(button) {
            Swal.fire({
                title: 'Convert to Invoice?',
                text: "This will create a new Invoice from this Proforma and mark it as converted.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0055a4',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, convert it!',
                cancelButtonText: 'Cancel',
                background: '#ffffff',
                color: '#0f172a',
                customClass: {
                    popup: 'rounded-[2rem] p-6 shadow-2xl border border-slate-100 font-sans',
                    title: 'text-2xl font-black uppercase tracking-tight italic text-[#0055a4]',
                    htmlContainer: 'text-sm font-medium text-slate-500 mt-2',
                    confirmButton: 'bg-[#0055a4] hover:bg-[#003d7a] text-white font-bold py-3 px-6 rounded-xl transition-all duration-200 active:scale-95 text-xs uppercase tracking-widest outline-none border-none mr-2',
                    cancelButton: 'bg-slate-500 hover:bg-slate-600 text-white font-bold py-3 px-6 rounded-xl transition-all duration-200 active:scale-95 text-xs uppercase tracking-widest outline-none border-none'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    button.closest('form').submit();
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.querySelector('input[name="search"]');
            const tableBody = document.getElementById('performa-invoices-table-body');
            const clearBtn = document.getElementById('clear-search');
            let debounceTimeout = null;

            if (searchInput && tableBody) {
                searchInput.addEventListener('input', function () {
                    const query = searchInput.value;

                    // Show/hide clear button
                    if (clearBtn) {
                        if (query.length > 0) {
                            clearBtn.classList.remove('hidden');
                        } else {
                            clearBtn.classList.add('hidden');
                        }
                    }

                    clearTimeout(debounceTimeout);
                    debounceTimeout = setTimeout(() => {
                        // Perform AJAX fetch request
                        fetch(`{{ route('performa-invoices.index') }}?search=${encodeURIComponent(query)}&per_page={{ $perPage }}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.text())
                        .then(html => {
                            tableBody.innerHTML = html;
                            // Reinitialize Lucide icons for the new table rows
                            if (window.lucide) {
                                window.lucide.createIcons({ icons: window.lucide.icons });
                            }
                        })
                        .catch(error => console.error('Error fetching performa invoices:', error));
                    }, 300); // 300ms debounce
                });

                if (clearBtn) {
                    clearBtn.addEventListener('click', function () {
                        searchInput.value = '';
                        clearBtn.classList.add('hidden');
                        searchInput.dispatchEvent(new Event('input'));
                        searchInput.focus();
                    });
                }
            }
        });
    </script>
    @endpush
</x-app-layout>
