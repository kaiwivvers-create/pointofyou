<?php

namespace App\Http\Controllers;

use App\Models\Order;
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
        
        $totalIncome = $orders->sum('total');
        $totalOrders = $orders->count();
        $averageOrderValue = $totalOrders > 0 ? $totalIncome / $totalOrders : 0;
        
        // For now, outcome is 0 - you can add expenses tracking later
        $totalOutcome = 0;
        $netProfit = $totalIncome - $totalOutcome;
        
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
        ]);
    }
    
    private function getChartData(string $period): array
    {
        if ($period === 'year') {
            // Monthly data for current year - SQLite compatible
            $monthlyData = Order::where('status', 'paid')
                ->whereRaw("strftime('%Y', paid_at) = ?", [now()->year])
                ->selectRaw("strftime('%m', paid_at) as month, SUM(total) as total")
                ->groupBy('month')
                ->orderBy('month')
                ->get();
            
            $labels = [];
            $data = [];
            for ($i = 1; $i <= 12; $i++) {
                $monthNum = str_pad($i, 2, '0', STR_PAD_LEFT);
                $monthData = $monthlyData->firstWhere('month', $monthNum);
                $labels[] = now()->month($i)->format('M');
                $data[] = $monthData ? $monthData->total : 0;
            }
            
            return [
                'labels' => $labels,
                'data' => $data,
                'label' => 'Monthly Income',
            ];
        } else {
            // Daily data for current month - SQLite compatible
            $dailyData = Order::where('status', 'paid')
                ->whereRaw("strftime('%Y', paid_at) = ?", [now()->year])
                ->whereRaw("strftime('%m', paid_at) = ?", [str_pad(now()->month, 2, '0', STR_PAD_LEFT)])
                ->selectRaw("strftime('%d', paid_at) as day, SUM(total) as total")
                ->groupBy('day')
                ->orderBy('day')
                ->get();
            
            $labels = [];
            $data = [];
            $daysInMonth = now()->daysInMonth;
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $dayNum = str_pad($i, 2, '0', STR_PAD_LEFT);
                $dayData = $dailyData->firstWhere('day', $dayNum);
                $labels[] = $i;
                $data[] = $dayData ? $dayData->total : 0;
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
        $totalOutcome = 0;
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
