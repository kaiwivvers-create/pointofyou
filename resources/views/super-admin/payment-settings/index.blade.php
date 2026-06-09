@extends('layouts.staff')

@section('title', 'Payment Settings')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Payment Settings</h1>
            <p class="staff-page-subtitle">Configure payment methods and details for your cafe.</p>
        </div>
    </div>

    <x-flash />

    <form method="POST" action="{{ route('super-admin.payment-settings.update') }}" enctype="multipart/form-data">
        @csrf
        
        <!-- QR Code Settings -->
        <div class="staff-card p-6 mb-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">QR Code Payment</h2>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1">QR Code Image</label>
                <input type="file" name="qr_code_image_raw" id="qr_code_image" class="staff-input" accept="image/*" onchange="openCropModal(event)">
                <input type="hidden" name="qr_code_cropped" id="qr_code_cropped">
                <p class="text-xs text-slate-500 mt-1">After selecting a file, you can crop it before saving.</p>
                @if ($settings->qr_code_image)
                    <div class="mt-2" id="current-qr-wrap">
                        <p class="text-sm text-slate-600 mb-1">Current QR code:</p>
                        <img src="{{ asset('storage/' . $settings->qr_code_image) }}" alt="QR Code" class="h-32 w-32 object-cover rounded-lg border border-slate-200" id="current-qr-img">
                    </div>
                @endif
                <div id="crop-preview-wrap" class="mt-2 hidden">
                    <p class="text-sm text-slate-600 mb-1">New QR code (cropped preview):</p>
                    <img id="crop-preview-img" class="h-32 w-32 object-cover rounded-lg border border-slate-200">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">QR Code Instructions</label>
                <textarea name="qr_code_instructions" id="qr_code_instructions" maxlength="5000" class="staff-input" rows="3" placeholder="Instructions for customers using QR code payment">{{ $settings->qr_code_instructions }}</textarea>
            </div>
        </div>
        
        <!-- Bank Transfer Settings -->
        <div class="staff-card p-6 mb-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Bank Transfer Details</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Bank Name</label>
                    <input type="text" name="bank_name" id="bank_name" value="{{ $settings->bank_name }}" maxlength="255" class="staff-input" placeholder="e.g., Chase Bank">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Account Number</label>
                    <input type="text" name="account_number" id="account_number" value="{{ $settings->account_number }}" maxlength="50" class="staff-input" placeholder="e.g., 1234567890">
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1">Account Name</label>
                <input type="text" name="account_name" id="account_name" value="{{ $settings->account_name }}" maxlength="255" class="staff-input" placeholder="e.g., Your Business Name">
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Bank Address</label>
                    <input type="text" name="bank_address" id="bank_address" value="{{ $settings->bank_address }}" maxlength="500" class="staff-input" placeholder="e.g., 123 Bank Street">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">SWIFT Code</label>
                    <input type="text" name="swift_code" id="swift_code" value="{{ $settings->swift_code }}" maxlength="20" class="staff-input" placeholder="e.g., CHASUS33">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Transfer Instructions</label>
                <textarea name="transfer_instructions" id="transfer_instructions" maxlength="5000" class="staff-input" rows="3" placeholder="Instructions for bank transfer payments">{{ $settings->transfer_instructions }}</textarea>
            </div>
        </div>
        
        <!-- Card Payment Settings -->
        <div class="staff-card p-6 mb-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Card Payment</h2>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Card Instructions</label>
                <textarea name="card_instructions" id="card_instructions" maxlength="5000" class="staff-input" rows="3" placeholder="Instructions for card payments">{{ $settings->card_instructions }}</textarea>
            </div>
        </div>
        
        <!-- Cash Payment Settings -->
        <div class="staff-card p-6 mb-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Cash Payment</h2>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Cash Instructions</label>
                <textarea name="cash_instructions" id="cash_instructions" maxlength="5000" class="staff-input" rows="3" placeholder="Instructions for cash payments">{{ $settings->cash_instructions }}</textarea>
            </div>
        </div>

        <!-- Tax Settings -->
        <div class="staff-card p-6 mb-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Tax Settings</h2>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Tax Rate (%)</label>
                <input type="number" name="tax_rate" id="tax_rate" value="{{ $settings->tax_rate ?? 10 }}" step="0.01" min="0" max="100" class="staff-input" placeholder="e.g., 10">
                <p class="text-xs text-slate-500 mt-1">Enter the tax rate as a percentage (e.g., 10 for 10%)</p>
            </div>
        </div>
        
        <div class="flex justify-end gap-3">
            <a href="{{ route('super-admin.dashboard') }}" class="staff-btn-secondary">Cancel</a>
            <button type="submit" class="staff-btn-primary">Save Settings</button>
        </div>
    </form>

    <!-- Crop Modal -->
    <div id="cropModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div id="cropModalContent" class="bg-white rounded-2xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden scale-95 opacity-0 transition-all duration-200">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-slate-900">Crop QR Code</h2>
                    <button type="button" onclick="closeCropModal()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-sm hover:bg-red-500 hover:text-white transition-colors">×</button>
                </div>
                <div class="relative bg-slate-100 rounded-lg overflow-hidden" style="height: 400px;">
                    <img id="cropImage" class="max-w-full" style="max-height: 400px;">
                </div>
                <div class="flex justify-end gap-3 mt-4">
                    <button type="button" onclick="closeCropModal()" class="px-4 py-2 rounded-lg bg-slate-200 text-slate-700 font-medium hover:bg-slate-300 transition-colors">Cancel</button>
                    <button type="button" onclick="applyCrop()" class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-medium hover:bg-emerald-700 transition-colors">Apply Crop</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.css">
    <script>
        let uploadedQrUrl = '{{ $settings->qr_code_image ? asset('storage/' . $settings->qr_code_image) : '' }}';
        let cropper = null;

        function openCropModal(event) {
            const fileInput = document.getElementById('qr_code_image');
            const cropModal = document.getElementById('cropModal');
            const cropContent = document.getElementById('cropModalContent');
            const cropImage = document.getElementById('cropImage');

            if (fileInput.files && fileInput.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    cropImage.src = e.target.result;
                    cropModal.classList.remove('hidden');
                    cropModal.classList.add('flex');

                    cropImage.onload = function() {
                        setTimeout(() => {
                            cropContent.classList.remove('scale-95', 'opacity-0');
                            cropContent.classList.add('scale-100', 'opacity-100');

                            if (cropper) {
                                cropper.destroy();
                            }

                            cropper = new Cropper(cropImage, {
                                aspectRatio: 1,
                                viewMode: 1,
                                dragMode: 'move',
                                autoCropArea: 0.8,
                                restore: false,
                                guides: true,
                                center: true,
                                highlight: false,
                                cropBoxMovable: true,
                                cropBoxResizable: true,
                                toggleDragModeOnDblclick: false,
                            });
                        }, 10);
                    };
                };
                reader.readAsDataURL(fileInput.files[0]);
            }
        }

        function closeCropModal() {
            const cropModal = document.getElementById('cropModal');
            const cropContent = document.getElementById('cropModalContent');

            if (cropper) {
                cropper.destroy();
                cropper = null;
            }

            cropContent.classList.remove('scale-100', 'opacity-100');
            cropContent.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                cropModal.classList.add('hidden');
                cropModal.classList.remove('flex');
                document.getElementById('qr_code_image').value = '';
            }, 200);
        }

        function applyCrop() {
            if (cropper) {
                const canvas = cropper.getCroppedCanvas({
                    width: 256,
                    height: 256,
                });

                const croppedDataUrl = canvas.toDataURL('image/jpeg', 0.9);
                document.getElementById('qr_code_cropped').value = croppedDataUrl;

                // Update preview
                uploadedQrUrl = croppedDataUrl;

                // Show cropped preview
                const cropPreviewWrap = document.getElementById('crop-preview-wrap');
                const cropPreviewImg = document.getElementById('crop-preview-img');
                cropPreviewImg.src = croppedDataUrl;
                cropPreviewWrap.classList.remove('hidden');

                // Hide current QR code if exists
                const currentQrWrap = document.getElementById('current-qr-wrap');
                if (currentQrWrap) {
                    currentQrWrap.classList.add('hidden');
                }

                closeCropModal();
            }
        }
    </script>
@endsection
