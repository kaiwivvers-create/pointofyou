<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Salary;
use App\Models\Attendance;
use App\Models\SalaryPayment;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index()
    {
        $employees = Employee::with(['user.dbRole', 'salaries'])
            ->whereNotNull('user_id')
            ->whereHas('user', function ($query) {
                $query->whereNotNull('role_id')
                      ->whereHas('dbRole', function ($query) {
                          $query->where('is_paid', true);
                      });
            })->paginate(20);
        return view('payroll.index', compact('employees'));
    }

    public function employees()
    {
        $employees = Employee::whereNotNull('user_id')
            ->whereHas('user', function ($query) {
                $query->whereNotNull('role_id')
                      ->whereHas('dbRole', function ($query) {
                          $query->where('is_paid', true);
                      });
            })->get();
        return view('payroll.employees', compact('employees'));
    }

    public function salaries()
    {
        $salaries = Salary::whereHas('employee', function ($query) {
            $query->whereHas('user', function ($query) {
                $query->whereHas('dbRole', function ($query) {
                    $query->where('is_paid', true);
                });
            });
        })->with('employee')->latest()->paginate(20);

        $employees = Employee::whereNotNull('user_id')
            ->whereHas('user', function ($query) {
                $query->whereNotNull('role_id')
                      ->whereHas('dbRole', function ($query) {
                          $query->where('is_paid', true);
                      });
            })->get();

        return view('payroll.salaries', compact('salaries', 'employees'));
    }

    public function attendance()
    {
        $attendance = Attendance::whereHas('employee', function ($query) {
            $query->whereHas('user', function ($query) {
                $query->whereHas('dbRole', function ($query) {
                    $query->where('is_paid', true);
                });
            });
        })->with('employee')->latest()->paginate(20);
        return view('payroll.attendance', compact('attendance'));
    }

    public function storeEmployee(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|string|max:255|unique:employees|regex:/^[a-zA-Z0-9\-_]+$/',
            'full_name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-.,\'@]+$/',
            'email' => 'required|email|max:255|unique:employees',
            'phone' => 'nullable|string|max:50|regex:/^[0-9\s\-\(\)\.]+$/',
            'position' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-.,\'@]+$/',
            'hire_date' => 'required|date',
            'base_salary' => 'required|numeric',
            'bank_name' => 'nullable|string|max:255|regex:/^[a-zA-Z0-9\s\-.,\'@]+$/',
            'bank_account' => 'nullable|string|max:50|regex:/^[0-9\-]+$/',
            'address' => 'nullable|string|max:500|regex:/^[a-zA-Z0-9\s\-.,\'@#]+$/',
            'status' => 'required|in:active,inactive,terminated'
        ]);

        Employee::create($validated);
        return redirect()->back()->with('success', 'Employee created successfully');
    }

    public function storeSalary(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'base_salary' => 'required|numeric',
            'overtime_rate' => 'nullable|numeric',
            'allowance' => 'nullable|numeric',
            'bonus' => 'nullable|numeric',
            'deductions' => 'nullable|numeric',
            'tax' => 'nullable|numeric',
            'period_start' => 'required|date',
            'period_end' => 'required|date',
            'status' => 'required|in:pending,approved,paid'
        ]);

        // Calculate net salary
        $validated['net_salary'] = $validated['base_salary'] + 
            ($validated['overtime_rate'] ?? 0) + 
            ($validated['allowance'] ?? 0) + 
            ($validated['bonus'] ?? 0) - 
            ($validated['deductions'] ?? 0) - 
            ($validated['tax'] ?? 0);

        Salary::create($validated);
        return redirect()->back()->with('success', 'Salary record created successfully');
    }

    public function storeAttendance(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
            'hours_worked' => 'nullable|numeric',
            'overtime_hours' => 'nullable|numeric',
            'status' => 'required|in:present,absent,late,leave',
            'notes' => 'nullable|string'
        ]);

        Attendance::create($validated);
        return redirect()->back()->with('success', 'Attendance recorded successfully');
    }
}
