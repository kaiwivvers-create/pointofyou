<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSettings;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentSettingsController extends Controller
{
    public function index(): View
    {
        $settings = PaymentSettings::getSettings();
        
        return view('super-admin.payment-settings.index', compact('settings'));
    }
    
    public function update(Request $request)
    {
        $request->validate([
            'qr_code_image_raw' => 'nullable|image|max:2048',
            'qr_code_cropped' => 'nullable|string',
            'qr_code_instructions' => 'nullable|string',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'account_name' => 'nullable|string|max:255',
            'bank_address' => 'nullable|string|max:255',
            'swift_code' => 'nullable|string|max:255',
            'card_instructions' => 'nullable|string',
            'transfer_instructions' => 'nullable|string',
            'cash_instructions' => 'nullable|string',
        ]);

        $settings = PaymentSettings::getSettings();

        $data = $request->except(['_token', '_method', 'qr_code_image_raw', 'qr_code_cropped']);

        // Handle cropped QR code (base64 data URL)
        if ($request->filled('qr_code_cropped')) {
            $croppedData = $request->input('qr_code_cropped');
            $imageData = str_replace('data:image/jpeg;base64,', '', $croppedData);
            $imageData = str_replace('data:image/png;base64,', '', $imageData);
            $imageData = base64_decode($imageData);

            $filename = 'qr_code_' . time() . '.jpg';
            $path = 'payment/' . $filename;
            \Storage::disk('public')->put($path, $imageData);
            $data['qr_code_image'] = $path;
        }

        $settings->update($data);

        return redirect()->route('super-admin.payment-settings.index')->with('success', 'Payment settings updated successfully.');
    }
}
