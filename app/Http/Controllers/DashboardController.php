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

        $warehouses = Warehouse::withCount('blocks')->get();
        
        $today = now()->toDateString();
        $totalOpnames = StockOpname::whereDate('created_at', $today)->count();
        $totalBundles = StockOpname::whereDate('created_at', $today)->sum('total_bundles');
        $totalPcs = StockOpname::whereDate('created_at', $today)->sum('total_pcs');
        $totalWeight = StockOpname::whereDate('created_at', $today)->sum('total_weight');

        // Progress per warehouse
        $warehouseStats = [];
        foreach ($warehouses as $wh) {
            $countedBlocks = StockOpname::whereDate('created_at', $today)->whereHas('block', function ($q) use ($wh) {
                $q->where('warehouse_id', $wh->id);
            })->distinct('block_id')->count('block_id');
            
            $totalPcsWh = StockOpname::whereDate('created_at', $today)->whereHas('block', function ($q) use ($wh) {
                $q->where('warehouse_id', $wh->id);
            })->sum('total_pcs');
            
            $totalBundlesWh = StockOpname::whereDate('created_at', $today)->whereHas('block', function ($q) use ($wh) {
                $q->where('warehouse_id', $wh->id);
            })->sum('total_bundles');

            $pct = $wh->blocks_count > 0 ? round(($countedBlocks / $wh->blocks_count) * 100) : 0;

            $warehouseStats[$wh->id] = [
                'name' => $wh->name,
                'counted' => $countedBlocks,
                'total' => $wh->blocks_count,
                'pct' => $pct,
                'total_pcs' => $totalPcsWh,
                'total_bundles' => $totalBundlesWh,
            ];
        }

        $opnameUsers = [];
        $todayOpnames = StockOpname::with('block.warehouse')->whereDate('created_at', $today)->get();
        foreach($todayOpnames as $op) {
            if (!$op->block || !$op->block->warehouse) continue;
            $whName = $op->block->warehouse->name;
            $user = $op->petugas_name ?: 'Tidak Diketahui';
            if(!isset($opnameUsers[$whName])) $opnameUsers[$whName] = [];
            if(!isset($opnameUsers[$whName][$user])) $opnameUsers[$whName][$user] = 0;
            $opnameUsers[$whName][$user]++;
        }

        // Top 3 Kategori Pipa berdasarkan Total Bundle hari ini
        $topCategories = StockOpname::with('pipeCategory')
            ->whereDate('created_at', $today)
            ->selectRaw('pipe_category_id, sum(total_bundles) as sum_bundles')
            ->groupBy('pipe_category_id')
            ->orderByDesc('sum_bundles')
            ->take(3)
            ->get()
            ->map(function($op) {
                return [
                    'name' => $op->pipeCategory ? $op->pipeCategory->name : 'Lainnya',
                    'bundles' => $op->sum_bundles
                ];
            });

        return view('dashboard', compact(
            'warehouses',
            'totalOpnames',
            'totalBundles',
            'totalPcs',
            'totalWeight',
            'warehouseStats',
            'opnameUsers',
            'topCategories'
        ));
    }
}
