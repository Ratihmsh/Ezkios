<?php

namespace App\Http\Controllers;

use App\Models\CashFlow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CashFlowController extends Controller
{
    /**
     * Display a listing of cash flows.
     */
    public function index(Request $request)
    {
        $query = CashFlow::with('createdBy');

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        $cashFlows = $query->latest()->paginate(10);

        // Summary (menghitung berdasarkan filter yang aktif)
        $totalIncome = (clone $query)->where('type', 'income')->sum('amount');
        $totalExpense = (clone $query)->where('type', 'expense')->sum('amount');
        $balance = $totalIncome - $totalExpense;

        // Categories for filter (merge from master table and existing transactions)
        $masterCategories = \App\Models\CashFlowCategory::pluck('name');
        $existingCategories = CashFlow::distinct()->pluck('category');
        $categories = $masterCategories->merge($existingCategories)->unique()->sort();

        return view('cash-flow.index', compact('cashFlows', 'totalIncome', 'totalExpense', 'balance', 'categories'));
    }

    /**
     * Show the form for creating a new cash flow.
     */
    public function create(Request $request)
    {
        // Default value untuk modal awal
        $defaults = [
            'type' => $request->query('type', 'income'),
            'category' => $request->query('category', 'Modal'),
            'transaction_date' => date('Y-m-d'),
            'payment_method' => 'Cash',
        ];

        $incomeCategories = \App\Models\CashFlowCategory::where('type', 'income')->orderBy('name')->get();
        $expenseCategories = \App\Models\CashFlowCategory::where('type', 'expense')->orderBy('name')->get();
        $paymentMethods = \App\Models\PaymentMethod::orderBy('name')->get();

        return view('cash-flow.create', compact('defaults', 'incomeCategories', 'expenseCategories', 'paymentMethods'));
    }

    /**
     * Store a newly created cash flow in storage.
     */
    public function store(Request $request)
    {
        // Hapus format ribuan dari amount (misal 1.000.000 menjadi 1000000)
        $request->merge([
            'amount' => str_replace('.', '', $request->amount)
        ]);

        $request->validate([
            'type' => 'required|in:income,expense',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string',
            'transaction_date' => 'required|date',
            'payment_method' => 'required|string|max:100',
            'fund_source' => 'required|in:modal,laba',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $data = $request->all();

        // Handle attachment
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('cash-flow-attachments', 'public');
            $data['attachment'] = $path;
        }

        $data['created_by'] = Auth::id();

        // Cek dan simpan kategori master jika belum ada
        \App\Models\CashFlowCategory::firstOrCreate([
            'name' => $data['category'],
            'type' => $data['type']
        ]);

        // Cek dan simpan metode pembayaran master jika belum ada
        \App\Models\PaymentMethod::firstOrCreate([
            'name' => $data['payment_method']
        ]);

        CashFlow::create($data);

        return redirect()->route('cash-flow.index')
            ->with('success', 'Transaksi kas berhasil ditambahkan!');
    }

    /**
     * Display the specified cash flow.
     */
    public function show(CashFlow $cashFlow)
    {
        $cashFlow->load('createdBy');
        return view('cash-flow.show', compact('cashFlow'));
    }

    /**
     * Show the form for editing the specified cash flow.
     */
    public function edit(CashFlow $cashFlow)
    {
        $incomeCategories = \App\Models\CashFlowCategory::where('type', 'income')->orderBy('name')->get();
        $expenseCategories = \App\Models\CashFlowCategory::where('type', 'expense')->orderBy('name')->get();
        $paymentMethods = \App\Models\PaymentMethod::orderBy('name')->get();
        
        return view('cash-flow.edit', compact('cashFlow', 'incomeCategories', 'expenseCategories', 'paymentMethods'));
    }

    /**
     * Update the specified cash flow in storage.
     */
    public function update(Request $request, CashFlow $cashFlow)
    {
        // Prevent editing automatically generated cash flows
        if ($cashFlow->reference_id && $cashFlow->reference_type) {
            return redirect()->route('cash-flow.index')
                ->with('error', 'Transaksi otomatis dari sistem tidak dapat diedit secara manual.');
        }

        // Hapus format ribuan dari amount
        if ($request->has('amount')) {
            $request->merge([
                'amount' => str_replace('.', '', $request->amount)
            ]);
        }

        $request->validate([
            'type' => 'required|in:income,expense',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string',
            'transaction_date' => 'required|date',
            'payment_method' => 'required|string|max:100',
            'fund_source' => 'required|in:modal,laba',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $data = $request->all();

        // Handle attachment
        if ($request->hasFile('attachment')) {
            // Delete old attachment
            if ($cashFlow->attachment) {
                Storage::disk('public')->delete($cashFlow->attachment);
            }
            $path = $request->file('attachment')->store('cash-flow-attachments', 'public');
            $data['attachment'] = $path;
        }

        $data['updated_by'] = Auth::id();

        // Cek dan simpan kategori master jika belum ada
        \App\Models\CashFlowCategory::firstOrCreate([
            'name' => $data['category'],
            'type' => $data['type']
        ]);

        // Cek dan simpan metode pembayaran master jika belum ada
        \App\Models\PaymentMethod::firstOrCreate([
            'name' => $data['payment_method']
        ]);

        $cashFlow->update($data);

        return redirect()->route('cash-flow.index')
            ->with('success', 'Transaksi kas berhasil diupdate!');
    }

    /**
     * Remove the specified cash flow from storage.
     */
    public function destroy(CashFlow $cashFlow)
    {
        // Delete attachment
        if ($cashFlow->attachment) {
            Storage::disk('public')->delete($cashFlow->attachment);
        }

        $cashFlow->delete();

        return redirect()->route('cash-flow.index')
            ->with('success', 'Transaksi kas berhasil dihapus!');
    }

    /**
     * Export cash flow report (optional).
     */
    public function export(Request $request)
    {
        // This is a placeholder for export functionality
        return redirect()->route('cash-flow.index')
            ->with('info', 'Fitur export sedang dalam pengembangan.');
    }
}
