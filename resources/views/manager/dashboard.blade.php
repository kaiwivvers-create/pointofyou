@extends('layouts.staff')

@section('title', 'Manager Dashboard')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Manager Dashboard</h1>
            <p class="staff-page-subtitle">Daily performance and operations.</p>
        </div>
    </div>

    <x-flash />

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="staff-card p-6">
            <p class="text-sm text-slate-500 mb-1">Today's Revenue</p>
            <p class="text-3xl font-bold text-slate-900">${{ number_format($todayRevenue, 2) }}</p>
        </div>
        <div class="staff-card p-6">
            <p class="text-sm text-slate-500 mb-1">Today's Orders</p>
            <p class="text-3xl font-bold text-slate-900">{{ $todayOrders }}</p>
        </div>
        <div class="staff-card p-6">
            <p class="text-sm text-slate-500 mb-1">Pending Orders</p>
            <p class="text-3xl font-bold text-slate-900">{{ $pendingOrders }}</p>
        </div>
        <div class="staff-card p-6">
            <p class="text-sm text-slate-500 mb-1">Menu Items</p>
            <p class="text-3xl font-bold text-slate-900">{{ $menuCount }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
        <div class="staff-card p-6">
            <p class="text-sm text-slate-500 mb-1">Today's Income</p>
            <p class="text-3xl font-bold text-emerald-600">${{ number_format($todayIncome, 2) }}</p>
        </div>
        <div class="staff-card p-6">
            <p class="text-sm text-slate-500 mb-1">This Month's Income</p>
            <p class="text-3xl font-bold text-slate-900">${{ number_format($thisMonthIncome, 2) }}</p>
        </div>
        <div class="staff-card p-6">
            <p class="text-sm text-slate-500 mb-1">Yesterday's Income</p>
            <p class="text-3xl font-bold text-slate-900">${{ number_format($yesterdayIncome, 2) }}</p>
        </div>
        <div class="staff-card p-6">
            <p class="text-sm text-slate-500 mb-1">Last Month's Income</p>
            <p class="text-3xl font-bold text-slate-900">${{ number_format($lastMonthIncome, 2) }}</p>
        </div>
    </div>

    <!-- Chart -->
    <div class="staff-card p-6 mb-8">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold text-slate-900">Monthly Income (This Year)</h2>
            <select name="chart_type" class="staff-input w-48" onchange="updateChart()">
                <option value="bar">Bar Chart</option>
                <option value="pie">Pie Chart</option>
            </select>
        </div>
        <div class="h-80 relative bg-slate-50 rounded-lg border border-slate-200">
            <canvas id="incomeChart"></canvas>
            <div id="chartError" class="hidden absolute inset-0 flex items-center justify-center bg-slate-50 rounded-lg">
                <p class="text-slate-500">Chart could not be loaded. Please check browser console for errors.</p>
            </div>
            <div id="chartEmpty" class="hidden absolute inset-0 flex items-center justify-center bg-slate-50 rounded-lg">
                <p class="text-slate-500">No income data for this year yet.</p>
            </div>
        </div>
    </div>

    <!-- Low Stock Alerts -->
    <div class="staff-card p-6 mb-8">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Low Stock Alerts</h2>
        @if($lowStockProducts->count() > 0)
            <div class="space-y-3">
                @foreach($lowStockProducts as $product)
                    <div class="flex items-center justify-between p-3 bg-red-50 border border-red-200 rounded-lg">
                        <div>
                            <p class="font-medium text-slate-900">{{ $product->name }}</p>
                            <p class="text-sm text-slate-600">
                                @if($product->stock_quantity <= 0)
                                    <span class="text-red-600 font-semibold">Out of stock!</span>
                                @else
                                    Only {{ $product->stock_quantity }} {{ $product->unit }} left (min: {{ $product->min_stock_level }})
                                @endif
                            </p>
                        </div>
                        <span class="text-xs text-slate-500">{{ $product->category->name ?? 'No category' }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-slate-500">All products are well stocked!</p>
        @endif
    </div>

    <!-- Expense Tracking -->
    <div class="staff-card p-6 mb-8">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Expense Tracking (This Month)</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="p-4 bg-slate-50 rounded-lg">
                <p class="text-sm text-slate-500 mb-1">Operational Expenses</p>
                <p class="text-2xl font-bold text-slate-900">${{ number_format($thisMonthExpenses, 2) }}</p>
            </div>
        </div>
        <div class="mt-4 p-4 rounded-lg border {{ $thisMonthIncome - $thisMonthExpenses < 0 ? 'bg-red-50 border-red-200' : 'bg-emerald-50 border-emerald-200' }}">
            <p class="text-sm text-slate-500 mb-1">Net Profit (Income - Expenses)</p>
            <p class="text-2xl font-bold {{ $thisMonthIncome - $thisMonthExpenses < 0 ? 'text-red-600' : 'text-emerald-600' }}">
                ${{ number_format($thisMonthIncome - $thisMonthExpenses, 2) }}
            </p>
        </div>
    </div>

    <script>
        console.log('Chart.js loaded:', typeof Chart !== 'undefined');
        console.log('Chart object:', Chart);
        
        let chart = null;
        
        function updateChart() {
            const chartType = document.querySelector('select[name="chart_type"]');
            if (chartType) {
                initChart(chartType.value);
            }
        }
        
        function initChart(chartType = 'bar') {
            console.log('initChart called with type:', chartType);
            
            const canvas = document.getElementById('incomeChart');
            console.log('Canvas element:', canvas);
            
            if (!canvas) {
                console.error('Chart canvas not found');
                return;
            }
            
            const ctx = canvas.getContext('2d');
            console.log('Canvas context:', ctx);
            
            if (!ctx) {
                console.error('Could not get 2D context from canvas');
                return;
            }
            
            const chartData = {{ json_encode($monthlyChartData) }};
            console.log('Chart data:', chartData);
            
            // Remove empty state logic - always try to render the chart
            const emptyDiv = document.getElementById('chartEmpty');
            const canvasEl = document.getElementById('incomeChart');
            if (emptyDiv) {
                emptyDiv.classList.add('hidden');
            }
            if (canvasEl) {
                canvasEl.style.display = 'block';
            }
            
            if (chart) {
                chart.destroy();
            }
            
            const config = {
                type: chartType === 'pie' ? 'doughnut' : 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Monthly Income',
                        data: chartData.data,
                        backgroundColor: chartType === 'pie' 
                            ? [
                                '#10b981', '#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b',
                                '#ef4444', '#06b6d4', '#84cc16', '#f97316', '#6366f1',
                                '#14b8a6', '#a855f7'
                            ]
                            : '#10b981',
                        borderColor: chartType === 'pie' 
                            ? '#ffffff'
                            : '#059669',
                        borderWidth: chartType === 'pie' ? 2 : 1,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: chartType === 'pie',
                            position: 'right',
                        },
                        title: {
                            display: false,
                        }
                    },
                    scales: chartType === 'pie' ? {} : {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toFixed(2);
                                }
                            }
                        }
                    }
                }
            };
            
            try {
                console.log('Creating chart with config:', config);
                chart = new Chart(ctx, config);
                console.log('Chart initialized successfully');
            } catch (error) {
                console.error('Error initializing chart:', error);
                const errorDiv = document.getElementById('chartError');
                if (errorDiv) {
                    errorDiv.classList.remove('hidden');
                }
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing chart');
            initChart();
        });
    </script>
@endsection
