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
        $this->clearStaleViteHotFile();

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

    private function clearStaleViteHotFile(): void
    {
        $hotFile = public_path('hot');
        $manifestFile = public_path('build/manifest.json');

        if (! is_file($hotFile) || ! is_file($manifestFile)) {
            return;
        }

        $hotUrl = trim((string) @file_get_contents($hotFile));

        if ($hotUrl === '') {
            @unlink($hotFile);
            return;
        }

        $parts = parse_url($hotUrl);
        $host = $parts['host'] ?? null;
        $port = $parts['port'] ?? null;

        if (! $host || ! $port) {
            @unlink($hotFile);
            return;
        }

        $host = trim($host, '[]');
        $connection = @fsockopen($host, (int) $port, $errno, $errstr, 0.2);

        if (is_resource($connection)) {
            fclose($connection);
            return;
        }

        @unlink($hotFile);
    }
}
