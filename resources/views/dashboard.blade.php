<x-app-layout>
    <x-slot name="header">
        Dashboard
        <span class="block text-xs font-bold text-slate-400 uppercase tracking-widest mt-1 not-italic">Combined report across all {{ $totalCompanies }} companies</span>
    </x-slot>

    <div class="space-y-8">
        <!-- Top Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Revenue -->
            <div class="bg-white p-6 sm:p-8 rounded-2xl sm:rounded-[2.5rem] shadow-sm border border-slate-100 group hover:shadow-xl hover:shadow-blue-500/5 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-[#0055a4] group-hover:scale-110 transition-transform">
                        <i data-lucide="indian-rupee" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-black text-green-500 bg-green-50 px-2 py-1 rounded-lg uppercase tracking-widest">Revenue</span>
                </div>
                <h3 class="text-3xl font-black text-slate-900 italic tracking-tighter">₹{{ number_format($totalRevenue, 0) }}</h3>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Total Collections</p>
            </div>

            <!-- Total Outstanding -->
            <div class="bg-white p-6 sm:p-8 rounded-2xl sm:rounded-[2.5rem] shadow-sm border border-slate-100 group hover:shadow-xl hover:shadow-red-500/5 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-red-50 rounded-2xl flex items-center justify-center text-[#d32d27] group-hover:scale-110 transition-transform">
                        <i data-lucide="clock" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-black text-red-500 bg-red-50 px-2 py-1 rounded-lg uppercase tracking-widest">Pending</span>
                </div>
                <h3 class="text-3xl font-black text-slate-900 italic tracking-tighter">₹{{ number_format($totalOutstanding, 0) }}</h3>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Total Outstanding</p>
            </div>

            <!-- Overdue Invoices -->
            <div class="bg-white p-6 sm:p-8 rounded-2xl sm:rounded-[2.5rem] shadow-sm border border-slate-100 group hover:shadow-xl hover:shadow-orange-500/5 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-orange-50 rounded-2xl flex items-center justify-center text-orange-600 group-hover:scale-110 transition-transform">
                        <i data-lucide="alert-circle" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-black text-orange-500 bg-orange-50 px-2 py-1 rounded-lg uppercase tracking-widest">Critical</span>
                </div>
                <h3 class="text-3xl font-black text-slate-900 italic tracking-tighter">{{ $overdueInvoices }}</h3>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Overdue Bills</p>
            </div>

            <!-- Renewals Due -->
            <div class="bg-white p-6 sm:p-8 rounded-2xl sm:rounded-[2.5rem] shadow-sm border border-slate-100 group hover:shadow-xl hover:shadow-purple-500/5 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-purple-50 rounded-2xl flex items-center justify-center text-purple-600 group-hover:scale-110 transition-transform">
                        <i data-lucide="refresh-cw" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-black text-purple-500 bg-purple-50 px-2 py-1 rounded-lg uppercase tracking-widest">Renewals</span>
                </div>
                <h3 class="text-3xl font-black text-slate-900 italic tracking-tighter">{{ $renewalsDue }}</h3>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Due in 30 Days</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Revenue Graph -->
            <div class="lg:col-span-2 bg-white p-6 sm:p-10 rounded-2xl sm:rounded-[3rem] shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-xl font-black text-slate-900 italic uppercase tracking-tighter">Revenue Growth</h3>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Monthly collection overview</p>
                    </div>
                </div>
                <div id="revenueChart"></div>
            </div>

            <!-- Recent Payments -->
            <div class="bg-white p-6 sm:p-10 rounded-2xl sm:rounded-[3rem] shadow-sm border border-slate-100">
                <h3 class="text-xl font-black text-slate-900 italic uppercase tracking-tighter mb-6">Recent Payments</h3>
                <div class="space-y-6">
                    @forelse($recentPayments as $payment)
                        <div class="flex items-center justify-between group">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 group-hover:bg-blue-50 group-hover:text-[#0055a4] transition-all">
                                    <i data-lucide="arrow-down-left" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-black text-slate-900 uppercase italic">{{ $payment->customer?->name ?? 'Deleted Customer' }}</p>
                                    <p class="text-[10px] font-bold text-slate-400">
                                        {{ $payment->invoice?->company?->name ?? 'N/A' }} &middot; {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M, Y') }}
                                        @if($payment->received_in)
                                            &middot; In: {{ $payment->received_in }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <p class="text-xs font-black text-green-600 italic">₹{{ number_format($payment->amount, 0) }}</p>
                        </div>
                    @empty
                        <div class="text-center py-10">
                            <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">No recent data</p>
                        </div>
                    @endforelse
                </div>
                <div class="mt-10 pt-6 border-t border-slate-50">
                    <a href="{{ route('payments.index') }}" class="text-[10px] font-black text-[#0055a4] uppercase tracking-widest hover:underline flex items-center justify-center gap-2">
                        View All Transactions <i data-lucide="chevron-right" class="w-3 h-3"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        window.__chartData = {
            values: @json(array_values($monthlyRevenue)),
            labels: @json(array_keys($monthlyRevenue))
        };
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var chartData = window.__chartData || { values: [], labels: [] };
            var options = {
                series: [{
                    name: 'Revenue',
                    data: chartData.values
                }],
                chart: {
                    height: 320,
                    type: 'area',
                    toolbar: { show: false },
                    fontFamily: 'Roboto, sans-serif'
                },
                colors: ['#0055a4'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.4,
                        opacityTo: 0.05,
                        stops: [0, 90, 100]
                    }
                },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 3 },
                xaxis: {
                    categories: chartData.labels,
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: { show: false },
                grid: { borderColor: '#f8fafc' },
                tooltip: { theme: 'light' }
            };

            var chart = new ApexCharts(document.querySelector("#revenueChart"), options);
            chart.render();
        });
    </script>
    @endpush
</x-app-layout>
