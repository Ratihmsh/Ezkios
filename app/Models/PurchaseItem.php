<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'purchase_id',
        'product_id',
        'quantity',
        'remaining_quantity',
        'purchase_price',
        'discount',
        'subtotal',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'purchase_price' => 'decimal:2',
            'discount' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    /**
     * Relasi ke Purchase
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Relasi ke Product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Accessor: Subtotal format Rupiah
     */
    public function getSubtotalFormattedAttribute()
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }

    /**
     * Accessor: Harga beli format Rupiah
     */
    public function getPurchasePriceFormattedAttribute()
    {
        return 'Rp ' . number_format($this->purchase_price, 0, ',', '.');
    }

    /**
     * Mutator: Hitung subtotal otomatis
     */
    public function setSubtotalAttribute($value)
    {
        // Jika subtotal tidak diisi, hitung otomatis
        if (empty($value) && isset($this->attributes['quantity']) && isset($this->attributes['purchase_price'])) {
            $this->attributes['subtotal'] = ($this->attributes['quantity'] * $this->attributes['purchase_price']) - ($this->attributes['discount'] ?? 0);
        } else {
            $this->attributes['subtotal'] = $value;
        }
    }
}
