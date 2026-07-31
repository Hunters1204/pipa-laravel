<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOpname extends Model
{
    protected $fillable = [
        'user_id',
        'petugas_name',
        'block_id',
        'pipe_category_id',
        'pipe_size_id',
        'pipe_type_id',
        'pipe_class_id',
        'left_bdl_per_row',
        'left_rows',
        'left_adjust',
        'left_bundles',
        'left_loose',
        'right_bdl_per_row',
        'right_rows',
        'right_adjust',
        'right_bundles',
        'right_loose',
        'total_bundles',
        'total_pcs',
        'total_loose',
        'total_weight',
        'opname_date',
        'input_date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    public function pipeCategory(): BelongsTo
    {
        return $this->belongsTo(PipeCategory::class);
    }

    public function pipeSize(): BelongsTo
    {
        return $this->belongsTo(PipeSize::class);
    }

    public function pipeType(): BelongsTo
    {
        return $this->belongsTo(PipeType::class);
    }

    public function pipeClass(): BelongsTo
    {
        return $this->belongsTo(PipeClass::class);
    }
}
