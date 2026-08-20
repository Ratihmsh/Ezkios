<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Loggable;

class CashFlow extends Model
{
    use HasFactory, Loggable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'category',
        'amount',
        'description',
        'transaction_date',
        'reference_type',
        'reference_id',
        'fund_source',
        'payment_method',
        'attachment',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'transaction_date' => 'date',
        ];
    }

    /**
     * Relasi polymorphic ke transaksi terkait (Sale atau Purchase)
     */
    public function reference()
    {
        return $this->morphTo();
    }

    /**
     * Relasi ke User yang membuat
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke User yang mengupdate
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope untuk filter berdasarkan type
     */
    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    /**
     * Scope untuk filter kategori
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope untuk filter tanggal
     */
    public function scopeDateRange($query, $start, $end)
    {
        return $query->whereBetween('transaction_date', [$start, $end]);
    }

    /**
     * Accessor: Amount dengan format Rupiah
     */
    public function getAmountFormattedAttribute()
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    /**
     * Accessor: Type dengan label
     */
    public function getTypeLabelAttribute()
    {
        return match ($this->type) {
            'income' => '💚 Uang Masuk',
            'expense' => '❤️ Uang Keluar',
            default => 'Tidak Diketahui',
        };
    }

    /**
     * Accessor: Type dengan badge color
     */
    public function getTypeColorAttribute()
    {
        return match ($this->type) {
            'income' => 'success',
            'expense' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Helper: Cek apakah income
     */
    public function isIncome(): bool
    {
        return $this->type === 'income';
    }

    /**
     * Helper: Cek apakah expense
     */
    public function isExpense(): bool
    {
        return $this->type === 'expense';
    }
}
