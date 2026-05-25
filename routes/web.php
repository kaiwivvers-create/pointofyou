<?php

use App\Http\Controllers\Admin\CafeTableController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\Cashier\OrderController as CashierOrderController;
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
    Route::post('/order', [TableScanController::class, 'placeOrder'])->name('table.order');
    Route::post('/leave', [TableScanController::class, 'clearTable'])->name('table.leave');
});

// Kiosk flow (no login)
Route::prefix('kiosk')->name('kiosk.')->group(function () {
    Route::get('/', [KioskController::class, 'welcome'])->name('welcome');
    Route::post('/type', [KioskController::class, 'setType'])->name('type');
    Route::get('/menu', [KioskController::class, 'menu'])->name('menu');
    Route::post('/cart/{menuItem}', [KioskController::class, 'addToCart'])->name('cart.add');
    Route::post('/cart/remove/{cartIndex}', [KioskController::class, 'removeFromCart'])->name('cart.remove');
    Route::post('/checkout', [KioskController::class, 'checkout'])->name('checkout');
    Route::post('/pay', [KioskController::class, 'pay'])->name('pay');
    Route::get('/success', [KioskController::class, 'success'])->name('success');
});

// Staff login
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AdminAuthController::class, 'create'])->name('admin.login');
    Route::post('/admin/login', [AdminAuthController::class, 'store'])->name('admin.login.store');
    Route::get('/login', [AdminAuthController::class, 'create']);
});

Route::middleware('auth')->group(function () {
    Route::post('/admin/logout', [AdminAuthController::class, 'destroy'])->name('admin.logout');

    // Super Admin (must be first to avoid conflicts)
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
        Route::post('tables/{cafeTable}/regenerate-qr', [CafeTableController::class, 'regenerateQr'])->name('tables.regenerate-qr');
        Route::delete('tables/{cafeTable}', [CafeTableController::class, 'destroy'])->name('tables.destroy');
    });

    // Admin dashboard + menu
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
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

    // Cashier
    Route::middleware('role:cashier')->prefix('cashier')->name('cashier.')->group(function () {
        Route::get('/', [CashierOrderController::class, 'index'])->name('dashboard');
        Route::post('orders/{order}/pay', [CashierOrderController::class, 'markPaid'])->name('orders.pay');
    });
});
