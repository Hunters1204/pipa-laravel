@extends('layouts.app')

@section('title', 'Laporan Stock Opname')

@push('styles')
<style>
    .report-table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border-radius: var(--radius-md);
        border: 1px solid var(--border-subtle);
        margin-bottom: var(--space-lg);
    }
    .report-table {
        width: 100%;
        min-width: 820px;
        border-collapse: collapse;
        font-size: 0.72rem;
    }
    .report-table thead tr {
        background: rgba(245, 158, 11, 0.12);
        border-bottom: 2px solid var(--border-accent);
    }
    .report-table th {
        padding: 8px 10px;
        text-align: left;
        font-size: 0.63rem;
        font-weight: 800;
        color: var(--accent-primary);
        text-transform: uppercase;
        white-space: nowrap;
    }
    .report-table tbody tr {
        border-bottom: 1px solid var(--border-subtle);
        transition: background 0.15s;
    }
    .report-table tbody tr:hover { background: rgba(255,255,255,0.03); }
    .report-table td {
        padding: 7px 10px;
        color: var(--text-primary);
        white-space: nowrap;
        vertical-align: middle;
    }
    .report-table tfoot td {
        padding: 8px 10px;
        font-weight: 800;
        background: rgba(245,158,11,0.08);
        border-top: 2px solid var(--border-accent);
    }
    .badge { display: inline-block; padding: 2px 7px; border-radius: 4px; font-size: 0.63rem; font-weight: 800; }
    .badge-ga { background: rgba(34,197,94,0.15); color: var(--success); border: 1px solid rgba(34,197,94,0.3); }
    .badge-gb { background: rgba(245,158,11,0.15); color: var(--accent-primary); border: 1px solid rgba(245,158,11,0.3); }
    .badge-class { background: rgba(99,102,241,0.15); color: #a5b4fc; border: 1px solid rgba(99,102,241,0.3); }
    .filter-select {
        flex: 1;
        min-width: 130px;
        padding: var(--space-sm) var(--space-md);
        background: var(--bg-input);
        border: 1px solid var(--border-medium);
        border-radius: var(--radius-md);
        color: #fff;
    .main-row { cursor: pointer; }
    .main-row:hover { background: rgba(245, 158, 11, 0.08) !important; }
</style>
@endpush

@section('content')
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-md);">
    <h3 style="font-weight: 800; font-size: 1.05rem; color: var(--accent-primary);">📋 LAPORAN FISIK</h3>
</div>

<div class="card" style="padding: var(--space-md); margin-bottom: var(--space-md);">
    <div style="font-size: 0.72rem; font-weight: 800; color: var(--text-tertiary); text-transform: uppercase; margin-bottom: 8px;">Unduh Laporan Excel (Per Gudang):</div>
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
        <a href="{{ route('report.export', ['opname_date' => $selectedDate]) }}" class="btn" style="padding:6px 12px; font-size:0.75rem; background:rgba(255,255,255,0.1); color:#fff; border:1px solid rgba(255,255,255,0.2);">
            📥 Semua Gudang
        </a>
        @foreach($warehouses as $wh)
        <a href="{{ route('report.export', ['warehouse_id' => $wh->id, 'opname_date' => $selectedDate]) }}" class="btn" style="padding:6px 12px; font-size:0.75rem; background:rgba(34,197,94,0.15); color:var(--success); border:1px solid rgba(34,197,94,0.3);">
            📥 {{ $wh->name }}
        </a>
        @endforeach
    </div>
</div>

{{-- FILTER --}}
<div class="card" style="padding: var(--space-md); margin-bottom: var(--space-md);">
    <form action="{{ route('report.index') }}" method="GET">
        <div style="display:flex; gap:var(--space-sm); flex-wrap:wrap;">
            <select name="warehouse_id" class="filter-select" onchange="this.form.submit()">
                <option value="">-- Semua Gudang --</option>
                @foreach($warehouses as $wh)
                    <option value="{{ $wh->id }}" {{ $selectedWarehouse == $wh->id ? 'selected' : '' }}>
                        {{ $wh->name }}
                    </option>
                @endforeach
            </select>
            <select name="opname_date" class="filter-select" onchange="this.form.submit()">
                <option value="">-- Semua Tanggal --</option>
                @foreach($availableDates as $d)
                    @if($d)
                        <option value="{{ $d }}" {{ $selectedDate == $d ? 'selected' : '' }}>
                            {{ date('d M Y', strtotime($d)) }}
                        </option>
                    @endif
                @endforeach
            </select>
        </div>
    </form>
</div>

{{-- RINGKASAN --}}
<div class="card" style="margin-bottom: var(--space-md);">
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--space-xs); text-align: center;">
        <div>
            <div style="font-size:1rem; font-weight:800; font-family:var(--font-mono); color:var(--text-secondary);">{{ number_format($summary['total_records']) }}</div>
            <div style="font-size:0.58rem; color:var(--text-tertiary); font-weight:700;">RECORD</div>
        </div>
        <div>
            <div style="font-size:1rem; font-weight:800; font-family:var(--font-mono); color:var(--accent-primary);">{{ number_format($summary['total_bundles']) }}</div>
            <div style="font-size:0.58rem; color:var(--text-tertiary); font-weight:700;">BUNDLE</div>
        </div>
        <div>
            <div style="font-size:1rem; font-weight:800; font-family:var(--font-mono); color:#fff;">{{ number_format($summary['total_pcs']) }}</div>
            <div style="font-size:0.58rem; color:var(--text-tertiary); font-weight:700;">PCS</div>
        </div>
    </div>
