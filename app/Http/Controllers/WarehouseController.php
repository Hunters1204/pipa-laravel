<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Block;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function show(Request $request, $id)
    {
        $filter = $request->get('filter', 'today');

        $warehouse = Warehouse::with(['blocks.stockOpnames' => function($q) use ($filter) {
            if ($filter === 'today') {
                $q->whereDate('created_at', now()->toDateString());
            } elseif ($filter === 'yesterday') {
                $q->whereDate('created_at', now()->subDay()->toDateString());
            } elseif ($filter === 'month') {
                $q->whereMonth('created_at', now()->month)
                  ->whereYear('created_at', now()->year);
            }
        }])->findOrFail($id);

        $stats = [
            'total' => $warehouse->blocks->count(),
            'counted' => $warehouse->blocks->filter(fn($b) => $b->stockOpnames->count() > 0)->count(),
        ];
        $stats['pct'] = $stats['total'] > 0 ? round(($stats['counted'] / $stats['total']) * 100) : 0;

        // Group blocks by Letter (A, B, C, etc)
        $groupedBlocks = $warehouse->blocks->groupBy(function ($block) {
            return substr($block->code, 0, 1);
        });

        // Find empty blocks (0 opnames)
        $emptyBlocks = $warehouse->blocks->filter(fn($b) => $b->stockOpnames->count() === 0)->pluck('code');

        return view('warehouse.show', compact('warehouse', 'groupedBlocks', 'stats', 'filter', 'emptyBlocks'));
    }
}
