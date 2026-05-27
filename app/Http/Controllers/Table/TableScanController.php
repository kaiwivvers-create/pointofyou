<?php

namespace App\Http\Controllers\Table;

use App\Http\Controllers\Controller;
use App\Models\CafeTable;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Promo;
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
        // Load visible categories in saved order
        $orderedCategories = MenuCategory::visible()->pluck('name');

        $allItems = MenuItem::query()
            ->where('is_available', true)
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        // Build ordered collection: only visible categories, in sort_order sequence
        $items = $orderedCategories->mapWithKeys(function ($cat) use ($allItems) {
            return [$cat => $allItems->get($cat, collect())];
        })->filter(fn($items) => $items->isNotEmpty());

        $promos = Promo::active()->ordered()->get();

        return view('table.menu', [
            'tableName' => $request->session()->get('cafe_table_name'),
            'itemsByCategory' => $items,
            'cart' => TableCart::items($request),
            'cartCount' => TableCart::count($request),
            'cartTotal' => TableCart::total($request),
            'promos' => $promos,
        ]);
    }

    public function addToCart(Request $request, MenuItem $menuItem): RedirectResponse
    {
        if (! $menuItem->is_available) {
            return back()->with('error', 'That item is not available right now.');
        }

        $request->validate([
            'quantity' => 'required|integer|min:1',
            'modifications' => 'nullable|array',
            'modifications.*' => 'exists:menu_item_modifications,id',
            'notes' => 'nullable|string|max:255',
        ]);

        $quantity = $request->integer('quantity');
        $modifications = $request->input('modifications', []);
        $notes = $request->input('notes', '');

        TableCart::add($request, $menuItem, $quantity, $modifications, $notes);

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

    public function updateCartItem(Request $request, int $index): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        TableCart::updateItemByIndex(
            $request,
            $index,
            (int) $validated['quantity'],
            $validated['notes'] ?? ''
        );

        return back();
    }

    public function removeCartItem(Request $request, int $index): RedirectResponse
    {
        TableCart::removeByIndex($request, $index);
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
                'modifications' => $line['modifications'] ?? [],
                'notes' => $line['notes'] ?? null,
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
