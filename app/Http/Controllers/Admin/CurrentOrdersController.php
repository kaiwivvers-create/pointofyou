<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gift;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Attendance;
use App\Models\Permit;
use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CurrentOrdersController extends Controller
{
    public function dashboard(): View
    {
        $user = auth()->user();
        $attendance = null;
        if ($user->employee_id) {
            $attendance = Attendance::where('employee_id', $user->employee_id)
                ->where('date', today())
                ->first();
        }

        $permit = Permit::where('user_id', $user->id)
            ->where('start_date', '<=', today())
            ->where(function($q) {
                $q->where('end_date', '>=', today())
                  ->orWhereNull('end_date');
            })
            ->where('status', 'approved')
            ->first();

        $orders = Order::with(['cafeTable', 'items.menuItem'])
            ->where('is_closed', false)
            ->whereIn('status', [OrderStatus::Pending, OrderStatus::Paid])
            ->latest()
            ->get();

        $todoOrders = collect();
        $inProgressOrders = collect();
        $readyOrders = collect();

        foreach ($orders as $order) {
            $readyItems = $order->items->where('is_ready', true)->count();
            $totalItems = $order->items->count();

            if ($readyItems === 0) {
                $todoOrders->push($order);
            } elseif ($readyItems < $totalItems) {
                $inProgressOrders->push($order);
            } else {
                $readyOrders->push($order);
            }
        }

        return view('chef.dashboard', compact(
            'attendance',
            'permit',
            'orders',
            'todoOrders',
            'inProgressOrders',
            'readyOrders'
        ));
    }

    public function index(): View
    {
        $activeOrders = Order::query()
            ->with(['cafeTable', 'items.menuItem', 'items.gift', 'items.product'])
            ->where('is_closed', false)
            ->where(function($q) {
                $q->whereHas('items', function ($query) {
                    $query->where('is_ready', false);
                })
                ->orWhere('status', OrderStatus::Pending)
                ->orWhere('is_picked_up', false);
            })
            ->latest()
            ->get();

        // Menu items (searchable in Add Item modal)
        $menuItems = MenuItem::where('is_available', true)->get();

        // Gifts (searchable)
        $gifts = Gift::where('is_active', true)->get();

        // Inventory products (searchable)
        $products = Product::where('stock_quantity', '>', 0)->get();

        // Build unified searchable items JSON for the modal
        $searchableItems = collect();

        foreach ($menuItems as $m) {
            $searchableItems->push([
                'id'       => $m->id,
                'type'     => 'menu_item',
                'name'     => $m->name,
                'image'    => $m->image ? asset('app-storage/' . $m->image) : null,
                'price'    => (float) $m->price,
                'category' => $m->category ?? 'Menu Item',
                'barcode'  => $m->barcode,
            ]);
        }

        foreach ($gifts as $g) {
            $searchableItems->push([
                'id'       => $g->id,
                'type'     => 'gift',
                'name'     => $g->name,
                'image'    => $g->image ? asset('app-storage/' . $g->image) : null,
                'price'    => (float) ($g->cost ?? 0),
                'category' => 'Gift',
                'barcode'  => $g->barcode ?? null,
            ]);
        }

        foreach ($products as $p) {
            $searchableItems->push([
                'id'       => $p->id,
                'type'     => 'product',
                'name'     => $p->name,
                'image'    => null,
                'price'    => (float) ($p->selling_price ?? 0),
                'category' => 'Inventory / Takeout',
                'barcode'  => $p->barcode ?? null,
            ]);
        }

        $searchableItemsJson = $searchableItems->values()->toArray();

        return view('admin.current-orders.index', compact(
            'activeOrders',
            'menuItems',
            'searchableItemsJson'
        ));
    }

    public function toggleReady(Request $request, OrderItem $orderItem)
    {
        $orderItem->update([
            'is_ready' => !$orderItem->is_ready,
        ]);

        return back()->with('success', 'Item status updated.');
    }

    /** Mark order as picked up (step 3 in lifecycle). */
    public function markPickedUp(Request $request, Order $order): JsonResponse
    {
        if ($order->is_closed) {
            return response()->json(['message' => 'Order is already closed.'], 400);
        }

        if ($order->is_picked_up) {
            return response()->json(['message' => 'Order is already marked as picked up.'], 400);
        }

        $order->update([
            'is_picked_up'  => true,
            'picked_up_by'  => $request->user()->id,
            'picked_up_at'  => now(),
        ]);

        return response()->json([
            'message' => "Order #{$order->id} marked as picked up.",
            'order'   => $order,
        ]);
    }

    public function pay(Request $request, Order $order): JsonResponse
    {
        if (! $order->isPending()) {
            return response()->json(['message' => 'This order is already paid.'], 400);
        }

        $validated = $request->validate([
            'payment_method' => 'required|string|in:cash,qr,card,transfer',
            'amount_paid'    => 'required|numeric|min:0',
        ]);

        $order->update([
            'status'         => OrderStatus::Paid,
            'paid_by'        => $request->user()->id,
            'paid_at'        => now(),
            'payment_method' => $validated['payment_method'],
            'amount_paid'    => $validated['amount_paid'],
        ]);

        return response()->json([
            'message' => "Order #{$order->id} paid successfully via {$validated['payment_method']}.",
            'order'   => $order,
        ]);
    }

    /** Close (hide) the order — only allowed when all steps are complete. */
    public function markClosed(Request $request, Order $order): JsonResponse
    {
        if ($order->is_closed) {
            return response()->json(['message' => 'This order is already closed.'], 400);
        }

        if (!$order->canClose()) {
            $missing = [];
            if (!$order->isFullyReady()) $missing[] = 'all items must be checked by chef';
            if (!$order->is_picked_up)   $missing[] = 'order must be picked up';
            if ($order->isPending())     $missing[] = 'order must be paid';

            return response()->json([
                'message' => 'Cannot close order yet: ' . implode(', ', $missing) . '.',
            ], 422);
        }

        $order->update([
            'is_closed' => true,
            'closed_by' => $request->user()->id,
            'closed_at' => now(),
        ]);

        return response()->json([
            'message' => "Order #{$order->id} closed.",
            'order'   => $order,
        ]);
    }

    /** Add an item to an existing open order — supports menu items, gifts, and products by barcode or ID. */
    public function addItem(Request $request, Order $order): JsonResponse
    {
        if ($order->is_closed) {
            return response()->json(['message' => 'Cannot add items to a closed order.'], 400);
        }

        $validated = $request->validate([
            'barcode'      => 'nullable|string',
            'menu_item_id' => 'nullable|integer',
            'gift_id'      => 'nullable|integer',
            'product_id'   => 'nullable|integer',
            'item_type'    => 'nullable|string|in:menu_item,gift,product',
            'quantity'     => 'required|integer|min:1',
        ]);

        if (empty($validated['barcode']) && empty($validated['menu_item_id']) && empty($validated['gift_id']) && empty($validated['product_id'])) {
            return response()->json(['message' => 'Must provide a barcode or select an item.'], 400);
        }

        $itemName  = null;
        $unitPrice = 0;

        // ── Find by barcode (search all types) ───────────────────────
        if (!empty($validated['barcode'])) {
            $bc = $validated['barcode'];

            $menuItem = MenuItem::where('barcode', $bc)->where('is_available', true)->first();
            if ($menuItem) {
                $itemName  = $menuItem->name;
                $unitPrice = $menuItem->price;
                $validated['menu_item_id'] = $menuItem->id;
                $validated['item_type']    = 'menu_item';
            }

            if (!$menuItem) {
                $gift = Gift::where('barcode', $bc)->where('is_active', true)->first();
                if ($gift) {
                    $itemName  = $gift->name;
                    $unitPrice = $gift->cost ?? 0;
                    $validated['gift_id']   = $gift->id;
                    $validated['item_type'] = 'gift';
                }
            }

            if (!$menuItem && !isset($gift)) {
                $product = Product::where('barcode', $bc)->first();
                if ($product) {
                    $itemName  = $product->name;
                    $unitPrice = $product->selling_price ?? 0;
                    $validated['product_id'] = $product->id;
                    $validated['item_type']  = 'product';
                }
            }

            if (!$itemName) {
                return response()->json(['message' => 'No item found with that barcode.'], 404);
            }
        }

        // ── Find by specific ID ───────────────────────────────────────
        if (!$itemName && !empty($validated['menu_item_id'])) {
            $menuItem = MenuItem::where('id', $validated['menu_item_id'])->where('is_available', true)->first();
            if (!$menuItem) return response()->json(['message' => 'Menu item not found.'], 404);
            $itemName  = $menuItem->name;
            $unitPrice = $menuItem->price;
            $validated['item_type'] = 'menu_item';
        }

        if (!$itemName && !empty($validated['gift_id'])) {
            $gift = Gift::where('id', $validated['gift_id'])->where('is_active', true)->first();
            if (!$gift) return response()->json(['message' => 'Gift not found.'], 404);
            $itemName  = $gift->name;
            $unitPrice = $gift->cost ?? 0;
            $validated['item_type'] = 'gift';
        }

        if (!$itemName && !empty($validated['product_id'])) {
            $product = Product::find($validated['product_id']);
            if (!$product) return response()->json(['message' => 'Product not found.'], 404);
            $itemName  = $product->name;
            $unitPrice = $product->selling_price ?? 0;
            $validated['item_type'] = 'product';
        }

        if (!$itemName) {
            return response()->json(['message' => 'Item not found or unavailable.'], 404);
        }

        $qty       = $validated['quantity'];
        $lineTotal = $unitPrice * $qty;

        $query = OrderItem::where('order_id', $order->id);
        
        if (!empty($validated['menu_item_id'])) {
            $query->where('menu_item_id', $validated['menu_item_id']);
        } elseif (!empty($validated['gift_id'])) {
            $query->where('gift_id', $validated['gift_id']);
        } elseif (!empty($validated['product_id'])) {
            $query->where('product_id', $validated['product_id']);
        }
        
        $existingItem = $query->first();

        if ($existingItem) {
            $existingItem->quantity += $qty;
            $existingItem->line_total += $lineTotal;
            $existingItem->save();
            $orderItem = $existingItem;
        } else {
            $orderItem = OrderItem::create([
                'order_id'    => $order->id,
                'menu_item_id'=> $validated['menu_item_id'] ?? null,
                'gift_id'     => $validated['gift_id'] ?? null,
                'product_id'  => $validated['product_id'] ?? null,
                'item_name'   => $itemName,
                'quantity'    => $qty,
                'unit_price'  => $unitPrice,
                'line_total'  => $lineTotal,
                'modifications' => [],
                'notes'       => 'Added by staff (' . ($validated['item_type'] ?? 'item') . ')',
            ]);
        }

        $order->increment('total', $lineTotal);

        return response()->json([
            'message' => "Added {$qty}x {$itemName} to order.",
            'item'    => $orderItem,
        ]);
    }
}
