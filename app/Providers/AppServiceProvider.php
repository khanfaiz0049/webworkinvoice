<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            if (\Illuminate\Support\Facades\Auth::check()) {
                $companies = \App\Models\Company::all();
                $activeCompany = null;
                
                $activeCompanyId = session('active_company_id') ?: \Illuminate\Support\Facades\Auth::user()->active_company_id;
                
                if ($activeCompanyId) {
                    $activeCompany = \App\Models\Company::find($activeCompanyId);
                }
                
                if (!$activeCompany && $companies->count() > 0) {
                    $activeCompany = $companies->first();
                }

                $view->with('companies', $companies);
                $view->with('activeCompany', $activeCompany);
            }
        });
    }
}
