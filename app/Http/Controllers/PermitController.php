<?php

namespace App\Http\Controllers;

use App\Models\Permit;
use Illuminate\Http\Request;

class PermitController extends Controller
{
    public function index()
    {
        $permits = Permit::with(['user', 'approvedBy'])
            ->latest()
            ->paginate(20);
        
        return view('permits.index', compact('permits'));
    }

    public function create()
    {
        return view('permits.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:leave,overtime,other',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'reason' => 'required|string|max:5000|regex:/^[a-zA-Z0-9\s\-.,!?@]+$/',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['status'] = 'pending';

        Permit::create($validated);

        return redirect()->route('permits.index')->with('success', 'Permit request submitted successfully.');
    }

    public function approve(Permit $permit)
    {
        $permit->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Permit approved successfully.');
    }

    public function reject(Request $request, Permit $permit)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:5000|regex:/^[a-zA-Z0-9\s\-.,!?@]+$/',
        ]);

        $permit->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Permit rejected successfully.');
    }
}
