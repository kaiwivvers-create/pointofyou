<?php

namespace App\Http\Controllers;

use App\Models\InventoryCategory;
use App\Models\MenuCategory;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\PurchaseExpenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryController extends Controller
{
    private function canEditInventory(): bool
    {
        $user = auth()->user();

        return $user && ($user->isSuperAdmin() || $user->isOwner() || $user->isAdmin());
    }

    public function index()
    {
        $products = Product::with('category')->paginate(20);
        $bulkProducts = Product::orderBy('name')->get(['id', 'name', 'stock_quantity', 'purchase_price']);
        $categories = InventoryCategory::where('type', 'ingredient')->orderBy('name')->get();
        return view('inventory.index', compact('products', 'categories', 'bulkProducts'));
    }

    public function supplies()
    {
        $products = Product::with('category')
            ->where('consume_on_takeout', true)
            ->paginate(20);
        $bulkProducts = Product::query()
            ->where('consume_on_takeout', true)
            ->orderBy('name')
            ->get(['id', 'name', 'stock_quantity', 'purchase_price']);
        $categories = InventoryCategory::where('type', 'supply')->orderBy('name')->get();

        return view('inventory.supplies', compact('products', 'categories', 'bulkProducts'));
    }

    public function categories()
    {
        $categories = MenuCategory::getOrdered();
        return view('inventory.categories', compact('categories'));
    }

    public function stockCategories()
    {
        $categories = InventoryCategory::query()
            ->withCount('products')
            ->orderBy('name')
            ->get();

        return view('inventory.stock-categories', compact('categories'));
    }

    public function stockMovements(Request $request)
    {
        $query = StockMovement::with('product');

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('product_type')) {
            $query->whereHas('product', function ($productQuery) use ($request) {
                if ($request->product_type === 'supply') {
                    $productQuery->where('consume_on_takeout', true);
                } elseif ($request->product_type === 'inventory') {
                    $productQuery->where('consume_on_takeout', false);
                }
            });
        }

        $sortBy = $request->string('sort_by', 'date')->toString();
        $sortDirection = $request->string('sort_direction', 'desc')->toString() === 'asc' ? 'asc' : 'desc';

        if ($sortBy === 'quantity') {
            $query->orderBy('quantity', $sortDirection);
        } elseif ($sortBy === 'amount') {
            $query->orderByRaw('(quantity * coalesce(unit_cost, 0)) ' . $sortDirection);
        } else {
            $query->orderBy('created_at', $sortDirection);
        }

        $movements = $query->paginate(20)->withQueryString();
        return view('inventory.stock-movements', compact('movements'));
    }

    public function bulkPurchaseHistory(Request $request)
    {
        $query = StockMovement::with('product')
            ->where('source', 'bulk_purchase')
            ->where('type', 'in');

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('product_type')) {
            $query->whereHas('product', function ($productQuery) use ($request) {
                if ($request->product_type === 'supply') {
                    $productQuery->where('consume_on_takeout', true);
                } elseif ($request->product_type === 'inventory') {
                    $productQuery->where('consume_on_takeout', false);
                }
            });
        }

        $sortBy = $request->string('sort_by', 'date')->toString();
        $sortDirection = $request->string('sort_direction', 'desc')->toString() === 'asc' ? 'asc' : 'desc';

        if ($sortBy === 'quantity') {
            $query->orderBy('quantity', $sortDirection);
        } elseif ($sortBy === 'amount') {
            $query->orderByRaw('(quantity * coalesce(unit_cost, 0)) ' . $sortDirection);
        } else {
            $query->orderBy('created_at', $sortDirection);
        }

        $movements = $query->paginate(20)->withQueryString();

        return view('inventory.bulk-purchases', compact('movements'));
    }

    public function exportStockMovements(Request $request): StreamedResponse
    {
        $query = StockMovement::with('product');

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('product_type')) {
            $query->whereHas('product', function ($productQuery) use ($request) {
                if ($request->product_type === 'supply') {
                    $productQuery->where('consume_on_takeout', true);
                } elseif ($request->product_type === 'inventory') {
                    $productQuery->where('consume_on_takeout', false);
                }
            });
        }

        $movements = $query->latest()->get();

        return response()->streamDownload(function () use ($movements) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Product', 'Type', 'Quantity', 'Unit Cost', 'Total', 'Source', 'Reference', 'Notes']);

            foreach ($movements as $movement) {
                fputcsv($file, [
                    $movement->created_at?->format('Y-m-d H:i:s'),
                    $movement->product?->name,
                    $movement->type,
                    $movement->quantity,
                    $movement->unit_cost,
                    ($movement->unit_cost ?? 0) * $movement->quantity,
                    $movement->source,
                    $movement->reference,
                    $movement->notes,
                ]);
            }

            fclose($file);
        }, 'stock-movements-' . now()->format('Y-m-d') . '.csv');
    }

    public function exportBulkPurchases(Request $request): StreamedResponse
    {
        $query = StockMovement::with('product')
            ->where('source', 'bulk_purchase')
            ->where('type', 'in');

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('product_type')) {
            $query->whereHas('product', function ($productQuery) use ($request) {
                if ($request->product_type === 'supply') {
                    $productQuery->where('consume_on_takeout', true);
                } elseif ($request->product_type === 'inventory') {
                    $productQuery->where('consume_on_takeout', false);
                }
            });
        }

        $movements = $query->latest()->get();

        return response()->streamDownload(function () use ($movements) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Reference', 'Product', 'Quantity', 'Unit Cost', 'Total', 'Type', 'Notes']);

            foreach ($movements as $movement) {
                fputcsv($file, [
                    $movement->created_at?->format('Y-m-d H:i:s'),
                    $movement->reference ?? 'Bulk Purchase',
                    $movement->product?->name,
                    $movement->quantity,
                    $movement->unit_cost,
                    ($movement->unit_cost ?? 0) * $movement->quantity,
                    $movement->product?->consume_on_takeout ? 'Supply' : 'Inventory',
                    $movement->notes,
                ]);
            }

            fclose($file);
        }, 'bulk-purchases-' . now()->format('Y-m-d') . '.csv');
    }

    public function storeProduct(Request $request)
    {
        if (! $this->canEditInventory()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-.,\'@]+$/',
            'sku' => 'required|string|max:100|unique:products|regex:/^[a-zA-Z0-9\-_]+$/',
            'inventory_category_id' => 'nullable|exists:inventory_categories,id',
            'purchase_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'stock_quantity' => 'required|integer',
            'min_stock_level' => 'required|integer',
            'unit' => 'required|string|max:50|regex:/^[a-zA-Z0-9\s\-.,]+$/',
            'description' => 'nullable|string|max:5000|regex:/^[a-zA-Z0-9\s\-.,!?@]+$/',
            'consume_on_takeout' => 'boolean',
            'consume_per_item' => 'nullable|integer|min:1',
        ]);

        $validated['consume_on_takeout'] = $request->boolean('consume_on_takeout');
        $validated['consume_per_item'] = $request->integer('consume_per_item', 1);

        Product::create($validated);
        return redirect()->back()->with('success', 'Product created successfully');
    }

    public function updateProduct(Request $request, Product $product)
    {
        if (! $this->canEditInventory()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-.,\'@]+$/',
            'sku' => 'required|string|max:100|unique:products,sku,' . $product->id . '|regex:/^[a-zA-Z0-9\-_]+$/',
            'inventory_category_id' => 'nullable|exists:inventory_categories,id',
            'purchase_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'stock_quantity' => 'required|integer',
            'min_stock_level' => 'required|integer',
            'unit' => 'required|string|max:50|regex:/^[a-zA-Z0-9\s\-.,]+$/',
            'description' => 'nullable|string|max:5000|regex:/^[a-zA-Z0-9\s\-.,!?@]+$/',
            'consume_on_takeout' => 'sometimes|boolean',
            'consume_per_item' => 'nullable|integer|min:1',
        ]);

        $validated['consume_on_takeout'] = $request->has('consume_on_takeout')
            ? $request->boolean('consume_on_takeout')
            : $product->consume_on_takeout;
        $validated['consume_per_item'] = $request->filled('consume_per_item')
            ? $request->integer('consume_per_item', 1)
            : $product->consume_per_item;

        $product->update($validated);

        return redirect()->back()->with('success', 'Product updated successfully');
    }

    public function destroyProduct(Product $product)
    {
        if (! $this->canEditInventory()) {
            abort(403);
        }

        $product->delete();

        return redirect()->back()->with('success', 'Product deleted successfully');
    }

    public function storeCategory(Request $request)
    {
        if (! $this->canEditInventory()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-.,\'@]+$/',
            'description' => 'nullable|string|max:5000|regex:/^[a-zA-Z0-9\s\-.,!?@]+$/',
            'type' => 'required|in:ingredient,supply',
        ]);

        InventoryCategory::create($validated);
        return redirect()->back()->with('success', 'Category created successfully');
    }

    public function updateCategory(Request $request, InventoryCategory $category)
    {
        if (! $this->canEditInventory()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-.,\'@]+$/',
            'description' => 'nullable|string|max:5000|regex:/^[a-zA-Z0-9\s\-.,!?@]+$/',
            'type' => 'required|in:ingredient,supply',
        ]);

        $category->update($validated);

        return redirect()->back()->with('success', 'Category updated successfully');
    }

    public function destroyCategory(InventoryCategory $category)
    {
        if (! $this->canEditInventory()) {
            abort(403);
        }

        $category->delete();

        return redirect()->back()->with('success', 'Category deleted successfully');
    }

    public function storeStockMovement(Request $request)
    {
        if (! $this->canEditInventory()) {
            abort(403);
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|integer',
            'unit_cost' => 'nullable|numeric',
            'reference' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        $product = Product::find($validated['product_id']);
        if ($product) {
            StockMovement::create([
                'product_id' => $product->id,
                'type' => $validated['type'],
                'source' => 'manual',
                'quantity' => $validated['quantity'],
                'unit_cost' => $product->purchase_price,
                'reference' => $validated['reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);
        }

        // Update product stock
        if ($validated['type'] === 'in' || $validated['type'] === 'adjustment') {
            $product->stock_quantity += $validated['quantity'];
        } else {
            $product->stock_quantity -= $validated['quantity'];
        }
        $product->save();

        if ($validated['type'] === 'in') {
            app(PurchaseExpenseService::class)->record(
                $product,
                (int) $validated['quantity'],
                $validated['reference'] ?? null,
                $product->consume_on_takeout ? 'supply' : 'inventory',
                $validated['notes'] ?? null
            );
        }

        return redirect()->back()->with('success', 'Stock movement recorded successfully');
    }

    public function storeBulkPurchase(Request $request)
    {
        if (! $this->canEditInventory()) {
            abort(403);
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['items'] as $item) {
                $product = Product::query()->findOrFail($item['product_id']);
                $quantity = (int) $item['quantity'];
                $unitCost = $product->purchase_price;

                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'in',
                    'source' => 'bulk_purchase',
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'reference' => $validated['reference'] ?? 'Bulk purchase',
                    'notes' => $validated['notes'] ?? null,
                ]);

                $product->increment('stock_quantity', $quantity);

                app(PurchaseExpenseService::class)->record(
                    $product,
                    $quantity,
                    $validated['reference'] ?? null,
                    $product->consume_on_takeout ? 'supply' : 'inventory',
                    $validated['notes'] ?? null
                );
            }
        });

        return redirect()->back()->with('success', 'Bulk purchase recorded successfully');
    }
}
