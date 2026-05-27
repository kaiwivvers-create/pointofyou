<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $activeOrders = Order::query()
            ->with(['cafeTable', 'items'])
            ->where('status', OrderStatus::Pending)
            ->orWhere('is_closed', false)
            ->latest()
            ->get();

        return view('cashier.orders.index', compact('activeOrders'));
    }

    public function markPaid(Request $request, Order $order): RedirectResponse
    {
        if (! $order->isPending()) {
            return back()->with('error', 'This order is already paid.');
        }

        $order->update([
            'status' => OrderStatus::Paid,
            'paid_by' => $request->user()->id,
            'paid_at' => now(),
        ]);

        return back()->with('success', "Order #{$order->id} marked as paid.");
    }

    public function markClosed(Request $request, Order $order): RedirectResponse
    {
        if ($order->is_closed) {
            return back()->with('error', 'This order is already marked as picked up.');
        }

        $order->update([
            'is_closed' => true,
        ]);

        return back()->with('success', "Order #{$order->id} marked as picked up/closed.");
    }
}
