<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\BrandSettings;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandSettingsController extends Controller
{
    public function index(): View
    {
        $settings = BrandSettings::getSettings();
        $menuItems = MenuItem::all();
        
        return view('super-admin.brand-settings.index', compact('settings', 'menuItems'));
    }
    
    public function update(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-.,\'@]+$/',
            'logo_fallback' => 'required|string|max:10|regex:/^[a-zA-Z0-9]+$/',
            'logo_file_raw' => 'nullable|image|max:2048',
            'logo_cropped' => 'nullable|string',
            'landing_kicker' => 'nullable|string|max:500|regex:/^[a-zA-Z0-9\s\-.,!?@]+$/',
            'landing_badge' => 'nullable|string|max:255|regex:/^[a-zA-Z0-9\s\-.,!?@]+$/',
            'fan_favourite_ids' => 'nullable|array',
            'fan_favourite_ids.*' => 'exists:menu_items,id',
            'address' => 'nullable|string|max:500|regex:/^[a-zA-Z0-9\s\-.,\'@#]+$/',
            'hours' => 'nullable|string|regex:/^[a-zA-Z0-9\s\-.,:]+$/',
            'instagram' => 'nullable|string|max:255|regex:/^[a-zA-Z0-9@._\-]+$/',
            'facebook' => 'nullable|string|max:255|regex:/^[a-zA-Z0-9@._\/\-]+$/',
            'phone' => 'nullable|string|max:50|regex:/^[0-9\s\-\(\)\.]+$/',
            'primary_color' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'accent_color' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'primary_font_color' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $settings = BrandSettings::getSettings();

        $data = $request->except(['_token', '_method', 'logo_file_raw', 'logo_cropped']);

        // Handle cropped logo (base64 data URL)
        if ($request->filled('logo_cropped')) {
            $croppedData = $request->input('logo_cropped');
            $imageData = str_replace('data:image/jpeg;base64,', '', $croppedData);
            $imageData = str_replace('data:image/png;base64,', '', $imageData);
            $imageData = base64_decode($imageData);

            $filename = 'logo_' . time() . '.jpg';
            $path = 'brand/' . $filename;
            \Storage::disk('public')->put($path, $imageData);
            $data['logo'] = $path;
        }

        $data['fan_favourite_ids'] = $request->fan_favourite_ids ?? [];

        $settings->update($data);

        return redirect()->route('super-admin.brand-settings.index')->with('success', 'Brand settings updated successfully.');
    }
}
