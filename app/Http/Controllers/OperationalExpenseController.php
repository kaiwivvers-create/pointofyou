<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use App\Models\OperationalExpense;
use Illuminate\Http\Request;

class OperationalExpenseController extends Controller
{
    public function index()
    {
        $expenses = OperationalExpense::with('category')->latest()->paginate(20);
        $categories = ExpenseCategory::all();
        return view('expenses.index', compact('expenses', 'categories'));
    }

    public function categories()
    {
        $categories = ExpenseCategory::all();
        return view('expenses.categories', compact('categories'));
    }

    public function storeExpense(Request $request)
    {
        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'amount' => 'required|numeric',
            'expense_date' => 'required|date',
            'receipt' => 'nullable|string',
            'reference' => 'nullable|string',
            'status' => 'required|in:pending,approved,rejected',
            'notes' => 'nullable|string'
        ]);

        OperationalExpense::create($validated);
        return redirect()->back()->with('success', 'Expense created successfully');
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'color' => 'nullable|string'
        ]);

        ExpenseCategory::create($validated);
        return redirect()->back()->with('success', 'Category created successfully');
    }

    public function approveExpense($id)
    {
        $expense = OperationalExpense::find($id);
        $expense->status = 'approved';
        $expense->save();
        return redirect()->back()->with('success', 'Expense approved successfully');
    }

    public function rejectExpense($id)
    {
        $expense = OperationalExpense::find($id);
        $expense->status = 'rejected';
        $expense->save();
        return redirect()->back()->with('success', 'Expense rejected successfully');
    }
}
