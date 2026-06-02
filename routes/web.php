<?php

use App\Http\Controllers\Admin\CafeTableController;
use App\Http\Controllers\Admin\PromoController;
use App\Http\Controllers\Admin\GiftController;
use App\Http\Controllers\Admin\PacketController;
use App\Http\Controllers\Admin\MenuCategoryController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\CurrentOrdersController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Cashier\OrderController as CashierOrderController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OperationalExpenseController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PermitController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\StaffScheduleController;
use App\Http\Controllers\SuperAdmin\BrandSettingsController;
use App\Http\Controllers\SuperAdmin\PaymentSettingsController;
use App\Http\Controllers\SuperAdmin\ActivityLogController;
use App\Http\Controllers\SuperAdmin\DatabaseManagementController;
use App\Http\Controllers\SuperAdmin\PermissionController;
use App\Http\Controllers\SuperAdmin\RoleController;
use App\Http\Controllers\SuperAdmin\UserController;
use App\Http\Controllers\Table\TableScanController;
use App\Http\Controllers\KioskController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Table QR flow (no login)
Route::get('/table', [TableScanController::class, 'welcome'])->name('table.welcome');
Route::get('/table/scan/{token}', [TableScanController::class, 'scan'])->name('table.scan');

Route::middleware('table.session')->prefix('table')->group(function () {
    Route::get('/menu', [TableScanController::class, 'menu'])->name('table.menu');
    Route::post('/cart/{menuItem}', [TableScanController::class, 'addToCart'])->name('table.cart.add');
    Route::patch('/cart', [TableScanController::class, 'updateCart'])->name('table.cart.update');
    Route::patch('/cart/update/{index}', [TableScanController::class, 'updateCartItem'])->name('table.cart.update.index');
    Route::post('/cart/remove/{index}', [TableScanController::class, 'removeCartItem'])->name('table.cart.remove.index');
    Route::post('/order', [TableScanController::class, 'placeOrder'])->name('table.order');
    Route::post('/leave', [TableScanController::class, 'clearTable'])->name('table.leave');
    Route::post('/promo/apply', [TableScanController::class, 'applyPromo'])->name('table.promo.apply');
});

// Kiosk flow (no login)
Route::prefix('kiosk')->name('kiosk.')->group(function () {
    Route::get('/', [KioskController::class, 'welcome'])->name('welcome');
    Route::post('/type', [KioskController::class, 'setType'])->name('type');
    Route::get('/menu', [KioskController::class, 'menu'])->name('menu');
    Route::post('/cart/{menuItem}', [KioskController::class, 'addToCart'])->name('cart.add');
    Route::patch('/cart/update/{cartIndex}', [KioskController::class, 'updateCartItem'])->name('cart.update');
    Route::post('/cart/remove/{cartIndex}', [KioskController::class, 'removeFromCart'])->name('cart.remove');
    Route::post('/checkout', [KioskController::class, 'checkout'])->name('checkout');
    Route::post('/pay', [KioskController::class, 'pay'])->name('pay');
    Route::get('/success', [KioskController::class, 'success'])->name('success');
    Route::post('/promo/apply', [KioskController::class, 'applyPromo'])->name('promo.apply');
});

// Staff login
Route::get('/admin/login', [AdminAuthController::class, 'create'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'store'])->name('admin.login.store')->middleware('guest');
Route::get('/login', [AdminAuthController::class, 'create']);

