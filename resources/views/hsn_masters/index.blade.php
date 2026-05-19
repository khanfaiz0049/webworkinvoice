<x-app-layout>
    <x-slot name="header">
        HSN Master
    </x-slot>

    <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-10 py-8 border-b border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50/50">
            <div>
                <h3 class="font-black text-2xl uppercase tracking-tighter italic text-slate-900">HSN Master Directory</h3>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Manage service names and HSN / SAC codes</p>
            </div>
            
            <div class="flex items-center gap-4 flex-1 md:max-w-md w-full">
                <form action="{{ route('hsn-masters.index') }}" method="GET" class="relative w-full" onsubmit="event.preventDefault();">
                    <input type="text" name="search" value="{{ request('search') }}" autocomplete="off" placeholder="Search by service name or HSN code..." class="w-full bg-white border border-slate-200 rounded-2xl pl-12 pr-10 py-3.5 text-xs font-bold text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all">
                    <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" x2="16.65" y1="21" y2="16.65"></line>
                        </svg>
                    </div>
                    <button type="button" id="clear-search" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors {{ request('search') ? '' : 'hidden' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </form>
            </div>

            <a href="{{ route('hsn-masters.create') }}" class="bg-[#d32d27] hover:bg-[#b21f24] text-white font-black py-3.5 px-8 rounded-2xl transition-all duration-200 shadow-lg shadow-red-500/20 active:scale-95 text-xs uppercase tracking-widest flex items-center gap-2 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Add HSN Master
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] bg-white border-b border-slate-100">
                        <th class="px-10 py-6">Service Name</th>
                        <th class="px-10 py-6">HSN / SAC Code</th>
                        <th class="px-10 py-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="hsn-table-body" class="divide-y divide-slate-50">
                    @include('hsn_masters.partials.table')
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.querySelector('input[name="search"]');
            const tableBody = document.getElementById('hsn-table-body');
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
                        fetch(`{{ route('hsn-masters.index') }}?search=${encodeURIComponent(query)}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.text())
                        .then(html => {
                            tableBody.innerHTML = html;
                        })
                        .catch(error => console.error('Error fetching HSN masters:', error));
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
