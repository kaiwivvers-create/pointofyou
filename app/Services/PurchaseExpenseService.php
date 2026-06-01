<?php

namespace App\Services;

use App\Models\ExpenseCategory;
use App\Models\OperationalExpense;
use App\Models\Product;

class PurchaseExpenseService
{
    public function record(Product $product, int $quantity, ?string $reference, string $itemType, ?string $notes = null): void
    {
        $expenseCategory = ExpenseCategory::firstOrCreate(
            ['name' => $itemType === 'supply' ? 'Takeout Supplies' : 'Inventory Purchases'],
            ['description' => 'Automatic expenses from stock purchases', 'color' => '#6366f1']
        );

        OperationalExpense::create([
            'expense_category_id' => $expenseCategory->id,
            'product_id' => $product->id,
            'source' => 'auto_stock_purchase',
            'item_type' => $itemType,
            'quantity' => $quantity,
            'title' => $product->name . ' x' . $quantity,
            'description' => $notes,
            'amount' => $product->purchase_price * $quantity,
            'expense_date' => now()->toDateString(),
            'reference' => $reference,
            'status' => 'approved',
            'notes' => $notes,
        ]);
    }
}
