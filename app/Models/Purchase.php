<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Loggable;

class Purchase extends Model
{
    use HasFactory, Loggable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'supplier_id',
        'purchase_date',
        'invoice_number',
        'total_amount',
        'discount',
        'tax',
        'shipping_cost',
        'grand_total',
        'paid_amount',
        'payment_status',
        'payment_method',
        'due_date',
        'notes',
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
            'purchase_date' => 'date',
            'total_amount' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_date' => 'date',
        ];
    }

    /**
     * Relasi ke Supplier
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Relasi ke Purchase Items (detail)
     */
    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function payments()
    {
        return $this->hasMany(PurchasePayment::class);
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
     * Relasi ke CashFlow (arus kas)
     */
    public function cashFlow()
    {
        return $this->morphOne(CashFlow::class, 'reference');
    }

    /**
     * Scope untuk status pembayaran
     */
    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopePartial($query)
    {
        return $query->where('payment_status', 'partial');
    }

    /**
     * Accessor: Grand Total format Rupiah
     */
    public function getGrandTotalFormattedAttribute()
    {
        return 'Rp ' . number_format($this->grand_total, 0, ',', '.');
    }

    /**
     * Accessor: Status pembayaran dengan label
     */
    public function getPaymentStatusLabelAttribute()
    {
        return match ($this->payment_status) {
            'pending' => 'Belum Dibayar',
            'partial' => 'Dibayar Sebagian',
            'paid' => 'Lunas',
            default => 'Tidak Diketahui',
        };
    }

    /**
     * Accessor: Status pembayaran dengan badge color
     */
    public function getPaymentStatusColorAttribute()
    {
        return match ($this->payment_status) {
            'pending' => 'danger',
            'partial' => 'warning',
            'paid' => 'success',
            default => 'secondary',
        };
    }

    /**
     * Hitung total item
     */
    public function getTotalItemsAttribute()
    {
        return $this->items()->count();
    }

    /**
     * Hitung total quantity
     */
    public function getTotalQuantityAttribute()
    {
        return $this->items()->sum('quantity');
    }
}