Route::middleware('auth')->group(function () {
    Route::post('/admin/logout', [AdminAuthController::class, 'destroy'])->name('admin.logout');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Super Admin / Management
    Route::prefix('super-admin')->name('super-admin.')->group(function () {
        Route::get('/', function () {
            return view('super-admin.dashboard', [
                'staffCount' => \App\Models\User::count(),
                'menuCount' => \App\Models\MenuItem::count(),
                'tableCount' => \App\Models\CafeTable::count(),
                'pendingOrders' => \App\Models\Order::where('status', 'pending')->count(),
                'roles' => \App\Models\Role::all(),
            ]);
        })->name('dashboard')->middleware('role:super_admin');

        Route::get('current-orders', [CurrentOrdersController::class, 'index'])->name('current-orders')->middleware('role:super_admin');

        Route::middleware('permission:users')->group(function () {
            Route::get('users', [UserController::class, 'index'])->name('users.index');
            Route::get('users/create', [UserController::class, 'create'])->name('users.create');
            Route::post('users', [UserController::class, 'store'])->name('users.store');
            Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
            Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        });

        Route::middleware('permission:permissions')->group(function () {
            Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
            Route::post('permissions', [PermissionController::class, 'update'])->name('permissions.update');
        });

        Route::middleware('permission:roles')->group(function () {
            Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
            Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create');
            Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
            Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
            Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
            Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
        });

        Route::middleware('permission:brand_settings')->group(function () {
            Route::get('brand-settings', [BrandSettingsController::class, 'index'])->name('brand-settings.index');
            Route::post('brand-settings', [BrandSettingsController::class, 'update'])->name('brand-settings.update');
        });

        Route::middleware('permission:payment_settings')->group(function () {
            Route::get('payment-settings', [PaymentSettingsController::class, 'index'])->name('payment-settings.index');
            Route::post('payment-settings', [PaymentSettingsController::class, 'update'])->name('payment-settings.update');
        });

        Route::middleware('permission:tables')->group(function () {
            Route::post('tables', [CafeTableController::class, 'store'])->name('tables.store');
            Route::post('tables/{cafeTable}/regenerate-qr', [CafeTableController::class, 'regenerateQr'])->name('tables.regenerate-qr');
            Route::delete('tables/{cafeTable}', [CafeTableController::class, 'destroy'])->name('tables.destroy');
        });

        Route::middleware('permission:activity_logs')->group(function () {
            Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
            Route::get('activity-logs/{activityLog}', [ActivityLogController::class, 'show'])->name('activity-logs.show');
            Route::post('activity-logs/{activityLog}/revert', [ActivityLogController::class, 'revert'])->name('activity-logs.revert');
            Route::delete('activity-logs/{activityLog}', [ActivityLogController::class, 'destroy'])->name('activity-logs.destroy');
            Route::delete('activity-logs/{activityLog}/permanently', [ActivityLogController::class, 'destroyPermanently'])->name('activity-logs.destroy-permanently');
        });

        Route::middleware('permission:database_management')->group(function () {
            Route::get('database', [DatabaseManagementController::class, 'index'])->name('database.index');
            Route::post('database/export', [DatabaseManagementController::class, 'export'])->name('database.export');
            Route::post('database/import', [DatabaseManagementController::class, 'import'])->name('database.import');
            Route::get('database/download/{filename}', [DatabaseManagementController::class, 'downloadBackup'])->name('database.download');
            Route::delete('database/delete/{filename}', [DatabaseManagementController::class, 'deleteBackup'])->name('database.delete');
            Route::post('database/clear-cache', [DatabaseManagementController::class, 'clearCache'])->name('database.clear-cache');
            Route::post('database/optimize', [DatabaseManagementController::class, 'optimize'])->name('database.optimize');
            Route::post('database/migrate', [DatabaseManagementController::class, 'migrate'])->name('database.migrate');
            Route::post('database/seed', [DatabaseManagementController::class, 'seed'])->name('database.seed');
        });
    });

    // Owner
    Route::prefix('owner')->name('owner.')->group(function () {
        Route::get('/', function () {
            // Get today's attendance status for the owner
            $user = auth()->user();
            $attendance = null;
            if ($user->employee_id) {
                $attendance = \App\Models\Attendance::where('employee_id', $user->employee_id)
                    ->where('date', today())
                    ->first();
            }

            // Get today's permit status
            $permit = \App\Models\Permit::where('user_id', $user->id)
                ->where('start_date', '<=', today())
                ->where(function($q) {
                    $q->where('end_date', '>=', today())
                      ->orWhereNull('end_date');
                })
                ->where('status', 'approved')
                ->first();

            // Pending permit requests
            $pendingPermits = \App\Models\Permit::with('user')
                ->where('status', 'pending')
                ->latest()
                ->get();

            // Active shifts today
            $activeShifts = \App\Models\Attendance::with('user')
                ->where('date', today())
                ->whereNotNull('check_in')
                ->whereNull('check_out')
                ->get();

            // Low stock alerts
            $lowStockProducts = \App\Models\Product::where('stock_quantity', '<=', 10)
                ->orderBy('stock_quantity')
                ->get();

            // Monthly chart data
            $monthlyData = \App\Models\Order::where('status', 'paid')
                ->whereYear('paid_at', now()->year)
                ->selectRaw("strftime('%m', paid_at) as month, SUM(total) as total")
                ->groupBy('month')
                ->orderBy('month')
                ->get();
            
            $monthlyChartData = [];
            for ($i = 1; $i <= 12; $i++) {
                $monthNum = str_pad($i, 2, '0', STR_PAD_LEFT);
                $monthData = $monthlyData->firstWhere('month', $monthNum);
                $monthlyChartData['labels'][] = now()->month($i)->format('M');
                $monthlyChartData['data'][] = $monthData ? $monthData->total : 0;
            }
            
            return view('owner.dashboard', [
                'attendance' => $attendance,
                'permit' => $permit,
                'pendingPermits' => $pendingPermits,
                'activeShifts' => $activeShifts,
                'lowStockProducts' => $lowStockProducts,
                'totalRevenue' => \App\Models\Order::where('status', 'paid')->sum('total'),
                'totalOrders' => \App\Models\Order::count(),
                'menuCount' => \App\Models\MenuItem::count(),
                'tableCount' => \App\Models\CafeTable::count(),
                'todayIncome' => \App\Models\Order::where('status', 'paid')->whereDate('paid_at', today())->sum('total'),
                'thisMonthIncome' => \App\Models\Order::where('status', 'paid')
                    ->whereYear('paid_at', now()->year)
                    ->whereMonth('paid_at', now()->month)
                    ->sum('total'),
                'yesterdayIncome' => \App\Models\Order::where('status', 'paid')->whereDate('paid_at', now()->subDay())->sum('total'),
                'lastMonthIncome' => \App\Models\Order::where('status', 'paid')
                    ->whereYear('paid_at', now()->subMonth()->year)
                    ->whereMonth('paid_at', now()->subMonth()->month)
                    ->sum('total'),
                'monthlyChartData' => $monthlyChartData,
            ]);
        })->name('dashboard')->middleware('role:owner');
    });

    // Manager
    Route::prefix('manager')->name('manager.')->group(function () {
        Route::get('/', function () {
            // Monthly chart data (matching reports calculation)
            $monthlyData = \App\Models\Order::where('status', 'paid')
                ->whereYear('paid_at', now()->year)
                ->selectRaw("CAST(strftime('%m', paid_at) AS INTEGER) as month, SUM(total) as total")
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            $monthlyChartData = [];
            $monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            for ($i = 1; $i <= 12; $i++) {
                $monthData = $monthlyData->firstWhere('month', $i);
                $monthlyChartData['labels'][] = $monthLabels[$i - 1];
                $monthlyChartData['data'][] = $monthData ? (float) $monthData->total : 0;
            }

            // Low stock alerts
            $lowStockProducts = \App\Models\Product::with('category')
                ->whereColumn('stock_quantity', '<=', 'min_stock_level')
                ->orderBy('stock_quantity')
                ->limit(10)
                ->get();

            // Expense tracking data (matching reports calculation)
            $thisMonthExpenses = \App\Models\OperationalExpense::where('source', 'auto_stock_purchase')
                ->whereMonth('expense_date', now()->month)
                ->whereYear('expense_date', now()->year)
                ->sum('amount');

            return view('manager.dashboard', [
                'todayRevenue' => \App\Models\Order::where('status', 'paid')->whereDate('paid_at', today())->sum('total'),
                'todayOrders' => \App\Models\Order::whereDate('created_at', today())->count(),
                'pendingOrders' => \App\Models\Order::where('status', 'pending')->count(),
                'menuCount' => \App\Models\MenuItem::count(),
                'todayIncome' => \App\Models\Order::where('status', 'paid')->whereDate('paid_at', today())->sum('total'),
                'thisMonthIncome' => \App\Models\Order::where('status', 'paid')
                    ->whereYear('paid_at', now()->year)
                    ->whereMonth('paid_at', now()->month)
                    ->sum('total'),
                'yesterdayIncome' => \App\Models\Order::where('status', 'paid')->whereDate('paid_at', now()->subDay())->sum('total'),
                'lastMonthIncome' => \App\Models\Order::where('status', 'paid')
                    ->whereYear('paid_at', now()->subMonth()->year)
                    ->whereMonth('paid_at', now()->subMonth()->month)
                    ->sum('total'),
                'monthlyChartData' => $monthlyChartData,
                'lowStockProducts' => $lowStockProducts,
                'thisMonthExpenses' => $thisMonthExpenses,
            ]);
        })->name('dashboard')->middleware('role:manager');
    });

    // Admin dashboard + menu
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', function () {
            return view('admin.dashboard', [
                'menuCount' => \App\Models\MenuItem::count(),
                'tableCount' => \App\Models\CafeTable::count(),
                'pendingOrders' => \App\Models\Order::where('status', 'pending')->count(),
            ]);
        })->name('dashboard')->middleware('role:owner,manager,admin,test');

        // Kitchen Dashboard (role-based, no permit required)
        Route::get('kitchen', [CurrentOrdersController::class, 'dashboard'])->name('kitchen.dashboard')->middleware('role:chef');

        // Current Orders and Pickup Station (role-based for chef, permission-based for others)
        Route::get('current-orders', [CurrentOrdersController::class, 'index'])->name('current-orders.index')->middleware('permission:current_orders')->middleware('role:chef,owner,manager,super_admin');
        Route::patch('current-orders/items/{orderItem}/toggle-ready', [CurrentOrdersController::class, 'toggleReady'])->name('current-orders.toggle-ready')->middleware('permission:current_orders');
        Route::get('pickup-station', [\App\Http\Controllers\Admin\PickupStationController::class, 'index'])->name('pickup-station.index')->middleware('permission:pickup_station');
        Route::post('pickup-station/orders/{order}/close', [\App\Http\Controllers\Admin\PickupStationController::class, 'markClosed'])->name('pickup-station.close')->middleware('permission:pickup_station');

        Route::middleware('permit')->group(function () {
            Route::resource('menu', MenuItemController::class)->except(['show'])->middleware('permission:menu');
            Route::resource('promos', PromoController::class)->except(['show'])->middleware('permission:promos');
            Route::resource('gifts', GiftController::class)->except(['show'])->middleware('permission:gifts');
            Route::resource('packets', PacketController::class)->except(['show'])->middleware('permission:packets');
            Route::get('tables', [CafeTableController::class, 'index'])->name('tables.index')->middleware('permission:tables');

            // Menu Category Manager
            Route::middleware('permission:categories')->group(function () {
                Route::get('menu-categories', [MenuCategoryController::class, 'index'])->name('menu-categories.index');
                Route::post('menu-categories', [MenuCategoryController::class, 'store'])->name('menu-categories.store');
                Route::put('menu-categories/{category}', [MenuCategoryController::class, 'update'])->name('menu-categories.update');
                Route::post('menu-categories/reorder', [MenuCategoryController::class, 'reorder'])->name('menu-categories.reorder');
                Route::patch('menu-categories/{category}/toggle', [MenuCategoryController::class, 'toggleVisibility'])->name('menu-categories.toggle');
                Route::delete('menu-categories/{category}', [MenuCategoryController::class, 'destroy'])->name('menu-categories.destroy');
            });
        });
    });

    // Cashier
    Route::prefix('cashier')->name('cashier.')->group(function () {
        Route::get('/', [CashierOrderController::class, 'index'])->name('dashboard')->middleware('role:cashier');
        Route::get('/current-orders', [CashierOrderController::class, 'currentOrders'])->name('current-orders')->middleware('permission:current_orders', 'attendance');
        Route::get('/payments', [CashierOrderController::class, 'payments'])->name('payments')->middleware('permission:orders', 'attendance');
        Route::get('/receipt/{order}', [CashierOrderController::class, 'receipt'])->name('receipt')->middleware('permission:orders', 'attendance');
        
        Route::middleware('permit', 'attendance')->group(function () {
            Route::post('orders/create', [CashierOrderController::class, 'create'])->name('orders.create')->middleware('permission:orders');
            Route::post('orders/{order}/pay', [CashierOrderController::class, 'markPaid'])->name('orders.pay')->middleware('permission:orders');
            Route::post('orders/{order}/close', [CashierOrderController::class, 'markClosed'])->name('orders.close')->middleware('permission:orders');
            Route::post('orders/{order}/adjustments', [CashierOrderController::class, 'storeAdjustment'])->name('orders.adjustments.store')->middleware('permission:orders');
            Route::delete('orders/{order}/adjustments/{adjustment}', [CashierOrderController::class, 'destroyAdjustment'])->name('orders.adjustments.destroy')->middleware('permission:orders');
            Route::get('orders/{order}/cart-items', [CashierOrderController::class, 'getCartItems'])->name('orders.cart-items')->middleware('permission:orders');
            Route::get('orders/table/{tableId}', [CashierOrderController::class, 'getOrderByTable'])->name('orders.table')->middleware('permission:orders');
        });
    });

    // Reports
    Route::prefix('reports')->name('reports.')->middleware('permit')->group(function () {
        Route::get('/', [ReportsController::class, 'index'])->name('index')->middleware('permission:reports');
        Route::get('export/csv', [ReportsController::class, 'exportCsv'])->name('export.csv')->middleware('permission:reports');
        Route::get('export/pdf', [ReportsController::class, 'exportPdf'])->name('export.pdf')->middleware('permission:reports');
        Route::get('print', [ReportsController::class, 'printView'])->name('print')->middleware('permission:reports');
    });

    // ERP Modules
    Route::middleware('permit')->middleware('permission:inventory')->prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::get('/supplies', [InventoryController::class, 'supplies'])->name('supplies');
        Route::get('/categories', [InventoryController::class, 'categories'])->name('categories');
        Route::get('/stock-categories', [InventoryController::class, 'stockCategories'])->name('stock-categories');
        Route::get('/stock-movements', [InventoryController::class, 'stockMovements'])->name('stock-movements');
        Route::get('/bulk-purchases/history', [InventoryController::class, 'bulkPurchaseHistory'])->name('bulk-purchases.history');
        Route::get('/stock-movements/export/csv', [InventoryController::class, 'exportStockMovements'])->name('stock-movements.export.csv');
        Route::get('/bulk-purchases/export/csv', [InventoryController::class, 'exportBulkPurchases'])->name('bulk-purchases.export.csv');
        Route::post('/products', [InventoryController::class, 'storeProduct'])->name('products.store');
        Route::put('/products/{product}', [InventoryController::class, 'updateProduct'])->name('products.update');
        Route::delete('/products/{product}', [InventoryController::class, 'destroyProduct'])->name('products.destroy');
        Route::post('/categories', [InventoryController::class, 'storeCategory'])->name('categories.store');
        Route::put('/categories/{category}', [InventoryController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/categories/{category}', [InventoryController::class, 'destroyCategory'])->name('categories.destroy');
        Route::post('/stock-movements', [InventoryController::class, 'storeStockMovement'])->name('stock-movements.store');
        Route::post('/bulk-purchases', [InventoryController::class, 'storeBulkPurchase'])->name('bulk-purchases.store');
        
        // Gift inventory routes
        Route::post('/gifts/stock-movement', [GiftController::class, 'stockMovement'])->name('gifts.stock-movement');
        Route::put('/gifts/inventory-update', [GiftController::class, 'inventoryUpdate'])->name('gifts.inventory-update');
    });

    Route::middleware('permit')->middleware('permission:payroll')->prefix('payroll')->name('payroll.')->group(function () {
        Route::get('/', [PayrollController::class, 'index'])->name('index');
        Route::get('/employees', [PayrollController::class, 'employees'])->name('employees');
        Route::get('/salaries', [PayrollController::class, 'salaries'])->name('salaries');
        Route::get('/attendance', [PayrollController::class, 'attendance'])->name('attendance');
        Route::post('/employees', [PayrollController::class, 'storeEmployee'])->name('employees.store');
        Route::post('/salaries', [PayrollController::class, 'storeSalary'])->name('salaries.store');
        Route::post('/attendance', [PayrollController::class, 'storeAttendance'])->name('attendance.store');
    });

    Route::middleware('permit')->middleware('permission:expenses')->prefix('expenses')->name('expenses.')->group(function () {
        Route::get('/', [OperationalExpenseController::class, 'index'])->name('index');
        Route::get('/categories', [OperationalExpenseController::class, 'categories'])->name('categories');
        Route::get('/export/csv', [OperationalExpenseController::class, 'exportCsv'])->name('export.csv');
        Route::post('/', [OperationalExpenseController::class, 'storeExpense'])->name('store');
        Route::post('/categories', [OperationalExpenseController::class, 'storeCategory'])->name('categories.store');
        Route::post('/{id}/approve', [OperationalExpenseController::class, 'approveExpense'])->name('approve');
        Route::post('/{id}/reject', [OperationalExpenseController::class, 'rejectExpense'])->name('reject');
    });

    // Permits
    Route::middleware('permission:users')->prefix('permits')->name('permits.')->group(function () {
        Route::get('/', [PermitController::class, 'index'])->name('index')->middleware('permit');
        Route::get('/create', [PermitController::class, 'create'])->name('create');
        Route::post('/', [PermitController::class, 'store'])->name('store');
        Route::post('/{permit}/approve', [PermitController::class, 'approve'])->name('approve')->middleware('permit');
        Route::post('/{permit}/reject', [PermitController::class, 'reject'])->name('reject')->middleware('permit');
    });

    // Staff Schedules
    Route::middleware('permit')->middleware('permission:users')->prefix('staff-schedules')->name('staff-schedules.')->group(function () {
        Route::get('/', [StaffScheduleController::class, 'index'])->name('index');
        Route::get('/create', [StaffScheduleController::class, 'create'])->name('create');
        Route::post('/', [StaffScheduleController::class, 'store'])->name('store');
        Route::delete('/{staffSchedule}', [StaffScheduleController::class, 'destroy'])->name('destroy');
    });

    // Attendance (check-in/check-out)
    Route::middleware('auth')->prefix('attendance')->name('attendance.')->group(function () {
        Route::post('/check-in', [AttendanceController::class, 'checkIn'])->name('check-in');
        Route::post('/check-out', [AttendanceController::class, 'checkOut'])->name('check-out');
        Route::get('/status', [AttendanceController::class, 'getCurrentStatus'])->name('status');
    });
});
