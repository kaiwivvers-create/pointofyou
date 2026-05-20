<?php

namespace App\Support;

use App\Models\MenuItem;
use Illuminate\Http\Request;

class TableCart
{
    private const SESSION_KEY = 'table_cart';

    public static function items(Request $request): array
    {
        return $request->session()->get(self::SESSION_KEY, []);
    }

    public static function add(Request $request, MenuItem $menuItem, int $quantity = 1): void
    {
        $cart = self::items($request);
        $id = (string) $menuItem->id;

        if (isset($cart[$id])) {
            $cart[$id]['quantity'] += $quantity;
        } else {
            $cart[$id] = [
                'menu_item_id' => $menuItem->id,
                'name' => $menuItem->name,
                'emoji' => $menuItem->emoji,
                'unit_price' => (float) $menuItem->price,
                'quantity' => $quantity,
            ];
        }

        $request->session()->put(self::SESSION_KEY, $cart);
    }

    public static function updateQuantity(Request $request, int $menuItemId, int $quantity): void
    {
        $cart = self::items($request);
        $id = (string) $menuItemId;

        if ($quantity <= 0) {
            unset($cart[$id]);
        } elseif (isset($cart[$id])) {
            $cart[$id]['quantity'] = $quantity;
        }

        $request->session()->put(self::SESSION_KEY, $cart);
    }

    public static function clear(Request $request): void
    {
        $request->session()->forget(self::SESSION_KEY);
    }

    public static function count(Request $request): int
    {
        return (int) collect(self::items($request))->sum('quantity');
    }

    public static function total(Request $request): float
    {
        return (float) collect(self::items($request))->sum(
            fn (array $line) => $line['unit_price'] * $line['quantity']
        );
    }
}
