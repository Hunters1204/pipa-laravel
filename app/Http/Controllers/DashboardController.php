<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\StockOpname;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Auto-seed master data if database is empty
        if (Warehouse::count() === 0) {
            \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
        }

        $query = Warehouse::withCount('blocks');
        if (Auth::user()->warehouse_id) {
            $query->where('id', Auth::user()->warehouse_id);
        }
        $warehouses = $query->get();
        $opnameQuery = StockOpname::query();
        if (Auth::user()->warehouse_id) {
            $opnameQuery->whereHas('block', function($q) {
                $q->where('warehouse_id', Auth::user()->warehouse_id);
            });
        }
        $totalOpnames = $opnameQuery->count();
        $totalBundles = (clone $opnameQuery)->sum('total_bundles');
        $totalPcs = (clone $opnameQuery)->sum('total_pcs');
        $totalWeight = StockOpname::sum('total_weight');

        // Progress per warehouse
        $warehouseStats = [];
        foreach ($warehouses as $wh) {
            $countedBlocks = StockOpname::whereHas('block', function ($q) use ($wh) {
                $q->where('warehouse_id', $wh->id);
            })->distinct('block_id')->count('block_id');

            $pct = $wh->blocks_count > 0 ? round(($countedBlocks / $wh->blocks_count) * 100) : 0;

            $warehouseStats[$wh->id] = [
                'counted' => $countedBlocks,
                'total' => $wh->blocks_count,
                'pct' => $pct,
            ];
        }

        return view('dashboard', compact(
            'warehouses',
            'totalOpnames',
            'totalBundles',
            'totalPcs',
            'totalWeight',
            'warehouseStats'
        ));
    }
}
