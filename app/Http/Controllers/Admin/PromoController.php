<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    public function index()
    {
        $promos = Promo::ordered()->get();
        return view('admin.promos.index', compact('promos'));
    }

    public function create()
    {
        return view('admin.promos.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'image' => 'required|image|max:5120',
            'cropped_image' => 'nullable|string',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'order' => 'integer',
        ]);

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

        $validated['is_active'] = $request->has('is_active');
        $validated['order'] = $request->input('order', 0);

        Promo::create($validated);
        return redirect()->route('admin.promos.index')->with('success', 'Promo created successfully');
    }

    public function edit(Promo $promo)
    {
        return view('admin.promos.edit', compact('promo'));
    }

    public function update(Request $request, Promo $promo)
    {
        $validated = $request->validate([
            'image' => 'nullable|image|max:5120',
            'cropped_image' => 'nullable|string',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'order' => 'integer',
        ]);

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

        $validated['is_active'] = $request->has('is_active');
        $validated['order'] = $request->input('order', $promo->order);

        $promo->update($validated);
        return redirect()->route('admin.promos.index')->with('success', 'Promo updated successfully');
    }

    public function destroy(Promo $promo)
    {
        $promo->delete();
        return redirect()->route('admin.promos.index')->with('success', 'Promo deleted successfully');
    }
}
