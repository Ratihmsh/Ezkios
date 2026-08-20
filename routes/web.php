<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\CashFlowController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\SettlementController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// ========== LANGUAGE TOGGLE ==========
Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'id'])) {
        session()->put('locale', $locale);
    }
    return redirect()->back();
})->name('lang.switch');

// ========== AUTH ==========
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ========== PROTECTED ROUTES ==========
Route::middleware('auth')->group(function () {

    // Dashboard default (redirect berdasarkan role)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ========== DASHBOARD KHUSUS ROLE ==========
    Route::get('/owner/dashboard', function () {
        return redirect()->route('dashboard');
    })->name('owner.dashboard');

    Route::get('/admin/dashboard', function () {
        return redirect()->route('dashboard');
    })->name('admin.dashboard');

    Route::get('/kasir/dashboard', function () {
        return redirect()->route('dashboard');
    })->name('kasir.dashboard');

    // ========== PRODUCTS ==========
    Route::middleware('permission:create_products')->group(function () {
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    });
    Route::middleware('permission:view_products')->group(function () {
        Route::get('/products/catalog', [ProductController::class, 'printCatalog'])->name('products.catalog');
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
    });
    Route::middleware('permission:edit_products')->group(function () {
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    });
    // ========== SUPPLIERS ==========
    Route::middleware('permission:create_suppliers')->group(function () {
        Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
        Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    });
    Route::middleware('permission:view_suppliers')->group(function () {
        Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
    });
    Route::middleware('permission:edit_suppliers')->group(function () {
        Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
        Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
    });
    // ========== PURCHASES ==========
    Route::middleware('permission:create_purchases')->group(function () {
        Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
        Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');

        // AJAX endpoints for creating supplier/product on the fly
        Route::post('/purchases/ajax/supplier', [PurchaseController::class, 'storeSupplierAjax'])->name('purchases.ajax.supplier');
        Route::post('/purchases/ajax/product', [PurchaseController::class, 'storeProductAjax'])->name('purchases.ajax.product');
    });
    Route::middleware('permission:view_purchases')->group(function () {
        Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
        Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
    });
    Route::middleware('permission:settle_purchases')->group(function () {
        Route::post('/purchases/{purchase}/payments', [PurchaseController::class, 'addPayment'])->name('purchases.payments.store');
    });
    Route::middleware('permission:print_purchases')->group(function () {
        Route::get('/purchases/{purchase}/print', [PurchaseController::class, 'print'])->name('purchases.print');
    });

    // ========== SALES ==========
    Route::middleware('permission:create_sales')->group(function () {
        Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
        Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
    });
    Route::middleware('permission:view_sales')->group(function () {
        Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
        Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    });
    Route::middleware('permission:settle_sales')->group(function () {
        Route::post('/sales/{sale}/payments', [SaleController::class, 'addPayment'])->name('sales.payments.store');
    });
    Route::middleware('permission:print_sales')->group(function () {
        Route::get('/sales/{sale}/print', [SaleController::class, 'print'])->name('sales.print');
    });

    // ========== CASH FLOW ==========
    Route::middleware('permission:create_cash_transactions')->group(function () {
        Route::get('/cash-flow/create', [CashFlowController::class, 'create'])->name('cash-flow.create');
        Route::post('/cash-flow', [CashFlowController::class, 'store'])->name('cash-flow.store');
    });
    Route::middleware('permission:view_reports')->group(function () {
        Route::get('/cash-flow', [CashFlowController::class, 'index'])->name('cash-flow.index');
        Route::get('/cash-flow/{cashFlow}', [CashFlowController::class, 'show'])->name('cash-flow.show');
    });

    //========== REPORTS ==========
    Route::middleware('permission:view_reports')->group(function () {
        Route::get('/reports', [ReportController::class, 'profitLoss'])->name('reports.index');
    });

    // ========== STOCK ADJUSTMENTS & SETTLEMENTS ==========
    Route::middleware('permission:adjust_stock')->group(function () {
        Route::get('/stock-adjustments', [StockAdjustmentController::class, 'index'])->name('stock-adjustments.index');
        Route::post('/stock-adjustments', [StockAdjustmentController::class, 'store'])->name('stock-adjustments.store');
    });

    Route::middleware('permission:close_cashier')->group(function () {
        Route::get('/settlements', [SettlementController::class, 'index'])->name('settlements.index');
        Route::post('/settlements', [SettlementController::class, 'store'])->name('settlements.store');
    });

    // ========== ROUTE TEST PERMISSION ==========
    // Route::get('/test-permission', function () {
    //     $user = auth()->user();

    //     if (!$user) {
    //         return 'Silakan login terlebih dahulu!';
    //     }

    //     return [
    //         'user' => $user->email,
    //         'role_id' => $user->role_id,
    //         'primary_role' => $user->role ? $user->role->name : null,
    //         'all_roles' => $user->roles()->pluck('name')->toArray(),
    //         'has_role_owner' => $user->hasRole('owner'),
    //         'has_role_admin' => $user->hasRole('admin'),
    //         'has_role_kasir' => $user->hasRole('kasir'),
    //         'has_permission_manage_users' => $user->hasPermission('manage_users'),
    //         'has_permission_view_products' => $user->hasPermission('view_products'),
    //     ];
    // });

    // ========== USER MANAGEMENT ==========
    Route::middleware('permission:view_users')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
    });
    Route::middleware('permission:create_users')->group(function () {
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
    });
    Route::middleware('permission:edit_users')->group(function () {
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    });

    // ========== NEWS EVENTS ==========
    Route::middleware('permission:view_news_events')->group(function () {
        Route::get('/news-events', [\App\Http\Controllers\NewsEventController::class, 'index'])->name('news-events.index');
        Route::get('/news-events/{news_event}', [\App\Http\Controllers\NewsEventController::class, 'show'])->name('news-events.show');
    });
    Route::middleware('permission:create_news_events')->group(function () {
        Route::get('/news-events/create', [\App\Http\Controllers\NewsEventController::class, 'create'])->name('news-events.create');
        Route::post('/news-events', [\App\Http\Controllers\NewsEventController::class, 'store'])->name('news-events.store');
    });
    Route::middleware('permission:edit_news_events')->group(function () {
        Route::get('/news-events/{news_event}/edit', [\App\Http\Controllers\NewsEventController::class, 'edit'])->name('news-events.edit');
        Route::put('/news-events/{news_event}', [\App\Http\Controllers\NewsEventController::class, 'update'])->name('news-events.update');
    });
    Route::middleware('permission:delete_news_events')->group(function () {
        Route::delete('/news-events/{news_event}', [\App\Http\Controllers\NewsEventController::class, 'destroy'])->name('news-events.destroy');
    });

    // ========== ACTIVITY LOGS ==========
    Route::middleware('permission:view_activity_logs')->group(function () {
        Route::get('/activity-logs', [\App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity-logs.index');
    });

    Route::middleware('permission:view_reports')->group(function () {
        Route::get('/reports', [App\Http\Controllers\ReportController::class, 'profitLoss'])->name('reports.index');
        Route::get('/reports/print', [App\Http\Controllers\ReportController::class, 'print'])->name('reports.print');
        Route::get('/reports/export-excel', [App\Http\Controllers\ReportController::class, 'exportExcel'])->name('reports.export-excel');
    });

    // ========== ROLE PERMISSION ==========
    Route::middleware('permission:view_roles')->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/{role}/permissions', [RoleController::class, 'permissions'])->name('roles.permissions');
    });
    Route::middleware('permission:create_roles')->group(function () {
        Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    });
    Route::middleware('permission:edit_roles')->group(function () {
        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::post('/roles/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('roles.update-permissions');
    });
    Route::middleware('permission:delete_roles')->group(function () {
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });

    // ========== PROMOTIONS ==========
    Route::middleware('permission:create_promotions')->group(function () {
        Route::get('/promotions/create', [\App\Http\Controllers\PromotionController::class, 'create'])->name('promotions.create');
        Route::post('/promotions', [\App\Http\Controllers\PromotionController::class, 'store'])->name('promotions.store');
    });
    Route::middleware('permission:view_promotions')->group(function () {
        Route::get('/promotions', [\App\Http\Controllers\PromotionController::class, 'index'])->name('promotions.index');
        Route::get('/promotions/{promotion}', [\App\Http\Controllers\PromotionController::class, 'show'])->name('promotions.show');
    });
    Route::middleware('permission:edit_promotions')->group(function () {
        Route::get('/promotions/{promotion}/edit', [\App\Http\Controllers\PromotionController::class, 'edit'])->name('promotions.edit');
        Route::put('/promotions/{promotion}', [\App\Http\Controllers\PromotionController::class, 'update'])->name('promotions.update');
        Route::patch('/promotions/{promotion}/toggle', [\App\Http\Controllers\PromotionController::class, 'toggleActive'])->name('promotions.toggle');
    });
    Route::middleware('permission:delete_promotions')->group(function () {
        Route::delete('/promotions/{promotion}', [\App\Http\Controllers\PromotionController::class, 'destroy'])->name('promotions.destroy');
    });
});

// ========== REDIRECT ROOT ==========
Route::get('/', function () {
    if (Auth::check()) {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->hasRole('owner')) {
            return redirect()->route('owner.dashboard');
        } elseif ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('kasir')) {
            return redirect()->route('kasir.dashboard');
        }
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});
