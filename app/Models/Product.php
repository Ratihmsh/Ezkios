<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Loggable;

class Product extends Model
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
        'category',
        'brand',
        'purchase_price',
        'selling_price',
        'discount',
        'stock',
        'min_stock',
        'unit',
        'description',
        'image',
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
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'discount' => 'decimal:2',
            'stock' => 'integer',
            'min_stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relasi ke purchase_items (detail pembelian)
     */
    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * Relasi ke sale_items (detail penjualan)
     */
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Scope untuk produk aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk produk dengan stok menipis
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'min_stock');
    }

    /**
     * Scope untuk produk dengan stok habis
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('stock', '<=', 0);
    }

    /**
     * Accessor: Nama produk dengan kode
     */
    public function getNameWithCodeAttribute()
    {
        return $this->code ? "[{$this->code}] {$this->name}" : $this->name;
    }

    /**
     * Accessor: Harga jual dengan format Rupiah
     */
    public function getSellingPriceFormattedAttribute()
    {
        return 'Rp ' . number_format($this->selling_price, 0, ',', '.');
    }

    /**
     * Accessor: Harga beli dengan format Rupiah
     */
    public function getPurchasePriceFormattedAttribute()
    {
        return 'Rp ' . number_format($this->purchase_price, 0, ',', '.');
    }

    /**
     * Mutator: Harga jual disimpan sebagai decimal
     */
    public function setSellingPriceAttribute($value)
    {
        $this->attributes['selling_price'] = str_replace(',', '.', $value);
    }

    /**
     * Mutator: Harga beli disimpan sebagai decimal
     */
    public function setPurchasePriceAttribute($value)
    {
        $this->attributes['purchase_price'] = str_replace(',', '.', $value);
    }

    /**
     * Cek apakah stok kurang dari minimum
     */
    public function isLowStock(): bool
    {
        return $this->stock <= $this->min_stock;
    }

    /**
     * Cek apakah stok habis
     */
    public function isOutOfStock(): bool
    {
        return $this->stock <= 0;
    }

    /**
     * Tambah stok
     */
    public function addStock(int $quantity): void
    {
        $this->stock += $quantity;
        $this->save();
    }

    /**
     * Kurangi stok
     */
    public function reduceStock(int $quantity): void
    {
        if ($this->stock < $quantity) {
            throw new \Exception('Stok tidak mencukupi!');
        }
        $this->stock -= $quantity;
        $this->save();
    }

    /**
     * Hitung keuntungan per produk
     */
    public function getProfitAttribute(): float
    {
        return $this->selling_price - $this->purchase_price;
    }

    /**
     * Hitung margin keuntungan (%)
     */
    public function getMarginAttribute(): float
    {
        if ($this->purchase_price == 0) {
            return 0;
        }
        return round((($this->selling_price - $this->purchase_price) / $this->purchase_price) * 100, 2);
    }
}
