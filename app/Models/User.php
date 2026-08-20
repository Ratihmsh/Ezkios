<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\Loggable;

#[Fillable([
    'name',
    'username',
    'email',
    'password',
    'role_id',
    'phone',
    'address',
    'is_active'
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable, Loggable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Relasi ke Role (one-to-many)
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // Relasi ke Roles (many-to-many)
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    // Cek apakah user memiliki role
    public function hasRole($roleName): bool
    {
        // Cek dari primary role
        if ($this->role && $this->role->name === $roleName) {
            return true;
        }

        // Cek dari many-to-many
        return $this->roles()->where('name', $roleName)->exists();
    }

    // Cek apakah user memiliki permission
    public function hasPermission($permissionName): bool
    {
        // Owner has all permissions
        if ($this->hasRole('owner')) {
            return true;
        }

        // Cek dari primary role
        if ($this->role) {
            return $this->role->permissions()->where('name', $permissionName)->exists();
        }

        // Cek dari many-to-many
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissionName) {
                $query->where('name', $permissionName);
            })
            ->exists();
    }

    // Helper methods
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isKasir(): bool
    {
        return $this->hasRole('kasir');
    }

    public function isOwner(): bool
    {
        return $this->hasRole('owner');
    }

    // Accessor
    public function getRoleNameAttribute()
    {
        return $this->role ? $this->role->name : null;
    }

    public function getRoleDisplayNameAttribute()
    {
        return $this->role ? $this->role->display_name : null;
    }
}
