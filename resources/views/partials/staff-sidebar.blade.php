@php
    $user = auth()->user();
    
    // Permission check function - strictly checks database permissions
    $can = function ($permission) use ($user) {
        // Super admin has all permissions
        if ($user->isSuperAdmin()) return true;
        
        // Check database for this role's permission
        $p = \App\Models\Permission::where('role', $user->role->value)
            ->where('permission', $permission)
            ->first();
        
        // Only return true if permission exists AND can_view is explicitly true
        // If permission doesn't exist or can_view is false/null, return false
        return $p && $p->can_view === true;
    };
@endphp

<div class="h-full flex flex-col justify-between overflow-y-auto hidden-scrollbar pb-6 pt-2">
    <div class="space-y-6">
        
        @if ($can('brand_settings') || $can('payment_settings') || $user->isSuperAdmin() || $user->isOwner() || $user->isManager() || $user->isChef() || $user->isCashier())
            <div>
                <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.14em]" style="color: {{ $brandSettings->primary_font_color ?? '#78350f' }};">Overview</p>
                <div class="flex flex-col gap-0.5">
                    @if ($user->isSuperAdmin())
                        @include('partials.staff-nav-link', [
                            'href' => route('super-admin.dashboard'),
                            'label' => 'Dashboard',
                            'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>',
                            'active' => request()->routeIs('super-admin.dashboard'),
                        ])
                        @include('partials.staff-nav-link', [
                            'href' => route('super-admin.current-orders'),
                            'label' => 'Current Orders',
                            'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>',
                            'active' => request()->routeIs('super-admin.current-orders'),
                        ])
                    @elseif ($user->isOwner())
                        @include('partials.staff-nav-link', [
                            'href' => route('owner.dashboard'),
                            'label' => 'Dashboard',
                            'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>',
                            'active' => request()->routeIs('owner.dashboard'),
                        ])
                    @elseif ($user->isManager())
                        @include('partials.staff-nav-link', [
                            'href' => route('manager.dashboard'),
                            'label' => 'Dashboard',
                            'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>',
                            'active' => request()->routeIs('manager.dashboard'),
                        ])
                    @elseif ($user->isChef())
                        @include('partials.staff-nav-link', [
                            'href' => route('admin.kitchen.dashboard'),
                            'label' => 'Dashboard',
                            'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>',
                            'active' => request()->routeIs('admin.kitchen.dashboard'),
                        ])
                    @elseif ($user->isCashier())
                        @include('partials.staff-nav-link', [
                            'href' => route('cashier.dashboard'),
                            'label' => 'Dashboard',
                            'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>',
                            'active' => request()->routeIs('cashier.dashboard'),
                        ])
                    @endif
                    
                    @if ($can('brand_settings'))
                        @include('partials.staff-nav-link', [
                            'href' => route('super-admin.brand-settings.index'),
                            'label' => 'Brand Settings',
                            'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" /></svg>',
                            'active' => request()->routeIs('super-admin.brand-settings.*'),
                        ])
                    @endif

                    @if ($can('payment_settings'))
                        @include('partials.staff-nav-link', [
                            'href' => route('super-admin.payment-settings.index'),
                            'label' => 'Payment Settings',
                            'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>',
                            'active' => request()->routeIs('super-admin.payment-settings.*'),
                        ])
                    @endif

                    @if ($can('activity_logs'))
                        @include('partials.staff-nav-link', [
                            'href' => route('super-admin.activity-logs.index'),
                            'label' => 'Activity Logs',
                            'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
                            'active' => request()->routeIs('super-admin.activity-logs.*'),
                        ])
                    @endif

                    @if ($can('database_management'))
                        @include('partials.staff-nav-link', [
                            'href' => route('super-admin.database.index'),
                            'label' => 'Database',
                            'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>',
                            'active' => request()->routeIs('super-admin.database.*'),
                        ])
                    @endif
                </div>
            </div>
        @endif

        @if ($can('users') || $can('permissions') || $can('roles'))
            <div>
                <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.14em]" style="color: {{ $brandSettings->primary_font_color ?? '#78350f' }};">Team</p>
                <div class="flex flex-col gap-0.5">
                    @if ($can('users'))
                        @include('partials.staff-nav-link', [
                            'href' => route('super-admin.users.index'),
                            'label' => 'Staff users',
                            'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>',
                            'active' => request()->routeIs('super-admin.users.*'),
                        ])
                    @endif
                    @if ($can('permissions'))
                        @include('partials.staff-nav-link', [
                            'href' => route('super-admin.permissions.index'),
                            'label' => 'Permissions',
                            'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>',
                            'active' => request()->routeIs('super-admin.permissions.*'),
                        ])
                    @endif
                    @if ($can('roles'))
                        @include('partials.staff-nav-link', [
                            'href' => route('super-admin.roles.index'),
                            'label' => 'Roles & Wages',
                            'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>',
                            'active' => request()->routeIs('super-admin.roles.*'),
                        ])
                    @endif
                    @if ($can('users'))
                        @include('partials.staff-nav-link', [
                            'href' => route('permits.index'),
                            'label' => 'Permits',
                            'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>',
                            'active' => request()->routeIs('permits.*'),
                        ])
                    @endif
                    @if ($can('users'))
                        @include('partials.staff-nav-link', [
                            'href' => route('staff-schedules.index'),
                            'label' => 'Staff Schedules',
                            'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V4m8 3V4m-9 8h10m-1 8H7a2 2 0 01-2-2V10h14v8a2 2 0 01-2 2z" /></svg>',
                            'active' => request()->routeIs('staff-schedules.*'),
                        ])
                    @endif
                </div>
            </div>
        @endif

        @if ($can('menu') || $can('categories') || $can('promos') || $can('packets') || $can('gifts') || $can('tables'))
            <div>
                <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.14em]" style="color: {{ $brandSettings->primary_font_color ?? '#78350f' }};">Bakery</p>
                <div class="flex flex-col gap-0.5">
                    @if ($can('menu'))
                        @include('partials.staff-nav-link', [
                            'href' => route('admin.menu.index'),
                            'label' => 'Menu',
                            'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>',
                            'active' => request()->routeIs('admin.menu.*'),
                        ])
                    @endif
                    @if ($can('categories'))
                        @include('partials.staff-nav-link', [
                            'href' => route('admin.menu-categories.index'),
                            'label' => 'Categories',
                            'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" /></svg>',
                            'active' => request()->routeIs('admin.menu-categories.*'),
                        ])
                    @endif
                    @if ($can('promos'))
                        @include('partials.staff-nav-link', [
                            'href' => route('admin.promos.index'),
                            'label' => 'Promos',
                            'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>',
                            'active' => request()->routeIs('admin.promos.*'),
                        ])
                    @endif
                    @if ($can('packets'))
                        @include('partials.staff-nav-link', [
                            'href' => route('admin.packets.index'),
                            'label' => 'Packets',
                            'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>',
                            'active' => request()->routeIs('admin.packets.*'),
                        ])
                    @endif
                    @if ($can('gifts'))
                        @include('partials.staff-nav-link', [
                            'href' => route('admin.gifts.index'),
                            'label' => 'Gifts & Toys',
                            'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" /></svg>',
                            'active' => request()->routeIs('admin.gifts.*'),
                        ])
                    @endif
                    @if ($can('tables'))
                        @include('partials.staff-nav-link', [
                            'href' => route('admin.tables.index'),
                            'label' => 'Tables & QR',
                            'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>',
                            'active' => request()->routeIs('admin.tables.*'),
                        ])
                    @endif
                    @if ($can('gifts'))
                        @include('partials.staff-nav-link', [
                            'href' => route('admin.barcodes.index'),
                            'label' => 'Barcode Manager',
                            'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm13-1h3a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1V4a1 1 0 011-1zM4 17a1 1 0 01-1-1v-3a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H4zm13-1h3a1 1 0 011-1v-3a1 1 0 01-1-1h-3a1 1 0 01-1 1v3a1 1 0 011 1z"></path></svg>',
                            'active' => request()->routeIs('admin.barcodes.*'),
                        ])
                    @endif
                </div>
            </div>
        @endif

        @if (($user->isChef() || $user->isCashier()) && ($can('current_orders') || $can('pickup_station')))
            <div>
                <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.14em]" style="color: {{ $brandSettings->primary_font_color ?? '#78350f' }};">Kitchen</p>
                <div class="flex flex-col gap-0.5">
                    @if ($can('current_orders'))
                        @if ($user->isChef())
                            @include('partials.staff-nav-link', [
                                'href' => route('admin.current-orders.index'),
                                'label' => 'Current Orders',
                                'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>',
                                'active' => request()->routeIs('admin.current-orders.*'),
                            ])
                        @elseif ($user->isCashier())
                            @include('partials.staff-nav-link', [
                                'href' => route('cashier.current-orders'),
                                'label' => 'Current Orders',
                                'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>',
                                'active' => request()->routeIs('cashier.current-orders'),
                            ])
                        @endif
                    @endif
                    @if ($can('pickup_station'))
                        @include('partials.staff-nav-link', [
                            'href' => route('admin.pickup-station.index'),
                            'label' => 'Pickup Station',
                            'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" /></svg>',
                            'active' => request()->routeIs('admin.pickup-station.*'),
                        ])
                    @endif
                </div>
            </div>
        @endif

        @if ($can('orders'))
            <div>
                <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.14em]" style="color: {{ $brandSettings->primary_font_color ?? '#78350f' }};">Operations</p>
                <div class="flex flex-col gap-0.5">
                    @if ($can('orders'))
                        @include('partials.staff-nav-link', [
                            'href' => route('cashier.payments'),
                            'label' => 'Payments',
                            'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>',
                            'active' => request()->routeIs('cashier.payments'),
                        ])
                        @if ($user->isCashier())
                            @include('partials.staff-nav-link', [
                                'href' => route('cashier.tables'),
                                'label' => 'Live Tables',
                                'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>',
                                'active' => request()->routeIs('cashier.tables'),
                            ])
                        @endif
                    @endif
                </div>
            </div>
        @endif

        @if ($can('inventory') || $can('payroll') || $can('expenses'))
            <div>
                <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.14em]" style="color: {{ $brandSettings->primary_font_color ?? '#78350f' }};">ERP</p>
                <div class="flex flex-col gap-0.5">
                    @if ($can('inventory'))
                        @include('partials.staff-nav-link', [
                            'href' => route('inventory.index'),
                            'label' => 'Inventory',
                            'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>',
                            'active' => request()->routeIs('inventory.index') || request()->routeIs('inventory.categories') || request()->routeIs('inventory.stock-movements'),
                        ])
                        @include('partials.staff-nav-link', [
                            'href' => route('inventory.supplies'),
                            'label' => 'Takeout Supplies',
                            'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V4m8 3V4m-9 8h10m-1 8H7a2 2 0 01-2-2V10h14v8a2 2 0 01-2 2z" /></svg>',
                            'active' => request()->routeIs('inventory.supplies'),
                        ])
                        @include('partials.staff-nav-link', [
                            'href' => route('inventory.stock-categories'),
                            'label' => 'Stock Categories',
                            'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h16" /></svg>',
                            'active' => request()->routeIs('inventory.stock-categories'),
                        ])
                        @include('partials.staff-nav-link', [
                            'href' => route('inventory.bulk-purchases.history'),
                            'label' => 'Bulk History',
                            'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2m-6 0a2 2 0 002 2h2a2 2 0 002-2m-6 0a2 2 0 012 2h2a2 2 0 012-2" /></svg>',
                            'active' => request()->routeIs('inventory.bulk-purchases.history'),
                        ])
                    @endif
                    @if ($can('payroll'))
                        @include('partials.staff-nav-link', [
                            'href' => route('payroll.index'),
                            'label' => 'Payroll',
                            'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>',
                            'active' => request()->routeIs('payroll.*'),
                        ])
                    @endif
                    @if ($can('expenses'))
                        @include('partials.staff-nav-link', [
                            'href' => route('expenses.index'),
                            'label' => 'Expenses',
                            'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
                            'active' => request()->routeIs('expenses.*'),
                        ])
                    @endif
                </div>
            </div>
        @endif

        @if ($can('reports'))
            <div>
                <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.14em]" style="color: {{ $brandSettings->primary_font_color ?? '#78350f' }};">Reports</p>
                <div class="flex flex-col gap-0.5">
                    @include('partials.staff-nav-link', [
                        'href' => route('reports.index'),
                        'label' => 'Financial Reports',
                        'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>',
                        'active' => request()->routeIs('reports.*'),
                    ])
                </div>
            </div>
        @endif
        
    </div>
</div>
