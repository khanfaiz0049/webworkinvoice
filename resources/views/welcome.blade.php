<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>WebWork Invoice - Multi-Company Billing Solution</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
        <noscript>
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        </noscript>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            body { font-family: 'Inter', sans-serif; }
            .bg-grid {
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Cpath d='M10 0 L10 100 M0 10 L100 10' stroke='%23e2e8f0' stroke-width='0.5' fill='none'/%3E%3C/svg%3E");
            }
        </style>
    </head>
    <body class="antialiased bg-white text-slate-900 min-h-screen overflow-x-hidden">
        <div class="fixed inset-0 bg-grid pointer-events-none opacity-40"></div>
        <div class="fixed inset-0 bg-gradient-to-tr from-blue-50/50 via-transparent to-red-50/30 pointer-events-none"></div>

        <nav class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex items-center justify-between border-b border-slate-100 bg-white/80 backdrop-blur-md sticky top-0">
            <div class="flex items-center">
                <div class="flex items-center overflow-hidden rounded-lg font-black text-2xl tracking-tighter shadow-sm border border-slate-200">
                    <div class="bg-[#0055a4] text-white px-3 py-1 pr-4" style="clip-path: polygon(0 0, 100% 0, 85% 100%, 0% 100%);">web</div>
                    <div class="bg-[#d32d27] text-white px-3 py-1 pl-4 -ml-3">work</div>
                </div>
            </div>

            <div class="flex items-center gap-6">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="bg-[#0055a4] hover:bg-[#00448a] text-white font-semibold py-2 px-6 rounded-lg transition-all duration-200 shadow-md active:scale-95">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-[#0055a4] hover:text-[#d32d27] transition-colors uppercase tracking-wider">Log in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="bg-[#d32d27] hover:bg-[#b21f24] text-white font-bold py-2.5 px-8 rounded-lg transition-all duration-200 shadow-lg shadow-red-500/20 active:scale-95 uppercase tracking-widest text-xs">Get Started</a>
                        @endif
                    @endauth
                @endif
            </div>
        </nav>

        <main class="relative z-10 pt-24 pb-32">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-slate-50 border border-slate-200 text-slate-600 text-xs font-bold mb-10 animate-fade-in uppercase tracking-widest">
                    <span class="flex h-2 w-2 rounded-full bg-[#d32d27]"></span>
                    Enterprise Billing Solution
                </div>

                <h1 class="text-6xl md:text-8xl font-black tracking-tight mb-8 text-slate-900 leading-[0.95]">
                    Fast. Reliable. <br class="hidden md:block"> <span class="text-[#0055a4]">Multi-Company</span> Billing.
                </h1>

                <p class="max-w-3xl mx-auto text-xl md:text-2xl text-slate-500 mb-12 font-medium leading-relaxed">
                    Streamline your accounting with GST-compliant invoicing, automated renewals, and professional management tools designed for modern teams.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-6 mb-24">
                    <a href="{{ route('register') }}" class="bg-[#0055a4] hover:bg-[#00448a] text-white font-black py-5 px-12 rounded-2xl transition-all duration-200 shadow-2xl shadow-blue-500/30 active:scale-95 text-xl flex items-center gap-3 uppercase tracking-tighter">
                        Create Account <i data-lucide="arrow-right" class="w-6 h-6"></i>
                    </a>
                    <a href="#features" class="px-10 py-5 rounded-2xl border-2 border-slate-200 hover:border-[#d32d27] hover:bg-red-50 transition-all font-bold text-slate-600 hover:text-[#d32d27] flex items-center gap-2 uppercase tracking-widest text-sm">
                        Watch Demo <i data-lucide="play-circle" class="w-5 h-5"></i>
                    </a>
                </div>

                <!-- Product Preview -->
                <div class="max-w-6xl mx-auto bg-slate-100 p-3 rounded-[2.5rem] shadow-2xl border border-slate-200 animate-fade-in-up">
                    <div class="bg-white rounded-[2rem] overflow-hidden aspect-[16/9] relative border border-slate-200 flex items-center justify-center">
                         <div class="absolute inset-0 bg-gradient-to-br from-[#0055a4]/5 to-[#d32d27]/5"></div>
                         <i data-lucide="layout-dashboard" class="w-24 h-24 text-slate-200"></i>
                         <div class="absolute bottom-12 left-12 text-left">
                            <div class="flex gap-2 mb-4">
                                <div class="w-3 h-3 rounded-full bg-[#d32d27]"></div>
                                <div class="w-3 h-3 rounded-full bg-[#0055a4]"></div>
                                <div class="w-3 h-3 rounded-full bg-slate-300"></div>
                            </div>
                            <p class="text-slate-900 text-3xl font-black uppercase tracking-tighter italic">Live Dashboard Preview</p>
                         </div>
                    </div>
                </div>
            </div>
        </main>

        <footer class="relative z-10 border-t border-slate-100 py-16 bg-slate-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-12">
                <div class="flex items-center overflow-hidden rounded-lg font-black text-xl tracking-tighter border border-slate-200 grayscale opacity-50 hover:grayscale-0 hover:opacity-100 transition-all">
                    <div class="bg-[#0055a4] text-white px-2 py-0.5 pr-3" style="clip-path: polygon(0 0, 100% 0, 85% 100%, 0% 100%);">web</div>
                    <div class="bg-[#d32d27] text-white px-2 py-0.5 pl-3 -ml-2">work</div>
                </div>
                <p class="text-sm text-slate-400 font-bold uppercase tracking-widest">&copy; {{ date('Y') }} WebWork Invoice. All rights reserved.</p>
                <div class="flex gap-8">
                    <a href="#" class="text-slate-400 hover:text-[#0055a4] transition-colors" aria-label="Twitter">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"></path></svg>
                    </a>
                    <a href="#" class="text-slate-400 hover:text-[#d32d27] transition-colors" aria-label="GitHub">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><path d="M15 22v-4a4.8 4.8 0 0 0-1-3.5c3 0 6-2 6-5.5.08-1.25-.27-2.48-1-3.5.28-1.15.28-2.35 0-3.5 0 0-1 0-3 1.5-2.64-.5-5.36-.5-8 0C6 2 5 2 5 2c-.3 1.15-.3 2.35 0 3.5A5.403 5.403 0 0 0 4 9c0 3.5 3 5.5 6 5.5-.39.49-.68 1.05-.85 1.65-.17.6-.22 1.23-.15 1.85v4"></path><path d="M9 18c-4.51 2-5-2-7-2"></path></svg>
                    </a>
                </div>
            </div>
        </footer>
    </body>
</html>
