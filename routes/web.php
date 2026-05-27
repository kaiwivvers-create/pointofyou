<?php

use App\Http\Controllers\Admin\CafeTableController;
use App\Http\Controllers\Admin\MenuCategoryController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\CurrentOrdersController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\Cashier\OrderController as CashierOrderController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OperationalExpenseController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SuperAdmin\BrandSettingsController;
use App\Http\Controllers\SuperAdmin\PermissionController;
use App\Http\Controllers\SuperAdmin\RoleController;
use App\Http\Controllers\SuperAdmin\UserController;
use App\Http\Controllers\Table\TableScanController;
use App\Http\Controllers\KioskController;
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
});

// Staff login
Route::get('/admin/login', [AdminAuthController::class, 'create'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'store'])->name('admin.login.store')->middleware('guest');
Route::get('/login', [AdminAuthController::class, 'create']);

Route::middleware('auth')->group(function () {
    Route::post('/admin/logout', [AdminAuthController::class, 'destroy'])->name('admin.logout');

    // Super Admin / Management
    Route::prefix('super-admin')->name('super-admin.')->group(function () {
        Route::get('/', function () {
            return view('super-admin.dashboard', [
                'staffCount' => \App\Models\User::count(),
                'menuCount' => \App\Models\MenuItem::count(),
                'tableCount' => \App\Models\CafeTable::count(),
                'pendingOrders' => \App\Models\Order::where('status', 'pending')->count(),
            ]);
        })->name('dashboard')->middleware('permission:dashboard');

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

        Route::middleware('permission:tables')->group(function () {
            Route::post('tables', [CafeTableController::class, 'store'])->name('tables.store');
            Route::post('tables/{cafeTable}/regenerate-qr', [CafeTableController::class, 'regenerateQr'])->name('tables.regenerate-qr');
            Route::delete('tables/{cafeTable}', [CafeTableController::class, 'destroy'])->name('tables.destroy');
        });
    });

    // Owner
    Route::prefix('owner')->name('owner.')->group(function () {
        Route::get('/', function () {
            // Monthly chart data
            $monthlyData = \App\Models\Order::where('status', 'paid')
                ->whereRaw("strftime('%Y', paid_at) = ?", [now()->year])
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
                'totalRevenue' => \App\Models\Order::where('status', 'paid')->sum('total'),
                'totalOrders' => \App\Models\Order::count(),
                'menuCount' => \App\Models\MenuItem::count(),
                'tableCount' => \App\Models\CafeTable::count(),
                'todayIncome' => \App\Models\Order::where('status', 'paid')->whereDate('paid_at', today())->sum('total'),
                'thisMonthIncome' => \App\Models\Order::where('status', 'paid')
                    ->whereRaw("strftime('%Y', paid_at) = ?", [now()->year])
                    ->whereRaw("strftime('%m', paid_at) = ?", [str_pad(now()->month, 2, '0', STR_PAD_LEFT)])
                    ->sum('total'),
                'yesterdayIncome' => \App\Models\Order::where('status', 'paid')->whereDate('paid_at', now()->subDay())->sum('total'),
                'lastMonthIncome' => \App\Models\Order::where('status', 'paid')
                    ->whereRaw("strftime('%Y', paid_at) = ?", [now()->subMonth()->year])
                    ->whereRaw("strftime('%m', paid_at) = ?", [str_pad(now()->subMonth()->month, 2, '0', STR_PAD_LEFT)])
                    ->sum('total'),
                'monthlyChartData' => $monthlyChartData,
            ]);
        })->name('dashboard')->middleware('permission:dashboard');
    });

    // Manager
    Route::prefix('manager')->name('manager.')->group(function () {
        Route::get('/', function () {
            // Monthly chart data
            $monthlyData = \App\Models\Order::where('status', 'paid')
                ->whereRaw("strftime('%Y', paid_at) = ?", [now()->year])
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
            
            return view('manager.dashboard', [
                'todayRevenue' => \App\Models\Order::where('status', 'paid')->whereDate('paid_at', today())->sum('total'),
                'todayOrders' => \App\Models\Order::whereDate('created_at', today())->count(),
                'pendingOrders' => \App\Models\Order::where('status', 'pending')->count(),
                'menuCount' => \App\Models\MenuItem::count(),
                'todayIncome' => \App\Models\Order::where('status', 'paid')->whereDate('paid_at', today())->sum('total'),
                'thisMonthIncome' => \App\Models\Order::where('status', 'paid')
                    ->whereRaw("strftime('%Y', paid_at) = ?", [now()->year])
                    ->whereRaw("strftime('%m', paid_at) = ?", [str_pad(now()->month, 2, '0', STR_PAD_LEFT)])
                    ->sum('total'),
                'yesterdayIncome' => \App\Models\Order::where('status', 'paid')->whereDate('paid_at', now()->subDay())->sum('total'),
                'lastMonthIncome' => \App\Models\Order::where('status', 'paid')
                    ->whereRaw("strftime('%Y', paid_at) = ?", [now()->subMonth()->year])
                    ->whereRaw("strftime('%m', paid_at) = ?", [str_pad(now()->subMonth()->month, 2, '0', STR_PAD_LEFT)])
                    ->sum('total'),
                'monthlyChartData' => $monthlyChartData,
            ]);
        })->name('dashboard')->middleware('permission:dashboard');
    });

    // Admin dashboard + menu
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', function () {
            return view('admin.dashboard', [
                'menuCount' => \App\Models\MenuItem::count(),
                'tableCount' => \App\Models\CafeTable::count(),
                'pendingOrders' => \App\Models\Order::where('status', 'pending')->count(),
            ]);
        })->name('dashboard')->middleware('permission:dashboard');

        Route::resource('menu', MenuItemController::class)->except(['show'])->middleware('permission:menu');
        Route::resource('promos', PromoController::class)->except(['show'])->middleware('permission:promos');
        Route::get('tables', [CafeTableController::class, 'index'])->name('tables.index')->middleware('permission:tables');

        // Menu Category Manager
        Route::middleware('permission:categories')->group(function () {
            Route::get('menu-categories', [MenuCategoryController::class, 'index'])->name('menu-categories.index');
            Route::post('menu-categories', [MenuCategoryController::class, 'store'])->name('menu-categories.store');
            Route::post('menu-categories/reorder', [MenuCategoryController::class, 'reorder'])->name('menu-categories.reorder');
            Route::patch('menu-categories/{category}/toggle', [MenuCategoryController::class, 'toggleVisibility'])->name('menu-categories.toggle');
            Route::delete('menu-categories/{category}', [MenuCategoryController::class, 'destroy'])->name('menu-categories.destroy');
        });

        // Kitchen Dashboard
        Route::get('current-orders', [CurrentOrdersController::class, 'index'])->name('current-orders.index')->middleware('permission:kitchen');
        Route::patch('current-orders/items/{orderItem}/toggle-ready', [CurrentOrdersController::class, 'toggleReady'])->name('current-orders.toggle-ready')->middleware('permission:kitchen');
    });

    // Cashier
    Route::prefix('cashier')->name('cashier.')->group(function () {
        Route::get('/', [CashierOrderController::class, 'index'])->name('dashboard')->middleware('permission:orders');
        Route::post('orders/{order}/pay', [CashierOrderController::class, 'markPaid'])->name('orders.pay')->middleware('permission:orders');
        Route::post('orders/{order}/close', [CashierOrderController::class, 'markClosed'])->name('orders.close')->middleware('permission:orders');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportsController::class, 'index'])->name('index')->middleware('permission:reports');
        Route::get('export/csv', [ReportsController::class, 'exportCsv'])->name('export.csv')->middleware('permission:reports');
        Route::get('export/pdf', [ReportsController::class, 'exportPdf'])->name('export.pdf')->middleware('permission:reports');
        Route::get('print', [ReportsController::class, 'printView'])->name('print')->middleware('permission:reports');
    });

    // ERP Modules
    Route::middleware('permission:inventory')->prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::get('/categories', [InventoryController::class, 'categories'])->name('categories');
        Route::get('/stock-movements', [InventoryController::class, 'stockMovements'])->name('stock-movements');
        Route::post('/products', [InventoryController::class, 'storeProduct'])->name('products.store');
        Route::post('/categories', [InventoryController::class, 'storeCategory'])->name('categories.store');
        Route::post('/stock-movements', [InventoryController::class, 'storeStockMovement'])->name('stock-movements.store');
    });

    Route::middleware('permission:payroll')->prefix('payroll')->name('payroll.')->group(function () {
        Route::get('/', [PayrollController::class, 'index'])->name('index');
        Route::get('/employees', [PayrollController::class, 'employees'])->name('employees');
        Route::get('/salaries', [PayrollController::class, 'salaries'])->name('salaries');
        Route::get('/attendance', [PayrollController::class, 'attendance'])->name('attendance');
        Route::post('/employees', [PayrollController::class, 'storeEmployee'])->name('employees.store');
        Route::post('/salaries', [PayrollController::class, 'storeSalary'])->name('salaries.store');
        Route::post('/attendance', [PayrollController::class, 'storeAttendance'])->name('attendance.store');
    });

    Route::middleware('permission:expenses')->prefix('expenses')->name('expenses.')->group(function () {
        Route::get('/', [OperationalExpenseController::class, 'index'])->name('index');
        Route::get('/categories', [OperationalExpenseController::class, 'categories'])->name('categories');
        Route::post('/', [OperationalExpenseController::class, 'storeExpense'])->name('store');
        Route::post('/categories', [OperationalExpenseController::class, 'storeCategory'])->name('categories.store');
        Route::post('/{id}/approve', [OperationalExpenseController::class, 'approveExpense'])->name('approve');
        Route::post('/{id}/reject', [OperationalExpenseController::class, 'rejectExpense'])->name('reject');
    });
});
