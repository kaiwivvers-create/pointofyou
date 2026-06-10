<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Traits\LogsActivity;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'role_id',
        'employee_id',
        'profile_picture',
        'face_descriptor',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    public function isOwner(): bool
    {
        return $this->role === UserRole::Owner;
    }

    public function isManager(): bool
    {
        return $this->role === UserRole::Manager;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isChef(): bool
    {
        return $this->role === UserRole::Chef;
    }

    public function isCashier(): bool
    {
        return $this->role === UserRole::Cashier;
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function dbRole()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function getDashboardRouteAttribute(): string
    {
        // Prioritize enum-based dashboard route for consistency
        return $this->role->dashboardRoute();
    }

    public function isStaffEmployee(): bool
    {
        return in_array($this->role, [UserRole::Cashier, UserRole::Admin, UserRole::Manager]) && $this->employee;
    }
}

