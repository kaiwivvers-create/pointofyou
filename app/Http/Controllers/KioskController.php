<?php

namespace App\Http\Controllers;

use App\Models\CafeTable;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class KioskController extends Controller
{
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

        $menuItems = MenuItem::where('is_available', true)->with('modifications')->get()->groupBy('category');
        $cart = Session::get('kiosk_cart', []);
        $cartTotal = collect($cart)->sum('line_total');

        return view('kiosk.menu', compact('menuItems', 'cart', 'cartTotal'));
    }

    public function addToCart(Request $request, MenuItem $menuItem)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'modifications' => 'nullable|array',
            'modifications.*' => 'exists:menu_item_modifications,id',
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

        $unitPrice = $menuItem->price + $modsTotal;
        $lineTotal = $unitPrice * $quantity;

        $cartItem = [
            'menu_item_id' => $menuItem->id,
            'name' => $menuItem->name,
            'emoji' => $menuItem->emoji,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => $lineTotal,
            'modifications' => $selectedMods,
        ];

        Session::push('kiosk_cart', $cartItem);

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

    public function checkout()
    {
        if (empty(Session::get('kiosk_cart'))) {
            return redirect()->route('kiosk.menu')->with('error', 'Cart is empty.');
        }
        return view('kiosk.checkout');
    }

    public function pay(Request $request)
    {
        $orderType = Session::get('kiosk_order_type', 'takeout');
        $cart = Session::get('kiosk_cart', []);

        if (empty($cart)) {
            return redirect()->route('kiosk.menu');
        }

        $request->validate([
            'table_number' => 'nullable|string',
        ]);

        $tableId = null;
        if ($orderType === 'dine_in') {
            // Find or create a temporary table for this table_number
            $tableNum = $request->input('table_number', 'Kiosk Dine-In');
            $table = CafeTable::firstOrCreate(
                ['name' => $tableNum],
                ['status' => 'occupied', 'token' => str()->random(16)]
            );
            $tableId = $table->id;
        } else {
            // Takeout
            $table = CafeTable::firstOrCreate(
                ['name' => 'Takeout'],
                ['status' => 'occupied', 'token' => str()->random(16)]
            );
            $tableId = $table->id;
        }

        $total = collect($cart)->sum('line_total');

        $order = Order::create([
            'cafe_table_id' => $tableId,
            'order_type' => $orderType,
            'status' => 'pending', // Will show in kitchen
            'total' => $total,
            'paid_at' => now(), // Marked as paid immediately from kiosk
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
            ]);
        }

        Session::forget('kiosk_cart');
        Session::forget('kiosk_order_type');

        return redirect()->route('kiosk.success')->with('order_id', $order->id);
    }

    public function success()
    {
        if (!Session::has('order_id')) {
            return redirect()->route('kiosk.welcome');
        }
        return view('kiosk.success', ['orderId' => Session::get('order_id')]);
    }
}
