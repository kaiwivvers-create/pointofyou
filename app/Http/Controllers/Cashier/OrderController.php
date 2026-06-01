<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderAdjustment;
use App\Models\OrderItem;
use App\Models\OperationalExpense;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\OrderInventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        // Get today's attendance status for the cashier
        $user = auth()->user();
        $attendance = null;
        if ($user->employee_id) {
            $attendance = \App\Models\Attendance::where('employee_id', $user->employee_id)
                ->where('date', today())
                ->first();
        }

        // Get today's permit status
        $permit = \App\Models\Permit::where('user_id', $user->id)
            ->where('start_date', '<=', today())
            ->where(function($q) {
                $q->where('end_date', '>=', today())
                  ->orWhereNull('end_date');
            })
            ->where('status', 'approved')
            ->first();

        // Get menu items with categories for the feed
        $menuItems = \App\Models\MenuItem::with('category')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Group by category
        $groupedMenuItems = $menuItems->groupBy(function($item) {
            return $item->category ? $item->category->name : 'Uncategorized';
        });

        // Get all products for inventory display
        $inventoryProducts = Product::query()
            ->with('category')
            ->orderBy('name')
            ->get();

        // Group inventory by category
        $groupedInventory = $inventoryProducts->groupBy(function($product) {
            return $product->category ? $product->category->name : 'Uncategorized';
        });

        $activeOrders = Order::query()
            ->with(['cafeTable', 'items', 'adjustments'])
            ->where('status', OrderStatus::Pending)
            ->orWhere('is_closed', false)
            ->latest()
            ->get();

        return view('cashier.dashboard', compact(
            'attendance',
            'permit',
            'groupedMenuItems',
            'groupedInventory',
            'activeOrders'
        ));
    }

    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.name' => 'required|string',
            'items.*.price' => 'required|numeric',
            'items.*.quantity' => 'required|integer|min:1',
            'order_type' => 'required|in:dine-in,takeout',
            'table_id' => 'nullable|exists:cafe_tables,id',
            'subtotal' => 'required|numeric',
            'tax' => 'required|numeric',
            'total' => 'required|numeric',
            'payment_method' => 'required|string|in:cash,card,qr,transfer',
        ]);

        try {
            DB::beginTransaction();

            // Create order
            $order = Order::create([
                'cafe_table_id' => $validated['order_type'] === 'dine-in' ? $validated['table_id'] : null,
                'status' => OrderStatus::Paid,
                'total' => $validated['total'],
                'paid_by' => auth()->id(),
                'paid_at' => now(),
                'payment_method' => $validated['payment_method'],
                'amount_paid' => $validated['total'],
                'is_closed' => false,
            ]);

            // Create order items and deduct inventory
            foreach ($validated['items'] as $item) {
                $menuItem = \App\Models\MenuItem::find($item['id']);
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                    'is_ready' => false,
                ]);

                // Deduct inventory for ingredients
                if ($menuItem) {
                    foreach ($menuItem->ingredients as $ingredient) {
                        $product = $ingredient->product;
                        if ($product) {
                            $neededQuantity = $ingredient->pivot->quantity * $item['quantity'];
                            
                            if ($product->stock_quantity >= $neededQuantity) {
                                $product->decrement('stock_quantity', $neededQuantity);
                                
                                StockMovement::create([
                                    'product_id' => $product->id,
                                    'type' => 'out',
                                    'quantity' => $neededQuantity,
                                    'unit_cost' => $product->unit_cost ?? 0,
                                    'reference' => "Order #{$order->id}",
                                    'notes' => "Menu item: {$menuItem->name}",
                                ]);
                            }
                        }
                    }
                }
            }

            // Deduct takeout box if takeout order
            if ($validated['order_type'] === 'takeout') {
                $takeoutBox = Product::where('name', 'Takeout Box')->first();
                if ($takeoutBox && $takeoutBox->stock_quantity > 0) {
                    $takeoutBox->decrement('stock_quantity', 1);
                    
                    StockMovement::create([
                        'product_id' => $takeoutBox->id,
                        'type' => 'out',
                        'quantity' => 1,
                        'unit_cost' => $takeoutBox->unit_cost ?? 0,
                        'reference' => "Order #{$order->id}",
                        'notes' => 'Takeout packaging',
                    ]);
                }
            }

            // Create operational expense for stock purchases
            OperationalExpense::create([
                'source' => 'auto_stock_purchase',
                'amount' => $validated['subtotal'],
                'description' => "Order #{$order->id} - Stock deduction",
                'expense_date' => now(),
                'status' => 'approved',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'order_id' => $order->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating order: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function markPaid(Request $request, Order $order): RedirectResponse|JsonResponse
    {
        if (! $order->isPending()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'This order is already paid.'], 400);
            }
            return back()->with('error', 'This order is already paid.');
        }

        $validated = $request->validate([
            'payment_method' => 'required|string|in:cash,qr,card,transfer',
            'amount_paid' => 'required|numeric|min:0',
        ]);

        $order->update([
            'status' => OrderStatus::Paid,
            'paid_by' => $request->user()->id,
            'paid_at' => now(),
            'payment_method' => $validated['payment_method'],
            'amount_paid' => $validated['amount_paid'],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => "Order #{$order->id} paid successfully via {$validated['payment_method']}.",
                'order' => $order
            ]);
        }

        return back()->with('success', "Order #{$order->id} paid successfully via {$validated['payment_method']}.");
    }

    public function markClosed(Request $request, Order $order): RedirectResponse
    {
        if ($order->is_closed) {
            return back()->with('error', 'This order is already marked as picked up.');
        }

        $order->update([
            'is_closed' => true,
            'closed_by' => $request->user()->id,
            'closed_at' => now(),
        ]);

        return back()->with('success', "Order #{$order->id} marked as picked up/closed.");
    }

    public function storeAdjustment(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'type' => 'required|in:charge,discount',
            'amount' => 'nullable|numeric|min:0.01',
            'product_id' => 'nullable|exists:products,id',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $quantity = (int) ($validated['quantity'] ?? 1);
        $product = null;

        if (! empty($validated['product_id'])) {
            if ($validated['type'] !== 'charge') {
                return back()->with('error', 'Inventory-linked bill items must be charges.');
            }

            $product = Product::query()->findOrFail($validated['product_id']);

            if ($quantity > $product->stock_quantity) {
                return back()->with('error', "Not enough {$product->name} in inventory.");
            }

            $validated['amount'] = $validated['amount'] ?? ((float) $product->selling_price * $quantity);
        }

        if (($validated['amount'] ?? null) === null) {
            return back()->with('error', 'Please enter an amount for the bill item.');
        }

        $adjustment = OrderAdjustment::create([
            'order_id' => $order->id,
            'product_id' => $product?->id,
            'label' => $validated['label'],
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'source' => 'manual',
            'quantity' => $quantity,
        ]);

        if ($product) {
            $product->decrement('stock_quantity', $quantity);

            StockMovement::create([
                'product_id' => $product->id,
                'type' => 'out',
                'quantity' => $quantity,
                'unit_cost' => $product->selling_price,
                'reference' => "Order #{$order->id}",
                'notes' => "Cashier bill item: {$adjustment->label}",
            ]);
        }

        app(OrderInventoryService::class)->recalculateOrderTotal($order->fresh(['items', 'adjustments']));

        return back()->with('success', "Bill updated for Order #{$order->id}.");
    }

    public function destroyAdjustment(Request $request, Order $order, OrderAdjustment $adjustment): RedirectResponse
    {
        if ($adjustment->order_id !== $order->id) {
            return back()->with('error', 'That bill line does not belong to this order.');
        }

        if ($adjustment->source !== 'manual') {
            return back()->with('error', 'System-generated bill lines cannot be removed here.');
        }

        if ($adjustment->product_id) {
            $product = Product::query()->find($adjustment->product_id);

            if ($product) {
                $product->increment('stock_quantity', $adjustment->quantity);

                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'in',
                    'quantity' => $adjustment->quantity,
                    'unit_cost' => $product->selling_price,
                    'reference' => "Order #{$order->id}",
                    'notes' => "Removed cashier bill item: {$adjustment->label}",
                ]);
            }
        }

        $adjustment->delete();
        app(OrderInventoryService::class)->recalculateOrderTotal($order->fresh(['items', 'adjustments']));

        return back()->with('success', "Bill line removed from Order #{$order->id}.");
    }

    public function currentOrders(): View
    {
        $activeOrders = Order::query()
            ->with(['cafeTable', 'items'])
            ->where('status', OrderStatus::Pending)
            ->latest()
            ->get();

        return view('cashier.current-orders', compact('activeOrders'));
    }

    public function payments(): View
    {
        $orders = Order::query()
            ->with(['cafeTable', 'items', 'cashier'])
            ->where('status', OrderStatus::Paid)
            ->latest()
            ->paginate(50);

        return view('cashier.payments', compact('orders'));
    }

    public function getCartItems(Order $order): JsonResponse
    {
        $cartItems = $order->items()->select('id', 'menu_item_id')->get();
        return response()->json($cartItems);
    }

    public function getOrderByTable($tableId): JsonResponse
    {
        $order = Order::where('cafe_table_id', $tableId)
            ->where('is_closed', false)
            ->with('items')
            ->first();

        return response()->json([
            'success' => !!$order,
            'order' => $order
        ]);
    }

    public function receipt(Order $order): View
    {
        $order->load(['items.menuItem', 'cafeTable', 'cashier', 'adjustments']);
        return view('cashier.receipt', compact('order'));
    }
}
