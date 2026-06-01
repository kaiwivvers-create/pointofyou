<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Employee;
use App\Models\Role;

echo "Debugging payroll users...\n";

// Check all roles
$roles = Role::all();
echo "\nAll roles:\n";
foreach ($roles as $role) {
    echo "  - {$role->name} (slug: {$role->slug}, is_paid: " . ($role->is_paid ? 'Yes' : 'No') . ", base_salary: \${$role->base_salary})\n";
}

// Check all users with role_id
$usersWithRole = User::whereNotNull('role_id')->with('dbRole')->get();
echo "\nUsers with role_id: {$usersWithRole->count()}\n";
foreach ($usersWithRole as $user) {
    $roleName = $user->dbRole ? $user->dbRole->name : 'none';
    $employeeId = $user->employee_id;
    echo "  - {$user->name} (role: {$roleName}, employee_id: {$employeeId})\n";
}

// Check all employees
$employees = Employee::with('user.dbRole')->get();
echo "\nAll employees: {$employees->count()}\n";
foreach ($employees as $employee) {
    $userName = $employee->user ? $employee->user->name : 'none';
    $roleName = $employee->user && $employee->user->dbRole ? $employee->user->dbRole->name : 'none';
    echo "  - {$employee->employee_id} {$userName} (role: {$roleName}, base_salary: \${$employee->base_salary})\n";
}

// Check payroll query
$payrollEmployees = Employee::with(['user.dbRole', 'salaries'])
    ->whereNotNull('user_id')
    ->whereHas('user', function ($query) {
        $query->whereNotNull('role_id');
    })->get();

echo "\nPayroll query results: {$payrollEmployees->count()}\n";
foreach ($payrollEmployees as $employee) {
    $userName = $employee->user ? $employee->user->name : 'none';
    $roleName = $employee->user && $employee->user->dbRole ? $employee->user->dbRole->name : 'none';
    echo "  - {$employee->employee_id} {$userName} (role: {$roleName})\n";
}
