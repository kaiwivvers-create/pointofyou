<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderAdjustment;
use App\Models\OrderItem;
use App\Models\MenuItem;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OrderInventoryService
{
    public function applyTakeoutSupplies(Order $order): void
    {
        if ($order->order_type !== 'takeout') {
            return;
        }

        $totalItemQuantity = (int) $order->items->sum('quantity');
        if ($totalItemQuantity < 1) {
            return;
        }

        $products = Product::query()
            ->where('consume_on_takeout', true)
            ->orderBy('name')
            ->get();

        foreach ($products as $product) {
            $requiredQuantity = $totalItemQuantity * max(1, (int) $product->consume_per_item);

            if ($requiredQuantity > $product->stock_quantity) {
                throw new RuntimeException("Not enough {$product->name} in inventory for this takeout order.");
            }
        }

        foreach ($products as $product) {
            $requiredQuantity = $totalItemQuantity * max(1, (int) $product->consume_per_item);
            $lineAmount = (float) $product->selling_price * $requiredQuantity;

            if ($requiredQuantity <= 0) {
                continue;
            }

            OrderAdjustment::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'label' => "{$product->name} x{$requiredQuantity}",
                'type' => 'charge',
                'amount' => $lineAmount,
                'source' => 'auto_supply',
                'quantity' => $requiredQuantity,
            ]);

            $product->decrement('stock_quantity', $requiredQuantity);

            StockMovement::create([
                'product_id' => $product->id,
                'type' => 'out',
                'source' => 'auto_supply',
                'quantity' => $requiredQuantity,
                'unit_cost' => $product->selling_price,
                'reference' => "Order #{$order->id}",
                'notes' => "Auto-consumed for takeout order #{$order->id}",
            ]);
        }
    }

    public function applyMenuItemIngredients(Order $order): void
    {
        $orderItems = $order->items()->with('menuItem.ingredients')->get();

        if ($orderItems->isEmpty()) {
            return;
        }

        // Collect all ingredient requirements
        $ingredientRequirements = [];

        foreach ($orderItems as $orderItem) {
            $menuItem = $orderItem->menuItem;
            if (!$menuItem) {
                continue;
            }

            $ingredients = $menuItem->ingredients;
            if ($ingredients->isEmpty()) {
                continue;
            }

            foreach ($ingredients as $ingredient) {
                $productId = $ingredient->id;
                $requiredQuantity = (float) $ingredient->pivot->quantity * $orderItem->quantity;

                if (!isset($ingredientRequirements[$productId])) {
                    $ingredientRequirements[$productId] = [
                        'product' => $ingredient,
                        'quantity' => 0,
                    ];
                }

                $ingredientRequirements[$productId]['quantity'] += $requiredQuantity;
            }
        }

        // Check stock availability
        foreach ($ingredientRequirements as $requirement) {
            $product = $requirement['product'];
            $requiredQuantity = $requirement['quantity'];

            if ($requiredQuantity > $product->stock_quantity) {
                throw new RuntimeException("Not enough {$product->name} in inventory. Required: {$requiredQuantity} {$product->unit}, Available: {$product->stock_quantity} {$product->unit}");
            }
        }

        // Decrease stock and create movements
        foreach ($ingredientRequirements as $requirement) {
            $product = $requirement['product'];
            $requiredQuantity = $requirement['quantity'];

            if ($requiredQuantity <= 0) {
                continue;
            }

            $product->decrement('stock_quantity', $requiredQuantity);

            StockMovement::create([
                'product_id' => $product->id,
                'type' => 'out',
                'quantity' => $requiredQuantity,
                'unit_cost' => $product->purchase_price,
                'reference' => "Order #{$order->id}",
                'notes' => "Ingredients consumed for menu items in order #{$order->id}",
            ]);
        }
    }

    public function recalculateOrderTotal(Order $order): float
    {
        $baseTotal = (float) $order->items()->sum('line_total');
        $adjustmentTotal = (float) $order->adjustments()
            ->get()
            ->sum(function (OrderAdjustment $adjustment) {
                return $adjustment->type === 'discount'
                    ? (float) $adjustment->amount * -1
                    : (float) $adjustment->amount;
            });

        $order->update(['total' => max(0, $baseTotal + $adjustmentTotal)]);

        return (float) $order->total;
    }
}
