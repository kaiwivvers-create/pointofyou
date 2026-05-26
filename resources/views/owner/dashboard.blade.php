@extends('layouts.staff')

@section('title', 'Owner Dashboard')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Owner Dashboard</h1>
            <p class="staff-page-subtitle">Overview of your cafe performance.</p>
        </div>
    </div>

    <x-flash />

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="staff-card p-6">
            <p class="text-sm text-slate-500 mb-1">Total Revenue</p>
            <p class="text-3xl font-bold text-slate-900">${{ number_format($totalRevenue, 2) }}</p>
        </div>
        <div class="staff-card p-6">
            <p class="text-sm text-slate-500 mb-1">Total Orders</p>
            <p class="text-3xl font-bold text-slate-900">{{ $totalOrders }}</p>
        </div>
        <div class="staff-card p-6">
            <p class="text-sm text-slate-500 mb-1">Menu Items</p>
            <p class="text-3xl font-bold text-slate-900">{{ $menuCount }}</p>
        </div>
        <div class="staff-card p-6">
            <p class="text-sm text-slate-500 mb-1">Tables</p>
            <p class="text-3xl font-bold text-slate-900">{{ $tableCount }}</p>
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
        <div class="h-80">
            <canvas id="incomeChart"></canvas>
        </div>
    </div>

    <script>
        let chart = null;
        
        function updateChart() {
            const chartType = document.querySelector('select[name="chart_type"]').value;
            initChart(chartType);
        }
        
        function initChart(chartType = 'bar') {
            const ctx = document.getElementById('incomeChart').getContext('2d');
            const chartData = {{ json_encode($monthlyChartData) }};
            
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
            
            chart = new Chart(ctx, config);
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            initChart();
        });
    </script>
@endsection
