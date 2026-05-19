<x-app-layout>
    <x-slot name="header">
        Edit Company
    </x-slot>

    <div class="max-w-5xl mx-auto pb-20">
        <form action="{{ route('companies.update', $company) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PATCH')

            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 rounded-2xl p-6 shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i data-lucide="alert-circle" class="w-5 h-5 text-red-500"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-black text-red-800 uppercase tracking-wider">Please resolve the following errors:</h3>
                            <ul class="mt-2 list-disc list-inside text-xs text-red-700 font-bold space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Company Identity -->
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-10 py-8 border-b border-slate-50 bg-slate-50/50">
                    <h3 class="font-black text-2xl uppercase tracking-tighter italic text-slate-900">Company Identity</h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Update your basic information</p>
                </div>
                <div class="p-10 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Display Name</label>
                        <input type="text" name="name" value="{{ old('name', $company->name) }}" required class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">GST Number</label>
                        <input type="text" name="gst_number" value="{{ old('gst_number', $company->gst_number) }}" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                    </div>
                    <!-- <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">HSN Code</label>
                        <input type="text" name="hsn_code" value="{{ old('hsn_code', $company->hsn_code) }}" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                    </div> -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">PAN Number</label>
                        <input type="text" name="pan_number" value="{{ old('pan_number', $company->pan_number) }}" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Starting Invoice Number</label>
                        <input type="number" name="invoice_starting_number" value="{{ old('invoice_starting_number', $company->invoice_starting_number) }}" required class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                    </div>
                </div>
            </div>

            <!-- Communication -->
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-10 py-8 border-b border-slate-50 bg-slate-50/50">
                    <h3 class="font-black text-2xl uppercase tracking-tighter italic text-slate-900">Communication</h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Address and contact details</p>
                </div>
                <div class="p-10 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Email</label>
                            <input type="email" name="email" value="{{ old('email', $company->email) }}" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone', $company->phone) }}" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Address</label>
                        <textarea name="address" rows="3" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">{{ old('address', $company->address) }}</textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">State</label>
                            <input type="text" name="state" value="{{ old('state', $company->state) }}" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Pincode</label>
                            <input type="text" name="pincode" value="{{ old('pincode', $company->pincode) }}" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Country</label>
                            <input type="text" name="country" value="{{ old('country', $company->country) }}" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bank Details -->
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-10 py-8 border-b border-slate-50 bg-slate-50/50">
                    <h3 class="font-black text-2xl uppercase tracking-tighter italic text-slate-900">Bank Details</h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Payment receiving information</p>
                </div>
                <div class="p-10 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Account Holder Name</label>
                            <input type="text" name="account_name" value="{{ old('account_name', $company->account_name) }}" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Bank Name</label>
                            <input type="text" name="bank_name" value="{{ old('bank_name', $company->bank_name) }}" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Account Number</label>
                            <input type="text" name="account_number" value="{{ old('account_number', $company->account_number) }}" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">IFSC Code</label>
                            <input type="text" name="ifsc_code" value="{{ old('ifsc_code', $company->ifsc_code) }}" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">SWIFT Code</label>
                            <input type="text" name="swift_code" value="{{ old('swift_code', $company->swift_code) }}" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">UPI ID</label>
                            <input type="text" name="upi_id" value="{{ old('upi_id', $company->upi_id) }}" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-[#0055a4] focus:border-[#0055a4] transition-all font-bold text-slate-900">
                        </div>
                    </div>
                    
                    <div class="pt-6 border-t border-slate-50">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1 block mb-3">Update Company Logo Image</label>
                        <div class="flex items-center gap-8">
                            @if($company->logo)
                                <div class="w-32 h-32 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-center overflow-hidden shrink-0">
                                    <img src="{{ asset('storage/' . $company->logo) }}" class="w-full h-full object-contain">
                                </div>
                            @else
                                <div class="w-32 h-32 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-center overflow-hidden shrink-0">
                                    <img src="{{ asset('storage/logo.png') }}" class="w-full h-full object-contain opacity-50">
                                </div>
                            @endif
                            <label for="logo" class="cursor-pointer bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl p-8 hover:border-[#0055a4] hover:bg-blue-50/50 transition-all group flex-1 text-center">
                                <input type="file" id="logo" name="logo" class="hidden" onchange="if(this.files.length) { this.nextElementSibling.querySelector('.logo-filename-text').textContent = this.files[0].name; }">
                                <div class="flex flex-col items-center gap-2">
                                    <i data-lucide="image" class="w-8 h-8 text-slate-300 group-hover:text-[#0055a4] transition-colors"></i>
                                    <span class="text-xs font-black text-slate-400 uppercase tracking-widest logo-filename-text">Click to upload NEW Logo</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-50">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1 block mb-3">Update QR / Barcode Image</label>
                        <div class="flex items-center gap-8">
                            @if($company->qr_code)
                                <div class="w-32 h-32 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-center overflow-hidden shrink-0">
                                    <img src="{{ asset('storage/' . $company->qr_code) }}" class="w-full h-full object-contain">
                                </div>
                            @endif
                            <label for="qr_code" class="cursor-pointer bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl p-8 hover:border-[#0055a4] hover:bg-blue-50/50 transition-all group flex-1 text-center">
                                <input type="file" id="qr_code" name="qr_code" class="hidden" onchange="if(this.files.length) { this.nextElementSibling.querySelector('.filename-text').textContent = this.files[0].name; }">
                                <div class="flex flex-col items-center gap-2">
                                    <i data-lucide="qr-code" class="w-8 h-8 text-slate-300 group-hover:text-[#0055a4] transition-colors"></i>
                                    <span class="text-xs font-black text-slate-400 uppercase tracking-widest filename-text">Click to upload NEW QR Code</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('companies.index') }}" class="text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-900 transition-colors">Cancel Changes</a>
                <button type="submit" class="bg-[#0055a4] hover:bg-[#00448a] text-white font-black py-5 px-16 rounded-[2rem] transition-all duration-200 shadow-2xl shadow-blue-500/30 active:scale-95 text-sm uppercase tracking-widest">
                    Update Company
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
