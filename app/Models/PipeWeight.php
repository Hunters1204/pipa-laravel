<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PipeWeight extends Model
{
    protected $fillable = ['pipe_size_id', 'pipe_type_id', 'weight_per_bundle'];

    public function size(): BelongsTo
    {
        return $this->belongsTo(PipeSize::class, 'pipe_size_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(PipeType::class, 'pipe_type_id');
    }

    // Alias relationships (camelCase convention)
    public function pipeSize(): BelongsTo
    {
        return $this->belongsTo(PipeSize::class, 'pipe_size_id');
    }

    public function pipeType(): BelongsTo
    {
        return $this->belongsTo(PipeType::class, 'pipe_type_id');
    }
}
