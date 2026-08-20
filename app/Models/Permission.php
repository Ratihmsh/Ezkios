<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'group',
        'description',
    ];

    // Relasi ke Role (many-to-many)
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'permission_role');
    }
}
