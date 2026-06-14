<?php $__env->startSection('title', 'Payments'); ?>

<?php $__env->startSection('content'); ?>
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Payments</h1>
            <p class="staff-page-subtitle">View and manage payment history.</p>
        </div>
    </div>

    <?php if (isset($component)) { $__componentOriginal5168fdb0c14fd91c6598264bc4be63f2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5168fdb0c14fd91c6598264bc4be63f2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.flash','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flash'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5168fdb0c14fd91c6598264bc4be63f2)): ?>
<?php $attributes = $__attributesOriginal5168fdb0c14fd91c6598264bc4be63f2; ?>
<?php unset($__attributesOriginal5168fdb0c14fd91c6598264bc4be63f2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5168fdb0c14fd91c6598264bc4be63f2)): ?>
<?php $component = $__componentOriginal5168fdb0c14fd91c6598264bc4be63f2; ?>
<?php unset($__componentOriginal5168fdb0c14fd91c6598264bc4be63f2); ?>
<?php endif; ?>

    <div class="staff-table-wrap">
        <div class="overflow-x-auto">
            <table class="staff-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Table</th>
                        <th>Total</th>
                        <th>Payment Method</th>
                        <th>Paid By</th>
                        <th>Paid At</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="font-semibold">#<?php echo e($order->id); ?></td>
                            <td><?php echo e($order->cafeTable?->name ?? 'N/A'); ?></td>
                            <td class="font-semibold">$<?php echo e(number_format($order->total, 2)); ?></td>
                            <td>
                                <span class="px-2 py-1 rounded-full text-xs font-medium 
                                    <?php if($order->payment_method === 'cash'): ?> bg-emerald-100 text-emerald-700
                                    <?php elseif($order->payment_method === 'card'): ?> bg-blue-100 text-blue-700
                                    <?php elseif($order->payment_method === 'qr'): ?> bg-purple-100 text-purple-700
                                    <?php else: ?> bg-slate-100 text-slate-700
                                    <?php endif; ?>">
                                    <?php echo e(ucfirst($order->payment_method)); ?>

                                </span>
                            </td>
                            <td><?php echo e($order->cashier?->name ?? 'N/A'); ?></td>
                            <td><?php echo e($order->paid_at?->format('M d, Y H:i') ?? 'N/A'); ?></td>
                            <td>
                                <?php if($order->is_closed): ?>
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700">Closed</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Open</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <button onclick="viewReceipt(<?php echo e($order->id); ?>)" class="staff-link">View Receipt</button>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="text-center text-slate-500 py-8">No payments found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php echo e($orders->links()); ?>


    <!-- Receipt Modal -->
    <div id="receipt-modal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[9999] opacity-0 transition-opacity duration-300">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto transform scale-95 transition-transform duration-300" id="receipt-modal-content">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-slate-900">Receipt</h2>
                    <button onclick="closeReceiptModal()" class="text-slate-400 hover:text-slate-600">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div id="receipt-content">
                    <div class="text-center py-8">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-slate-900"></div>
                    </div>
                </div>
                <div class="mt-4 flex gap-2">
                    <button onclick="printReceipt()" class="staff-btn-primary flex-1">Print Receipt</button>
                    <button onclick="closeReceiptModal()" class="staff-btn-secondary flex-1">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function viewReceipt(orderId) {
            const modal = document.getElementById('receipt-modal');
            const modalContent = document.getElementById('receipt-modal-content');
            const content = document.getElementById('receipt-content');
            
            // Show modal with animation
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modalContent.classList.remove('scale-95');
                modalContent.classList.add('scale-100');
            }, 10);
            
            // Load content
            content.innerHTML = '<div class="text-center py-8"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-slate-900"></div></div>';
            
            fetch('<?php echo e(route('cashier.receipt', ':id')); ?>'.replace(':id', orderId))
                .then(response => response.text())
                .then(html => {
                    content.innerHTML = html;
                })
                .catch(error => {
                    content.innerHTML = '<div class="text-center py-8 text-red-600">Failed to load receipt.</div>';
                });
        }
        
        function closeReceiptModal() {
            const modal = document.getElementById('receipt-modal');
            const modalContent = document.getElementById('receipt-modal-content');
            
            // Animate out
            modal.classList.add('opacity-0');
            modalContent.classList.remove('scale-100');
            modalContent.classList.add('scale-95');
            
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 300);
        }
        
        function printReceipt() {
            const content = document.getElementById('receipt-content');
            const printWindow = window.open('', '', 'width=600,height=800');
            printWindow.document.write('<html><head><title>Receipt</title>');
            printWindow.document.write('<style>body{font-family:monospace;padding:20px;}.receipt-header{text-align:center;margin-bottom:20px;}.receipt-item{display:flex;justify-content:space-between;margin:5px 0;}.receipt-total{border-top:1px solid #000;margin-top:10px;padding-top:10px;font-weight:bold;}</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write(content.innerHTML);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.print();
        }
        
        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeReceiptModal();
            }
        });
        
        // Close modal on backdrop click
        document.getElementById('receipt-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeReceiptModal();
            }
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.staff', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\cindy\OneDrive\Documents\tick B\pplg\writing hting\pointofyou\pointofyou\resources\views/cashier/payments.blade.php ENDPATH**/ ?>