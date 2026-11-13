<?php

namespace App\Providers;

use App\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
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

        // Get all permissions from the database and create gates dynamically
        if (Schema::hasTable('permissions')) {
            Permission::all()->each(function ($permission) {
                Gate::define($permission->permission_name, function ($user) use ($permission) {
                    return $user->usertype->hasPermission($permission->permission_name) || $user->usertype->name === 'SuperAdmin';
                });
            });
        }
    }
}
