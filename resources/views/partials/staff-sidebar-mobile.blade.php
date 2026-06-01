@php
    $user = auth()->user();
    
    $can = function ($permission) use ($user) {
        if ($user->isSuperAdmin()) return true;
        $p = \App\Models\Permission::where('role', $user->role->value)
            ->where('permission', $permission)
            ->first();
        return $p && $p->can_view;
    };

    $allPossibleLinks = [
        ['permission' => 'dashboard', 'route' => $user->isSuperAdmin() ? 'super-admin.dashboard' : ($user->isOwner() ? 'owner.dashboard' : ($user->isManager() ? 'manager.dashboard' : 'admin.dashboard')), 'label' => 'Home', 'match' => '*.dashboard'],
        ['permission' => 'brand_settings', 'route' => 'super-admin.brand-settings.index', 'label' => 'Brand', 'match' => 'super-admin.brand-settings.*'],
        ['permission' => 'users', 'route' => 'super-admin.users.index', 'label' => 'Staff', 'match' => 'super-admin.users.*'],
        ['permission' => 'permissions', 'route' => 'super-admin.permissions.index', 'label' => 'Permissions', 'match' => 'super-admin.permissions.*'],
        ['permission' => 'roles', 'route' => 'super-admin.roles.index', 'label' => 'Roles', 'match' => 'super-admin.roles.*'],
        ['permission' => 'menu', 'route' => 'admin.menu.index', 'label' => 'Menu', 'match' => 'admin.menu.*'],
        ['permission' => 'categories', 'route' => 'admin.menu-categories.index', 'label' => 'Categories', 'match' => 'admin.menu-categories.*'],
        ['permission' => 'promos', 'route' => 'admin.promos.index', 'label' => 'Promos', 'match' => 'admin.promos.*'],
        ['permission' => 'tables', 'route' => 'admin.tables.index', 'label' => 'Tables', 'match' => 'admin.tables.*'],
        ['permission' => 'kitchen', 'route' => 'admin.current-orders.index', 'label' => 'Kitchen', 'match' => 'admin.current-orders.*'],
        ['permission' => 'kitchen', 'route' => 'admin.pickup-station.index', 'label' => 'Pickup Station', 'match' => 'admin.pickup-station.*'],
        ['permission' => 'orders', 'route' => 'cashier.dashboard', 'label' => 'Pay', 'match' => 'cashier.*'],
        ['permission' => 'inventory', 'route' => 'inventory.index', 'label' => 'Inventory', 'match' => 'inventory.index|inventory.categories|inventory.stock-movements|inventory.bulk-purchases.history'],
        ['permission' => 'inventory', 'route' => 'inventory.supplies', 'label' => 'Supplies', 'match' => 'inventory.supplies'],
        ['permission' => 'inventory', 'route' => 'inventory.stock-categories', 'label' => 'Stock Categories', 'match' => 'inventory.stock-categories'],
        ['permission' => 'inventory', 'route' => 'inventory.bulk-purchases.history', 'label' => 'Bulk History', 'match' => 'inventory.bulk-purchases.history'],
        ['permission' => 'payroll', 'route' => 'payroll.index', 'label' => 'Payroll', 'match' => 'payroll.*'],
        ['permission' => 'expenses', 'route' => 'expenses.index', 'label' => 'Expenses', 'match' => 'expenses.*'],
        ['permission' => 'reports', 'route' => 'reports.index', 'label' => 'Reports', 'match' => 'reports.*'],
        ['route' => '#', 'label' => 'Profile', 'match' => '', 'action' => 'openProfileModal()'],
    ];

    $links = [];
    foreach ($allPossibleLinks as $link) {
        if (isset($link['permission']) && !$can($link['permission'])) {
            continue;
        }
        $links[] = $link;
    }
@endphp

<div class="flex gap-1 min-w-max pb-1">
    @foreach ($links as $link)
        @php
            $matches = explode('|', $link['match']);
            $isActive = false;
            foreach ($matches as $match) {
                if (request()->routeIs($match)) {
                    $isActive = true;
                    break;
                }
            }
        @endphp
        <a
            @if(isset($link['action']))
                href="javascript:void(0)"
                onclick="{{ $link['action'] }}; return false;"
            @else
                href="{{ route($link['route']) }}"
            @endif
            @class([
                'shrink-0 rounded px-2 py-1 text-[10px] font-semibold transition-colors',
                'bg-slate-900 text-white shadow-sm' => $isActive || (str_contains($link['match'], '*.dashboard') && in_array(request()->route()->getName(), ['super-admin.dashboard', 'owner.dashboard', 'manager.dashboard', 'admin.dashboard'])),
                'bg-slate-100 text-slate-700 hover:bg-slate-200' => !($isActive || (str_contains($link['match'], '*.dashboard') && in_array(request()->route()->getName(), ['super-admin.dashboard', 'owner.dashboard', 'manager.dashboard', 'admin.dashboard']))),
            ])
            style="{{ ($isActive || (str_contains($link['match'], '*.dashboard') && in_array(request()->route()->getName(), ['super-admin.dashboard', 'owner.dashboard', 'manager.dashboard', 'admin.dashboard']))) ? 'background-color: ' . ($brandSettings->primary_font_color ?? '#78350f') : '' }}"
        >{{ $link['label'] }}</a>
    @endforeach
</div>
