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
                $q->whereDate('stock_opnames.created_at', now()->toDateString());
            } elseif ($filter === 'yesterday') {
                $q->whereDate('stock_opnames.created_at', now()->subDay()->toDateString());
            } elseif ($filter === 'month') {
                $q->whereMonth('stock_opnames.created_at', now()->month)
                  ->whereYear('stock_opnames.created_at', now()->year);
            }
            return $q;
        };

        $warehouses = Warehouse::withCount('blocks')->get();
        
        $totalOpnames = $applyFilter(StockOpname::query())->count();
        $totalBundles = $applyFilter(StockOpname::query())->sum('total_bundles');
        $totalPcs = $applyFilter(StockOpname::query())->sum('total_pcs');
        $totalWeight = $applyFilter(StockOpname::query())->sum('total_weight');

        // Aggregate query for warehouse stats
        $opnameAggregates = StockOpname::query()
            ->join('blocks', 'stock_opnames.block_id', '=', 'blocks.id')
            ->selectRaw('
                blocks.warehouse_id,
                count(distinct block_id) as counted_blocks,
                sum(total_pcs) as total_pcs,
                sum(total_bundles) as total_bundles
            ');
        $opnameAggregates = $applyFilter($opnameAggregates)->groupBy('blocks.warehouse_id')->get()->keyBy('warehouse_id');

        // Progress per warehouse
        $warehouseStats = [];
        foreach ($warehouses as $wh) {
            $agg = $opnameAggregates->get($wh->id);
            $countedBlocks = $agg ? $agg->counted_blocks : 0;
            $totalPcsWh = $agg ? $agg->total_pcs : 0;
            $totalBundlesWh = $agg ? $agg->total_bundles : 0;

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

        // Aggregate query for users per warehouse
        $userOpnameStats = StockOpname::query()
            ->join('blocks', 'stock_opnames.block_id', '=', 'blocks.id')
            ->join('warehouses', 'blocks.warehouse_id', '=', 'warehouses.id')
            ->selectRaw('warehouses.name as wh_name, stock_opnames.petugas_name, count(*) as user_count')
            ->whereNotNull('stock_opnames.petugas_name');
        
        $userOpnameStats = $applyFilter($userOpnameStats)
            ->groupBy('warehouses.name', 'stock_opnames.petugas_name')
            ->get();
        
        $opnameUsers = [];
        foreach($userOpnameStats as $stat) {
            $whName = $stat->wh_name;
            $user = $stat->petugas_name ?: 'Tidak Diketahui';
            if(!isset($opnameUsers[$whName])) $opnameUsers[$whName] = [];
            $opnameUsers[$whName][$user] = $stat->user_count;
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
