<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PickupStationController extends Controller
{
    public function index(): View
    {
        // Fetch active orders (not closed)
        $activeOrders = Order::query()
            ->with(['cafeTable', 'items'])
            ->where('is_closed', false)
            ->latest()
            ->get();

        // Fetch recently closed orders (last 20)
        $recentlyClosed = Order::query()
            ->with(['cafeTable', 'closedBy'])
            ->where('is_closed', true)
            ->latest('closed_at')
            ->limit(20)
            ->get();

        return view('admin.pickup-station.index', compact('activeOrders', 'recentlyClosed'));
    }

    public function markClosed(Request $request, Order $order): RedirectResponse
    {
        if ($order->is_closed) {
            return back()->with('error', 'This order is already marked as picked up.');
        }

        if (! $order->isFullyReady()) {
            return back()->with('error', "Order #{$order->id} is not ready yet. Please wait for the kitchen to finish all items.");
        }

        $order->update([
            'is_closed' => true,
            'closed_by' => $request->user()->id,
            'closed_at' => now(),
        ]);

        return back()->with('success', "Order #{$order->id} marked as picked up.");
    }
}
