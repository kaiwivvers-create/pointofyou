@php
    $bulkPurchaseTitle = $bulkPurchaseTitle ?? 'Bulk Purchase';
    $bulkPurchaseDescription = $bulkPurchaseDescription ?? 'Add multiple stock items at once.';
    $productOptions = collect($bulkProducts ?? [])->map(function ($product) {
        return '<option value="' . $product->id . '" data-price="' . e($product->purchase_price) . '">' . e($product->name) . ' — ' . e($product->stock_quantity) . ' in stock</option>';
    })->implode('');
@endphp

<div id="bulkPurchaseModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] transition-opacity duration-200">
    <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full mx-4 max-h-[90vh] overflow-y-auto transform transition-all duration-200 scale-95 opacity-0" id="bulkPurchaseModalContent">
        <div class="p-6 border-b border-slate-200 flex justify-between items-start gap-4">
            <div>
                <h2 class="text-xl font-semibold text-slate-900">{{ $bulkPurchaseTitle }}</h2>
                <p class="text-sm text-slate-500 mt-1">{{ $bulkPurchaseDescription }}</p>
            </div>
            <button type="button" onclick="closeBulkPurchaseModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form method="POST" action="{{ route('inventory.bulk-purchases.store') }}" class="p-6 space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Reference</label>
                    <input type="text" name="reference" maxlength="255" class="staff-input" placeholder="Supplier invoice, receipt number, etc.">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                    <input type="text" name="notes" maxlength="1000" class="staff-input" placeholder="Optional notes">
                </div>
            </div>

            <div class="flex items-center justify-between gap-4">
                <p class="text-sm font-semibold text-slate-700">Items</p>
                <button type="button" onclick="addBulkPurchaseRow()" class="text-sm font-semibold text-amber-700 hover:text-amber-800">+ Add row</button>
            </div>

            <div id="bulkPurchaseRows" class="space-y-3"></div>

            <div class="pt-2 flex gap-3 justify-end">
                <button type="button" onclick="closeBulkPurchaseModal()" class="staff-btn-secondary">Cancel</button>
                <button type="submit" class="staff-btn-primary">Save Bulk Purchase</button>
            </div>
        </form>
    </div>
</div>

<script>
    const bulkPurchaseProductOptions = @json($productOptions);

    function bulkPurchaseRowTemplate(index) {
        return `
            <div class="grid grid-cols-1 md:grid-cols-[1.5fr_0.5fr_0.7fr_0.7fr_auto] gap-3 items-end bulk-purchase-row">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Product</label>
                    <select name="items[${index}][product_id]" class="staff-input bulk-purchase-product" required>
                        <option value="">Select item</option>
                        ${bulkPurchaseProductOptions}
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Quantity</label>
                    <input type="number" min="1" name="items[${index}][quantity]" value="1" class="staff-input bulk-purchase-quantity" required>
                    <p class="mt-1 text-[11px] text-slate-500">Use this to buy multiple units of the same item at once.</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Unit Cost</label>
                    <input type="number" step="0.01" min="0" name="items[${index}][unit_cost]" class="staff-input bulk-purchase-unit-cost bg-slate-50" placeholder="Auto" readonly>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Total</label>
                    <input type="number" step="0.01" min="0" name="items[${index}][total]" class="staff-input bulk-purchase-total bg-slate-50" placeholder="Auto" readonly>
                </div>
                <div>
                    <button type="button" onclick="removeBulkPurchaseRow(this)" class="w-full py-3 rounded-lg bg-slate-100 text-slate-600 hover:bg-red-100 hover:text-red-700 font-semibold transition-colors">Remove</button>
                </div>
            </div>
        `;
    }

    function addBulkPurchaseRow() {
        const rows = document.getElementById('bulkPurchaseRows');
        const index = rows.querySelectorAll('.bulk-purchase-row').length;
        rows.insertAdjacentHTML('beforeend', bulkPurchaseRowTemplate(index));
    }

    function syncBulkPurchasePrice(selectElement) {
        const row = selectElement.closest('.bulk-purchase-row');
        if (!row) return;

        const unitCostInput = row.querySelector('.bulk-purchase-unit-cost');
        const quantityInput = row.querySelector('.bulk-purchase-quantity');
        const totalInput = row.querySelector('.bulk-purchase-total');
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const price = selectedOption?.dataset?.price;

        if (unitCostInput && price) {
            unitCostInput.value = Number(price).toFixed(2);
        }

        // Calculate total
        calculateBulkPurchaseTotal(row);
    }

    function calculateBulkPurchaseTotal(row) {
        const unitCostInput = row.querySelector('.bulk-purchase-unit-cost');
        const quantityInput = row.querySelector('.bulk-purchase-quantity');
        const totalInput = row.querySelector('.bulk-purchase-total');

        if (!unitCostInput || !quantityInput || !totalInput) return;

        const unitCost = Number(unitCostInput.value || 0);
        const quantity = Number(quantityInput.value || 1);
        const total = unitCost * quantity;

        totalInput.value = total.toFixed(2);
    }

    function removeBulkPurchaseRow(button) {
        const rows = document.getElementById('bulkPurchaseRows');
        const row = button.closest('.bulk-purchase-row');
        if (row) {
            row.remove();
        }
        if (!rows.querySelector('.bulk-purchase-row')) {
            addBulkPurchaseRow();
        }
    }

    function openBulkPurchaseModal() {
        const modal = document.getElementById('bulkPurchaseModal');
        const content = document.getElementById('bulkPurchaseModalContent');
        const rows = document.getElementById('bulkPurchaseRows');

        rows.innerHTML = '';
        addBulkPurchaseRow();
        setTimeout(() => {
            const selectElement = rows.querySelector('.bulk-purchase-product');
            if (selectElement) {
                syncBulkPurchasePrice(selectElement);
            }
        }, 0);

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
        }, 10);
    }

    function closeBulkPurchaseModal() {
        const modal = document.getElementById('bulkPurchaseModal');
        const content = document.getElementById('bulkPurchaseModalContent');

        content.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 200);
    }

    document.getElementById('bulkPurchaseModal')?.addEventListener('click', function (e) {
        if (e.target === this) {
            closeBulkPurchaseModal();
        }
    });

    document.addEventListener('change', function (e) {
        if (e.target.classList && e.target.classList.contains('bulk-purchase-product')) {
            syncBulkPurchasePrice(e.target);
        }
    });

    document.addEventListener('input', function (e) {
        if (e.target.classList && e.target.classList.contains('bulk-purchase-quantity')) {
            const row = e.target.closest('.bulk-purchase-row');
            if (row) {
                calculateBulkPurchaseTotal(row);
            }
        }
    });
</script>
