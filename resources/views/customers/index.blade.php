<x-app-layout>
    <x-slot name="header">
        Customers
    </x-slot>

    <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-10 py-8 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="font-black text-2xl uppercase tracking-tighter italic text-slate-900">Customer Directory</h3>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Manage your clients across all companies</p>
            </div>
            <a href="{{ route('customers.create') }}" class="bg-[#d32d27] hover:bg-[#b21f24] text-white font-black py-3 px-8 rounded-xl transition-all duration-200 shadow-lg shadow-red-500/20 active:scale-95 text-xs uppercase tracking-widest flex items-center gap-2">
                <i data-lucide="user-plus" class="w-4 h-4"></i> Add Customer
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] bg-white">
                        <th class="px-10 py-6">Customer Name</th>
                        <th class="px-10 py-6">Email</th>
                        <th class="px-10 py-6">Phone</th>
                        <th class="px-10 py-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($customers as $customer)
                        <tr class="group hover:bg-blue-50/30 transition-colors">
                            <td class="px-10 py-6 font-bold text-slate-900 uppercase tracking-tight italic">{{ $customer->name }}</td>
                            <td class="px-10 py-6 text-sm text-slate-500 font-medium">{{ $customer->email }}</td>
                            <td class="px-10 py-6 text-sm text-slate-500 font-medium">{{ $customer->phone }}</td>
                            <td class="px-10 py-6 text-right flex items-center justify-end gap-2">
                                <a href="{{ route('customers.edit', $customer) }}" class="p-2 text-slate-400 hover:text-[#0055a4] transition-colors"><i data-lucide="edit-3" class="w-5 h-5"></i></a>
                                <form action="{{ route('customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this customer?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-slate-400 hover:text-[#d32d27] transition-colors"><i data-lucide="trash-2" class="w-5 h-5"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-10 py-20 text-center">
                                <div class="flex flex-col items-center gap-4">
                                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center border border-slate-100">
                                        <i data-lucide="users" class="w-10 h-10 text-slate-200"></i>
                                    </div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">No customers found</p>
                                    <a href="{{ route('customers.create') }}" class="text-[#0055a4] font-bold text-xs underline uppercase tracking-widest">Add your first customer</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
