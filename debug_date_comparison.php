<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Order;

echo "Debugging date comparison...\n";

// Check paid_at dates
$paidOrders = Order::where('status', 'paid')->whereNotNull('paid_at')->get();
echo "\nPaid orders:\n";
foreach ($paidOrders as $order) {
    $year = date('Y', strtotime($order->paid_at));
    $month = date('m', strtotime($order->paid_at));
    echo "  - Order #{$order->id}: \${$order->total} paid at {$order->paid_at} (year: {$year}, month: {$month})\n";
}

// Test the query
echo "\nTesting this month query...\n";
echo "Current year: " . now()->year . "\n";
echo "Current month: " . str_pad(now()->month, 2, '0', STR_PAD_LEFT) . "\n";

$thisMonthIncome = Order::where('status', 'paid')
    ->whereRaw("strftime('%Y', paid_at) = ?", [now()->year])
    ->whereRaw("strftime('%m', paid_at) = ?", [str_pad(now()->month, 2, '0', STR_PAD_LEFT)])
    ->get();

echo "Orders matching this month query: {$thisMonthIncome->count()}\n";
foreach ($thisMonthIncome as $order) {
    echo "  - Order #{$order->id}: \${$order->total} paid at {$order->paid_at}\n";
}

// Try a simpler query
echo "\nTrying simpler query...\n";
$simpleQuery = Order::where('status', 'paid')
    ->whereYear('paid_at', now()->year)
    ->whereMonth('paid_at', now()->month)
    ->get();

echo "Orders matching simpler query: {$simpleQuery->count()}\n";
foreach ($simpleQuery as $order) {
    echo "  - Order #{$order->id}: \${$order->total} paid at {$order->paid_at}\n";
}
