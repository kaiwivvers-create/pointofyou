<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use App\Models\PromoRule;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PromoController extends Controller
{
    public function index()
    {
        $promos = Promo::ordered()->with('rules.buyItem', 'rules.getItem')->get();
        return view('admin.promos.index', compact('promos'));
    }

    public function create()
    {
        return view('admin.promos.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validateWithBag('createPromo', [
            'image' => 'required|image|max:5120',
            'cropped_image' => 'nullable|string',
            'title' => 'nullable|string|max:255|regex:/^[a-zA-Z0-9\s\-.,\'@]+$/',
            'description' => 'nullable|string|max:5000|regex:/^[a-zA-Z0-9\s\-.,!?@]+$/',
            'is_active' => 'boolean',
            'order' => ['required', 'integer', 'min:0', 'unique:promos,order'],
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'rules' => 'nullable|array',
            'rules.*.buy_item_id' => 'nullable|exists:menu_items,id',
            'rules.*.get_item_id' => 'nullable|exists:menu_items,id',
            'rules.*.gift_id' => 'nullable|exists:gifts,id',
            'rules.*.buy_quantity' => 'nullable|integer|min:1',
            'rules.*.get_quantity' => 'nullable|integer|min:1',
        ]);

        // Custom validation: if there's a get item, there must be a buy item
        $rules = $validated['rules'] ?? [];
        foreach ($rules as $rule) {
            if (!empty($rule['get_item_id']) && empty($rule['buy_item_id'])) {
                return back()->withInput()->withErrors([
                    'rules' => 'You cannot have a "get" item without a "buy" item. Please select a buy item first.',
                ], 'createPromo');
            }
        }

        // Use cropped image if available, otherwise use original
        if ($request->filled('cropped_image')) {
            $imageData = preg_replace('#^data:image/\w+;base64,#i', '', $request->cropped_image);
            $imageData = base64_decode($imageData);
            $filename = 'promo_' . time() . '.jpg';
            $path = 'promos/' . $filename;
            \Storage::disk('public')->put($path, $imageData);
            $validated['image'] = $path;
        } elseif ($request->hasFile('image')) {
            $path = $request->file('image')->store('promos', 'public');
            $validated['image'] = $path;
        }

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        $validated['order'] = $request->input('order', 0);
        $validated['discount_type'] = $request->input('discount_type') ?: null;
        $validated['discount_value'] = $request->input('discount_value') ?: null;

        $rules = $validated['rules'] ?? [];
        unset($validated['rules']);

        $promo = Promo::create($validated);

        // Create promo rules
        foreach ($rules as $rule) {
            if (!empty($rule['buy_item_id']) || !empty($rule['get_item_id']) || !empty($rule['gift_id'])) {
                PromoRule::create([
                    'promo_id' => $promo->id,
                    'buy_item_id' => $rule['buy_item_id'] ?: null,
                    'get_item_id' => $rule['get_item_id'] ?: null,
                    'gift_id' => $rule['gift_id'] ?: null,
                    'buy_quantity' => $rule['buy_quantity'] ?? 1,
                    'get_quantity' => $rule['get_quantity'] ?? 1,
                ]);
            }
        }

        return redirect()->route('admin.promos.index')->with('success', 'Promo created successfully');
    }

    public function edit(Promo $promo)
    {
        return view('admin.promos.edit', compact('promo'));
    }

    public function update(Request $request, Promo $promo)
    {
        $validated = $request->validateWithBag('editPromo', [
            'image' => 'nullable|image|max:5120',
            'cropped_image' => 'nullable|string',
            'title' => 'nullable|string|max:255|regex:/^[a-zA-Z0-9\s\-.,\'@]+$/',
            'description' => 'nullable|string|max:5000|regex:/^[a-zA-Z0-9\s\-.,!?@]+$/',
            'is_active' => 'boolean',
            'order' => ['required', 'integer', 'min:0', Rule::unique('promos', 'order')->ignore($promo->id)],
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'rules' => 'nullable|array',
            'rules.*.buy_item_id' => 'nullable|exists:menu_items,id',
            'rules.*.get_item_id' => 'nullable|exists:menu_items,id',
            'rules.*.gift_id' => 'nullable|exists:gifts,id',
            'rules.*.buy_quantity' => 'nullable|integer|min:1',
            'rules.*.get_quantity' => 'nullable|integer|min:1',
        ]);

        // Custom validation: if there's a get item, there must be a buy item
        $rules = $validated['rules'] ?? [];
        foreach ($rules as $rule) {
            if (!empty($rule['get_item_id']) && empty($rule['buy_item_id'])) {
                return back()->withInput()->withErrors([
                    'rules' => 'You cannot have a "get" item without a "buy" item. Please select a buy item first.',
                ], 'editPromo');
            }
        }

        // Use cropped image if available, otherwise use original
        if ($request->filled('cropped_image')) {
            $imageData = preg_replace('#^data:image/\w+;base64,#i', '', $request->cropped_image);
            $imageData = base64_decode($imageData);
            $filename = 'promo_' . time() . '.jpg';
            $path = 'promos/' . $filename;
            \Storage::disk('public')->put($path, $imageData);
            $validated['image'] = $path;
        } elseif ($request->hasFile('image')) {
            $path = $request->file('image')->store('promos', 'public');
            $validated['image'] = $path;
        }

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        $validated['order'] = $request->input('order', $promo->order);
        $validated['discount_type'] = $request->input('discount_type') ?: null;
        $validated['discount_value'] = $request->input('discount_value') ?: null;

        $rules = $validated['rules'] ?? [];
        unset($validated['rules']);

        $promo->update($validated);

        // Delete existing rules and recreate
        $promo->rules()->delete();

        foreach ($rules as $rule) {
            if (!empty($rule['buy_item_id']) || !empty($rule['get_item_id']) || !empty($rule['gift_id'])) {
                PromoRule::create([
                    'promo_id' => $promo->id,
                    'buy_item_id' => $rule['buy_item_id'] ?: null,
                    'get_item_id' => $rule['get_item_id'] ?: null,
                    'gift_id' => $rule['gift_id'] ?: null,
                    'buy_quantity' => $rule['buy_quantity'] ?? 1,
                    'get_quantity' => $rule['get_quantity'] ?? 1,
                ]);
            }
        }

        return redirect()->route('admin.promos.index')->with('success', 'Promo updated successfully');
    }

    public function destroy(Promo $promo)
    {
        $promo->delete();
        return redirect()->route('admin.promos.index')->with('success', 'Promo deleted successfully');
    }
}
