<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index()
    {
        $products = Product::latest()->paginate(10);
        $totalProducts = Product::count();
        $activeProducts = Product::where('is_active', true)->count();
        $lowStockProducts = Product::where('is_active', true)->whereColumn('stock', '<=', 'min_stock')->where('stock', '>', 0)->count();
        $outOfStockProducts = Product::where('is_active', true)->where('stock', '<=', 0)->count();

        return view('products.index', compact('products', 'totalProducts', 'activeProducts', 'lowStockProducts', 'outOfStockProducts'));
    }

    /**
     * Print product catalog.
     */
    public function printCatalog(Request $request)
    {
        $query = Product::where('is_active', true);
        
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $products = $query->orderBy('category', 'asc')->orderBy('name', 'asc')->get();

        return view('products.catalog', compact('products'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $categories = \App\Models\ProductCategory::orderBy('name')->pluck('name');
        $units = \App\Models\ProductUnit::orderBy('name')->pluck('name');
        return view('products.create', compact('categories', 'units'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        // Format selling_price
        if ($request->has('selling_price')) {
            $request->merge([
                'selling_price' => str_replace('.', '', $request->selling_price)
            ]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:products,code|max:50',
            'category' => 'required|string|max:100',
            'selling_price' => 'required|numeric|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'unit' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
        ]);

        $data = $request->all();

        // Upload image
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $data['image'] = $imagePath;
        }

        // Set default values for hidden/removed fields
        $data['purchase_price'] = 0;
        $data['discount'] = 0;
        $data['stock'] = 0;
        $data['brand'] = null;
        $data['min_stock'] = $data['min_stock'] ?? 0;
        $data['is_active'] = $request->has('is_active') ? true : false;

        // Save dynamic category & unit
        if (!empty($data['category'])) {
            \App\Models\ProductCategory::firstOrCreate(['name' => $data['category']]);
        }
        if (!empty($data['unit'])) {
            \App\Models\ProductUnit::firstOrCreate(['name' => $data['unit']]);
        }

        Product::create($data);

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan!');
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        $product->load(['purchaseItems.purchase.supplier', 'saleItems.sale']);
        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        $categories = \App\Models\ProductCategory::orderBy('name')->pluck('name');
        $units = \App\Models\ProductUnit::orderBy('name')->pluck('name');
        return view('products.edit', compact('product', 'categories', 'units'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        // Format selling_price
        if ($request->has('selling_price')) {
            $request->merge([
                'selling_price' => str_replace('.', '', $request->selling_price)
            ]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:products,code,' . $product->id . '|max:50',
            'category' => 'required|string|max:100',
            'selling_price' => 'required|numeric|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'unit' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
        ]);

        $data = $request->all();

        // Upload image
        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $imagePath = $request->file('image')->store('products', 'public');
            $data['image'] = $imagePath;
        }

        // Set default values (do not touch stock in update so we don't overwrite current stock, though it's protected by mass assignment if we unset it, but actually the model has fillable stock. Let's just unset it from data just in case)
        unset($data['stock']); 
        unset($data['selling_price']);
        unset($data['purchase_price']);
        $data['min_stock'] = $data['min_stock'] ?? 0;
        $data['is_active'] = $request->has('is_active') ? true : false;

        // Save dynamic category & unit
        if (!empty($data['category'])) {
            \App\Models\ProductCategory::firstOrCreate(['name' => $data['category']]);
        }
        if (!empty($data['unit'])) {
            \App\Models\ProductUnit::firstOrCreate(['name' => $data['unit']]);
        }

        $product->update($data);

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil diupdate!');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        // Delete image
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil dihapus!');
    }
}
