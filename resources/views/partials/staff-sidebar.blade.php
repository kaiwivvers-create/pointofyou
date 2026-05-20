@php
    $user = auth()->user();
@endphp

<nav class="flex flex-1 flex-col gap-6 overflow-y-auto text-sm">
    @if ($user->isSuperAdmin())
        <div>
            <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.14em] text-amber-500/90">Overview</p>
            <div class="flex flex-col gap-0.5">
                @include('partials.staff-nav-link', [
                    'href' => route('super-admin.dashboard'),
                    'label' => 'Dashboard',
                    'icon' => '🏠',
                    'active' => request()->routeIs('super-admin.dashboard'),
                ])
            </div>
        </div>

        <div>
            <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.14em] text-amber-500/90">Team</p>
            <div class="flex flex-col gap-0.5">
                @include('partials.staff-nav-link', [
                    'href' => route('super-admin.users.index'),
                    'label' => 'Staff users',
                    'icon' => '👥',
                    'active' => request()->routeIs('super-admin.users.*'),
                ])
            </div>
        </div>

        <div>
            <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.14em] text-amber-500/90">Bakery</p>
            <div class="flex flex-col gap-0.5">
                @include('partials.staff-nav-link', [
                    'href' => route('admin.menu.index'),
                    'label' => 'Menu',
                    'icon' => '📋',
                    'active' => request()->routeIs('admin.menu.*'),
                ])
                @include('partials.staff-nav-link', [
                    'href' => route('admin.tables.index'),
                    'label' => 'Tables & QR',
                    'icon' => '🪑',
                    'active' => request()->routeIs('admin.tables.*'),
                ])
            </div>
        </div>

        <div>
            <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.14em] text-amber-500/90">Operations</p>
            <div class="flex flex-col gap-0.5">
                @include('partials.staff-nav-link', [
                    'href' => route('cashier.dashboard'),
                    'label' => 'Payments',
                    'icon' => '💳',
                    'active' => request()->routeIs('cashier.*'),
                ])
            </div>
        </div>
    @elseif ($user->isAdmin())
        <div>
            <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.14em] text-amber-500/90">Overview</p>
            <div class="flex flex-col gap-0.5">
                @include('partials.staff-nav-link', [
                    'href' => route('admin.dashboard'),
                    'label' => 'Dashboard',
                    'icon' => '🏠',
                    'active' => request()->routeIs('admin.dashboard'),
                ])
            </div>
        </div>

        <div>
            <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.14em] text-amber-500/90">Bakery</p>
            <div class="flex flex-col gap-0.5">
                @include('partials.staff-nav-link', [
                    'href' => route('admin.menu.index'),
                    'label' => 'Menu',
                    'icon' => '📋',
                    'active' => request()->routeIs('admin.menu.*'),
                ])
                @include('partials.staff-nav-link', [
                    'href' => route('admin.tables.index'),
                    'label' => 'Tables & QR',
                    'icon' => '🪑',
                    'active' => request()->routeIs('admin.tables.*'),
                ])
            </div>
        </div>
    @elseif ($user->isCashier())
        <div>
            <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.14em] text-amber-500/90">Operations</p>
            <div class="flex flex-col gap-0.5">
                @include('partials.staff-nav-link', [
                    'href' => route('cashier.dashboard'),
                    'label' => 'Payments',
                    'icon' => '💳',
                    'active' => request()->routeIs('cashier.*'),
                ])
            </div>
        </div>
    @endif
</nav>
