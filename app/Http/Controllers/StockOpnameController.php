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

        // Get unique pipe specifications that have been inputted in this block
        $allOpnames = $todayOpnames->merge($historyOpnames->flatten());
        $existingSpecs = $allOpnames->unique(function ($item) {
            return $item->pipe_category_id . '-' . $item->pipe_size_id . '-' . $item->pipe_type_id . '-' . $item->pipe_class_id;
        })->take(8); // Show up to 8 unique specs

        return view('opname.create', compact(
            'warehouse',
            'block',
            'todayOpnames',
            'historyOpnames',
            'categories',
            'sizes',
            'types',
            'classes',
            'existingSpecs'
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

        $size = PipeSize::findOrFail($request->pipe_size_id);
        $pcsPerBundle = $size->pcs_per_bundle;

        // Calculate total mode directly
        $totalAutoBdl = (int) $request->total_bdl_per_row * (int) $request->total_rows;
        $totalBundles = max(0, $totalAutoBdl + (int) $request->total_adjust);
        $totalLoose = (int) $request->total_loose;
        $totalPcs = ($totalBundles * $pcsPerBundle) + $totalLoose;

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

            'left_bdl_per_row' => (int) $request->total_bdl_per_row,
            'left_rows' => (int) $request->total_rows,
            'left_adjust' => (int) $request->total_adjust,
            'left_bundles' => $totalBundles,
            'left_loose' => $totalLoose,

            'right_bdl_per_row' => 0,
            'right_rows' => 0,
            'right_adjust' => 0,
            'right_bundles' => 0,
            'right_loose' => 0,

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

    public function edit($id)
    {
        $editOpname = StockOpname::findOrFail($id);
        $block = $editOpname->block;
        $warehouse = $block->warehouse;

        $today = now()->toDateString();

        // Today's entries for this block
        $todayOpnames = StockOpname::with(['pipeCategory', 'pipeSize', 'pipeType', 'pipeClass', 'user'])
            ->where('block_id', $block->id)
            ->where('input_date', $today)
            ->latest()
            ->get();

        // Previous days' history
        $historyOpnames = StockOpname::with(['pipeCategory', 'pipeSize', 'pipeType', 'pipeClass', 'user'])
            ->where('block_id', $block->id)
            ->whereDate('input_date', '<', $today)
            ->latest()
            ->get()
            ->groupBy('input_date');

        $categories = PipeCategory::all();
        $sizes = PipeSize::all();
        $types = PipeType::all();
        $classes = PipeClass::all();

        return view('opname.create', compact(
            'warehouse',
            'block',
            'todayOpnames',
            'historyOpnames',
            'categories',
            'sizes',
            'types',
            'classes',
            'editOpname'
        ));
    }

    public function update(Request $request, $id)
    {
        $opname = StockOpname::findOrFail($id);

        $request->validate([
            'pipe_category_id' => 'required|exists:pipe_categories,id',
            'pipe_size_id' => 'required|exists:pipe_sizes,id',
            'pipe_type_id' => 'required|exists:pipe_types,id',
            'pipe_class_id' => 'nullable|exists:pipe_classes,id',
        ]);

        $size = PipeSize::findOrFail($request->pipe_size_id);
        $pcsPerBundle = $size->pcs_per_bundle;

        // Calculate total mode directly
        $totalAutoBdl = (int) $request->total_bdl_per_row * (int) $request->total_rows;
        $totalBundles = max(0, $totalAutoBdl + (int) $request->total_adjust);
        $totalLoose = (int) $request->total_loose;
        $totalPcs = ($totalBundles * $pcsPerBundle) + $totalLoose;

        $opname->update([
            'pipe_category_id' => $request->pipe_category_id,
            'pipe_size_id' => $request->pipe_size_id,
            'pipe_type_id' => $request->pipe_type_id,
            'pipe_class_id' => $request->pipe_class_id ?: null,

            'total_bundles' => $totalBundles,
            'total_pcs' => $totalPcs,
            'total_loose' => $totalLoose,
            // Assuming we don't save the formulas, just the totals since the previous fields were for left/right
            // Wait, we DO need to save the formula (total_bdl_per_row, total_rows, etc) if we want to repopulate them!
            // But the database schema doesn't have `total_bdl_per_row`. It has `left_bdl_per_row` etc.
            // Let's repurpose `left_*` to store the total mode inputs so we can edit them later.
            'left_bdl_per_row' => (int) $request->total_bdl_per_row,
            'left_rows' => (int) $request->total_rows,
            'left_adjust' => (int) $request->total_adjust,
            'left_bundles' => $totalBundles,
            'left_loose' => $totalLoose,
        ]);

        $block = $opname->block;
        return redirect()->route('opname.create', [
            'warehouse' => $block->warehouse_id,
            'block' => $block->code
        ])->with('success', "Item pipa berhasil diperbarui!");
    }

    public function destroy($id)
    {
        $opname = StockOpname::findOrFail($id);
        $block = $opname->block;
        $opname->delete();

        return redirect()->route('opname.create', [
            'warehouse' => $block->warehouse_id,
            'block' => $block->code
        ])->with('success', "Item pipa berhasil dihapus.");
    }

    public function report(Request $request)
    {
        $warehouses = Warehouse::all();
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
