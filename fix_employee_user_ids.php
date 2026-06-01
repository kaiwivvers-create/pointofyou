<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Employee;

echo "Fixing employee records to set user_id...\n";

$employeesWithoutUser = Employee::whereNull('user_id')->get();
echo "Found {$employeesWithoutUser->count()} employees without user_id\n";

foreach ($employeesWithoutUser as $employee) {
    // Find user by matching email
    $user = User::where('email', $employee->email)->first();
    if ($user) {
        $employee->user_id = $user->id;
        $employee->save();
        echo "  Set user_id={$user->id} for employee {$employee->employee_id} ({$employee->full_name})\n";
    } else {
        echo "  No user found for employee {$employee->employee_id} ({$employee->email})\n";
    }
}

echo "\nDone.\n";
