@extends('layouts.staff')

@section('title', 'Barcode Manager')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold font-display text-slate-800">Barcode Manager</h1>
            <p class="text-slate-500 mt-1">Assign and manage barcodes for menu items, gifts, and inventory products.</p>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <form action="{{ route('admin.barcodes.index') }}" method="GET" class="p-6 border-b border-slate-100 flex gap-4 bg-slate-50 items-center">
            <div class="relative flex-1 text-slate-400 focus-within:text-blue-500">
                <svg class="absolute left-4 top-1/2 -mt-2.5 size-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"></path></svg>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search by name, category, or barcode..." class="w-full pl-12 pr-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-slate-800">
            </div>
            <button type="submit" class="px-6 py-2 bg-slate-800 text-white font-semibold rounded-xl hover:bg-slate-700 transition-colors">Search</button>
            @if(isset($search) && $search)
                <a href="{{ route('admin.barcodes.index') }}" class="px-6 py-2 bg-slate-200 text-slate-700 font-semibold rounded-xl hover:bg-slate-300 transition-colors">Clear</a>
            @endif
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left" id="barcodeTable">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Item Name</th>
                        <th class="px-6 py-4 font-semibold text-center">Type / Category</th>
                        <th class="px-6 py-4 font-semibold w-1/3">Barcode (Numbers Only)</th>
                        <th class="px-6 py-4 font-semibold w-24">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($paginator as $item)
                        <tr class="hover:bg-slate-50/50 transition-colors item-row">
                            <td class="px-6 py-4">
                                <p class="font-bold text-slate-800 item-name">{{ $item['name'] }}</p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                                    {{ $item['type'] === 'menu_item' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $item['type'] === 'gift' ? 'bg-purple-100 text-purple-800' : '' }}
                                    {{ $item['type'] === 'product' ? 'bg-amber-100 text-amber-800' : '' }} item-category">
                                    {{ $item['category'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="relative">
                                    <input type="text" value="{{ $item['barcode'] }}"
                                        onchange="updateBarcode({{ $item['id'] }}, '{{ $item['type'] }}', this)"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                        placeholder="Enter numbers..."
                                        class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono item-barcode">
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span id="status-{{ $item['type'] }}-{{ $item['id'] }}" class="text-sm">
                                    @if($item['barcode'])
                                        <svg class="size-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    @else
                                        <svg class="size-5 text-red-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    @endif
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="p-6 border-t border-slate-100">
            {{ $paginator->links() }}
        </div>
    </div>
</div>

<script>


function updateBarcode(id, type, inputElement) {
    const statusEl = document.getElementById(`status-${type}-${id}`);
    const newValue = inputElement.value;
    
    statusEl.innerHTML = '<svg class="animate-spin size-5 text-blue-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

    fetch('{{ route('admin.barcodes.update') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            id: id,
            type: type,
            barcode: newValue
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (newValue.trim() !== '') {
                statusEl.innerHTML = '<svg class="size-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
            } else {
                statusEl.innerHTML = '<svg class="size-5 text-red-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
            }
        } else {
            statusEl.innerHTML = '<svg class="size-5 text-red-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
            alert(data.message || 'Failed to update barcode.');
        }
    })
    .catch(err => {
        statusEl.innerHTML = '<svg class="size-5 text-red-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
        alert('An error occurred.');
    });
}
</script>
@endsection
