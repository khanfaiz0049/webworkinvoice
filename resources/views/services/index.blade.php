<x-app-layout>
    <x-slot name="header">Services Master</x-slot>

    <div class="max-w-5xl mx-auto space-y-6"
         x-data="{
            addOpen: false,
            editOpen: false,
            editId: null,
            editName: '',
            editDescription: '',
            editIsActive: true,
            openEdit(id, name, desc, active) {
                this.editId = id;
                this.editName = name;
                this.editDescription = desc;
                this.editIsActive = active;
                this.editOpen = true;
            }
         }">

        {{-- ── Page Header Card ─────────────────────────────────────── --}}
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 sm:px-10 py-8 border-b border-slate-50 bg-gradient-to-br from-slate-50 to-white flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <div class="w-10 h-10 rounded-2xl bg-[#0055a4] flex items-center justify-center shadow-lg shadow-blue-500/20">
                            <i data-lucide="layers" class="w-5 h-5 text-white"></i>
                        </div>
                        <h1 class="font-black text-2xl uppercase tracking-tighter italic text-slate-900">Services Master</h1>
                    </div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest ml-13">
                        {{ $services->whereNull('deleted_at')->count() }} active &bull;
                        {{ $services->whereNotNull('deleted_at')->count() }} archived
                    </p>
                </div>

                <button @click="addOpen = true"
                    id="btn-add-service"
                    class="flex items-center gap-2 bg-[#0055a4] hover:bg-[#004482] text-white font-black py-4 px-8 rounded-2xl transition-all duration-200 shadow-xl shadow-blue-500/20 active:scale-95 text-xs uppercase tracking-widest">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    Add Service
                </button>
            </div>

            {{-- ── Services Table ─────────────────────────────────── --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] bg-slate-50/70 border-b border-slate-100">
                            <th class="px-6 sm:px-10 py-5">#</th>
                            <th class="px-4 py-5">Service Name</th>
                            <th class="px-4 py-5 hidden sm:table-cell">Description</th>
                            <th class="px-4 py-5 text-center">Status</th>
                            <th class="px-4 sm:px-6 py-5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($services as $service)
                            <tr class="group transition-colors hover:bg-slate-50/50 {{ $service->trashed() ? 'opacity-50' : '' }}">
                                <td class="px-6 sm:px-10 py-5 text-[10px] font-black text-slate-300 uppercase tracking-widest">
                                    {{ $loop->iteration }}
                                </td>
                                <td class="px-4 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-xl {{ $service->trashed() ? 'bg-slate-100' : 'bg-blue-50' }} flex items-center justify-center shrink-0">
                                            <i data-lucide="tag" class="w-4 h-4 {{ $service->trashed() ? 'text-slate-400' : 'text-[#0055a4]' }}"></i>
                                        </div>
                                        <span class="font-black text-sm text-slate-900 {{ $service->trashed() ? 'line-through text-slate-400' : '' }}">
                                            {{ $service->name }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-5 hidden sm:table-cell">
                                    <span class="text-xs text-slate-500 font-medium">{{ $service->description ?: '—' }}</span>
                                </td>
                                <td class="px-4 py-5 text-center">
                                    @if($service->trashed())
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest bg-slate-100 text-slate-500">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            Archived
                                        </span>
                                    @elseif($service->is_active)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest bg-amber-50 text-amber-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 sm:px-6 py-5">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($service->trashed())
                                            {{-- Restore --}}
                                            <form method="POST" action="{{ route('services.restore', $service->id) }}" class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest text-emerald-600 hover:bg-emerald-50 transition-all border border-transparent hover:border-emerald-100">
                                                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                                    Restore
                                                </button>
                                            </form>
                                        @else
                                            {{-- Toggle Active --}}
                                            <form method="POST" action="{{ route('services.toggle', $service->id) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all border border-transparent
                                                        {{ $service->is_active ? 'text-amber-600 hover:bg-amber-50 hover:border-amber-100' : 'text-emerald-600 hover:bg-emerald-50 hover:border-emerald-100' }}"
                                                    title="{{ $service->is_active ? 'Deactivate' : 'Activate' }}">
                                                    <i data-lucide="{{ $service->is_active ? 'toggle-right' : 'toggle-left' }}" class="w-4 h-4"></i>
                                                    <span class="hidden sm:inline">{{ $service->is_active ? 'Disable' : 'Enable' }}</span>
                                                </button>
                                            </form>

                                            {{-- Edit --}}
                                            <button type="button"
                                                @click="openEdit({{ $service->id }}, '{{ addslashes($service->name) }}', '{{ addslashes($service->description ?? '') }}', {{ $service->is_active ? 'true' : 'false' }})"
                                                class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest text-[#0055a4] hover:bg-blue-50 transition-all border border-transparent hover:border-blue-100">
                                                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                                <span class="hidden sm:inline">Edit</span>
                                            </button>

                                            {{-- Delete --}}
                                            <form method="POST" action="{{ route('services.destroy', $service->id) }}" class="inline"
                                                  onsubmit="return confirm('Archive service \'{{ addslashes($service->name) }}\'? It can be restored later.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest text-[#d32d27] hover:bg-red-50 transition-all border border-transparent hover:border-red-100">
                                                    <i data-lucide="archive" class="w-3.5 h-3.5"></i>
                                                    <span class="hidden sm:inline">Archive</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-10 py-20 text-center">
                                    <div class="flex flex-col items-center gap-4">
                                        <div class="w-16 h-16 rounded-3xl bg-slate-100 flex items-center justify-center">
                                            <i data-lucide="layers" class="w-8 h-8 text-slate-300"></i>
                                        </div>
                                        <div>
                                            <p class="font-black text-slate-400 uppercase tracking-widest text-xs">No services found</p>
                                            <p class="text-[10px] text-slate-300 mt-1">Add your first service to get started</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── Info Card ──────────────────────────────────────────── --}}
        <div class="bg-blue-50 border border-blue-100 rounded-2xl px-6 py-4 flex items-start gap-3">
            <div class="w-8 h-8 rounded-xl bg-[#0055a4] flex items-center justify-center shrink-0 mt-0.5">
                <i data-lucide="info" class="w-4 h-4 text-white"></i>
            </div>
            <div>
                <p class="text-xs font-black text-[#0055a4] uppercase tracking-widest">How services work</p>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Services added here will appear as options in the <strong>Projects</strong> module's service selection dropdown.
                    Archived services are hidden from new projects but preserved for existing records.
                </p>
            </div>
        </div>


        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- ADD SERVICE MODAL                                                   --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        <div x-show="addOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
             @click.self="addOpen = false"
             style="display: none;">

            <div x-show="addOpen"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="bg-white rounded-[2.5rem] shadow-2xl border border-slate-100 w-full max-w-lg overflow-hidden">

                {{-- Modal Header --}}
                <div class="px-8 pt-8 pb-6 border-b border-slate-50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-[#0055a4] flex items-center justify-center shadow-lg shadow-blue-500/20">
                            <i data-lucide="plus-circle" class="w-5 h-5 text-white"></i>
                        </div>
                        <div>
                            <h2 class="font-black text-lg uppercase tracking-tighter italic text-slate-900">Add New Service</h2>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Will appear in project dropdowns</p>
                        </div>
                    </div>
                    <button @click="addOpen = false" class="w-9 h-9 rounded-xl hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-700 transition-all">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                {{-- Modal Body --}}
                <form method="POST" action="{{ route('services.store') }}" class="px-8 py-6 space-y-5">
                    @csrf
                    <div class="space-y-2">
                        <label for="add_name" class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Service Name <span class="text-[#d32d27]">*</span></label>
                        <input type="text" id="add_name" name="name" required autofocus
                            class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900 placeholder:text-slate-300"
                            placeholder="e.g. Google Workspace">
                    </div>
                    <div class="space-y-2">
                        <label for="add_desc" class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Description <span class="text-slate-300">(optional)</span></label>
                        <input type="text" id="add_desc" name="description"
                            class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900 placeholder:text-slate-300"
                            placeholder="Brief description of this service">
                    </div>
                    <div class="flex items-center justify-between pt-4 border-t border-slate-50">
                        <button type="button" @click="addOpen = false"
                            class="text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-700 transition-colors px-2 py-1">
                            Cancel
                        </button>
                        <button type="submit"
                            class="flex items-center gap-2 bg-[#0055a4] hover:bg-[#004482] text-white font-black py-3.5 px-8 rounded-2xl transition-all duration-200 shadow-lg shadow-blue-500/20 active:scale-95 text-xs uppercase tracking-widest">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            Save Service
                        </button>
                    </div>
                </form>
            </div>
        </div>


        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- EDIT SERVICE MODAL                                                  --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        <div x-show="editOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
             @click.self="editOpen = false"
             style="display: none;">

            <div x-show="editOpen"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="bg-white rounded-[2.5rem] shadow-2xl border border-slate-100 w-full max-w-lg overflow-hidden">

                {{-- Modal Header --}}
                <div class="px-8 pt-8 pb-6 border-b border-slate-50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-amber-500 flex items-center justify-center shadow-lg shadow-amber-500/20">
                            <i data-lucide="pencil" class="w-5 h-5 text-white"></i>
                        </div>
                        <div>
                            <h2 class="font-black text-lg uppercase tracking-tighter italic text-slate-900">Edit Service</h2>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest" x-text="editName"></p>
                        </div>
                    </div>
                    <button @click="editOpen = false" class="w-9 h-9 rounded-xl hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-700 transition-all">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                {{-- Modal Body --}}
                <form method="POST" :action="`{{ url('/services') }}/${editId}`" class="px-8 py-6 space-y-5">
                    @csrf
                    @method('PUT')
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Service Name <span class="text-[#d32d27]">*</span></label>
                        <input type="text" name="name" x-model="editName" required
                            class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition-all font-bold text-slate-900">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Description <span class="text-slate-300">(optional)</span></label>
                        <input type="text" name="description" x-model="editDescription"
                            class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition-all font-bold text-slate-900 placeholder:text-slate-300"
                            placeholder="Brief description of this service">
                    </div>
                    <div class="flex items-center gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <label class="flex items-center gap-3 cursor-pointer select-none flex-1">
                            <div class="relative">
                                <input type="checkbox" name="is_active" value="1" x-model="editIsActive" class="sr-only peer">
                                <div class="w-11 h-6 rounded-full border-2 transition-all peer-checked:bg-emerald-500 peer-checked:border-emerald-500 bg-slate-200 border-slate-300"></div>
                                <div class="absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow-sm transition-all peer-checked:translate-x-5"></div>
                            </div>
                            <span class="text-xs font-black uppercase tracking-widest" :class="editIsActive ? 'text-emerald-600' : 'text-slate-400'"
                                x-text="editIsActive ? 'Active — shown in projects' : 'Inactive — hidden from projects'"></span>
                        </label>
                    </div>
                    <div class="flex items-center justify-between pt-4 border-t border-slate-50">
                        <button type="button" @click="editOpen = false"
                            class="text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-700 transition-colors px-2 py-1">
                            Cancel
                        </button>
                        <button type="submit"
                            class="flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white font-black py-3.5 px-8 rounded-2xl transition-all duration-200 shadow-lg shadow-amber-500/20 active:scale-95 text-xs uppercase tracking-widest">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            Update Service
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
