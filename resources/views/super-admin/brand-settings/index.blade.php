@extends('layouts.staff')

@section('title', 'Brand Settings')

@section('content')
    <div class="staff-page-header">
        <div>
            <h1 class="staff-page-title">Brand Settings</h1>
            <p class="staff-page-subtitle">Customize your cafe's appearance and information.</p>
        </div>
    </div>

    <x-flash />

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Form Column -->
        <form method="POST" action="{{ route('super-admin.brand-settings.update') }}" enctype="multipart/form-data">
            @csrf
            
            <!-- Brand Info -->
            <div class="staff-card p-6 mb-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Brand Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">App Name</label>
                        <input type="text" name="app_name" id="app_name" value="{{ $settings->app_name }}" class="staff-input" required oninput="updatePreview()">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Logo Fallback Letter(s)</label>
                        <input type="text" name="logo_fallback" id="logo_fallback" value="{{ $settings->logo_fallback }}" class="staff-input" required maxlength="10" oninput="updatePreview()">
                        <p class="text-xs text-slate-500 mt-1">Used when no logo is uploaded (max 10 characters)</p>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Logo</label>
                    <input type="file" name="logo_file_raw" id="logo" class="staff-input" accept="image/*" onchange="openCropModal(event)">
                    <input type="hidden" name="logo_cropped" id="logo_cropped">
                    <p class="text-xs text-slate-500 mt-1">After selecting a file, you can crop it before saving. This logo will also be used as the favicon.</p>
                    @if ($settings->logo)
                        <div class="mt-2" id="current-logo-wrap">
                            <p class="text-sm text-slate-600 mb-1">Current logo:</p>
                            <img src="{{ asset('storage/' . $settings->logo) }}" alt="Logo" class="h-16 w-16 object-cover rounded-lg" id="current-logo-img">
                        </div>
                    @endif
                    <div id="crop-preview-wrap" class="mt-2 hidden">
                        <p class="text-sm text-slate-600 mb-1">New logo (cropped preview):</p>
                        <img id="crop-preview-img" class="h-16 w-16 object-cover rounded-lg border border-slate-200">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Landing Page Badge</label>
                    <input type="text" name="landing_badge" id="landing_badge" value="{{ $settings->landing_badge ?? 'Artisan bakery since 2026' }}" class="staff-input" placeholder="e.g., Artisan bakery since 2026" oninput="updatePreview()">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Landing Page Kicker</label>
                    <input type="text" name="landing_kicker" id="landing_kicker" value="{{ $settings->landing_kicker }}" class="staff-input" placeholder="e.g., Freshly baked goodness, every day." oninput="updatePreview()">
                </div>
            </div>
            
            <!-- Fan Favourites -->
            <div class="staff-card p-6 mb-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Fan Favourites</h2>
                <p class="text-sm text-slate-600 mb-4">Select menu items to display on the landing page.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach ($menuItems as $item)
                        <label class="flex items-center gap-2 p-3 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                            <input type="checkbox" name="fan_favourite_ids[]" value="{{ $item->id }}" {{ in_array($item->id, $settings->fan_favourite_ids ?? []) ? 'checked' : '' }} class="rounded">
                            <div class="flex-1">
                                <p class="font-medium text-slate-900">{{ $item->name }}</p>
                                <p class="text-sm text-slate-600">${{ number_format($item->price, 2) }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
            
            <!-- Contact Info -->
            <div class="staff-card p-6 mb-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Contact Information</h2>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Address</label>
                    <input type="text" name="address" id="address" value="{{ $settings->address }}" class="staff-input" placeholder="123 Baker Street" oninput="updatePreview()">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Hours</label>
                    <textarea name="hours" id="hours" class="staff-input" rows="3" placeholder="Mon – Fri: 6am – 3pm&#10;Sat – Sun: 7am – 4pm" oninput="updatePreview()">{{ $settings->hours }}</textarea>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Instagram</label>
                        <input type="text" name="instagram" id="instagram" value="{{ $settings->instagram }}" class="staff-input" placeholder="@yourcafe" oninput="updatePreview()">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Facebook</label>
                        <input type="text" name="facebook" id="facebook" value="{{ $settings->facebook }}" class="staff-input" placeholder="facebook.com/yourcafe" oninput="updatePreview()">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
                        <input type="text" name="phone" id="phone" value="{{ $settings->phone }}" class="staff-input" placeholder="(555) 123-4567" oninput="updatePreview()">
                    </div>
                </div>
            </div>
            
            <!-- Colors -->
            <div class="staff-card p-6 mb-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Color Scheme</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Primary Color</label>
                        <div class="flex gap-2">
                            <input type="color" name="primary_color" id="primary_color" value="{{ $settings->primary_color }}" class="h-10 w-16 rounded cursor-pointer" oninput="updatePreview()">
                            <input type="text" name="primary_color" id="primary_color_text" value="{{ $settings->primary_color }}" class="staff-input flex-1" required maxlength="7" oninput="updatePreview()">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Primary Font Color</label>
                        <div class="flex gap-2">
                            <input type="color" name="primary_font_color" id="primary_font_color" value="{{ $settings->primary_font_color }}" class="h-10 w-16 rounded cursor-pointer" oninput="updatePreview()">
                            <input type="text" name="primary_font_color" id="primary_font_color_text" value="{{ $settings->primary_font_color }}" class="staff-input flex-1" required maxlength="7" oninput="updatePreview()">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Secondary Color</label>
                        <div class="flex gap-2">
                            <input type="color" name="secondary_color" id="secondary_color" value="{{ $settings->secondary_color }}" class="h-10 w-16 rounded cursor-pointer" oninput="updatePreview()">
                            <input type="text" name="secondary_color" id="secondary_color_text" value="{{ $settings->secondary_color }}" class="staff-input flex-1" required maxlength="7" oninput="updatePreview()">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Accent Color</label>
                        <div class="flex gap-2">
                            <input type="color" name="accent_color" id="accent_color" value="{{ $settings->accent_color }}" class="h-10 w-16 rounded cursor-pointer" oninput="updatePreview()">
                            <input type="text" name="accent_color" id="accent_color_text" value="{{ $settings->accent_color }}" class="staff-input flex-1" required maxlength="7" oninput="updatePreview()">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end gap-3">
                <a href="{{ route('super-admin.dashboard') }}" class="staff-btn-secondary">Cancel</a>
                <button type="submit" class="staff-btn-primary">Save Settings</button>
            </div>
        </form>
        
        <!-- Preview Column -->
        <div class="lg:sticky lg:top-6 lg:self-start">
            <div class="staff-card p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Live Preview</h2>
                <div id="preview-container" class="border-2 border-dashed border-slate-300 rounded-lg p-4 min-h-[400px]" style="background-color: {{ $settings->secondary_color }};">
                    <!-- Header Preview -->
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b" style="border-color: {{ $settings->primary_color }}40;">
                        <div id="preview-logo" class="w-10 h-10 rounded-lg flex items-center justify-center text-xl font-semibold text-white" style="background-color: {{ $settings->primary_color }};">
                            @if ($settings->logo)
                                <img src="{{ asset('storage/' . $settings->logo) }}" alt="Logo" class="w-full h-full object-cover rounded-lg">
                            @else
                                {{ $settings->logo_fallback }}
                            @endif
                        </div>
                        <span id="preview-app-name" class="text-xl font-semibold" style="color: {{ $settings->primary_color }};">{{ $settings->app_name }}</span>
                    </div>
                    
                    <!-- Hero Preview -->
                    <div class="rounded-lg p-6 mb-6 text-white" style="background-color: {{ $settings->primary_color }};">
                        <p id="preview-badge" class="text-sm opacity-90 mb-2">{{ $settings->landing_badge ?? 'Artisan bakery since 2026' }}</p>
                        <h3 id="preview-kicker" class="text-2xl font-semibold leading-tight">{{ $settings->landing_kicker ?? 'Baked with love, served with warmth' }}</h3>
                    </div>
                    
                    <!-- Contact Preview -->
                    <div class="space-y-3">
                        <p class="font-semibold text-slate-900">Contact Info</p>
                        <p id="preview-address" class="text-sm text-slate-600">{{ $settings->address ?? '123 Baker Street' }}</p>
                        <div id="preview-hours" class="text-sm text-slate-600 whitespace-pre-line">{{ $settings->hours ?? "Mon – Fri: 6am – 3pm\nSat – Sun: 7am – 4pm" }}</div>
                        @if ($settings->instagram || $settings->facebook || $settings->phone)
                            <div class="flex gap-3 text-sm">
                                @if ($settings->instagram)
                                    <a href="{{ $settings->instagram }}" class="text-blue-600 hover:underline">Instagram</a>
                                @endif
                                @if ($settings->facebook)
                                    <a href="{{ $settings->facebook }}" class="text-blue-600 hover:underline">Facebook</a>
                                @endif
                                @if ($settings->phone)
                                    <a href="tel:{{ $settings->phone }}" class="text-blue-600 hover:underline">{{ $settings->phone }}</a>
                                @endif
                            </div>
                        @endif
                    </div>
                    
                    <!-- Color Preview -->
                    <div class="mt-6 pt-4 border-t border-slate-200">
                        <p class="font-semibold text-slate-900 mb-3">Color Scheme</p>
                        <div class="flex gap-3">
                            <div class="text-center">
                                <div class="w-12 h-12 rounded-lg mb-1" style="background-color: {{ $settings->primary_color }};"></div>
                                <p class="text-xs text-slate-600">Primary</p>
                            </div>
                            <div class="text-center">
                                <div class="w-12 h-12 rounded-lg mb-1" style="background-color: {{ $settings->primary_font_color }};"></div>
                                <p class="text-xs text-slate-600">Font</p>
                            </div>
                            <div class="text-center">
                                <div class="w-12 h-12 rounded-lg mb-1" style="background-color: {{ $settings->secondary_color }};"></div>
                                <p class="text-xs text-slate-600">Secondary</p>
                            </div>
                            <div class="text-center">
                                <div class="w-12 h-12 rounded-lg mb-1" style="background-color: {{ $settings->accent_color }};"></div>
                                <p class="text-xs text-slate-600">Accent</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Crop Modal -->
    <div id="cropModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div id="cropModalContent" class="bg-white rounded-2xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden scale-95 opacity-0 transition-all duration-200">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-slate-900">Crop Logo</h2>
                    <button type="button" onclick="closeCropModal()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-sm hover:bg-red-500 hover:text-white transition-colors">×</button>
                </div>
                <div class="relative bg-slate-100 rounded-lg overflow-hidden" style="height: 400px;">
                    <img id="cropImage" class="max-w-full" style="max-height: 400px;">
                </div>
                <div class="flex justify-end gap-3 mt-4">
                    <button type="button" onclick="closeCropModal()" class="px-4 py-2 rounded-lg bg-slate-200 text-slate-700 font-medium hover:bg-slate-300 transition-colors">Cancel</button>
                    <button type="button" onclick="applyCrop()" class="px-4 py-2 rounded-lg bg-amber-800 text-white font-medium hover:bg-amber-900 transition-colors">Apply Crop</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let uploadedLogoUrl = '{{ $settings->logo ? asset('storage/' . $settings->logo) : '' }}';
        let cropper = null;

        function updatePreview() {
            const appName = document.getElementById('app_name').value;
            const logoFallback = document.getElementById('logo_fallback').value;
            const landingBadge = document.getElementById('landing_badge').value;
            const landingKicker = document.getElementById('landing_kicker').value;
            const address = document.getElementById('address').value;
            const hours = document.getElementById('hours').value;
            const instagram = document.getElementById('instagram').value;
            const facebook = document.getElementById('facebook').value;
            const phone = document.getElementById('phone').value;
            const primaryColor = document.getElementById('primary_color').value;
            const primaryFontColor = document.getElementById('primary_font_color').value;
            const secondaryColor = document.getElementById('secondary_color').value;
            const accentColor = document.getElementById('accent_color').value;

            // Sync color inputs
            document.getElementById('primary_color_text').value = primaryColor;
            document.getElementById('primary_font_color_text').value = primaryFontColor;
            document.getElementById('secondary_color_text').value = secondaryColor;
            document.getElementById('accent_color_text').value = accentColor;

            // Update preview
            document.getElementById('preview-app-name').textContent = appName;
            document.getElementById('preview-app-name').style.color = primaryColor;
            document.getElementById('preview-badge').textContent = landingBadge || 'Artisan bakery since 2026';
            document.getElementById('preview-kicker').textContent = landingKicker || 'Baked with love, served with warmth';
            document.getElementById('preview-address').textContent = address || '123 Baker Street';
            document.getElementById('preview-hours').textContent = hours || "Mon – Fri: 6am – 3pm\nSat – Sun: 7am – 4pm";

            // Update logo
            const logoContainer = document.getElementById('preview-logo');
            if (uploadedLogoUrl) {
                logoContainer.innerHTML = `<img src="${uploadedLogoUrl}" alt="Logo" class="w-full h-full object-cover rounded-lg">`;
            } else {
                logoContainer.innerHTML = logoFallback;
                logoContainer.style.backgroundColor = primaryColor;
            }

            // Update colors
            document.getElementById('preview-container').style.backgroundColor = secondaryColor;
            document.querySelector('#preview-container > div:first-child').style.borderColor = primaryColor + '40';
            document.querySelector('#preview-container > div:nth-child(2)').style.backgroundColor = primaryColor;

            // Update color swatches
            const colorSwatches = document.querySelectorAll('#preview-container .w-12');
            colorSwatches[0].style.backgroundColor = primaryColor;
            colorSwatches[1].style.backgroundColor = primaryFontColor;
            colorSwatches[2].style.backgroundColor = secondaryColor;
            colorSwatches[3].style.backgroundColor = accentColor;
        }

        function openCropModal(event) {
            const fileInput = document.getElementById('logo');
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
                document.getElementById('logo').value = '';
            }, 200);
        }

        function applyCrop() {
            if (cropper) {
                const canvas = cropper.getCroppedCanvas({
                    width: 256,
                    height: 256,
                });

                const croppedDataUrl = canvas.toDataURL('image/jpeg', 0.9);
                document.getElementById('logo_cropped').value = croppedDataUrl;

                // Update preview
                uploadedLogoUrl = croppedDataUrl;
                updatePreview();

                // Show cropped preview
                const cropPreviewWrap = document.getElementById('crop-preview-wrap');
                const cropPreviewImg = document.getElementById('crop-preview-img');
                cropPreviewImg.src = croppedDataUrl;
                cropPreviewWrap.classList.remove('hidden');

                closeCropModal();
            }
        }

        // Sync color text inputs to color pickers
        document.getElementById('primary_color_text').addEventListener('input', function() {
            document.getElementById('primary_color').value = this.value;
            updatePreview();
        });
        document.getElementById('primary_font_color_text').addEventListener('input', function() {
            document.getElementById('primary_font_color').value = this.value;
            updatePreview();
        });
        document.getElementById('secondary_color_text').addEventListener('input', function() {
            document.getElementById('secondary_color').value = this.value;
            updatePreview();
        });
        document.getElementById('accent_color_text').addEventListener('input', function() {
            document.getElementById('accent_color').value = this.value;
            updatePreview();
        });
    </script>
@endsection
