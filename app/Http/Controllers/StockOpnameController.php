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
        $warehouseName = '';

        if ($selectedWarehouse) {
            $wh = Warehouse::find($selectedWarehouse);
            if ($wh) $warehouseName = $wh->name;
        }

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
        $filename = "Laporan_Stock_Opname_" . ($warehouseName ? str_replace(' ', '_', $warehouseName) . "_" : "") . date('Ymd_His') . ".xls";
        $excelTitle = "LAPORAN STOCK OPNAME" . ($warehouseName ? " - " . strtoupper($warehouseName) : "");

        $response = new StreamedResponse(function () use ($opnames, $excelTitle) {
            echo "<html>";
            echo "<head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'></head>";
            echo "<body>";
            echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse:collapse; font-family:sans-serif; font-size:12px;'>";
            echo "<thead>";
            echo "<tr><th colspan='15' style='background:#f59e0b; color:#fff; font-size:16px; padding:10px;'>" . $excelTitle . "</th></tr>";
            echo "<tr style='background:#fde68a; font-weight:bold;'>";
            echo "<th>No</th>";
            echo "<th>Tgl Input</th>";
            echo "<th>Tgl Opname</th>";
            echo "<th>Petugas</th>";
            echo "<th>Gudang</th>";
            echo "<th>Blok</th>";
            echo "<th>SLOC</th>";
            echo "<th>Kategori Pipa</th>";
            echo "<th>Ukuran Pipa</th>";
            echo "<th>Grade</th>";
            echo "<th>Class</th>";
            echo "<th>Pcs/Bdl</th>";
            echo "<th>Total Bundle</th>";
            echo "<th>Pieces Lepas</th>";
            echo "<th>Total Pcs</th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";

            $totalBundles = 0;
            $totalPcs = 0;

            foreach ($opnames as $i => $row) {
                $totalBundles += $row->total_bundles;
                $totalPcs += $row->total_pcs;
                
                echo "<tr>";
                echo "<td>" . ($i + 1) . "</td>";
                echo "<td>" . $row->input_date . "</td>";
                echo "<td>" . $row->opname_date . "</td>";
                echo "<td>" . $row->petugas_name . "</td>";
                echo "<td>" . ($row->block->warehouse->name ?? '') . "</td>";
                echo "<td style=\"mso-number-format:'\@';\">" . ($row->block->code ?? '') . "</td>";
                echo "<td style=\"mso-number-format:'\@';\">" . ($row->block->sloc_code ?? '') . "</td>";
                echo "<td>" . ($row->pipeCategory->name ?? '') . "</td>";
                echo "<td>" . ($row->pipeSize->size_label ?? '') . "</td>";
                echo "<td>" . ($row->pipeType->code ?? '') . "</td>";
                echo "<td>" . ($row->pipeClass->name ?? '-') . "</td>";
                echo "<td>" . ($row->pipeSize->pcs_per_bundle ?? 0) . "</td>";
                echo "<td style='font-weight:bold;'>" . $row->total_bundles . "</td>";
                echo "<td>" . $row->total_loose . "</td>";
                echo "<td style='font-weight:bold;'>" . $row->total_pcs . "</td>";
                echo "</tr>";
            }
            
            echo "</tbody>";
            echo "<tfoot>";
            echo "<tr style='background:#fde68a; font-weight:bold;'>";
            echo "<td colspan='12' align='right'>TOTAL KESELURUHAN</td>";
            echo "<td>" . $totalBundles . "</td>";
            echo "<td></td>";
            echo "<td>" . $totalPcs . "</td>";
            echo "</tr>";
            echo "</tfoot>";
            echo "</table>";
            echo "</body></html>";
        });

        $response->headers->set('Content-Type', 'application/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }
}
