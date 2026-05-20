<?php

namespace App\Http\Controllers\Table;

use App\Http\Controllers\Controller;
use App\Models\CafeTable;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Enums\OrderStatus;
use App\Support\TableCart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TableScanController extends Controller
{
    public function welcome(): View
    {
        return view('table.welcome');
    }

    public function scan(string $token): RedirectResponse
    {
        $table = CafeTable::query()
            ->where('token', $token)
            ->where('is_active', true)
            ->firstOrFail();

        session([
            'cafe_table_id' => $table->id,
            'cafe_table_name' => $table->name,
        ]);

        TableCart::clear(request());

        return redirect()
            ->route('table.menu')
            ->with('success', "You're seated at {$table->name}. Browse the menu below!");
    }

    public function menu(Request $request): View
    {
        $items = MenuItem::query()
            ->where('is_available', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        return view('table.menu', [
            'tableName' => $request->session()->get('cafe_table_name'),
            'itemsByCategory' => $items,
            'cart' => TableCart::items($request),
            'cartCount' => TableCart::count($request),
            'cartTotal' => TableCart::total($request),
        ]);
    }

    public function addToCart(Request $request, MenuItem $menuItem): RedirectResponse
    {
        if (! $menuItem->is_available) {
            return back()->with('error', 'That item is not available right now.');
        }

        TableCart::add($request, $menuItem);

        return back()->with('success', "Added {$menuItem->name} to your order.");
    }

    public function updateCart(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'menu_item_id' => ['required', 'integer', 'exists:menu_items,id'],
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        TableCart::updateQuantity(
            $request,
            (int) $validated['menu_item_id'],
            (int) $validated['quantity']
        );

        return back();
    }

    public function placeOrder(Request $request): RedirectResponse
    {
        $cart = TableCart::items($request);

        if (empty($cart)) {
            return back()->with('error', 'Your cart is empty.');
        }

        $tableId = $request->session()->get('cafe_table_id');

        $order = Order::create([
            'cafe_table_id' => $tableId,
            'status' => OrderStatus::Pending,
            'total' => TableCart::total($request),
        ]);

        foreach ($cart as $line) {
            $lineTotal = $line['unit_price'] * $line['quantity'];

            OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $line['menu_item_id'],
                'item_name' => $line['name'],
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'],
                'line_total' => $lineTotal,
            ]);
        }

        TableCart::clear($request);

        return redirect()
            ->route('table.menu')
            ->with('success', "Order #{$order->id} sent to the counter. Pay when you're ready!");
    }

    public function clearTable(Request $request): RedirectResponse
    {
        $request->session()->forget(['cafe_table_id', 'cafe_table_name']);
        TableCart::clear($request);

        return redirect()->route('table.welcome');
    }
}
