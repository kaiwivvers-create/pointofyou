<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CurrentOrdersController extends Controller
{
    public function index(): View
    {
        // Get all pending orders and paid orders that have unready items
        // Wait, for the kitchen we just want all pending/paid orders for today maybe?
        // Let's just fetch all orders that have ANY items that are NOT ready yet, OR are pending.
        
        $activeOrders = Order::query()
            ->with(['cafeTable', 'items.menuItem'])
            ->where('is_closed', false)
            ->where(function($q) {
                $q->whereHas('items', function ($query) {
                    $query->where('is_ready', false);
                })
                ->orWhere('status', OrderStatus::Pending);
            })
            ->latest()
            ->get();

        return view('admin.current-orders.index', compact('activeOrders'));
    }

    public function toggleReady(Request $request, OrderItem $orderItem)
    {
        $orderItem->update([
            'is_ready' => !$orderItem->is_ready,
        ]);

        return back()->with('success', 'Item status updated.');
    }
}
