<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Loggable;

class Sale extends Model
{
    use HasFactory, Loggable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sale_date',
        'invoice_number',
        'customer_name',
        'customer_phone',
        'total_amount',
        'discount',
        'tax',
        'shipping_cost',
        'grand_total',
        'paid_amount',
        'change_amount',
        'payment_status',
        'is_settled',
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
            'sale_date' => 'date',
            'total_amount' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'change_amount' => 'decimal:2',
            'due_date' => 'date',
        ];
    }

    /**
     * Relasi ke Sale Items (detail)
     */
    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments()
    {
        return $this->hasMany(SalePayment::class);
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

    public function promotions()
    {
        return $this->belongsToMany(Promotion::class, 'promotion_sale');
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
     * Accessor: Sisa tagihan (jika belum lunas)
     */
    public function getRemainingAmountAttribute()
    {
        if ($this->payment_status === 'paid') {
            return 0;
        }
        return $this->grand_total - $this->paid_amount;
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

    /**
     * Cek apakah sudah lunas
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Cek apakah ada sisa tagihan
     */
    public function hasRemaining(): bool
    {
        return $this->payment_status !== 'paid' && $this->remaining_amount > 0;
    }
}
