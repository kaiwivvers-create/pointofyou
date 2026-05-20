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
        $pendingOrders = Order::query()
            ->with(['cafeTable', 'items'])
            ->where('status', OrderStatus::Pending)
            ->latest()
            ->get();

        $recentPaid = Order::query()
            ->with(['cafeTable'])
            ->where('status', OrderStatus::Paid)
            ->latest()
            ->limit(10)
            ->get();

        return view('cashier.orders.index', compact('pendingOrders', 'recentPaid'));
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
}
