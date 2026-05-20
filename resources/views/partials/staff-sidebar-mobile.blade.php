@php
    $user = auth()->user();
    $links = [];

    if ($user->isSuperAdmin()) {
        $links = [
            ['route' => 'super-admin.dashboard', 'label' => 'Home', 'match' => 'super-admin.dashboard'],
            ['route' => 'super-admin.users.index', 'label' => 'Staff', 'match' => 'super-admin.users.*'],
            ['route' => 'admin.menu.index', 'label' => 'Menu', 'match' => 'admin.menu.*'],
            ['route' => 'admin.tables.index', 'label' => 'Tables', 'match' => 'admin.tables.*'],
            ['route' => 'cashier.dashboard', 'label' => 'Pay', 'match' => 'cashier.*'],
        ];
    } elseif ($user->isAdmin()) {
        $links = [
            ['route' => 'admin.dashboard', 'label' => 'Home', 'match' => 'admin.dashboard'],
            ['route' => 'admin.menu.index', 'label' => 'Menu', 'match' => 'admin.menu.*'],
            ['route' => 'admin.tables.index', 'label' => 'Tables', 'match' => 'admin.tables.*'],
        ];
    } elseif ($user->isCashier()) {
        $links = [
            ['route' => 'cashier.dashboard', 'label' => 'Payments', 'match' => 'cashier.*'],
        ];
    }
@endphp

<div class="flex gap-2 min-w-max pb-1">
    @foreach ($links as $link)
        <a
            href="{{ route($link['route']) }}"
            @class([
                'shrink-0 rounded-full px-4 py-2 text-xs font-semibold transition-colors',
                'bg-amber-800 text-amber-50 shadow-sm' => request()->routeIs($link['match']),
                'bg-amber-100 text-amber-900 hover:bg-amber-200' => ! request()->routeIs($link['match']),
            ])
        >{{ $link['label'] }}</a>
    @endforeach
</div>
