<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // ========== DEFINE GATES ==========

        // Dashboard
        Gate::define('view_dashboard', function (User $user) {
            return $user->hasPermission('view_dashboard');
        });

        // Products
        Gate::define('view_products', function (User $user) {
            return $user->hasPermission('view_products');
        });
        Gate::define('create_products', function (User $user) {
            return $user->hasPermission('create_products');
        });
        Gate::define('edit_products', function (User $user) {
            return $user->hasPermission('edit_products');
        });
        Gate::define('delete_products', function (User $user) {
            return $user->hasPermission('delete_products');
        });

        // Suppliers
        Gate::define('view_suppliers', function (User $user) {
            return $user->hasPermission('view_suppliers');
        });
        Gate::define('create_suppliers', function (User $user) {
            return $user->hasPermission('create_suppliers');
        });
        Gate::define('edit_suppliers', function (User $user) {
            return $user->hasPermission('edit_suppliers');
        });
        Gate::define('delete_suppliers', function (User $user) {
            return $user->hasPermission('delete_suppliers');
        });

        // Purchases
        Gate::define('view_purchases', function (User $user) {
            return $user->hasPermission('view_purchases');
        });
        Gate::define('create_purchases', function (User $user) {
            return $user->hasPermission('create_purchases');
        });
        Gate::define('edit_purchases', function (User $user) {
            return $user->hasPermission('edit_purchases');
        });
        Gate::define('delete_purchases', function (User $user) {
            return $user->hasPermission('delete_purchases');
        });
        Gate::define('print_purchases', function (User $user) {
            return $user->hasPermission('print_purchases');
        });

        // Sales
        Gate::define('view_sales', function (User $user) {
            return $user->hasPermission('view_sales');
        });
        Gate::define('create_sales', function (User $user) {
            return $user->hasPermission('create_sales');
        });
        Gate::define('edit_sales', function (User $user) {
            return $user->hasPermission('edit_sales');
        });
        Gate::define('delete_sales', function (User $user) {
            return $user->hasPermission('delete_sales');
        });
        Gate::define('print_sales', function (User $user) {
            return $user->hasPermission('print_sales');
        });

        // Cash Flow
        Gate::define('view_cash_flow', function (User $user) {
            return $user->hasPermission('view_cash_flow');
        });
        Gate::define('create_cash_flow', function (User $user) {
            return $user->hasPermission('create_cash_flow');
        });
        Gate::define('edit_cash_flow', function (User $user) {
            return $user->hasPermission('edit_cash_flow');
        });
        Gate::define('delete_cash_flow', function (User $user) {
            return $user->hasPermission('delete_cash_flow');
        });

        // Reports
        Gate::define('view_reports', function (User $user) {
            return $user->hasPermission('view_reports');
        });

        // User Management (Hanya Owner)
        Gate::define('manage_users', function (User $user) {
            return $user->hasPermission('manage_users');
        });
        Gate::define('manage_roles', function (User $user) {
            return $user->hasPermission('manage_roles');
        });
        Gate::define('manage_permissions', function (User $user) {
            return $user->hasPermission('manage_permissions');
        });

        // ========== SUPER ADMIN (Owner) ==========
        // Owner dapat melakukan semua aksi
        Gate::before(function (User $user, $ability) {
            if ($user->hasRole('owner')) {
                return true;
            }
        });
    }
}
