<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    protected $fillable = ['name', 'description'];

    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class);
    }
}
