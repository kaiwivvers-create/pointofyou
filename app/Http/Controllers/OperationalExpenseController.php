<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use App\Models\OperationalExpense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OperationalExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $query = OperationalExpense::with(['category', 'product'])
            ->where('source', 'auto_stock_purchase');

        if ($request->filled('date_from')) {
            $query->whereDate('expense_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('expense_date', '<=', $request->date_to);
        }

        if ($request->filled('item_type')) {
            $query->where('item_type', $request->item_type);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        $sortBy = $request->string('sort_by', 'date')->toString();
        $sortDirection = $request->string('sort_direction', 'desc')->toString() === 'asc' ? 'asc' : 'desc';

        if ($sortBy === 'amount') {
            $query->orderBy('amount', $sortDirection);
        } elseif ($sortBy === 'title') {
            $query->orderBy('title', $sortDirection);
        } else {
            $query->orderBy('expense_date', $sortDirection)->orderBy('id', $sortDirection);
        }

        $expenses = $query->paginate(20)->withQueryString();
        $categories = ExpenseCategory::all();
        return view('expenses.index', compact('expenses', 'categories'));
    }

    public function categories(): View
    {
        $categories = ExpenseCategory::all();
        return view('expenses.categories', compact('categories'));
    }

    public function storeExpense(Request $request): RedirectResponse
    {
        abort(403, 'Manual expenses are disabled. Stock purchases create expenses automatically.');
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-.,\'@]+$/',
            'description' => 'nullable|string|max:5000|regex:/^[a-zA-Z0-9\s\-.,!?@]+$/',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/'
        ]);

        ExpenseCategory::create($validated);
        return redirect()->back()->with('success', 'Category created successfully');
    }

    public function approveExpense($id): RedirectResponse
    {
        $expense = OperationalExpense::find($id);
        $expense->status = 'approved';
        $expense->save();
        return redirect()->back()->with('success', 'Expense approved successfully');
    }

    public function rejectExpense($id): RedirectResponse
    {
        $expense = OperationalExpense::find($id);
        $expense->status = 'rejected';
        $expense->save();
        return redirect()->back()->with('success', 'Expense rejected successfully');
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $query = OperationalExpense::with(['category', 'product'])
            ->where('source', 'auto_stock_purchase');

        if ($request->filled('date_from')) {
            $query->whereDate('expense_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('expense_date', '<=', $request->date_to);
        }

        if ($request->filled('item_type')) {
            $query->where('item_type', $request->item_type);
        }

        if ($request->filled('sort_by') && $request->sort_by === 'amount') {
            $query->orderBy('amount', $request->get('sort_direction', 'desc'));
        } elseif ($request->filled('sort_by') && $request->sort_by === 'title') {
            $query->orderBy('title', $request->get('sort_direction', 'desc'));
        } else {
            $query->orderBy('expense_date', $request->get('sort_direction', 'desc'));
        }

        $expenses = $query->get();

        return response()->streamDownload(function () use ($expenses) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Title', 'Category', 'Item Type', 'Quantity', 'Amount', 'Reference', 'Source', 'Notes']);

            foreach ($expenses as $expense) {
                fputcsv($file, [
                    $expense->expense_date?->format('Y-m-d'),
                    $expense->title,
                    $expense->category?->name,
                    $expense->item_type,
                    $expense->quantity,
                    $expense->amount,
                    $expense->reference,
                    $expense->source,
                    $expense->notes,
                ]);
            }

            fclose($file);
        }, 'expenses-' . now()->format('Y-m-d') . '.csv');
    }

}
