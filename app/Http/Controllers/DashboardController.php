<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\CashFlow;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Dashboard utama - SAMA UNTUK SEMUA ROLE
     * Tapi data disesuaikan berdasarkan permission
     */
    public function index()
    {
        $user = Auth::user();

        // Data dasar yang bisa dilihat semua role
        $data = [
            'user' => $user,
        ];

        // Ambil News Events yang aktif dan valid sesuai tanggal
        $data['newsEvents'] = \App\Models\NewsEvent::where('is_active', true)
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->latest()
            ->get();

        if ($user->hasPermission('view_sales')) {
            $data['unpaidSales'] = Sale::whereIn('payment_status', ['pending', 'partial'])
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<=', today())
                ->latest()->take(5)->get();
        }

        if ($user->hasPermission('view_purchases')) {
            $data['unpaidPurchases'] = Purchase::whereIn('payment_status', ['pending', 'partial'])
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<=', today())
                ->latest()->take(5)->get();
        }

        if ($user->hasPermission('view_products')) {
            $data['lowStockProducts'] = Product::where('is_active', true)->whereColumn('stock', '<=', 'min_stock')->take(5)->get();
        }

        return view('dashboard', $data);
    }

    /**
     * Dashboard untuk Owner (sama dengan index)
     */
    public function ownerDashboard()
    {
        return $this->index();
    }

    /**
     * Dashboard untuk Admin (sama dengan index)
     */
    public function adminDashboard()
    {
        return $this->index();
    }

    /**
     * Dashboard untuk Kasir (sama dengan index)
     */
    public function kasirDashboard()
    {
        return $this->index();
    }
}
