<?php

namespace App\Support;

use App\Models\MenuItem;
use Illuminate\Http\Request;

class TableCart
{
    private const SESSION_KEY = 'table_cart';

    private static function signatureForLine(int $menuItemId, array $modifications, ?string $notes, ?int $flavorId): string
    {
        return implode('|', [
            $menuItemId,
            md5(json_encode(array_values($modifications))),
            md5((string) $notes),
            (string) $flavorId,
        ]);
    }

    public static function items(Request $request): array
    {
        return $request->session()->get(self::SESSION_KEY, []);
    }

    public static function add(Request $request, MenuItem $menuItem, int $quantity = 1, array $modifications = [], ?int $flavorId = null, ?string $notes = ''): void
    {
        $cart = self::items($request);

        $modsTotal = 0;
        $selectedMods = [];

        if (!empty($modifications)) {
            $availableMods = $menuItem->modifications->keyBy('id');
            foreach ($modifications as $modId) {
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
        if ($flavorId) {
            $availableFlavors = $menuItem->flavors->keyBy('id');
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

        $signature = self::signatureForLine(
            $menuItem->id,
            $selectedMods,
            $notes,
            $flavorId
        );

        foreach ($cart as $index => $existingItem) {
            if (($existingItem['signature'] ?? null) === $signature) {
                $cart[$index]['quantity'] += $quantity;
                $request->session()->put(self::SESSION_KEY, $cart);
                return;
            }
        }

        $cart[] = [
            'menu_item_id' => $menuItem->id,
            'name' => $menuItem->name,
            'emoji' => $menuItem->emoji,
            'unit_price' => (float) $menuItem->price + $modsTotal + $flavorTotal,
            'quantity' => $quantity,
            'modifications' => $selectedMods,
            'flavor' => $selectedFlavor,
            'notes' => $notes,
            'signature' => $signature,
        ];

        $request->session()->put(self::SESSION_KEY, $cart);
    }

    public static function addCustomItem(Request $request, array $cartItem): void
    {
        $cart = self::items($request);
        $cart[] = $cartItem;
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

    public static function updateItemByIndex(Request $request, int $index, int $quantity, ?string $notes = ''): void
    {
        $cart = self::items($request);

        if (isset($cart[$index])) {
            if ($quantity <= 0) {
                unset($cart[$index]);
                $cart = array_values($cart); // re-index
            } else {
                $cart[$index]['quantity'] = $quantity;
                if ($request->exists('notes')) {
                    $cart[$index]['notes'] = $notes;
                    $cart[$index]['signature'] = self::signatureForLine(
                        (int) ($cart[$index]['menu_item_id'] ?? 0),
                        $cart[$index]['modifications'] ?? [],
                        $notes,
                        $cart[$index]['flavor']['id'] ?? null
                    );
                }
            }
        }

        $request->session()->put(self::SESSION_KEY, $cart);
    }

    public static function removeByIndex(Request $request, int $index): void
    {
        $cart = self::items($request);

        if (isset($cart[$index])) {
            unset($cart[$index]);
            $cart = array_values($cart); // re-index
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
