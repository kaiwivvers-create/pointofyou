<?php

namespace App\Http\Controllers;

use App\Models\CafeTable;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Promo;
use App\Models\Packet;
use App\Services\OrderInventoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class KioskController extends Controller
{
    private function cartSignature(MenuItem $menuItem, array $modifications, ?string $notes, ?int $flavorId): string
    {
        return implode('|', [
            $menuItem->id,
            md5(json_encode(array_values($modifications))),
            md5((string) $notes),
            (string) $flavorId,
        ]);
    }

    public function welcome()
    {
        return view('kiosk.welcome');
    }

    public function setType(Request $request)
    {
        $request->validate(['order_type' => 'required|in:dine_in,takeout']);
        Session::put('kiosk_order_type', $request->order_type);
        return redirect()->route('kiosk.menu');
    }

    public function menu()
    {
        if (!Session::has('kiosk_order_type')) {
            return redirect()->route('kiosk.welcome');
        }

        // Load visible categories in saved order
        $orderedCategories = MenuCategory::visible()->pluck('name');

        $allItems = MenuItem::where('is_available', true)->orderBy('name')->with('modifications')->with('flavors')->get()->groupBy('category');

        // Build ordered collection: only visible categories, in sort_order sequence
        $menuItems = $orderedCategories->mapWithKeys(function ($cat) use ($allItems) {
            return [$cat => $allItems->get($cat, collect())];
        })->filter(fn($items) => $items->isNotEmpty());

        // Add packets as a special category
        $packets = Packet::where('is_active', true)->orderBy('order')->get();
        if ($packets->isNotEmpty()) {
            $menuItems['packets'] = $packets;
        }

        $cart = Session::get('kiosk_cart', []);
        $cartTotal = collect($cart)->sum('line_total');
        $promos = Promo::active()->ordered()->get();

        return view('kiosk.menu', compact('menuItems', 'cart', 'cartTotal', 'promos'));
    }

    private function unavailableCartItems(array $cart): array
    {
        $menuItemIds = collect($cart)->pluck('menu_item_id')->unique()->values();
        if ($menuItemIds->isEmpty()) {
            return [];
        }

        $availableItems = MenuItem::query()
            ->whereIn('id', $menuItemIds)
            ->pluck('is_available', 'id');

        $unavailable = [];

        foreach ($cart as $item) {
            $menuItemId = $item['menu_item_id'] ?? null;
            $isAvailable = $menuItemId ? ($availableItems[$menuItemId] ?? false) : false;

            if (! $isAvailable) {
                $unavailable[] = $item['name'] ?? 'One of your items';
            }
        }

        return array_values(array_unique($unavailable));
    }

    public function addToCart(Request $request, MenuItem $menuItem)
    {
        if (! $menuItem->is_available) {
            return back()->with('error', 'That item is not available right now.');
        }

        $request->validate([
            'quantity' => 'required|integer|min:1',
            'modifications' => 'nullable|array',
            'modifications.*' => 'exists:menu_item_modifications,id',
            'flavor' => 'nullable|exists:flavors,id',
            'notes' => 'nullable|string|max:255',
        ]);

        $quantity = $request->integer('quantity');
        $selectedMods = [];
        $modsTotal = 0;

        if ($request->has('modifications')) {
            $availableMods = $menuItem->modifications->keyBy('id');
            foreach ($request->modifications as $modId) {
                if ($availableMods->has($modId)) {
                    $mod = $availableMods->get($modId);
                    $selectedMods[] = [
                        'name' => $mod->name,
                        'additional_price' => $mod->additional_price,
                    ];
                    $modsTotal += $mod->additional_price;
                }
            }
        }

        // Handle flavor selection
        $selectedFlavor = null;
        $flavorTotal = 0;
        if ($request->has('flavor')) {
            $availableFlavors = $menuItem->flavors->keyBy('id');
            $flavorId = $request->input('flavor');
            if ($availableFlavors->has($flavorId)) {
                $flavor = $availableFlavors->get($flavorId);
                $selectedFlavor = [
                    'id' => $flavor->id,
                    'name' => $flavor->name,
                    'additional_price' => $flavor->additional_price,
                ];
                $flavorTotal += $flavor->additional_price;
            }
        }

        $unitPrice = $menuItem->price + $modsTotal + $flavorTotal;
        $lineTotal = $unitPrice * $quantity;
        $signature = $this->cartSignature($menuItem, $selectedMods, $request->input('notes'), $selectedFlavor['id'] ?? null);

        $cartItem = [
            'menu_item_id' => $menuItem->id,
            'name' => $menuItem->name,
            'emoji' => $menuItem->emoji,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => $lineTotal,
            'modifications' => $selectedMods,
            'flavor' => $selectedFlavor,
            'notes' => $request->input('notes'),
            'signature' => $signature,
        ];

        $cart = Session::get('kiosk_cart', []);
        foreach ($cart as $index => $existingItem) {
            if (($existingItem['signature'] ?? null) === $signature) {
                $cart[$index]['quantity'] += $quantity;
                $cart[$index]['line_total'] = $cart[$index]['unit_price'] * $cart[$index]['quantity'];
                Session::put('kiosk_cart', $cart);
                return redirect()->back()->with('success', 'Item added to cart.');
            }
        }

        $cart[] = $cartItem;
        Session::put('kiosk_cart', $cart);

        return redirect()->back()->with('success', 'Item added to cart.');
    }

    public function removeFromCart($cartIndex)
    {
        $cart = Session::get('kiosk_cart', []);
        if (isset($cart[$cartIndex])) {
            unset($cart[$cartIndex]);
            Session::put('kiosk_cart', array_values($cart)); // re-index
        }
        return redirect()->back();
    }

    public function updateCartItem(Request $request, $cartIndex)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:20',
            'notes' => 'nullable|string|max:255|regex:/^[a-zA-Z0-9\s\-.,!?@]+$/',
        ]);

        $cart = Session::get('kiosk_cart', []);
        if (isset($cart[$cartIndex])) {
            $cart[$cartIndex]['quantity'] = $request->integer('quantity');
            $cart[$cartIndex]['line_total'] = $cart[$cartIndex]['unit_price'] * $request->integer('quantity');
            if ($request->has('notes')) {
                $cart[$cartIndex]['notes'] = $request->input('notes');
            }
            Session::put('kiosk_cart', $cart);
        }

        return redirect()->back();
    }

    public function checkout()
    {
        if (empty(Session::get('kiosk_cart'))) {
            return redirect()->route('kiosk.menu')->with('error', 'Cart is empty.');
        }
        return view('kiosk.checkout', [
            'orderType' => Session::get('kiosk_order_type', 'takeout'),
        ]);
    }

    public function pay(Request $request)
    {
        $orderType = Session::get('kiosk_order_type', 'takeout');
        $cart = Session::get('kiosk_cart', []);
        $inventoryService = app(OrderInventoryService::class);

        $unavailableItems = $this->unavailableCartItems($cart);
        if (! empty($unavailableItems)) {
            return redirect()->route('kiosk.menu')->with('error', 'These items are no longer available: ' . implode(', ', $unavailableItems));
        }

        if (empty($cart)) {
            return redirect()->route('kiosk.menu');
        }

        $request->validate([
            'table_number' => 'nullable|string',
        ]);

        try {
            $order = DB::transaction(function () use ($request, $orderType, $cart, $inventoryService) {
                $tableId = null;
                if ($orderType === 'dine_in') {
                    $tableNum = $request->input('table_number', 'Kiosk Dine-In');
                    $table = CafeTable::firstOrCreate(
                        ['name' => $tableNum],
                        ['status' => 'occupied', 'token' => str()->random(16)]
                    );
                    $tableId = $table->id;
                } else {
                    $table = CafeTable::firstOrCreate(
                        ['name' => 'Takeout'],
                        ['status' => 'occupied', 'token' => str()->random(16)]
                    );
                    $tableId = $table->id;
                }

                $order = Order::create([
                    'cafe_table_id' => $tableId,
                    'order_type' => $orderType,
                    'status' => 'pending',
                    'total' => 0,
                    'paid_at' => now(),
                ]);

                foreach ($cart as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'menu_item_id' => $item['menu_item_id'],
                        'item_name' => $item['name'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'line_total' => $item['line_total'],
                        'modifications' => $item['modifications'] ?? [],
                        'notes' => $item['notes'] ?? null,
                    ]);
                }

                $inventoryService->applyMenuItemIngredients($order);
                $inventoryService->applyTakeoutSupplies($order);
                $inventoryService->recalculateOrderTotal($order);

                return $order;
            });
        } catch (\RuntimeException $exception) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $exception->getMessage()], 422);
            }

            return redirect()->route('kiosk.menu')->with('error', $exception->getMessage());
        }

        $pickupLabel = $orderType === 'takeout'
            ? 'Takeout'
            : $request->input('table_number', 'Kiosk Dine-In');

        Session::flash('order_id', $order->id);
        Session::flash('order_type', $orderType);
        Session::flash('pickup_label', $pickupLabel);
        Session::forget('kiosk_cart');
        Session::forget('kiosk_order_type');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Payment completed.',
                'order_id' => $order->id,
                'redirect_url' => route('kiosk.success'),
            ]);
        }

        return redirect()->route('kiosk.success')->with('order_id', $order->id);
    }

    public function success()
    {
        if (!Session::has('order_id')) {
            return redirect()->route('kiosk.welcome');
        }
        return view('kiosk.success', [
            'orderId' => Session::get('order_id'),
            'orderType' => Session::get('order_type', 'takeout'),
            'pickupLabel' => Session::get('pickup_label', 'Takeout'),
        ]);
    }

    public function applyPromo(Request $request)
    {
        $request->validate(['promo_id' => 'required|exists:promos,id']);

        $promo = Promo::findOrFail($request->promo_id);

        if (!$promo->is_active) {
            return redirect()->back()->with('error', 'This promo is not active.');
        }

        // Get current cart
        $cart = Session::get('kiosk_cart', []);

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
        Session::put('kiosk_cart', $cart);

        // Store promo in session - one promo per order
        Session::put('kiosk_promo', $promo->id);

        return redirect()->back()->with('success', 'Promo applied successfully!');
    }
}
