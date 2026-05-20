<?php

use App\Http\Controllers\Admin\CafeTableController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\Cashier\OrderController as CashierOrderController;
use App\Http\Controllers\SuperAdmin\UserController;
use App\Http\Controllers\Table\TableScanController;
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
    Route::post('/order', [TableScanController::class, 'placeOrder'])->name('table.order');
    Route::post('/leave', [TableScanController::class, 'clearTable'])->name('table.leave');
});

// Staff login
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AdminAuthController::class, 'create'])->name('admin.login');
    Route::post('/admin/login', [AdminAuthController::class, 'store'])->name('admin.login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/admin/logout', [AdminAuthController::class, 'destroy'])->name('admin.logout');

    // Admin dashboard + menu
    Route::middleware('role:admin,super_admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', function () {
            return view('admin.dashboard', [
                'menuCount' => \App\Models\MenuItem::count(),
                'tableCount' => \App\Models\CafeTable::count(),
                'pendingOrders' => \App\Models\Order::where('status', 'pending')->count(),
            ]);
        })->name('dashboard');

        Route::resource('menu', MenuItemController::class)->except(['show']);
        Route::get('tables', [CafeTableController::class, 'index'])->name('tables.index');
    });

    // Super Admin
    Route::middleware('role:super_admin')->prefix('super-admin')->name('super-admin.')->group(function () {
        Route::get('/', function () {
            return view('super-admin.dashboard', [
                'staffCount' => \App\Models\User::count(),
                'menuCount' => \App\Models\MenuItem::count(),
                'tableCount' => \App\Models\CafeTable::count(),
                'pendingOrders' => \App\Models\Order::where('status', 'pending')->count(),
            ]);
        })->name('dashboard');

        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::post('tables', [CafeTableController::class, 'store'])->name('tables.store');
        Route::delete('tables/{cafeTable}', [CafeTableController::class, 'destroy'])->name('tables.destroy');
    });

    // Cashier
    Route::middleware('role:cashier,super_admin')->prefix('cashier')->name('cashier.')->group(function () {
        Route::get('/', [CashierOrderController::class, 'index'])->name('dashboard');
        Route::post('orders/{order}/pay', [CashierOrderController::class, 'markPaid'])->name('orders.pay');
    });
});
