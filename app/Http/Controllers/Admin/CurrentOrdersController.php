<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Attendance;
use App\Models\Permit;
use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CurrentOrdersController extends Controller
{
    public function dashboard(): View
    {
        // Get today's attendance status for the chef
        $user = auth()->user();
        $attendance = null;
        if ($user->employee_id) {
            $attendance = Attendance::where('employee_id', $user->employee_id)
                ->where('date', today())
                ->first();
        }

        // Get today's permit status
        $permit = Permit::where('user_id', $user->id)
            ->where('start_date', '<=', today())
            ->where(function($q) {
                $q->where('end_date', '>=', today())
                  ->orWhereNull('end_date');
            })
            ->where('status', 'approved')
            ->first();

        // Get orders for KDS
        // To Do: New orders (pending or paid, not started)
        // In Progress: Orders being cooked  
        // Ready: Orders ready for pickup
        
        $orders = Order::with(['cafeTable', 'items.menuItem'])
            ->where('is_closed', false)
            ->whereIn('status', [OrderStatus::Pending, OrderStatus::Paid])
            ->latest()
            ->get();

        // Categorize orders for KDS
        $todoOrders = collect();
        $inProgressOrders = collect();
        $readyOrders = collect();

        foreach ($orders as $order) {
            // Check if any items are ready
            $readyItems = $order->items->where('is_ready', true)->count();
            $totalItems = $order->items->count();
            
            if ($readyItems === 0) {
                // No items ready - todo
                $todoOrders->push($order);
            } elseif ($readyItems < $totalItems) {
                // Some items ready - in progress
                $inProgressOrders->push($order);
            } else {
                // All items ready - ready
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
