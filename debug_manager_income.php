<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Order;

echo "Debugging manager dashboard income...\n";

// Check orders
$allOrders = Order::count();
echo "\nTotal orders: {$allOrders}\n";

$paidOrders = Order::where('status', 'paid')->count();
echo "Paid orders: {$paidOrders}\n";

// Today's income
$todayIncome = Order::where('status', 'paid')->whereDate('paid_at', today())->sum('total');
echo "Today's income: \${$todayIncome}\n";

// This month income
$thisMonthIncome = Order::where('status', 'paid')
    ->whereRaw("strftime('%Y', paid_at) = ?", [now()->year])
    ->whereRaw("strftime('%m', paid_at) = ?", [str_pad(now()->month, 2, '0', STR_PAD_LEFT)])
    ->sum('total');
echo "This month income: \${$thisMonthIncome}\n";

// Yesterday income
$yesterdayIncome = Order::where('status', 'paid')->whereDate('paid_at', now()->subDay())->sum('total');
echo "Yesterday income: \${$yesterdayIncome}\n";

// Last month income
$lastMonthIncome = Order::where('status', 'paid')
    ->whereRaw("strftime('%Y', paid_at) = ?", [now()->subMonth()->year])
    ->whereRaw("strftime('%m', paid_at) = ?", [str_pad(now()->subMonth()->month, 2, '0', STR_PAD_LEFT)])
    ->sum('total');
echo "Last month income: \${$lastMonthIncome}\n";

// Check paid_at dates
$paidOrdersWithDates = Order::where('status', 'paid')->whereNotNull('paid_at')->limit(5)->get();
echo "\nRecent paid orders:\n";
foreach ($paidOrdersWithDates as $order) {
    echo "  - Order #{$order->id}: \${$order->total} paid at {$order->paid_at}\n";
}
