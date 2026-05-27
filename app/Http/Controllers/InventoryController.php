<?php

namespace App\Http\Controllers;

use App\Models\InventoryCategory;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->paginate(20);
        $categories = InventoryCategory::all();
        return view('inventory.index', compact('products', 'categories'));
    }

    public function categories()
    {
        $categories = InventoryCategory::all();
        return view('inventory.categories', compact('categories'));
    }

    public function stockMovements()
    {
        $movements = StockMovement::with('product')->latest()->paginate(20);
        return view('inventory.stock-movements', compact('movements'));
    }

    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'sku' => 'required|string|unique:products',
            'inventory_category_id' => 'nullable|exists:inventory_categories,id',
            'purchase_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'stock_quantity' => 'required|integer',
            'min_stock_level' => 'required|integer',
            'unit' => 'required|string',
            'description' => 'nullable|string'
        ]);

        Product::create($validated);
        return redirect()->back()->with('success', 'Product created successfully');
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string'
        ]);

        InventoryCategory::create($validated);
        return redirect()->back()->with('success', 'Category created successfully');
    }

    public function storeStockMovement(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|integer',
            'unit_cost' => 'nullable|numeric',
            'reference' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        StockMovement::create($validated);

        // Update product stock
        $product = Product::find($validated['product_id']);
        if ($validated['type'] === 'in' || $validated['type'] === 'adjustment') {
            $product->stock_quantity += $validated['quantity'];
        } else {
            $product->stock_quantity -= $validated['quantity'];
        }
        $product->save();

        return redirect()->back()->with('success', 'Stock movement recorded successfully');
    }
}
