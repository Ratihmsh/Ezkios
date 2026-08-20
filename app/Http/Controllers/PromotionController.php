<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use App\Models\Product;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function index()
    {
        $promotions = Promotion::with('product')->orderBy('created_at', 'desc')->get();
        return view('promotions.index', compact('promotions'));
    }

    public function create()
    {
        $products = Product::orderBy('name')->get();
        $categories = Product::whereNotNull('category')->select('category')->distinct()->pluck('category');
        $paymentMethods = PaymentMethod::all()->pluck('name')->toArray();
        if(empty($paymentMethods)) {
            $paymentMethods = ['Tunai', 'Transfer Bank', 'QRIS'];
        }
        return view('promotions.create', compact('products', 'categories', 'paymentMethods'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:product_discount,product_markup,transaction_discount,buy_x_get_y,category_discount',
            'product_id' => 'nullable|exists:products,id|required_if:type,product_discount,product_markup,buy_x_get_y',
            'reward_product_id' => 'nullable|exists:products,id|required_if:type,buy_x_get_y',
            'reward_qty' => 'nullable|integer|min:1|required_if:type,buy_x_get_y',
            'category_name' => 'nullable|string|required_if:type,category_discount',
            'promo_code' => 'nullable|string|unique:promotions,promo_code',
            'usage_limit' => 'nullable|integer|min:1',
            'payment_method' => 'nullable|string',
            'min_qty' => 'required|integer|min:1',
            'min_spend' => 'required|numeric|min:0',
            'value_type' => 'required|in:percentage,fixed_amount',
            'value' => 'required|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $finalPaymentMethod = $request->payment_method;
        if ($finalPaymentMethod === 'Lainnya_Baru' && $request->filled('payment_method_new')) {
            $finalPaymentMethod = $request->payment_method_new;
            PaymentMethod::firstOrCreate(['name' => $finalPaymentMethod]);
        }
        
        $data = $request->all();
        $data['payment_method'] = $finalPaymentMethod ?: null;

        Promotion::create($data);

        return redirect()->route('promotions.index')->with('success', 'Promosi berhasil ditambahkan.');
    }

    public function show(Promotion $promotion)
    {
        $promotion->load(['product', 'rewardProduct', 'sales.createdBy']);
        return view('promotions.show', compact('promotion'));
    }

    public function edit(Promotion $promotion)
    {
        $products = Product::orderBy('name')->get();
        $categories = Product::whereNotNull('category')->select('category')->distinct()->pluck('category');
        $paymentMethods = PaymentMethod::all()->pluck('name')->toArray();
        if(empty($paymentMethods)) {
            $paymentMethods = ['Tunai', 'Transfer Bank', 'QRIS'];
        }
        return view('promotions.edit', compact('promotion', 'products', 'categories', 'paymentMethods'));
    }

    public function update(Request $request, Promotion $promotion)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:product_discount,product_markup,transaction_discount,buy_x_get_y,category_discount',
            'product_id' => 'nullable|exists:products,id|required_if:type,product_discount,product_markup,buy_x_get_y',
            'reward_product_id' => 'nullable|exists:products,id|required_if:type,buy_x_get_y',
            'reward_qty' => 'nullable|integer|min:1|required_if:type,buy_x_get_y',
            'category_name' => 'nullable|string|required_if:type,category_discount',
            'promo_code' => 'nullable|string|unique:promotions,promo_code,'.$promotion->id,
            'usage_limit' => 'nullable|integer|min:1',
            'payment_method' => 'nullable|string',
            'min_qty' => 'required|integer|min:1',
            'min_spend' => 'required|numeric|min:0',
            'value_type' => 'required|in:percentage,fixed_amount',
            'value' => 'required|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $finalPaymentMethod = $request->payment_method;
        if ($finalPaymentMethod === 'Lainnya_Baru' && $request->filled('payment_method_new')) {
            $finalPaymentMethod = $request->payment_method_new;
            PaymentMethod::firstOrCreate(['name' => $finalPaymentMethod]);
        }
        
        $data = $request->all();
        $data['payment_method'] = $finalPaymentMethod ?: null;

        $promotion->update($data);

        return redirect()->route('promotions.index')->with('success', 'Promosi berhasil diperbarui.');
    }

    public function destroy(Promotion $promotion)
    {
        $promotion->delete();
        return redirect()->route('promotions.index')->with('success', 'Promosi berhasil dihapus.');
    }

    public function toggleActive($id)
    {
        $promo = Promotion::findOrFail($id);
        $promo->is_active = !$promo->is_active;
        $promo->save();
        return back()->with('success', 'Status promosi berhasil diubah.');
    }
}
