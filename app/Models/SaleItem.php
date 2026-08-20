<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'selling_price',
        'discount',
        'subtotal',
        'total_cogs',
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
            'selling_price' => 'decimal:2',
            'discount' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    /**
     * Relasi ke Sale
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
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
     * Accessor: Harga jual format Rupiah
     */
    public function getSellingPriceFormattedAttribute()
    {
        return 'Rp ' . number_format($this->selling_price, 0, ',', '.');
    }

    /**
     * Mutator: Hitung subtotal otomatis
     */
    public function setSubtotalAttribute($value)
    {
        // Jika subtotal tidak diisi, hitung otomatis
        if (empty($value) && isset($this->attributes['quantity']) && isset($this->attributes['selling_price'])) {
            $this->attributes['subtotal'] = ($this->attributes['quantity'] * $this->attributes['selling_price']) - ($this->attributes['discount'] ?? 0);
        } else {
            $this->attributes['subtotal'] = $value;
        }
    }
}
