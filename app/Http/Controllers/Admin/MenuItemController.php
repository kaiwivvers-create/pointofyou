<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MenuItemController extends Controller
{
    public function index(Request $request): View
    {
        $query = MenuItem::query();

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'available') {
                $query->where('is_available', true);
            } elseif ($request->status === 'hidden') {
                $query->where('is_available', false);
            }
        }

        $menuItems = $query->orderBy('category')->orderBy('name')->paginate(15);

        return view('admin.menu.index', compact('menuItems'));
    }

    public function create(): View
    {
        return view('admin.menu.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['required', 'string', 'max:50'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_available' => ['boolean'],
            'modifications' => ['nullable', 'array'],
            'modifications.*.name' => ['required', 'string', 'max:255'],
            'modifications.*.additional_price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:5120'],
            'cropped_image' => ['nullable', 'string'],
        ]);

        $validated['is_available'] = $request->boolean('is_available', true);

        $menuItem = MenuItem::create($validated);

        // Handle image upload - prioritize cropped image if present
        if ($request->has('cropped_image') && strlen($request->input('cropped_image')) > 100) {
            // Handle cropped image from base64
            $imageData = $request->input('cropped_image');
            \Log::info('Cropped image data length: ' . strlen($imageData));
            \Log::info('Cropped image starts with: ' . substr($imageData, 0, 50));
            
            $image = str_replace('data:image/png;base64,', '', $imageData);
            $image = str_replace('data:image/jpeg;base64,', '', $imageData);
            $image = str_replace('data:image/webp;base64,', '', $imageData);
            $image = base64_decode($image);
            
            \Log::info('Decoded image size: ' . strlen($image) . ' bytes');
            
            if (strlen($image) > 0) {
                $fileName = 'menu-item-' . $menuItem->id . '-' . time() . '.jpg';
                $path = 'menu-items/' . $fileName;
                Storage::disk('public')->put($path, $image);
                $menuItem->image = $path;
                $menuItem->save();
                \Log::info('Image saved to: ' . $path);
            } else {
                \Log::error('Decoded image is empty');
            }
        } elseif ($request->hasFile('image')) {
            // Handle cropped image from base64
            $imageData = $request->input('cropped_image');
            \Log::info('Cropped image data length: ' . strlen($imageData));
            \Log::info('Cropped image starts with: ' . substr($imageData, 0, 50));
            
            $image = str_replace('data:image/png;base64,', '', $imageData);
            $image = str_replace('data:image/jpeg;base64,', '', $imageData);
            $image = str_replace('data:image/webp;base64,', '', $imageData);
            $image = base64_decode($image);
            
            \Log::info('Decoded image size: ' . strlen($image) . ' bytes');
            
            $fileName = 'menu-item-' . $menuItem->id . '-' . time() . '.jpg';
            $path = 'menu-items/' . $fileName;
            Storage::disk('public')->put($path, $image);
            $menuItem->image = $path;
            $menuItem->save();
        } elseif ($request->hasFile('image')) {
            $image = $request->file('image');
            $path = $image->store('menu-items', 'public');
            $menuItem->image = $path;
            $menuItem->save();
        }

        if (!empty($validated['modifications'])) {
            $menuItem->modifications()->createMany($validated['modifications']);
        }

        return redirect()->route('admin.menu.index')->with('success', 'Menu item added.');
    }

    public function edit(MenuItem $menu): View
    {
        return view('admin.menu.edit', ['menuItem' => $menu]);
    }

    public function update(Request $request, MenuItem $menu): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['required', 'string', 'max:50'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_available' => ['boolean'],
            'modifications' => ['nullable', 'array'],
            'modifications.*.name' => ['required', 'string', 'max:255'],
            'modifications.*.additional_price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:5120'],
            'cropped_image' => ['nullable', 'string'],
        ]);

        $validated['is_available'] = $request->boolean('is_available');

        $menu->update($validated);

        // Handle image upload - prioritize cropped image if present
        if ($request->has('cropped_image') && strlen($request->input('cropped_image')) > 100) {
            // Handle cropped image from base64
            // Delete old image if exists
            if ($menu->image) {
                Storage::disk('public')->delete($menu->image);
            }
            $imageData = $request->input('cropped_image');
            \Log::info('Update - Cropped image data length: ' . strlen($imageData));
            
            $image = str_replace('data:image/png;base64,', '', $imageData);
            $image = str_replace('data:image/jpeg;base64,', '', $imageData);
            $image = str_replace('data:image/webp;base64,', '', $imageData);
            $image = base64_decode($image);
            
            \Log::info('Update - Decoded image size: ' . strlen($image) . ' bytes');
            
            if (strlen($image) > 0) {
                $fileName = 'menu-item-' . $menu->id . '-' . time() . '.png';
                $path = 'menu-items/' . $fileName;
                Storage::disk('public')->put($path, $image);
                $menu->image = $path;
                $menu->save();
                \Log::info('Update - Image saved to: ' . $path);
            } else {
                \Log::error('Update - Decoded image is empty');
            }
        } elseif ($request->hasFile('image')) {
            // Delete old image if exists
            if ($menu->image) {
                Storage::disk('public')->delete($menu->image);
            }
            $image = $request->file('image');
            $path = $image->store('menu-items', 'public');
            $menu->image = $path;
            $menu->save();
        }

        $menu->modifications()->delete();
        if (!empty($validated['modifications'])) {
            $menu->modifications()->createMany($validated['modifications']);
        }

        return redirect()->route('admin.menu.index')->with('success', 'Menu item updated.');
    }

    public function destroy(MenuItem $menu): RedirectResponse
    {
        $menu->delete();

        return redirect()->route('admin.menu.index')->with('success', 'Menu item removed.');
    }
}
