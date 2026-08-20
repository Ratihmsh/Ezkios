<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Loggable;

class Supplier extends Model
{
    use HasFactory, Loggable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'phone',
        'email',
        'address',
        'city',
        'postal_code',
        'contact_person',
        'contact_person_phone',
        'notes',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relasi ke tabel purchases (pembelian)
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Scope untuk supplier aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Accessor: Nama supplier dengan kode
     */
    public function getNameWithCodeAttribute()
    {
        return $this->code ? "[{$this->code}] {$this->name}" : $this->name;
    }
}
