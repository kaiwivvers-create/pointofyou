<?php

namespace App\Http\Controllers\Table;

use App\Http\Controllers\Controller;
use App\Models\CafeTable;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Promo;
use App\Models\Packet;
use App\Services\OrderInventoryService;
use App\Enums\OrderStatus;
use App\Support\TableCart;
use Illuminate\Support\Facades\DB;
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
        // Load visible categories in saved order (excluding promos and packets)
        $orderedCategories = MenuCategory::visible()->whereNotIn('name', ['promos', 'packets'])->pluck('name');

        $allItems = MenuItem::query()
            ->where('is_available', true)
            ->orderBy('name')
            ->with('modifications')
            ->with('flavors')
            ->get()
            ->groupBy('category');

        // Build ordered collection: only visible categories, in sort_order sequence
        $items = $orderedCategories->mapWithKeys(function ($cat) use ($allItems) {
            return [$cat => $allItems->get($cat, collect())];
        })->filter(fn($items) => $items->isNotEmpty());

        // Add packets items at the beginning (right under promos carousel)
        $packets = Packet::where('is_active', true)->orderBy('order')->with('items')->get();
        if ($packets->isNotEmpty()) {
            $items = collect(['packets' => $packets->map(function($packet) {
                $packet->items = $packet->items->map(function($item) {
                    $item->pivot = $item->pivot;
                    return $item;
                });
                return $packet;
            })])->merge($items);
        }

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

    private function unavailableCartItems(Request $request): array
    {
        $cart = TableCart::items($request);
        $menuItemIds = collect($cart)->pluck('menu_item_id')->unique()->values();
        if ($menuItemIds->isEmpty()) {
            return [];
        }

        $availableItems = MenuItem::query()
            ->whereIn('id', $menuItemIds)
            ->pluck('is_available', 'id');

        $unavailable = [];

        foreach ($cart as $item) {
            if (isset($item['is_packet']) && $item['is_packet']) {
                continue;
            }

            $menuItemId = $item['menu_item_id'] ?? null;
            $isAvailable = $menuItemId ? ($availableItems[$menuItemId] ?? false) : false;

            if (! $isAvailable) {
                $unavailable[] = $item['name'] ?? 'One of your items';
            }
        }

        return array_values(array_unique($unavailable));
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
            'flavor' => 'nullable|exists:flavors,id',
            'flavors' => 'nullable|exists:flavors,id',
            'notes' => 'nullable|string|max:255',
        ]);

        $quantity = $request->integer('quantity');
        $modifications = $request->input('modifications', []);
        $flavorId = $request->input('flavor', $request->input('flavors'));
        $notes = $request->input('notes', '');

        TableCart::add($request, $menuItem, $quantity, $modifications, $flavorId, $notes);

        return back()->with('success', "Added {$menuItem->name} to your order.");
    }

    public function addPacketToCart(Request $request, Packet $packet): RedirectResponse
    {
        if (! $packet->is_active) {
            return back()->with('error', 'That packet is not available right now.');
        }

        $request->validate([
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:255',
        ]);

        $quantity = $request->integer('quantity');
        $unitPrice = $packet->fixed_price;
        $lineTotal = $unitPrice * $quantity;
        $notes = $request->input('notes', '');
        $signature = 'packet_' . $packet->id . '|' . md5((string) $notes);

        $cart = TableCart::items($request);
        
        // Check if packet already exists in cart
        foreach ($cart as $index => $existingItem) {
            if (($existingItem['signature'] ?? null) === $signature) {
                TableCart::updateItemByIndex($request, $index, $existingItem['quantity'] + $quantity);
                return back()->with('success', 'Packet added to your order.');
            }
        }

        // Build packet contents for display
        $packetContents = [];
        if ($packet->items) {
            foreach ($packet->items as $item) {
                $packetContents[] = [
                    'name' => $item->name,
                    'quantity' => $item->pivot->quantity ?? 1
                ];
            }
        }

        // Add new packet to cart
        $cartItem = [
            'menu_item_id' => null, // Packets don't have menu_item_id
            'packet_id' => $packet->id,
            'name' => $packet->name,
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'line_total' => $lineTotal,
            'modifications' => [],
            'flavor' => null,
            'notes' => $notes,
            'signature' => $signature,
            'is_packet' => true,
            'packet_contents' => $packetContents,
        ];

        TableCart::addCustomItem($request, $cartItem);

        return back()->with('success', 'Packet added to your order.');
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
            'notes' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\s\-.,!?@]+$/'],
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
        $inventoryService = app(OrderInventoryService::class);

        $unavailableItems = $this->unavailableCartItems($request);
        if (! empty($unavailableItems)) {
            return back()->with('error', 'These items are no longer available: ' . implode(', ', $unavailableItems));
        }

        if (empty($cart)) {
            return back()->with('error', 'Your cart is empty.');
        }

        $tableId = $request->session()->get('cafe_table_id');

        try {
            $order = DB::transaction(function () use ($tableId, $cart, $request, $inventoryService) {
                $order = Order::create([
                    'cafe_table_id' => $tableId,
                    'status' => OrderStatus::Paid,
                    'total' => 0,
                    'order_type' => 'dine_in',
                    'paid_at' => now(),
                    'paid_by' => null,
                    'payment_method' => 'table_order',
                ]);

                foreach ($cart as $line) {
                    // Handle packets - expand them into individual menu items
                    if (isset($line['is_packet']) && $line['is_packet'] && isset($line['packet_id'])) {
                        $packet = Packet::find($line['packet_id']);
                        if ($packet && $packet->items) {
                            $packetNotes = trim((string) ($line['notes'] ?? ''));
                            $packetNoteText = 'Part of packet: ' . $packet->name;
                            if ($packetNotes !== '') {
                                $packetNoteText .= ' | Packet notes: ' . $packetNotes;
                            }
                            foreach ($packet->items as $packetItem) {
                                $quantity = ($packetItem->pivot->quantity ?? 1) * $line['quantity'];
                                $unitPrice = $packetItem->price ?? 0;
                                $lineTotal = $unitPrice * $quantity;
                                OrderItem::create([
                                    'order_id' => $order->id,
                                    'menu_item_id' => $packetItem->id,
                                    'item_name' => $packetItem->name,
                                    'quantity' => $quantity,
                                    'unit_price' => $unitPrice,
                                    'line_total' => $lineTotal,
                                    'modifications' => [],
                                    'notes' => $packetNoteText,
                                ]);
                            }
                        }
                    } else {
                        // Regular menu item
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
                }

                $inventoryService->applyMenuItemIngredients($order);
                $inventoryService->applyTakeoutSupplies($order);
                $inventoryService->recalculateOrderTotal($order);

                return $order;
            });
        } catch (\RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
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

    public function applyPromo(Request $request): RedirectResponse
    {
        $request->validate(['promo_id' => 'required|exists:promos,id']);

        $promo = Promo::findOrFail($request->promo_id);

        if (!$promo->is_active) {
            return redirect()->back()->with('error', 'This promo is not active.');
        }

        // Get current cart
        $cart = $request->session()->get('table_cart', []);

        // Remove any existing promo from cart
        $cart = array_filter($cart, function($item) {
            return !isset($item['is_promo']);
        });

        // Add promo to cart as a special item
        $cart[] = [
            'menu_item_id' => 0, // Special ID for promo items
            'name' => 'Promo: ' . ($promo->title ?? 'Special Offer'),
            'unit_price' => $promo->discount_value ?? 0,
            'quantity' => 1,
            'line_total' => $promo->discount_value ?? 0,
            'modifications' => [],
            'flavor' => null,
            'notes' => $promo->description ?? '',
            'signature' => 'promo_' . $promo->id,
            'is_promo' => true,
            'promo_id' => $promo->id,
        ];

        // Store updated cart
        $request->session()->put('table_cart', $cart);

        // Store promo in session - one promo per order
        $request->session()->put('table_promo', $promo->id);

        return redirect()->back()->with('success', 'Promo applied successfully!');
    }
}
