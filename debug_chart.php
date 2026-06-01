<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Order;

echo "Debugging manager dashboard chart data...\n";

// Monthly chart data
$monthlyData = Order::where('status', 'paid')
    ->whereRaw("strftime('%Y', paid_at) = ?", [now()->year])
    ->selectRaw("strftime('%m', paid_at) as month, SUM(total) as total")
    ->groupBy('month')
    ->orderBy('month')
    ->get();

echo "\nMonthly data for " . now()->year . ":\n";
foreach ($monthlyData as $data) {
    echo "  Month {$data->month}: \${$data->total}\n";
}

$monthlyChartData = [];
for ($i = 1; $i <= 12; $i++) {
    $monthNum = str_pad($i, 2, '0', STR_PAD_LEFT);
    $monthData = $monthlyData->firstWhere('month', $monthNum);
    $monthlyChartData['labels'][] = now()->month($i)->format('M');
    $monthlyChartData['data'][] = $monthData ? floatval($monthData->total) : 0;
}

echo "\nChart data:\n";
echo "Labels: " . implode(', ', $monthlyChartData['labels']) . "\n";
echo "Data: " . implode(', ', $monthlyChartData['data']) . "\n";
