<?php

namespace App\Providers;

use App\Repositories\Eloquent\MedicineOutgoingRepositoryImpl;
use App\Repositories\MedicineOutgoingRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            MedicineOutgoingRepository::class,
            MedicineOutgoingRepositoryImpl::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
