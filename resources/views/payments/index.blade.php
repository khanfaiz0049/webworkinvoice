<x-app-layout>
    <x-slot name="header">
        Payments
    </x-slot>

    <div class="bg-white rounded-2xl sm:rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden w-full">
        <div class="px-4 sm:px-6 lg:px-10 py-6 sm:py-8 border-b border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50/50 w-full">
            <div class="w-full md:w-auto">
                <h3 class="font-black text-2xl uppercase tracking-tighter italic text-slate-900">Payment History</h3>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Track all your collections and customer payments</p>
            </div>

            <div class="flex items-center gap-4 flex-1 md:max-w-md w-full">
                <form action="{{ route('payments.index') }}" method="GET" class="relative w-full" onsubmit="event.preventDefault();">
                    <input type="text" name="search" value="{{ request('search') }}" autocomplete="off" placeholder="Search by customer, invoice #, method..." class="w-full bg-white border border-slate-200 rounded-2xl pl-12 pr-10 py-3.5 text-xs font-bold text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all">
                    <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </div>
                    <button type="button" id="clear-search" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors {{ request('search') ? '' : 'hidden' }}">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>

            <a href="{{ route('payments.create') }}" class="w-full md:w-auto bg-[#d32d27] hover:bg-[#b21f24] text-white font-black py-3 px-8 rounded-xl transition-all duration-200 shadow-lg shadow-red-500/20 active:scale-95 text-xs uppercase tracking-widest flex items-center justify-center gap-2 shrink-0">
                <i data-lucide="plus" class="w-4 h-4"></i> Record Payment
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] bg-white">
                        <th class="px-4 sm:px-6 lg:px-10 py-6">Date</th>
                        <th class="px-4 sm:px-6 lg:px-10 py-6">Customer</th>
                        <th class="px-4 sm:px-6 lg:px-10 py-6">Invoice #</th>
                        <th class="px-4 sm:px-6 lg:px-10 py-6">Method</th>
                        <th class="px-4 sm:px-6 lg:px-10 py-6">Amount</th>
                        <th class="px-4 sm:px-6 lg:px-10 py-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="payments-table-body" class="divide-y divide-slate-50">
                    @include('payments.partials.table')
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

        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.querySelector('input[name="search"]');
            const tableBody = document.getElementById('payments-table-body');
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
                        fetch(`{{ route('payments.index') }}?search=${encodeURIComponent(query)}`, {
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
                        .catch(error => console.error('Error fetching payments:', error));
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
