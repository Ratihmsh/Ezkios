<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    //
    protected $fillable = [
        'name',
        'type',
        'product_id',
        'reward_product_id',
        'reward_qty',
        'category_name',
        'promo_code',
        'usage_limit',
        'used_count',
        'payment_method',
        'min_qty',
        'min_spend',
        'value_type',
        'value',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function rewardProduct()
    {
        return $this->belongsTo(Product::class, 'reward_product_id');
    }
    
    public function isValid()
    {
        if (!$this->is_active) return false;
        if ($this->start_date && $this->start_date > now()) return false;
        if ($this->end_date && $this->end_date < now()) return false;
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) return false;
        return true;
    }

    public function sales()
    {
        return $this->belongsToMany(Sale::class, 'promotion_sale');
    }
}
