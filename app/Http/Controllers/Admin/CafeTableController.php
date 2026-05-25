<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CafeTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CafeTableController extends Controller
{
    public function index(): View
    {
        $tables = CafeTable::query()->orderBy('name')->get();

        return view('admin.tables.index', compact('tables'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        CafeTable::create($validated);

        return back()->with('success', 'Table created.');
    }

    public function destroy(CafeTable $cafeTable): RedirectResponse
    {
        $cafeTable->delete();

        return back()->with('success', 'Table removed.');
    }

    public function regenerateQr(CafeTable $cafeTable): RedirectResponse
    {
        $cafeTable->token = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(12));
        $cafeTable->save();

        return back()->with('success', 'QR code regenerated.');
    }
}
