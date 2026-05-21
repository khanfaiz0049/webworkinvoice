<x-app-layout>
    <x-slot name="header">Settings</x-slot>

    @php
        $defaultTab = 'null';
        if (session('status') === 'profile-updated' || session('status') === 'password-updated' || $errors->any()) {
            $defaultTab = "'profile'";
        } elseif (session('backup_log') || session('success') || session('error')) {
            $defaultTab = "'backup'";
        }
    @endphp

    <div x-data="{ activeTab: {{ $defaultTab }} }" class="space-y-8">
         
        {{-- Settings Navigation Grid Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl">
            {{-- Profile Settings Button Card --}}
            <button type="button" 
                @click="activeTab = (activeTab === 'profile' ? null : 'profile')"
                :class="activeTab === 'profile' ? 'border-[#0055a4] bg-blue-50/20 ring-2 ring-[#0055a4]/10' : 'border-slate-200 bg-white hover:border-blue-300 hover:shadow-md hover:scale-[1.01]'"
                class="w-full text-left p-6 rounded-2xl border transition-all duration-300 cursor-pointer flex gap-5 items-start">
                <div :class="activeTab === 'profile' ? 'bg-[#0055a4] text-white' : 'bg-slate-100 text-[#0055a4] hover:bg-[#0055a4]/10'"
                    class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 transition-colors duration-300">
                    <i data-lucide="user" class="w-6 h-6"></i>
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between">
                        <h3 :class="activeTab === 'profile' ? 'text-[#0055a4]' : 'text-slate-900'"
                            class="text-sm font-black uppercase tracking-widest transition-colors duration-300">
                            Profile Settings
                        </h3>
                        <i data-lucide="chevron-right" 
                           :class="activeTab === 'profile' ? 'rotate-90 text-[#0055a4]' : 'text-slate-400'"
                           class="w-4 h-4 transition-transform duration-300"></i>
                    </div>
                    <p class="text-xs text-slate-500 mt-2 leading-relaxed font-medium">
                        Manage your user account credentials, update email address, change password, or delete your account.
                    </p>
                </div>
            </button>

            {{-- Backup & Restore Button Card --}}
            <button type="button" 
                @click="activeTab = (activeTab === 'backup' ? null : 'backup')"
                :class="activeTab === 'backup' ? 'border-[#0055a4] bg-blue-50/20 ring-2 ring-[#0055a4]/10' : 'border-slate-200 bg-white hover:border-blue-300 hover:shadow-md hover:scale-[1.01]'"
                class="w-full text-left p-6 rounded-2xl border transition-all duration-300 cursor-pointer flex gap-5 items-start">
                <div :class="activeTab === 'backup' ? 'bg-[#0055a4] text-white' : 'bg-slate-100 text-[#0055a4] hover:bg-[#0055a4]/10'"
                    class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 transition-colors duration-300">
                    <i data-lucide="shield-check" class="w-6 h-6"></i>
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between">
                        <h3 :class="activeTab === 'backup' ? 'text-[#0055a4]' : 'text-slate-900'"
                            class="text-sm font-black uppercase tracking-widest transition-colors duration-300">
                            Backup & Restore
                        </h3>
                        <i data-lucide="chevron-right" 
                           :class="activeTab === 'backup' ? 'rotate-90 text-[#0055a4]' : 'text-slate-400'"
                           class="w-4 h-4 transition-transform duration-300"></i>
                    </div>
                    <p class="text-xs text-slate-500 mt-2 leading-relaxed font-medium">
                        Monitor database connections and system diagnostics, create manual/compressed SQL backups, or restore data.
                    </p>
                </div>
            </button>
        </div>

        {{-- Profile Settings Tab Content --}}
        <div x-show="activeTab === 'profile'" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-4"
             class="space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>

        {{-- Backup & Restore Tab Content --}}
        <div x-show="activeTab === 'backup'" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-4"
             class="space-y-8">
            {{-- ── Diagnostics Grid ─────────────────────────────────────────── --}}
            <div>
                <h2 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-4">System Diagnostics</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">

                    {{-- DB Connection --}}
                    <div class="bg-white rounded-2xl border {{ $health['mysql_connection'] ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }} p-5 flex flex-col items-center text-center shadow-sm">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-3 {{ $health['mysql_connection'] ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                            <i data-lucide="{{ $health['mysql_connection'] ? 'database' : 'x-circle' }}" class="w-5 h-5"></i>
                        </div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1">DB Connection</p>
                        <p class="text-xs font-bold {{ $health['mysql_connection'] ? 'text-green-700' : 'text-red-700' }}">
                            {{ $health['mysql_connection'] ? 'Connected' : 'FAILED' }}
                        </p>
                    </div>

                    {{-- mysqldump --}}
                    <div class="bg-white rounded-2xl border {{ $health['mysqldump_found'] ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }} p-5 flex flex-col items-center text-center shadow-sm">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-3 {{ $health['mysqldump_found'] ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                            <i data-lucide="{{ $health['mysqldump_found'] ? 'terminal' : 'x-circle' }}" class="w-5 h-5"></i>
                        </div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1">mysqldump</p>
                        <p class="text-xs font-bold {{ $health['mysqldump_found'] ? 'text-green-700' : 'text-red-700' }}">
                            {{ $health['mysqldump_found'] ? 'Found' : 'Not Found' }}
                        </p>
                    </div>

                    {{-- mysql binary --}}
                    <div class="bg-white rounded-2xl border {{ $health['mysql_found'] ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }} p-5 flex flex-col items-center text-center shadow-sm">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-3 {{ $health['mysql_found'] ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                            <i data-lucide="{{ $health['mysql_found'] ? 'terminal' : 'x-circle' }}" class="w-5 h-5"></i>
                        </div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1">mysql client</p>
                        <p class="text-xs font-bold {{ $health['mysql_found'] ? 'text-green-700' : 'text-red-700' }}">
                            {{ $health['mysql_found'] ? 'Found' : 'Not Found' }}
                        </p>
                    </div>

                    {{-- Backup Folder --}}
                    <div class="bg-white rounded-2xl border {{ $health['backup_folder_ok'] ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }} p-5 flex flex-col items-center text-center shadow-sm">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-3 {{ $health['backup_folder_ok'] ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                            <i data-lucide="{{ $health['backup_folder_ok'] ? 'folder-check' : 'folder-x' }}" class="w-5 h-5"></i>
                        </div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1">Backup Folder</p>
                        <p class="text-xs font-bold {{ $health['backup_folder_ok'] ? 'text-green-700' : 'text-red-700' }}">
                            {{ $health['backup_folder_ok'] ? 'Writable' : 'Not Writable' }}
                        </p>
                    </div>

                    {{-- Database Size --}}
                    <div class="bg-white rounded-2xl border border-blue-100 bg-blue-50 p-5 flex flex-col items-center text-center shadow-sm">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-3 bg-blue-100 text-blue-600">
                            <i data-lucide="hard-drive" class="w-5 h-5"></i>
                        </div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1">Database Size</p>
                        <p class="text-xs font-bold text-blue-700">
                            {{ $health['database_size_mb'] !== null ? $health['database_size_mb'] . ' MB' : 'N/A' }}
                        </p>
                    </div>

                    {{-- Latest Backup --}}
                    <div class="bg-white rounded-2xl border border-slate-200 p-5 flex flex-col items-center text-center shadow-sm">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-3 bg-slate-100 text-slate-600">
                            <i data-lucide="clock" class="w-5 h-5"></i>
                        </div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1">Latest Backup</p>
                        <p class="text-xs font-bold text-slate-700">
                            {{ $health['latest_backup'] ?? 'None yet' }}
                        </p>
                    </div>

                </div>

                {{-- Path details (collapsed) --}}
                @if($health['mysqldump_path'] || $health['mysql_path'])
                <div class="mt-4 bg-slate-50 border border-slate-200 rounded-2xl p-4 text-xs text-slate-500 font-mono space-y-1">
                    <p><span class="font-black text-slate-700">OS:</span> {{ $health['os'] }}</p>
                    @if($health['mysqldump_path'])
                        <p><span class="font-black text-slate-700">mysqldump:</span> {{ $health['mysqldump_path'] }}</p>
                    @endif
                    @if($health['mysql_path'])
                        <p><span class="font-black text-slate-700">mysql:</span> {{ $health['mysql_path'] }}</p>
                    @endif
                    <p><span class="font-black text-slate-700">Backup folder:</span> {{ $health['backup_folder'] }}</p>
                    <p><span class="font-black text-slate-700">Total stored backups:</span> {{ $health['backup_count'] }}</p>
                </div>
                @endif
            </div>

            {{-- ── Restore Log (shown after a restore attempt) ───────────────── --}}
            @if(session('backup_log'))
            <div class="bg-slate-900 rounded-2xl p-6 font-mono text-xs space-y-1.5">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3">Restore Log</p>
                @foreach(session('backup_log') as $line)
                    <p class="{{ str_starts_with($line, '✓') ? 'text-green-400' : (str_starts_with($line, '✗') ? 'text-red-400' : (str_starts_with($line, '⚠') ? 'text-yellow-400' : 'text-slate-300')) }}">{{ $line }}</p>
                @endforeach
            </div>
            @endif

            {{-- ── Actions Row ───────────────────────────────────────────────── --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- ── BACKUP PANEL ──────────────────────────────────────────── --}}
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 rounded-2xl bg-[#0055a4]/10 flex items-center justify-center">
                            <i data-lucide="download" class="w-6 h-6 text-[#0055a4]"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-black uppercase tracking-widest text-slate-900">Create Backup</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Export the current database as a SQL file</p>
                        </div>
                    </div>

                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-6 text-xs text-blue-700 leading-relaxed">
                        This system is configured for <strong>both Local (Windows/XAMPP) and Live (Linux)</strong> environments.
                        If <code class="bg-blue-100 px-1 rounded">mysqldump</code> is not auto-detected, set
                        <code class="bg-blue-100 px-1 rounded">MYSQLDUMP_PATH</code> in your <code class="bg-blue-100 px-1 rounded">.env</code> file.
                    </div>

                    <div class="space-y-3">
                        {{-- Download directly --}}
                        <form method="POST" action="{{ route('backup.download') }}">
                            @csrf
                            <input type="hidden" name="gzip" value="0">
                            <button id="btn-download-sql" type="submit"
                                class="w-full flex items-center justify-center gap-3 px-6 py-3.5 bg-[#0055a4] hover:bg-[#004482] text-white text-xs font-black uppercase tracking-widest rounded-xl transition-all duration-200 shadow-lg shadow-blue-500/20 active:scale-95 cursor-pointer {{ !$health['mysqldump_found'] || !$health['mysql_connection'] ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}">
                                <i data-lucide="download-cloud" class="w-4 h-4"></i>
                                Download SQL Backup
                            </button>
                        </form>

                        {{-- Store (save to server) --}}
                        <form method="POST" action="{{ route('backup.store') }}">
                            @csrf
                            <input type="hidden" name="gzip" value="0">
                            <button id="btn-store-sql" type="submit"
                                class="w-full flex items-center justify-center gap-3 px-6 py-3 border-2 border-[#0055a4] text-[#0055a4] hover:bg-blue-50 text-xs font-black uppercase tracking-widest rounded-xl transition-all duration-200 cursor-pointer {{ !$health['mysqldump_found'] || !$health['mysql_connection'] ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}">
                                <i data-lucide="save" class="w-4 h-4"></i>
                                Save Backup to Server
                            </button>
                        </form>
                    </div>

                    @if(!$health['mysqldump_found'])
                        <p class="mt-4 text-xs text-red-600 font-medium">
                            ⚠ <strong>mysqldump</strong> was not found. Backup is disabled. Set
                            <code class="bg-red-50 px-1 rounded">MYSQLDUMP_PATH</code> in your <code>.env</code>.
                        </p>
                    @endif
                </div>

                {{-- ── RESTORE PANEL ─────────────────────────────────────────── --}}
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 rounded-2xl bg-[#d32d27]/10 flex items-center justify-center">
                            <i data-lucide="upload" class="w-6 h-6 text-[#d32d27]"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-black uppercase tracking-widest text-slate-900">Restore Database</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Import a .sql backup file</p>
                        </div>
                    </div>

                    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6 text-xs text-red-700 leading-relaxed font-medium">
                        ⚠ <strong>Warning:</strong> Restoring will permanently overwrite your current database.
                        An <strong>emergency backup</strong> is created automatically before the restore begins.
                        Please ensure you have a recent backup before proceeding.
                    </div>

                    <form id="restore-form" method="POST" action="{{ route('backup.restore') }}" enctype="multipart/form-data"
                        x-data="{ filename: '', confirming: false }">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">
                                    Select .sql Backup File
                                </label>
                                <input type="file" id="sql_file" name="sql_file" accept=".sql"
                                    class="w-full text-xs text-slate-600 border border-slate-200 rounded-xl px-4 py-3 cursor-pointer file:mr-4 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-600 hover:file:bg-slate-200 transition-all"
                                    @change="filename = $event.target.files[0]?.name || ''">
                                @error('sql_file')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <template x-if="!confirming">
                                <button type="button" id="btn-restore-confirm"
                                    @click="if(filename) confirming = true"
                                    :class="filename ? '' : 'opacity-40 cursor-not-allowed'"
                                    class="w-full flex items-center justify-center gap-3 px-6 py-3.5 bg-[#d32d27] hover:bg-[#b21f24] text-white text-xs font-black uppercase tracking-widest rounded-xl transition-all duration-200 shadow-lg shadow-red-500/20 active:scale-95 cursor-pointer">
                                    <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                                    Upload & Restore
                                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                                </button>
                            </template>

                            <template x-if="confirming">
                                <div class="space-y-2">
                                    <p class="text-xs text-center text-red-700 font-bold">
                                        Are you absolutely sure? This will overwrite ALL current data.
                                    </p>
                                    <div class="flex gap-3">
                                        <button type="button" @click="confirming = false"
                                            class="flex-1 px-4 py-3 border-2 border-slate-300 text-slate-600 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-slate-50 transition-all cursor-pointer">
                                            Cancel
                                        </button>
                                        <button type="submit" id="btn-restore-go"
                                            class="flex-1 flex items-center justify-center gap-2 px-4 py-3 bg-[#d32d27] text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-[#b21f24] transition-all shadow-lg shadow-red-500/20 active:scale-95 cursor-pointer">
                                            <i data-lucide="zap" class="w-4 h-4"></i>
                                            Yes, Restore Now
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </form>

                    @if(!$health['mysql_found'])
                        <p class="mt-4 text-xs text-red-600 font-medium">
                            ⚠ <strong>mysql</strong> binary was not found. Restore is disabled. Set
                            <code class="bg-red-50 px-1 rounded">MYSQL_PATH</code> in your <code>.env</code>.
                        </p>
                    @endif
                </div>
            </div>

            {{-- ── Stored Backups Table ──────────────────────────────────────── --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between px-8 py-5 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <i data-lucide="archive" class="w-5 h-5 text-[#0055a4]"></i>
                        <h2 class="text-sm font-black uppercase tracking-widest text-slate-900">Stored Backups</h2>
                        <span class="text-xs font-bold text-slate-400">({{ count($backups) }} file{{ count($backups) === 1 ? '' : 's' }})</span>
                    </div>
                    <span class="text-xs text-slate-400">Auto-cleaned after 30 days · saved in <code class="bg-slate-100 px-1 rounded">storage/app/backups/</code></span>
                </div>

                @if(count($backups) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50">
                                    <th class="text-left px-8 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400">Filename</th>
                                    <th class="text-left px-4 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400">Size</th>
                                    <th class="text-left px-4 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400">Created</th>
                                    <th class="text-right px-8 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($backups as $backup)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-8 py-4 font-mono text-slate-700">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="{{ str_ends_with($backup['filename'], '.gz') ? 'file-archive' : 'file-code' }}" class="w-4 h-4 text-slate-400 flex-shrink-0"></i>
                                            {{ $backup['filename'] }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-slate-500">{{ $backup['size_mb'] }} MB</td>
                                    <td class="px-4 py-4 text-slate-500">{{ $backup['created_at'] }}</td>
                                    <td class="px-8 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('backup.files.download', $backup['filename']) }}"
                                                class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-[#0055a4] text-[10px] font-black uppercase tracking-widest rounded-lg hover:bg-blue-100 transition-colors">
                                                <i data-lucide="download" class="w-3.5 h-3.5"></i> Download
                                            </a>
                                            <form method="POST" action="{{ route('backup.files.destroy', $backup['filename']) }}"
                                                onsubmit="return confirm('Delete {{ $backup['filename'] }}? This cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="flex items-center gap-1.5 px-3 py-1.5 bg-red-50 text-red-600 text-[10px] font-black uppercase tracking-widest rounded-lg hover:bg-red-100 transition-colors cursor-pointer">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-20 text-slate-400">
                        <i data-lucide="inbox" class="w-12 h-12 mb-4 opacity-30"></i>
                        <p class="text-sm font-black uppercase tracking-widest">No stored backups yet</p>
                        <p class="text-xs mt-1">Click "Save Backup to Server" to create your first stored backup.</p>
                    </div>
                @endif
            </div>

            {{-- ── CLI Reference Card ────────────────────────────────────────── --}}
            <div class="bg-slate-900 rounded-2xl p-6">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-4">Artisan Commands (CLI)</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 font-mono text-xs">
                    <div class="space-y-2">
                        <p class="text-slate-400"># Run a backup</p>
                        <p class="text-green-400">php artisan backup:run</p>
                        <p class="text-slate-400"># Run a compressed backup</p>
                        <p class="text-green-400">php artisan backup:run --gzip</p>
                    </div>
                    <div class="space-y-2">
                        <p class="text-slate-400"># Show diagnostics</p>
                        <p class="text-green-400">php artisan backup:health</p>
                        <p class="text-slate-400"># JSON diagnostics</p>
                        <p class="text-green-400">php artisan backup:health --json</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
