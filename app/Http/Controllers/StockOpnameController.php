<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Warehouse;
use App\Models\PipeCategory;
use App\Models\PipeSize;
use App\Models\PipeType;
use App\Models\PipeClass;
use App\Models\StockOpname;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StockOpnameController extends Controller
{
    public function create($warehouseId, $blockId)
    {
        if (Auth::user()->warehouse_id && Auth::user()->warehouse_id != $warehouseId) {
            abort(403, 'Akses ditolak. Anda hanya dapat mengisi opname di ' . optional(Auth::user()->warehouse)->name);
        }

        $warehouse = Warehouse::findOrFail($warehouseId);
        $block = Block::where('warehouse_id', $warehouseId)
            ->where('code', $blockId)
            ->firstOrFail();

        $today = now()->toDateString();

        // Today's entries for this block
        $todayOpnames = StockOpname::with(['pipeCategory', 'pipeSize', 'pipeType', 'pipeClass', 'user'])
            ->where('block_id', $block->id)
            ->where('input_date', $today)
            ->latest()
            ->get();

        // Previous days' history (grouped by date descending)
        $historyOpnames = StockOpname::with(['pipeCategory', 'pipeSize', 'pipeType', 'pipeClass', 'user'])
            ->where('block_id', $block->id)
            ->whereDate('input_date', '<', $today)
            ->latest()
            ->get()
            ->groupBy('input_date');

        $categories = PipeCategory::all();
        $sizes = PipeSize::all();
        $types = PipeType::all();   // G-A, G-B
        $classes = PipeClass::all();  // SCH40, L, M, BSA, MED

        return view('opname.create', compact(
            'warehouse',
            'block',
            'todayOpnames',
            'historyOpnames',
            'categories',
            'sizes',
            'types',
            'classes',
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'block_id' => 'required|exists:blocks,id',
            'pipe_category_id' => 'required|exists:pipe_categories,id',
            'pipe_size_id' => 'required|exists:pipe_sizes,id',
            'pipe_type_id' => 'required|exists:pipe_types,id',
            'pipe_class_id' => 'nullable|exists:pipe_classes,id',
        ]);

        $block = Block::findOrFail($request->block_id);
        if (Auth::user()->warehouse_id && Auth::user()->warehouse_id != $block->warehouse_id) {
            abort(403, 'Akses ditolak. Anda tidak dapat mengisi opname di gudang ini.');
        }

        $size = PipeSize::findOrFail($request->pipe_size_id);
        $pcsPerBundle = $size->pcs_per_bundle;

        // Calculate left side
        $leftAutoBdl = (int) $request->left_bdl_per_row * (int) $request->left_rows;
        $leftBundles = max(0, $leftAutoBdl + (int) $request->left_adjust);
        $leftLoose = (int) $request->left_loose;
        $leftPcs = ($leftBundles * $pcsPerBundle) + $leftLoose;

        // Calculate right side
        $rightAutoBdl = (int) $request->right_bdl_per_row * (int) $request->right_rows;
        $rightBundles = max(0, $rightAutoBdl + (int) $request->right_adjust);
        $rightLoose = (int) $request->right_loose;
        $rightPcs = ($rightBundles * $pcsPerBundle) + $rightLoose;

        // Totals (loose digabung ke pcs)
        $totalBundles = $leftBundles + $rightBundles;
        $totalLoose = $leftLoose + $rightLoose;
        $totalPcs = $leftPcs + $rightPcs; // includes loose

        $currentUser = Auth::user();
        $today = now()->toDateString();

        StockOpname::create([
            'user_id' => $currentUser->id,
            'petugas_name' => $currentUser->name,
            'block_id' => $request->block_id,
            'pipe_category_id' => $request->pipe_category_id,
            'pipe_size_id' => $request->pipe_size_id,
            'pipe_type_id' => $request->pipe_type_id,
            'pipe_class_id' => $request->pipe_class_id ?: null,

            'left_bdl_per_row' => (int) $request->left_bdl_per_row,
            'left_rows' => (int) $request->left_rows,
            'left_adjust' => (int) $request->left_adjust,
            'left_bundles' => $leftBundles,
            'left_loose' => $leftLoose,

            'right_bdl_per_row' => (int) $request->right_bdl_per_row,
            'right_rows' => (int) $request->right_rows,
            'right_adjust' => (int) $request->right_adjust,
            'right_bundles' => $rightBundles,
            'right_loose' => $rightLoose,

            'total_bundles' => $totalBundles,
            'total_pcs' => $totalPcs,
            'total_loose' => $totalLoose,
            'total_weight' => 0,
            'opname_date' => $today,
            'input_date' => $today,
        ]);

        $block = Block::findOrFail($request->block_id);

        if ($request->action === 'add_more') {
            return redirect()->route('opname.create', [
                'warehouse' => $block->warehouse_id,
                'block' => $block->code
            ])->with('success', "Item pipa ditambahkan ke Blok {$block->code}! Tambah jenis pipa lain jika ada.");
        }

        if ($request->action === 'next') {
            $nextBlock = Block::where('warehouse_id', $block->warehouse_id)
                ->where('id', '>', $block->id)
                ->first();

            if ($nextBlock) {
                return redirect()->route('opname.create', [
                    'warehouse' => $block->warehouse_id,
                    'block' => $nextBlock->code
                ])->with('success', "Blok {$block->code} disimpan! Melanjutkan ke Blok {$nextBlock->code}");
            }
        }

        return redirect()->route('warehouse.show', $block->warehouse_id)
            ->with('success', "Data Blok {$block->code} berhasil disimpan!");
    }

    public function destroy($id)
    {
        $opname = StockOpname::findOrFail($id);
        $block = $opname->block;
        
        if (Auth::user()->warehouse_id && Auth::user()->warehouse_id != $block->warehouse_id) {
            abort(403, 'Akses ditolak. Anda tidak dapat menghapus opname di gudang ini.');
        }

        $opname->delete();

        return redirect()->route('opname.create', [
            'warehouse' => $block->warehouse_id,
            'block' => $block->code
        ])->with('success', "Item pipa berhasil dihapus.");
    }

    public function report(Request $request)
    {
        $warehouses = Warehouse::query();
        if (Auth::user()->warehouse_id) {
            $warehouses->where('id', Auth::user()->warehouse_id);
        }
        $warehouses = $warehouses->get();
        $selectedWarehouse = $request->query('warehouse_id');
        $selectedDate = $request->query('opname_date');

        $query = StockOpname::with([
            'block.warehouse',
            'pipeCategory',
            'pipeSize',
            'pipeType',
            'pipeClass',
            'user'
        ])->latest();
        
        $userWarehouseId = Auth::user()->warehouse_id;
        
        if ($userWarehouseId) {
            $selectedWarehouse = $userWarehouseId;
        }

        if ($selectedWarehouse) {
            $query->whereHas('block', function ($q) use ($selectedWarehouse) {
                $q->where('warehouse_id', $selectedWarehouse);
            });
        }

        if ($selectedDate) {
            $query->where('input_date', $selectedDate);
        }

        $opnames = $query->get();

        $summary = [
            'total_records' => $opnames->count(),
            'total_bundles' => $opnames->sum('total_bundles'),
            'total_pcs' => $opnames->sum('total_pcs'),
        ];

        // Distinct dates for filter dropdown — safe with nullable input_date
        $availableDates = StockOpname::whereNotNull('input_date')
            ->distinct()
            ->orderByDesc('input_date')
            ->pluck('input_date');

        return view('report.index', compact(
            'warehouses',
            'opnames',
            'summary',
            'selectedWarehouse',
            'selectedDate',
            'availableDates'
        ));
    }

    public function export(Request $request)
    {
        $selectedWarehouse = $request->query('warehouse_id');
        $selectedDate = $request->query('opname_date');

        $query = StockOpname::with([
            'block.warehouse',
            'pipeCategory',
            'pipeSize',
            'pipeType',
            'pipeClass',
            'user'
        ])->latest();

        $userWarehouseId = Auth::user()->warehouse_id;
        
        if ($userWarehouseId) {
            $selectedWarehouse = $userWarehouseId;
        }

        if ($selectedWarehouse) {
            $query->whereHas('block', function ($q) use ($selectedWarehouse) {
                $q->where('warehouse_id', $selectedWarehouse);
            });
        }

        if ($selectedDate) {
            $query->where('input_date', $selectedDate);
        }

        $opnames = $query->get();
        $filename = "stock_opname_spindo_" . date('Y-m-d_His') . ".csv";

        $response = new StreamedResponse(function () use ($opnames) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

            fputcsv($handle, [
                'Tanggal Input',
                'Tgl Opname',
                'Petugas',
                'Gudang',
                'Blok',
                'SLOC',
                'Kategori Pipa',
                'Ukuran Pipa',
                'Grade',
                'Class',
                'Pcs/Bdl',
                'L Bdl/Baris',
                'L Baris',
                'L Bundle',
                'R Bdl/Baris',
                'R Baris',
                'R Bundle',
                'Total Bundle',
                'Total Pcs (incl. loose)',
            ]);

            foreach ($opnames as $row) {
                fputcsv($handle, [
                    $row->input_date,
                    $row->opname_date,
                    $row->petugas_name,
                    $row->block->warehouse->name ?? '',
                    $row->block->code ?? '',
                    $row->block->sloc_code ?? '',
                    $row->pipeCategory->name ?? '',
                    $row->pipeSize->size_label ?? '',
                    $row->pipeType->code ?? '',
                    $row->pipeClass->name ?? '-',
                    $row->pipeSize->pcs_per_bundle ?? 0,
                    $row->left_bdl_per_row,
                    $row->left_rows,
                    $row->left_bundles,
                    $row->right_bdl_per_row,
                    $row->right_rows,
                    $row->right_bundles,
                    $row->total_bundles,
                    $row->total_pcs,
                ]);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }
}
