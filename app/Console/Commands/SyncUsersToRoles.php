<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class SyncUsersToRoles extends Command
{
    protected $signature = 'app:sync-users-to-roles';

    protected $description = 'Sync existing users to new roles table';

    public function handle()
    {
        $this->info('Syncing users to roles table...');

        $synced = 0;
        $skipped = 0;

        $users = User::whereNull('role_id')->get();

        foreach ($users as $user) {
            $role = Role::where('slug', $user->role->value)->first();

            if ($role) {
                $user->role_id = $role->id;
                $user->save();
                $synced++;
                $this->info("Synced: {$user->name} -> {$role->name}");
            } else {
                $this->warn("No role found for: {$user->role->value}");
                $skipped++;
            }
        }

        $this->info("Synced {$synced} users to roles.");
        $this->info("Skipped {$skipped} users.");

        return Command::SUCCESS;
    }
}
