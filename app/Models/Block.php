<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Block extends Model
{
    protected $fillable = ['warehouse_id', 'code', 'sloc_code'];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function stockOpnames()
    {
        return $this->hasMany(StockOpname::class);
    }

    public function latestStockOpname(): HasOne
    {
        return $this->hasOne(StockOpname::class)->latestOfMany();
    }
}
