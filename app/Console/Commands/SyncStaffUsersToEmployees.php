<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Console\Command;

class SyncStaffUsersToEmployees extends Command
{
    protected $signature = 'app:sync-staff-users-to-employees';

    protected $description = 'Sync existing staff users to employee records';

    public function handle()
    {
        $this->info('Syncing staff users to employee records...');

        $staffRoles = [UserRole::Cashier, UserRole::Admin, UserRole::Manager];
        $synced = 0;
        $skipped = 0;

        foreach ($staffRoles as $role) {
            $users = User::where('role', $role)->whereNull('employee_id')->get();

            foreach ($users as $user) {
                $employee = Employee::create([
                    'employee_id' => 'EMP-' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
                    'full_name' => $user->name,
                    'email' => $user->email,
                    'position' => $role->label(),
                    'hire_date' => now(),
                    'base_salary' => 0,
                    'status' => 'active',
                ]);

                $user->employee_id = $employee->id;
                $user->save();

                $synced++;
                $this->info("Synced user: {$user->name} ({$role->label()})");
            }
        }

        $this->info("Synced {$synced} users to employee records.");
        $this->info("Skipped {$skipped} users (already linked).");

        return Command::SUCCESS;
    }
}
