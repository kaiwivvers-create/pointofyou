@php
    $user = auth()->user();
@endphp

<nav class="flex flex-1 flex-col gap-6 overflow-y-auto text-sm">
    @if ($user->isSuperAdmin())
        <div>
            <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">Overview</p>
            <div class="flex flex-col gap-0.5">
                @include('partials.staff-nav-link', [
                    'href' => route('super-admin.dashboard'),
                    'label' => 'Dashboard',
                    'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>',
                    'active' => request()->routeIs('super-admin.dashboard'),
                ])
            </div>
        </div>

        <div>
            <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">Team</p>
            <div class="flex flex-col gap-0.5">
                @include('partials.staff-nav-link', [
                    'href' => route('super-admin.users.index'),
                    'label' => 'Staff users',
                    'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>',
                    'active' => request()->routeIs('super-admin.users.*'),
                ])
            </div>
        </div>

        <div>
            <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">Bakery</p>
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
            <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">Operations</p>
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
            <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">Overview</p>
            <div class="flex flex-col gap-0.5">
                @include('partials.staff-nav-link', [
                    'href' => route('admin.dashboard'),
                    'label' => 'Dashboard',
                    'icon' => '<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>',
                    'active' => request()->routeIs('admin.dashboard'),
                ])
            </div>
        </div>

        <div>
            <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">Bakery</p>
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
    @elseif ($user->isCashier())
        <div>
            <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">Operations</p>
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
</nav>
