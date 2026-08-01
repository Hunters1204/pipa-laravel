<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\StockOpname;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Auto-seed master data if database is empty
        if (Warehouse::count() === 0) {
            \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
        }

        $filter = $request->get('filter', 'today');

        $applyFilter = function($q) use ($filter) {
            if ($filter === 'today') {
                $q->whereDate('created_at', now()->toDateString());
            } elseif ($filter === 'yesterday') {
                $q->whereDate('created_at', now()->subDay()->toDateString());
            } elseif ($filter === 'month') {
                $q->whereMonth('created_at', now()->month)
                  ->whereYear('created_at', now()->year);
            }
            return $q;
        };

        $warehouses = Warehouse::withCount('blocks')->get();
        
        $totalOpnames = $applyFilter(StockOpname::query())->count();
        $totalBundles = $applyFilter(StockOpname::query())->sum('total_bundles');
        $totalPcs = $applyFilter(StockOpname::query())->sum('total_pcs');
        $totalWeight = $applyFilter(StockOpname::query())->sum('total_weight');

        // Progress per warehouse
        $warehouseStats = [];
        foreach ($warehouses as $wh) {
            $countedBlocks = $applyFilter(StockOpname::query())->whereHas('block', function ($q) use ($wh) {
                $q->where('warehouse_id', $wh->id);
            })->distinct('block_id')->count('block_id');
            
            $totalPcsWh = $applyFilter(StockOpname::query())->whereHas('block', function ($q) use ($wh) {
                $q->where('warehouse_id', $wh->id);
            })->sum('total_pcs');
            
            $totalBundlesWh = $applyFilter(StockOpname::query())->whereHas('block', function ($q) use ($wh) {
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
        $filteredOpnames = $applyFilter(StockOpname::with('block.warehouse'))->get();
        foreach($filteredOpnames as $op) {
            if (!$op->block || !$op->block->warehouse) continue;
            $whName = $op->block->warehouse->name;
            $user = $op->petugas_name ?: 'Tidak Diketahui';
            if(!isset($opnameUsers[$whName])) $opnameUsers[$whName] = [];
            if(!isset($opnameUsers[$whName][$user])) $opnameUsers[$whName][$user] = 0;
            $opnameUsers[$whName][$user]++;
        }

        // Top 3 Kategori Pipa berdasarkan Total Bundle
        $topCategories = $applyFilter(StockOpname::with('pipeCategory'))
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

        // 7-Day Trend Chart Data
        $chartData = ['labels' => [], 'data' => []];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $chartData['labels'][] = $date->format('d M');
            $chartData['data'][] = StockOpname::whereDate('created_at', $date->toDateString())->sum('total_bundles');
        }

        // Recent Activity (Live Feed)
        $recentActivities = StockOpname::with(['block.warehouse', 'pipeSize'])
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'warehouses',
            'totalOpnames',
            'totalBundles',
            'totalPcs',
            'totalWeight',
            'warehouseStats',
            'opnameUsers',
            'topCategories',
            'filter',
            'chartData',
            'recentActivities'
        ));
    }
}
