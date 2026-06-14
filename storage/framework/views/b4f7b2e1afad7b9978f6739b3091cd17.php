<?php $__env->startSection('title', 'Reports'); ?>

<?php
    $user = auth()->user();
    $userPermissions = [];
    if ($user) {
        $userPermissions = \App\Models\Permission::where('role', $user->role->value)
            ->get()
            ->keyBy('permission');
    }
    
    $can = function($permission, $action = 'view') use ($user, $userPermissions) {
        if (!$user) return false;
        if ($user->isSuperAdmin()) return true;
        $perm = $userPermissions->get($permission);
        if (!$perm) return false;
        return $action === 'edit' ? $perm->can_edit : $perm->can_view;
    };
?>

<?php $__env->startSection('content'); ?>
    <div id="print-content">
    <div class="print-header">
        <h1 class="text-2xl font-bold text-slate-900">Financial Report</h1>
        <p class="text-slate-600">Generated on <?php echo e(now()->format('F j, Y H:i')); ?></p>
        <p class="text-slate-500 text-sm mt-1">
            <?php if($period === 'today'): ?>
                Period: Today (<?php echo e(now()->format('F j, Y')); ?>)
            <?php elseif($period === 'week'): ?>
                Period: This Week (<?php echo e(now()->startOfWeek()->format('M j')); ?> - <?php echo e(now()->endOfWeek()->format('M j, Y')); ?>)
            <?php elseif($period === 'month'): ?>
                Period: This Month (<?php echo e(now()->format('F Y')); ?>)
            <?php elseif($period === 'year'): ?>
                Period: This Year (<?php echo e(now()->format('Y')); ?>)
            <?php elseif($period === 'custom' && $startDate && $endDate): ?>
                Period: <?php echo e(\Carbon\Carbon::parse($startDate)->format('M j, Y')); ?> - <?php echo e(\Carbon\Carbon::parse($endDate)->format('M j, Y')); ?>

            <?php endif; ?>
        </p>
    </div>

    <div class="staff-page-header no-print">
        <div>
            <h1 class="staff-page-title">Reports</h1>
            <p class="staff-page-subtitle">Income, outcome, and financial analytics.</p>
        </div>
        <div class="flex gap-2 no-print">
            <a href="<?php echo e(route('reports.export.csv', request()->all())); ?>" class="staff-btn-secondary no-print">Export CSV</a>
            <button onclick="window.open('<?php echo e(route('reports.print', request()->all())); ?>', '_blank')" class="staff-btn-secondary no-print">Print PDF</button>
        </div>
    </div>

    <?php if (isset($component)) { $__componentOriginal5168fdb0c14fd91c6598264bc4be63f2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5168fdb0c14fd91c6598264bc4be63f2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.flash','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flash'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5168fdb0c14fd91c6598264bc4be63f2)): ?>
<?php $attributes = $__attributesOriginal5168fdb0c14fd91c6598264bc4be63f2; ?>
<?php unset($__attributesOriginal5168fdb0c14fd91c6598264bc4be63f2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5168fdb0c14fd91c6598264bc4be63f2)): ?>
<?php $component = $__componentOriginal5168fdb0c14fd91c6598264bc4be63f2; ?>
<?php unset($__componentOriginal5168fdb0c14fd91c6598264bc4be63f2); ?>
<?php endif; ?>

    <!-- Chart Controls -->
    <div class="staff-card p-6 mb-6 no-print">
        <div class="flex flex-col sm:flex-row gap-4 items-center">
            <div class="flex-1">
                <label class="block text-sm font-medium text-slate-700 mb-1">Chart Period</label>
                <select name="chart_period" class="staff-input" onchange="updateChart()">
                    <option value="year" <?php echo e($chartPeriod === 'year' ? 'selected' : ''); ?>>Yearly (Monthly)</option>
                    <option value="month" <?php echo e($chartPeriod === 'month' ? 'selected' : ''); ?>>Monthly (Daily)</option>
                </select>
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-slate-700 mb-1">Chart Type</label>
                <select name="chart_type" class="staff-input" onchange="updateChart()">
                    <option value="bar" <?php echo e($chartType === 'bar' ? 'selected' : ''); ?>>Bar Chart</option>
                    <option value="pie" <?php echo e($chartType === 'pie' ? 'selected' : ''); ?>>Pie Chart</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Chart -->
    <div class="staff-card p-6 mb-6 chart-section no-print">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">
            <?php echo e($chartPeriod === 'year' ? 'Monthly Income (This Year)' : 'Daily Income (This Month)'); ?>

        </h2>
        <div class="h-80">
            <canvas id="incomeChart"></canvas>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="staff-card p-6 mb-6 no-print">
        <form method="GET" action="<?php echo e(route('reports.index')); ?>" class="flex flex-col sm:flex-row gap-4 no-print">
            <div class="flex-1">
                <label class="block text-sm font-medium text-slate-700 mb-1">Period</label>
                <select name="period" class="staff-input" onchange="toggleCustomDates()">
                    <option value="today" <?php echo e($period === 'today' ? 'selected' : ''); ?>>Today</option>
                    <option value="week" <?php echo e($period === 'week' ? 'selected' : ''); ?>>This Week</option>
                    <option value="month" <?php echo e($period === 'month' ? 'selected' : ''); ?>>This Month</option>
                    <option value="year" <?php echo e($period === 'year' ? 'selected' : ''); ?>>This Year</option>
                    <option value="custom" <?php echo e($period === 'custom' ? 'selected' : ''); ?>>Custom Range</option>
                </select>
            </div>
            <div id="customDates" class="flex gap-4 <?php echo e($period !== 'custom' ? 'hidden' : ''); ?>">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Start Date</label>
                    <input type="date" name="start_date" value="<?php echo e($startDate ?? ''); ?>" class="staff-input">
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-slate-700 mb-1">End Date</label>
                    <input type="date" name="end_date" value="<?php echo e($endDate ?? ''); ?>" class="staff-input">
                </div>
            </div>
            <div class="flex items-end">
                <button type="submit" class="staff-btn-primary">Apply Filter</button>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="staff-card p-6">
            <p class="text-sm text-slate-500 mb-1">Total Income</p>
            <p class="text-3xl font-bold text-emerald-600">$<?php echo e(number_format($totalIncome, 2)); ?></p>
        </div>
        <div class="staff-card p-6">
            <p class="text-sm text-slate-500 mb-1">Total Outcome</p>
            <p class="text-3xl font-bold text-red-600">$<?php echo e(number_format($totalOutcome, 2)); ?></p>
        </div>
        <div class="staff-card p-6">
            <p class="text-sm text-slate-500 mb-1">Net Profit</p>
            <p class="text-3xl font-bold <?php echo e($netProfit >= 0 ? 'text-slate-900' : 'text-red-600'); ?>">$<?php echo e(number_format($netProfit, 2)); ?></p>
        </div>
        <div class="staff-card p-6">
            <p class="text-sm text-slate-500 mb-1">Total Orders</p>
            <p class="text-3xl font-bold text-slate-900"><?php echo e($totalOrders); ?></p>
        </div>
    </div>

    <!-- Additional Statistics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="staff-card p-6">
            <p class="text-sm text-slate-500 mb-1">Items Ordered</p>
            <p class="text-2xl font-bold text-slate-900"><?php echo e($totalItemsOrdered); ?></p>
        </div>
        <div class="staff-card p-6">
            <p class="text-sm text-slate-500 mb-1">Takeout Supplies Used</p>
            <p class="text-2xl font-bold text-slate-900"><?php echo e($takeoutSuppliesUsed); ?></p>
        </div>
        <div class="staff-card p-6">
            <p class="text-sm text-slate-500 mb-1">Stock Cost</p>
            <p class="text-2xl font-bold text-red-600">$<?php echo e(number_format($totalStockCost, 2)); ?></p>
        </div>
        <div class="staff-card p-6">
            <p class="text-sm text-slate-500 mb-1">Stock Purchased</p>
            <p class="text-2xl font-bold text-slate-900"><?php echo e($totalStockQuantity); ?> units</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
        <div class="staff-card p-6">
            <p class="text-sm text-slate-500 mb-1">Payroll Costs</p>
            <p class="text-2xl font-bold text-red-600">$<?php echo e(number_format($payrollCosts, 2)); ?></p>
        </div>
        <div class="staff-card p-6">
            <p class="text-sm text-slate-500 mb-1">Average Order Value</p>
            <p class="text-2xl font-bold text-slate-900">$<?php echo e(number_format($averageOrderValue, 2)); ?></p>
        </div>
    </div>

    <!-- Popular Items -->
    <div class="staff-card p-6 mb-6">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Most Popular Items</h2>
        <div class="staff-table-wrap">
            <div class="overflow-x-auto">
                <table class="staff-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th class="text-right">Quantity Ordered</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $popularItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="font-semibold text-slate-900"><?php echo e($item->menuItem?->name ?? 'Unknown'); ?></td>
                                <td class="text-right font-semibold text-emerald-600"><?php echo e($item->total_quantity); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="2" class="py-8 text-center text-slate-500">No items ordered in this period</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="staff-card p-6">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Recent Orders</h2>
        <div class="staff-table-wrap">
            <div class="overflow-x-auto">
                <table class="staff-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Table</th>
                            <th>Date</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="font-semibold text-slate-900">#<?php echo e($order->id); ?></td>
                                <td><?php echo e($order->cafeTable->name); ?></td>
                                <td><?php echo e($order->paid_at ? $order->paid_at->format('M d, Y H:i') : '-'); ?></td>
                                <td class="text-right font-semibold text-emerald-600">$<?php echo e(number_format($order->total, 2)); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-500">No orders in this period</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>

    <script>
        let chart = null;
        
        function toggleCustomDates() {
            const period = document.querySelector('select[name="period"]').value;
            const customDates = document.getElementById('customDates');
            if (period === 'custom') {
                customDates.classList.remove('hidden');
            } else {
                customDates.classList.add('hidden');
            }
        }
        
        function updateChart() {
            const chartPeriod = document.querySelector('select[name="chart_period"]').value;
            const chartType = document.querySelector('select[name="chart_type"]').value;
            
            // Update URL with new parameters
            const url = new URL(window.location);
            url.searchParams.set('chart_period', chartPeriod);
            url.searchParams.set('chart_type', chartType);
            window.location.href = url.toString();
        }
        
        function initChart() {
            const ctx = document.getElementById('incomeChart').getContext('2d');
            const chartPeriod = '<?php echo e($chartPeriod); ?>';
            const chartType = '<?php echo e($chartType); ?>';
            const chartData = <?php echo \Illuminate\Support\Js::from($chartData)->toHtml() ?>;
            
            if (chart) {
                chart.destroy();
            }
            
            const config = {
                type: chartType === 'pie' ? 'doughnut' : 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: chartData.label,
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
        
        function printReport() {
            // Hide elements before printing
            const noPrintElements = document.querySelectorAll('.no-print');
            noPrintElements.forEach(el => el.style.display = 'none');
            
            const aside = document.querySelector('aside');
            if (aside) aside.style.display = 'none';
            
            const alerts = document.querySelectorAll('.alert, .flash-message');
            alerts.forEach(el => el.style.display = 'none');
            
            // Show print header
            const printHeader = document.querySelector('.print-header');
            if (printHeader) printHeader.style.display = 'block';
            
            // Print
            window.print();
            
            // Restore elements after printing
            setTimeout(() => {
                noPrintElements.forEach(el => el.style.display = '');
                if (aside) aside.style.display = '';
                alerts.forEach(el => el.style.display = '');
                if (printHeader) printHeader.style.display = 'none';
            }, 500);
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            initChart();
        });
    </script>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    body.printing aside,
    body.printing .staff-page-header,
    body.printing .no-print,
    body.printing form,
    body.printing .alert,
    body.printing .flash-message,
    body.printing .chart-section {
        display: none !important;
    }
    
    body.printing .print-header {
        display: block !important;
        margin-bottom: 30px;
        text-align: center;
    }
    
    body.printing main,
    body.printing .max-w-7xl,
    body.printing .container {
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    
    body.printing .staff-card {
        border: 1px solid #ddd;
        box-shadow: none;
        page-break-inside: avoid;
        margin-bottom: 20px !important;
    }
    
    body.printing .staff-table {
        border: 1px solid #ddd;
    }
    
    body.printing .staff-table th,
    body.printing .staff-table td {
        border: 1px solid #ddd;
    }
    
    @media print {
        aside, .staff-page-header, .no-print, form, .alert, .flash-message {
            display: none !important;
        }
        
        .print-header {
            display: block !important;
            margin-bottom: 30px;
            text-align: center;
        }
        
        main, .max-w-7xl, .container {
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        
        .staff-card {
            border: 1px solid #ddd;
            box-shadow: none;
            page-break-inside: avoid;
            margin-bottom: 20px !important;
        }
        
        .staff-table {
            border: 1px solid #ddd;
        }
        
        .staff-table th, .staff-table td {
            border: 1px solid #ddd;
        }
    }
    
    .print-header {
        display: none;
    }
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.staff', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\cindy\OneDrive\Documents\tick B\pplg\writing hting\pointofyou\pointofyou\resources\views/reports/index.blade.php ENDPATH**/ ?>