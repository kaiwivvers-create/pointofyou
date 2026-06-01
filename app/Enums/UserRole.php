<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Owner = 'owner';
    case Manager = 'manager';
    case Admin = 'admin';
    case Chef = 'chef';
    case Cashier = 'cashier';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Owner => 'Owner',
            self::Manager => 'Manager',
            self::Admin => 'Admin',
            self::Chef => 'Chef',
            self::Cashier => 'Cashier',
        };
    }

    public function dashboardRoute(): string
    {
        return match ($this) {
            self::SuperAdmin => 'super-admin.dashboard',
            self::Owner => 'owner.dashboard',
            self::Manager => 'manager.dashboard',
            self::Admin => 'admin.dashboard',
            self::Chef => 'admin.kitchen.dashboard',
            self::Cashier => 'cashier.dashboard',
        };
    }
}
