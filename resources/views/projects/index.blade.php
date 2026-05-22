<x-app-layout>
    <x-slot name="header">
        Projects
    </x-slot>

    <div class="bg-white rounded-2xl sm:rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden w-full">
        {{-- Header bar --}}
        <div class="px-4 sm:px-6 lg:px-10 py-6 sm:py-8 border-b border-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/50 w-full">
            <div class="w-full sm:w-auto">
                <h3 class="font-black text-2xl uppercase tracking-tighter italic text-slate-900">Client Projects</h3>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Track services, renewals & project status</p>
            </div>
            <div class="flex items-center gap-3 w-full sm:w-auto">
                {{-- Status filter pills --}}
                <div class="hidden sm:flex items-center gap-2">
                    <a href="{{ route('projects.index') }}" class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ !request('status') ? 'bg-[#d32d27] text-white shadow-lg shadow-red-500/20' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">All</a>
                    <a href="{{ route('projects.index', ['status' => 'open']) }}" class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ request('status') === 'open' ? 'bg-[#d32d27] text-white shadow-lg shadow-red-500/20' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">Open</a>
                    <a href="{{ route('projects.index', ['status' => 'closed']) }}" class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ request('status') === 'closed' ? 'bg-[#d32d27] text-white shadow-lg shadow-red-500/20' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">Closed</a>
                </div>
                <a href="{{ route('projects.create') }}" class="w-full sm:w-auto bg-[#d32d27] hover:bg-[#b21f24] text-white font-black py-3 px-8 rounded-xl transition-all duration-200 shadow-lg shadow-red-500/20 active:scale-95 text-xs uppercase tracking-widest flex items-center justify-center gap-2 shrink-0">
                    <i data-lucide="plus" class="w-4 h-4"></i> New Project
                </a>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] bg-white">
                        <th class="px-4 sm:px-6 lg:px-10 py-6">Project</th>
                        <th class="px-4 sm:px-6 lg:px-10 py-6">Client</th>
                        <th class="px-4 sm:px-6 lg:px-10 py-6">Services</th>
                        <th class="px-4 sm:px-6 lg:px-10 py-6">Amount</th>
                        <th class="px-4 sm:px-6 lg:px-10 py-6">Renewal</th>
                        <th class="px-4 sm:px-6 lg:px-10 py-6">Status</th>
                        <th class="px-4 sm:px-6 lg:px-10 py-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($projects as $project)
                        @php
                            $isOverdue = $project->renewal_date && $project->renewal_date->isPast() && $project->status === 'open';
                            $isDueSoon = $project->renewal_date && !$project->renewal_date->isPast() && $project->renewal_date->diffInDays(now()) <= 7 && $project->status === 'open';
                        @endphp
                        <tr class="group hover:bg-blue-50/30 transition-colors {{ $isOverdue ? 'bg-red-50/20' : '' }}">
                            {{-- Project Name --}}
                            <td class="px-4 sm:px-6 lg:px-10 py-6">
                                <div class="font-bold text-slate-900 uppercase tracking-tight italic">{{ $project->name }}</div>
                                <div class="text-[10px] text-slate-400 font-black uppercase tracking-widest mt-0.5">{{ $project->start_date->format('d M, Y') }}</div>
                            </td>

                            {{-- Client --}}
                            <td class="px-4 sm:px-6 lg:px-10 py-6">
                                <div class="text-sm font-bold text-slate-600 italic">{{ $project->customer?->name ?? 'Deleted Client' }}</div>
                                <div class="text-[10px] text-slate-400 font-black uppercase tracking-widest">{{ $project->company?->name ?? '—' }}</div>
                            </td>

                            {{-- Services --}}
                            <td class="px-4 sm:px-6 lg:px-10 py-6">
                                <div class="flex flex-wrap gap-1.5 max-w-[220px]">
                                    @foreach($project->services ?? [] as $service)
                                        <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-blue-50 text-[#0055a4] border border-blue-100">{{ $service }}</span>
                                    @endforeach
                                </div>
                            </td>

                            {{-- Amount --}}
                            <td class="px-4 sm:px-6 lg:px-10 py-6">
                                <span class="text-sm font-black text-slate-900">₹{{ number_format($project->amount, 0) }}</span>
                            </td>

                            {{-- Renewal --}}
                            <td class="px-4 sm:px-6 lg:px-10 py-6">
                                <div class="text-sm font-bold {{ $isOverdue ? 'text-red-500' : ($isDueSoon ? 'text-amber-500' : 'text-slate-500') }}">
                                    @if($project->renewal_date)
                                        {{ $project->renewal_date->format('d M, Y') }}
                                        @if($isOverdue)
                                            <span class="block text-[8px] font-black uppercase tracking-widest text-red-500">Overdue</span>
                                        @elseif($isDueSoon)
                                            <span class="block text-[8px] font-black uppercase tracking-widest text-amber-500">Due Soon</span>
                                        @endif
                                    @else
                                        <span class="text-slate-400 font-bold uppercase tracking-widest">—</span>
                                    @endif
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-slate-100 text-slate-500 mt-1 inline-block">{{ $project->renewal_period_label }}</span>
                            </td>

                            {{-- Status --}}
                            <td class="px-4 sm:px-6 lg:px-10 py-6">
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest {{ $project->status === 'open' ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400' }}">
                                    {{ $project->status }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 sm:px-6 lg:px-10 py-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('projects.edit', $project) }}" class="p-2 text-slate-400 hover:text-[#d32d27] transition-colors" title="Edit">
                                        <i data-lucide="edit-3" class="w-5 h-5"></i>
                                    </a>
                                    <form action="{{ route('projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Delete this project?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-slate-400 hover:text-[#d32d27] transition-colors" title="Delete">
                                            <i data-lucide="trash-2" class="w-5 h-5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 sm:px-6 lg:px-10 py-20 text-center">
                                <div class="flex flex-col items-center gap-4">
                                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center border border-slate-100">
                                        <i data-lucide="briefcase" class="w-10 h-10 text-slate-200"></i>
                                    </div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">No projects found</p>
                                    <a href="{{ route('projects.create') }}" class="text-[#d32d27] font-bold text-xs underline uppercase tracking-widest">Create your first project</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
