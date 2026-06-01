@extends('layouts.staff')

@section('title', 'Current Orders')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-end">
        <div>
            <h1 class="font-display text-3xl font-bold text-slate-900 tracking-tight">Kitchen Dashboard</h1>
            <p class="text-slate-500 mt-1">Live feed of active orders being prepared.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="relative flex h-3 w-3">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
            </span>
            <span class="text-sm font-semibold text-slate-600">Auto-refreshing</span>
        </div>
    </div>

    <x-flash />

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($activeOrders as $order)
            <div class="bg-white rounded-3xl shadow-sm border {{ $order->status->value === 'paid' ? 'border-emerald-200' : 'border-amber-200' }} overflow-hidden flex flex-col relative group">
                
                @if($order->status->value === 'paid')
                    <div class="absolute top-0 right-0 bg-emerald-100 text-emerald-800 text-xs font-bold px-3 py-1 rounded-bl-xl border-b border-l border-emerald-200 uppercase tracking-wider">
                        Paid
                    </div>
                @endif
                
                <div class="p-5 border-b border-slate-100 {{ $order->status->value === 'paid' ? 'bg-emerald-50/50' : 'bg-amber-50/50' }}">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-display text-xl font-bold text-slate-900">
                                @if($order->order_type === 'dine_in' && $order->cafeTable)
                                    Table {{ $order->cafeTable->name }}
                                @else
                                    Takeout
                                @endif
                            </h3>
                            <p class="text-xs font-medium text-slate-500 mt-1">
                                Order #{{ $order->id }} &middot; {{ $order->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            @if($order->isFullyReady())
                                <span class="bg-indigo-100 text-indigo-700 text-[10px] font-bold px-2 py-0.5 rounded border border-indigo-200 uppercase tracking-wider">Ready</span>
                            @else
                                <span class="bg-blue-100 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded border border-blue-200 uppercase tracking-wider">Preparing</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="p-5 flex-1 space-y-4">
                    @foreach($order->items as $item)
                        <div class="flex items-start gap-4 p-3 rounded-2xl border {{ $item->is_ready ? 'bg-slate-50 border-slate-100 opacity-60' : 'bg-white border-slate-200 shadow-sm' }} transition-all">
                            <form action="{{ route('admin.current-orders.toggle-ready', $item->id) }}" method="POST" class="shrink-0 mt-0.5">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-6 h-6 rounded-md flex items-center justify-center border-2 transition-colors {{ $item->is_ready ? 'bg-emerald-500 border-emerald-500 text-white' : 'bg-white border-slate-300 text-transparent hover:border-emerald-400' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </button>
                            </form>
                            
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start gap-2">
                                    <h4 class="font-bold text-slate-900 text-sm {{ $item->is_ready ? 'line-through text-slate-500' : '' }}"><span class="text-amber-600 mr-1">{{ $item->quantity }}x</span> {{ $item->item_name }}</h4>
                                </div>
                                
                                @if(!empty($item->modifications))
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        @foreach($item->modifications as $mod)
                                            <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                                + {{ is_array($mod) ? $mod['name'] : $mod }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                @if($item->notes)
                                    <div class="mt-2 p-2 rounded-lg bg-amber-50 border border-amber-100 text-xs font-semibold text-amber-900 flex gap-1.5 items-start">
                                        <svg class="size-4 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
                                        <span class="break-words">{{ $item->notes }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 flex flex-col items-center justify-center text-center bg-white rounded-3xl border border-dashed border-slate-300">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                    <svg class="size-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                </div>
                <h3 class="text-xl font-display font-bold text-slate-900">No active orders</h3>
                <p class="text-slate-500 mt-1 max-w-sm">The kitchen is clear. New orders will appear here automatically.</p>
            </div>
        @endforelse
    </div>
</div>

<script>
    // Auto-refresh every 10 seconds
    let refreshTimer = setTimeout(function() {
        window.location.reload();
    }, 10000);

    // Reset timer on visibility change to prevent spamming server in background
    document.addEventListener("visibilitychange", function() {
        if (document.visibilityState === 'visible') {
            refreshTimer = setTimeout(function() {
                window.location.reload();
            }, 10000);
        } else {
            clearTimeout(refreshTimer);
        }
    });
</script>
@endsection
