@php
    $user = auth()->user();
    
    // Get permissions for the user's role
    $userPermissions = [];
    if ($user) {
        $userPermissions = \App\Models\Permission::where('role', $user->role->value)
            ->get()
            ->keyBy('permission');
    }
    
    $can = function($permission, $action = 'view') use ($user, $userPermissions) {
        if (!$user) return false;
        // Super admin has all permissions
        if ($user->isSuperAdmin()) return true;
        // Check permission from database
        $perm = $userPermissions->get($permission);
        if (!$perm) return false;
        return $action === 'edit' ? $perm->can_edit : $perm->can_view;
    };
@endphp

@if ($user)
<nav class="flex flex-1 flex-col gap-6 overflow-y-auto text-sm">
    @if ($user->isOwner())
        <div>
            <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.14em]" style="color: {{ $brandSettings->primary_color ?? '#f59e0b' }};">Overview</p>
            <div class="flex flex-col gap-0.5">
                @if ($can('dashboard'))
                    @include('partials.staff-nav-link', [
                        'href' => route('owner.dashboard'),
                        'label' => 'Dashboard',
                        'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>',
                        'active' => request()->routeIs('owner.dashboard'),
                    ])
                @endif
            </div>
        </div>

        @if ($can('menu') || $can('tables'))
            <div>
                <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.14em]" style="color: {{ $brandSettings->primary_color ?? '#f59e0b' }};">Bakery</p>
                <div class="flex flex-col gap-0.5">
                    @if ($can('menu'))
                        @include('partials.staff-nav-link', [
                            'href' => route('admin.menu.index'),
                            'label' => 'Menu',
                            'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>',
                            'active' => request()->routeIs('admin.menu.*'),
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
                </div>
            </div>
        @endif

        @if ($can('orders'))
            <div>
                <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.14em]" style="color: {{ $brandSettings->primary_color ?? '#f59e0b' }};">Operations</p>
                <div class="flex flex-col gap-0.5">
                    @include('partials.staff-nav-link', [
                        'href' => route('cashier.dashboard'),
                        'label' => 'Payments',
                        'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>',
                        'active' => request()->routeIs('cashier.*'),
                    ])
                </div>
            </div>
        @endif

        @if ($can('reports'))
            <div>
                <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.14em]" style="color: {{ $brandSettings->primary_color ?? '#f59e0b' }};">Reports</p>
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
    @elseif ($user->isManager())
        <div>
            <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.14em]" style="color: {{ $brandSettings->primary_color ?? '#f59e0b' }};">Overview</p>
            <div class="flex flex-col gap-0.5">
                @if ($can('dashboard'))
                    @include('partials.staff-nav-link', [
                        'href' => route('manager.dashboard'),
                        'label' => 'Dashboard',
                        'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>',
                        'active' => request()->routeIs('manager.dashboard'),
                    ])
                @endif
            </div>
        </div>

        @if ($can('menu') || $can('tables'))
            <div>
                <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.14em]" style="color: {{ $brandSettings->primary_color ?? '#f59e0b' }};">Bakery</p>
                <div class="flex flex-col gap-0.5">
                    @if ($can('menu'))
                        @include('partials.staff-nav-link', [
                            'href' => route('admin.menu.index'),
                            'label' => 'Menu',
                            'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>',
                            'active' => request()->routeIs('admin.menu.*'),
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
                </div>
            </div>
        @endif

        @if ($can('orders'))
            <div>
                <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.14em]" style="color: {{ $brandSettings->primary_color ?? '#f59e0b' }};">Operations</p>
                <div class="flex flex-col gap-0.5">
                    @include('partials.staff-nav-link', [
                        'href' => route('cashier.dashboard'),
                        'label' => 'Payments',
                        'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>',
                        'active' => request()->routeIs('cashier.*'),
                    ])
                </div>
            </div>
        @endif
    @elseif ($user->isSuperAdmin())
        <div>
            <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.14em]" style="color: {{ $brandSettings->primary_color ?? '#f59e0b' }};">Overview</p>
            <div class="flex flex-col gap-0.5">
                @include('partials.staff-nav-link', [
                    'href' => route('super-admin.dashboard'),
                    'label' => 'Dashboard',
                    'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>',
                    'active' => request()->routeIs('super-admin.dashboard'),
                ])
                @include('partials.staff-nav-link', [
                    'href' => route('super-admin.brand-settings.index'),
                    'label' => 'Brand Settings',
                    'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" /></svg>',
                    'active' => request()->routeIs('super-admin.brand-settings.*'),
                ])
            </div>
        </div>

        <div>
            <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.14em]" style="color: {{ $brandSettings->primary_color ?? '#f59e0b' }};">Team</p>
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
            </div>
        </div>

        <div>
            <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.14em]" style="color: {{ $brandSettings->primary_color ?? '#f59e0b' }};">Bakery</p>
            <div class="flex flex-col gap-0.5">
                @include('partials.staff-nav-link', [
                    'href' => route('admin.menu.index'),
                    'label' => 'Menu',
                    'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>',
                    'active' => request()->routeIs('admin.menu.*'),
                ])
                @include('partials.staff-nav-link', [
                    'href' => route('admin.tables.index'),
                    'label' => 'Tables & QR',
                    'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>',
                    'active' => request()->routeIs('admin.tables.*'),
                ])
            </div>
        </div>

        <div>
            <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.14em]" style="color: {{ $brandSettings->primary_color ?? '#f59e0b' }};">Operations</p>
            <div class="flex flex-col gap-0.5">
                @include('partials.staff-nav-link', [
                    'href' => route('cashier.dashboard'),
                    'label' => 'Payments',
                    'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>',
                    'active' => request()->routeIs('cashier.*'),
                ])
            </div>
        </div>
    @elseif ($user->isAdmin())
        <div>
            <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.14em]" style="color: {{ $brandSettings->primary_color ?? '#f59e0b' }};">Overview</p>
            <div class="flex flex-col gap-0.5">
                @if ($can('dashboard'))
                    @include('partials.staff-nav-link', [
                        'href' => route('admin.dashboard'),
                        'label' => 'Dashboard',
                        'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>',
                        'active' => request()->routeIs('admin.dashboard'),
                    ])
                @endif
            </div>
        </div>

        @if ($can('menu') || $can('tables'))
            <div>
                <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.14em]" style="color: {{ $brandSettings->primary_color ?? '#f59e0b' }};">Bakery</p>
                <div class="flex flex-col gap-0.5">
                    @if ($can('menu'))
                        @include('partials.staff-nav-link', [
                            'href' => route('admin.menu.index'),
                            'label' => 'Menu',
                            'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>',
                            'active' => request()->routeIs('admin.menu.*'),
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
                </div>
            </div>
        @endif
    @elseif ($user->isCashier())
        @if ($can('orders'))
            <div>
                <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.14em]" style="color: {{ $brandSettings->primary_color ?? '#f59e0b' }};">Operations</p>
                <div class="flex flex-col gap-0.5">
                    @include('partials.staff-nav-link', [
                        'href' => route('cashier.dashboard'),
                        'label' => 'Payments',
                        'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>',
                        'active' => request()->routeIs('cashier.*'),
                    ])
                </div>
            </div>
        @endif
    @endif
@endif
</nav>
