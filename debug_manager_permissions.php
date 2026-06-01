<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Permission;
use App\Models\Role;

echo "Debugging manager permissions...\n";

// Check all permissions
$allPermissions = Permission::all();
echo "\nAll permissions:\n";
foreach ($allPermissions as $perm) {
    echo "  - {$perm->permission} (role: {$perm->role}, can_view: {$perm->can_view}, can_edit: {$perm->can_edit})\n";
}

// Check manager role permissions
$managerPerms = Permission::where('role', 'manager')->get();
echo "\nManager role permissions:\n";
foreach ($managerPerms as $perm) {
    echo "  - {$perm->permission} (can_view: {$perm->can_view}, can_edit: {$perm->can_edit})\n";
}

// Check if dashboard permission exists for manager
$dashboardPerm = Permission::where('role', 'manager')->where('permission', 'dashboard')->first();
if ($dashboardPerm) {
    echo "\nDashboard permission for manager: can_view={$dashboardPerm->can_view}, can_edit={$dashboardPerm->can_edit}\n";
} else {
    echo "\nDashboard permission does NOT exist for manager role\n";
}
