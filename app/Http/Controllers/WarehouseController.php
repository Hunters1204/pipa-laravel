<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Block;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WarehouseController extends Controller
{
    public function show(Warehouse $warehouse)
    {
        if (Auth::user()->warehouse_id && Auth::user()->warehouse_id !== $warehouse->id) {
            abort(403, 'Akses ditolak. Anda hanya dapat mengakses ' . optional(Auth::user()->warehouse)->name);
        }

        $warehouse->load(['blocks.stockOpnames']);

        $stats = [
            'total' => $warehouse->blocks->count(),
            'counted' => $warehouse->blocks->filter(fn($b) => $b->stockOpnames->count() > 0)->count(),
        ];
        $stats['pct'] = $stats['total'] > 0 ? round(($stats['counted'] / $stats['total']) * 100) : 0;

        // Group blocks by Letter (A, B, C, etc)
        $groupedBlocks = $warehouse->blocks->groupBy(function ($block) {
            return substr($block->code, 0, 1);
        });

        return view('warehouse.show', compact('warehouse', 'groupedBlocks', 'stats'));
    }
}
