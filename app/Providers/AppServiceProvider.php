<?php

namespace App\Providers;

use App\Models\Notification;
use App\Observers\NotificationObserver;
use App\Pagination\CustomPagination;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            \Illuminate\Pagination\LengthAwarePaginator::class,
            \App\Pagination\CustomPagination::class
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Cashier::useCustomerModel(\App\Models\WebUser::class);
        Notification::observe(NotificationObserver::class);
    }
}
