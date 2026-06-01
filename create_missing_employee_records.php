<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Employee;
use App\Models\Role;

echo "Creating missing employee records for users with role_id...\n";

$usersWithoutEmployee = User::whereNotNull('role_id')
    ->whereDoesntHave('employee')
    ->with('dbRole')
    ->get();

echo "Found {$usersWithoutEmployee->count()} users without employee records\n";

foreach ($usersWithoutEmployee as $user) {
    $role = $user->dbRole;
    if ($role) {
        $employee = Employee::create([
            'employee_id' => 'EMP-' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
            'full_name' => $user->name,
            'email' => $user->email,
            'position' => $role->name,
            'base_salary' => $role->base_salary ?? 0,
            'hire_date' => now(),
            'status' => 'active',
        ]);

        $user->employee_id = $employee->id;
        $user->save();
        
        echo "  Created employee record for {$user->name} (role: {$role->name})\n";
    }
}

echo "\nDone.\n";