</div>

{{-- TABEL --}}
@if($opnames->count() > 0)
<div class="report-table-wrap">
    <table class="report-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Tgl Input</th>
                <th>Petugas</th>
                <th>Gudang</th>
                <th>Blok</th>
                <th>SLOC</th>
                <th>Kategori</th>
                <th>Ukuran</th>
                <th>Grade</th>
                <th>Class</th>
                <th>Pcs/Bdl</th>
                <th>Total Bdl</th>
                <th>Total Pcs</th>
            </tr>
        </thead>
        <tbody>
            @foreach($opnames as $i => $op)
            @php
            $detailData = [
                'date' => $op->created_at ? $op->created_at->format('d/m/Y H:i:s') : '-',
                'petugas' => $op->petugas_name,
                'warehouse' => optional($op->block)->warehouse->name ?? '-',
                'block' => optional($op->block)->code ?? '-',
                'sloc' => optional($op->block)->sloc_code ?? '-',
                'category' => optional($op->pipeCategory)->name ?? '-',
                'size' => optional($op->pipeSize)->size_label ?? '-',
                'grade' => optional($op->pipeType)->code ?? '-',
                'class' => optional($op->pipeClass)->name ?? '-',
                'pcs_bdl' => optional($op->pipeSize)->pcs_per_bundle ?? 0,
                'left_bdl_per_row' => $op->left_bdl_per_row,
                'left_rows' => $op->left_rows,
                'left_adjust' => $op->left_adjust,
                'total_loose' => $op->total_loose,
                'total_bundles' => number_format($op->total_bundles),
                'total_pcs' => number_format($op->total_pcs)
            ];
            @endphp
            <tr class="main-row" onclick='showDetailModal(@json($detailData))' title="Klik untuk melihat detail lengkap">
                <td style="color:var(--text-tertiary);">{{ $i + 1 }}</td>
                <td style="font-family:var(--font-mono); color:var(--text-secondary);">
                    {{ $op->created_at ? $op->created_at->format('d/m/Y H:i:s') : '-' }}
                </td>
                <td>{{ $op->petugas_name }}</td>
                <td style="color:var(--text-secondary);">{{ optional($op->block)->warehouse->name ?? '-' }}</td>
                <td style="font-family:var(--font-mono); font-weight:800; color:var(--accent-primary);">{{ optional($op->block)->code ?? '-' }}</td>
                <td style="font-family:var(--font-mono); color:var(--text-tertiary);">{{ optional($op->block)->sloc_code ?? '-' }}</td>
                <td style="color:var(--text-secondary);">{{ optional($op->pipeCategory)->name ?? '-' }}</td>
                <td style="font-family:var(--font-mono); font-weight:700;">{{ optional($op->pipeSize)->size_label ?? '-' }}</td>
                <td>
                    @if($op->pipeType)
                        <span class="badge {{ $op->pipeType->code === 'G-A' ? 'badge-ga' : 'badge-gb' }}">{{ $op->pipeType->code }}</span>
                    @else <span style="color:var(--text-tertiary);">-</span> @endif
                </td>
                <td>
                    @if($op->pipeClass)
                        <span class="badge badge-class">{{ $op->pipeClass->name }}</span>
                    @else <span style="color:var(--text-tertiary);">-</span> @endif
                </td>
                <td style="color:var(--text-secondary);">{{ optional($op->pipeSize)->pcs_per_bundle ?? 0 }}</td>
                <td style="font-family:var(--font-mono); font-weight:800; color:var(--accent-primary);">{{ number_format($op->total_bundles) }}</td>
                <td style="font-family:var(--font-mono); font-weight:800;">{{ number_format($op->total_pcs) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
                <td colspan="11" style="text-align:right; color:var(--accent-primary);">TOTAL</td>
                <td style="font-family:var(--font-mono); color:var(--accent-primary);">{{ number_format($summary['total_bundles']) }}</td>
                <td style="font-family:var(--font-mono);">{{ number_format($summary['total_pcs']) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
@else
    <div style="text-align:center; padding:var(--space-xl) var(--space-md); color:var(--text-tertiary); font-size:0.85rem;">
        <div style="font-size:2rem; margin-bottom:8px;">📭</div>
        Belum ada data opname untuk filter yang dipilih.
    </div>
@endif
@endsection

@push('scripts')
<script>
    function showDetailModal(data) {
        const overlay = document.createElement('div');
        overlay.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.8);z-index:9999;display:flex;align-items:center;justify-content:center;padding:20px; backdrop-filter:blur(4px);';
        overlay.addEventListener('click', function(e) { if (e.target === overlay) overlay.remove(); });
        
        const content = `
            <div style="background:#1e1e2e;border:1px solid rgba(255,255,255,0.1);border-radius:12px;padding:24px;max-width:500px;width:100%;position:relative;box-shadow:0 10px 40px rgba(0,0,0,0.5); max-height: 90vh; overflow-y: auto;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;border-bottom:1px solid rgba(255,255,255,0.1);padding-bottom:12px;">
                    <h3 style="font-weight:800;color:var(--accent-primary);margin:0;font-size:1.2rem;">📋 Detail Laporan Fisik</h3>
                    <button onclick="this.closest('[style*=fixed]').remove()" style="background:none;border:none;color:var(--text-secondary);font-size:1.5rem;cursor:pointer;line-height:1;">&times;</button>
                </div>
                
                <!-- Section 1: Lokasi & Waktu -->
                <div style="margin-bottom:16px;">
                    <div style="color:var(--text-tertiary);font-size:0.75rem;text-transform:uppercase;margin-bottom:8px;font-weight:800;letter-spacing:0.5px;">📍 Lokasi & Waktu</div>
                    <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.05);border-radius:8px;padding:12px;display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div><div style="font-size:0.7rem;color:var(--text-tertiary);">Petugas</div><div style="font-size:0.85rem;color:#fff;">${data.petugas}</div></div>
                        <div><div style="font-size:0.7rem;color:var(--text-tertiary);">Tanggal Input</div><div style="font-size:0.85rem;color:#fff;font-family:var(--font-mono);">${data.date}</div></div>
                        <div><div style="font-size:0.7rem;color:var(--text-tertiary);">Gudang</div><div style="font-size:0.85rem;color:#fff;">${data.warehouse}</div></div>
                        <div><div style="font-size:0.7rem;color:var(--text-tertiary);">Blok / SLOC</div><div style="font-size:0.85rem;color:#fff;"><span style="color:var(--accent-primary);font-weight:800;">${data.block}</span> / ${data.sloc}</div></div>
                    </div>
                </div>

                <!-- Section 2: Spesifikasi Material -->
                <div style="margin-bottom:16px;">
                    <div style="color:var(--text-tertiary);font-size:0.75rem;text-transform:uppercase;margin-bottom:8px;font-weight:800;letter-spacing:0.5px;">⚙️ Spesifikasi Material</div>
                    <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.05);border-radius:8px;padding:12px;display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div><div style="font-size:0.7rem;color:var(--text-tertiary);">Kategori</div><div style="font-size:0.85rem;color:#fff;">${data.category}</div></div>
                        <div><div style="font-size:0.7rem;color:var(--text-tertiary);">Ukuran (Isi ${data.pcs_bdl} Pcs/Bdl)</div><div style="font-size:0.85rem;color:#fff;font-weight:800;">${data.size}</div></div>
                        <div><div style="font-size:0.7rem;color:var(--text-tertiary);">Grade</div><div style="font-size:0.85rem;color:#fff;">${data.grade}</div></div>
                        <div><div style="font-size:0.7rem;color:var(--text-tertiary);">Class</div><div style="font-size:0.85rem;color:#fff;">${data.class}</div></div>
                    </div>
                </div>

                <!-- Section 3: Perhitungan -->
                <div>
                    <div style="color:var(--text-tertiary);font-size:0.75rem;text-transform:uppercase;margin-bottom:8px;font-weight:800;letter-spacing:0.5px;">🧮 Hasil Perhitungan Fisik</div>
                    <div style="background:rgba(0,0,0,0.25);border:1px solid rgba(255,255,255,0.05);border-radius:8px;padding:16px;margin-bottom:12px;">
                        <div style="font-size:0.7rem;color:var(--text-tertiary);margin-bottom:4px;">Rumus Total Bundle</div>
                        <div style="font-family:var(--font-mono);color:#fff;font-size:1.05rem;display:flex;align-items:center;flex-wrap:wrap;gap:6px;">
                            <span style="color:var(--text-secondary);">(</span>
                            <span>${data.left_bdl_per_row}</span> <span style="font-size:0.65rem;color:var(--text-tertiary);">Bdl/Brs</span> 
                            <span style="color:var(--accent-primary);">×</span> 
                            <span>${data.left_rows}</span> <span style="font-size:0.65rem;color:var(--text-tertiary);">Brs</span>
                            <span style="color:var(--text-secondary);">)</span>
                            <span style="color:var(--accent-primary);">+</span>
                            <span>${data.left_adjust}</span> <span style="font-size:0.65rem;color:var(--text-tertiary);">Adj</span>
                            <span style="color:var(--text-secondary);">=</span>
                            <span style="font-weight:800;color:var(--accent-primary);">${data.total_bundles}</span>
                        </div>
                    </div>
                    <div style="display:flex;gap:12px;">
                        <div style="flex:1;background:rgba(0,0,0,0.25);border:1px solid rgba(255,255,255,0.05);border-radius:8px;padding:12px;text-align:center;">
                            <div style="font-size:0.7rem;color:var(--text-tertiary);margin-bottom:4px;text-transform:uppercase;">Pieces Lepas</div>
                            <div style="font-family:var(--font-mono);font-size:1.2rem;font-weight:800;color:#fff;">${data.total_loose} <span style="font-size:0.7rem;font-weight:400;color:var(--text-secondary);">Pcs</span></div>
                        </div>
                        <div style="flex:1;background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.3);border-radius:8px;padding:12px;text-align:center;">
                            <div style="font-size:0.7rem;color:var(--accent-primary);margin-bottom:4px;text-transform:uppercase;font-weight:800;">Total Pcs Akhir</div>
                            <div style="font-family:var(--font-mono);font-size:1.3rem;font-weight:800;color:var(--accent-primary);">${data.total_pcs}</div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        overlay.innerHTML = content;
        document.body.appendChild(overlay);
    }
</script>
@endpush
