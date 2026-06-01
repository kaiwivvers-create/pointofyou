<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Role;

echo "Reverting admin role to is_paid=false...\n";

$adminRole = Role::where('slug', 'admin')->first();
if ($adminRole) {
    $adminRole->is_paid = false;
    $adminRole->base_salary = 2500;
    $adminRole->save();
    echo "Admin role reverted: is_paid=false, base_salary=\$2500\n";
} else {
    echo "Admin role not found\n";
}
