<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OperationalExpense;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsController extends Controller
{
    public function index(Request $request): View
    {
        $period = $request->get('period', 'today'); // today, week, month, year, custom
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $chartPeriod = $request->get('chart_period', 'year'); // year, month
        $chartType = $request->get('chart_type', 'bar'); // bar, pie
        
        $query = Order::where('status', 'paid');
        
        // Apply date filtering
        switch ($period) {
            case 'today':
                $query->whereDate('paid_at', today());
                break;
            case 'week':
                $query->whereBetween('paid_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereYear('paid_at', now()->year)
                      ->whereMonth('paid_at', now()->month);
                break;
            case 'year':
                $query->whereYear('paid_at', now()->year);
                break;
            case 'custom':
                if ($startDate && $endDate) {
                    $query->whereBetween('paid_at', [$startDate, $endDate]);
                }
                break;
        }
        
        $orders = $query->get();
        $expenses = $this->buildExpenseQuery($period, $startDate, $endDate)->get();
        
        $totalIncome = $orders->sum('total');
        $totalOrders = $orders->count();
        $averageOrderValue = $totalOrders > 0 ? $totalIncome / $totalOrders : 0;
        $totalOutcome = $expenses->sum('amount');
        $netProfit = $totalIncome - $totalOutcome;

        // Additional comprehensive statistics
        $orderIds = $orders->pluck('id');
        
        // Items ordered count
        $totalItemsOrdered = \App\Models\OrderItem::whereIn('order_id', $orderIds)->sum('quantity');
        
        // Most popular items
        $popularItems = \App\Models\OrderItem::select('menu_item_id', \DB::raw('SUM(quantity) as total_quantity'))
            ->whereIn('order_id', $orderIds)
            ->with('menuItem')
            ->groupBy('menu_item_id')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();
        
        // Takeout supplies used
        $takeoutBoxProduct = \App\Models\Product::where('name', 'Takeout Box')->first();
        $takeoutSuppliesUsed = 0;
        if ($takeoutBoxProduct) {
            $takeoutSuppliesUsed = \App\Models\StockMovement::where('product_id', $takeoutBoxProduct->id)
                ->where('type', 'out')
                ->whereBetween('created_at', [$this->getStartDate($period, $startDate), $this->getEndDate($period, $endDate)])
                ->sum('quantity');
        }
        
        // Stock costs and purchases
        $stockPurchases = \App\Models\StockMovement::where('type', 'in')
            ->whereBetween('created_at', [$this->getStartDate($period, $startDate), $this->getEndDate($period, $endDate)])
            ->get();
        $totalStockCost = $stockPurchases->sum(function($movement) {
            return $movement->quantity * $movement->unit_cost;
        });
        $totalStockQuantity = $stockPurchases->sum('quantity');
        
        // Payroll costs
        $payrollCosts = \App\Models\SalaryPayment::whereBetween('payment_date', [$this->getStartDate($period, $startDate), $this->getEndDate($period, $endDate)])
            ->sum('amount');
        
        // Chart data
        $chartData = $this->getChartData($chartPeriod);
        
        return view('reports.index', [
            'period' => $period,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalIncome' => $totalIncome,
            'totalOutcome' => $totalOutcome,
            'netProfit' => $netProfit,
            'totalOrders' => $totalOrders,
            'averageOrderValue' => $averageOrderValue,
            'orders' => $orders->take(10),
            'chartPeriod' => $chartPeriod,
            'chartType' => $chartType,
            'chartData' => $chartData,
            'totalItemsOrdered' => $totalItemsOrdered,
            'popularItems' => $popularItems,
            'takeoutSuppliesUsed' => $takeoutSuppliesUsed,
            'totalStockCost' => $totalStockCost,
            'totalStockQuantity' => $totalStockQuantity,
            'payrollCosts' => $payrollCosts,
        ]);
    }

    private function getStartDate(string $period, ?string $startDate): string
    {
        switch ($period) {
            case 'today':
                return now()->startOfDay()->toDateTimeString();
            case 'week':
                return now()->startOfWeek()->toDateTimeString();
            case 'month':
                return now()->startOfMonth()->toDateTimeString();
            case 'year':
                return now()->startOfYear()->toDateTimeString();
            case 'custom':
                return $startDate ? \Carbon\Carbon::parse($startDate)->startOfDay()->toDateTimeString() : now()->startOfDay()->toDateTimeString();
            default:
                return now()->startOfDay()->toDateTimeString();
        }
    }

    private function getEndDate(string $period, ?string $endDate): string
    {
        switch ($period) {
            case 'today':
                return now()->endOfDay()->toDateTimeString();
            case 'week':
                return now()->endOfWeek()->toDateTimeString();
            case 'month':
                return now()->endOfMonth()->toDateTimeString();
            case 'year':
                return now()->endOfYear()->toDateTimeString();
            case 'custom':
                return $endDate ? \Carbon\Carbon::parse($endDate)->endOfDay()->toDateTimeString() : now()->endOfDay()->toDateTimeString();
            default:
                return now()->endOfDay()->toDateTimeString();
        }
    }
    
    private function getChartData(string $period): array
    {
        if ($period === 'year') {
            // Monthly data for current year - SQLite compatible
            $monthlyData = Order::where('status', 'paid')
                ->whereYear('paid_at', now()->year)
                ->selectRaw("CAST(strftime('%m', paid_at) AS INTEGER) as month, SUM(total) as total")
                ->groupBy('month')
                ->orderBy('month')
                ->get();
            
            $labels = [];
            $data = [];
            $monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            for ($i = 1; $i <= 12; $i++) {
                $monthData = $monthlyData->firstWhere('month', $i);
                $labels[] = $monthLabels[$i - 1];
                $data[] = $monthData ? (float) $monthData->total : 0;
            }
            
            return [
                'labels' => $labels,
                'data' => $data,
                'label' => 'Monthly Income',
            ];
        } else {
            // Daily data for current month - SQLite compatible
            $dailyData = Order::where('status', 'paid')
                ->whereYear('paid_at', now()->year)
                ->whereMonth('paid_at', now()->month)
                ->selectRaw("CAST(strftime('%d', paid_at) AS INTEGER) as day, SUM(total) as total")
                ->groupBy('day')
                ->orderBy('day')
                ->get();
            
            $labels = [];
            $data = [];
            $daysInMonth = now()->daysInMonth;
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $dayData = $dailyData->firstWhere('day', $i);
                $labels[] = $i;
                $data[] = $dayData ? (float) $dayData->total : 0;
            }
            
            return [
                'labels' => $labels,
                'data' => $data,
                'label' => 'Daily Income',
            ];
        }
    }
    
    public function exportCsv(Request $request): StreamedResponse
    {
        $period = $request->get('period', 'today');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $query = Order::where('status', 'paid');
        
        switch ($period) {
            case 'today':
                $query->whereDate('paid_at', today());
                break;
            case 'week':
                $query->whereBetween('paid_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereYear('paid_at', now()->year)
                      ->whereMonth('paid_at', now()->month);
                break;
            case 'year':
                $query->whereYear('paid_at', now()->year);
                break;
            case 'custom':
                if ($startDate && $endDate) {
                    $query->whereBetween('paid_at', [$startDate, $endDate]);
                }
                break;
        }
        
        $orders = $query->get();
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="report-' . $period . '-' . now()->format('Y-m-d') . '.csv"',
        ];
        
        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Order ID', 'Table', 'Date', 'Total', 'Status']);
            
            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->id,
                    $order->cafeTable->name,
                    $order->paid_at ? $order->paid_at->format('Y-m-d H:i:s') : '',
                    $order->total,
                    $order->status->value,
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    private function buildExpenseQuery(string $period, ?string $startDate, ?string $endDate)
    {
        $query = OperationalExpense::query()->where('source', 'auto_stock_purchase');

        switch ($period) {
            case 'today':
                $query->whereDate('expense_date', today());
                break;
            case 'week':
                $query->whereBetween('expense_date', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereYear('expense_date', now()->year)
                      ->whereMonth('expense_date', now()->month);
                break;
            case 'year':
                $query->whereYear('expense_date', now()->year);
                break;
            case 'custom':
                if ($startDate && $endDate) {
                    $query->whereBetween('expense_date', [$startDate, $endDate]);
                }
                break;
        }

        return $query;
    }
    
    public function exportPdf(Request $request)
    {
        // For PDF export, redirect to print view
        return redirect()->route('reports.print', $request->all());
    }
    
    public function printView(Request $request): View
    {
        $period = $request->get('period', 'today');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $query = Order::where('status', 'paid');
        
        switch ($period) {
            case 'today':
                $query->whereDate('paid_at', today());
                break;
            case 'week':
                $query->whereBetween('paid_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereRaw("strftime('%Y', paid_at) = ?", [now()->year])
                      ->whereRaw("strftime('%m', paid_at) = ?", [str_pad(now()->month, 2, '0', STR_PAD_LEFT)]);
                break;
            case 'year':
                $query->whereRaw("strftime('%Y', paid_at) = ?", [now()->year]);
                break;
            case 'custom':
                if ($startDate && $endDate) {
                    $query->whereBetween('paid_at', [$startDate, $endDate]);
                }
                break;
        }
        
        $orders = $query->get();
        
        $totalIncome = $orders->sum('total');
        $totalOrders = $orders->count();
        $averageOrderValue = $totalOrders > 0 ? $totalIncome / $totalOrders : 0;
        $totalOutcome = $this->buildExpenseQuery($period, $startDate, $endDate)->sum('amount');
        $netProfit = $totalIncome - $totalOutcome;
        
        return view('reports.print', [
            'period' => $period,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalIncome' => $totalIncome,
            'totalOutcome' => $totalOutcome,
            'netProfit' => $netProfit,
            'totalOrders' => $totalOrders,
            'averageOrderValue' => $averageOrderValue,
            'orders' => $orders->take(10),
        ]);
    }
}
