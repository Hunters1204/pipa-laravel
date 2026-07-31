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
        font-size: 0.85rem;
    }
</style>
@endpush

@section('content')
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-md);">
    <h3 style="font-weight: 800; font-size: 1.05rem; color: var(--accent-primary);">📋 LAPORAN FISIK</h3>
    <a href="{{ route('report.export', ['warehouse_id' => $selectedWarehouse, 'opname_date' => $selectedDate]) }}"
       class="btn btn-success" style="padding: 6px 12px; font-size: 0.78rem;">
        📥 Export CSV
    </a>
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
                <th>Bdl Kiri</th>
                <th>Bdl Kanan</th>
                <th>Total Bdl</th>
                <th>Total Pcs</th>
            </tr>
        </thead>
        <tbody>
            @foreach($opnames as $i => $op)
            <tr>
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
                <td style="font-family:var(--font-mono);">{{ $op->left_bundles }}</td>
                <td style="font-family:var(--font-mono);">{{ $op->right_bundles }}</td>
                <td style="font-family:var(--font-mono); font-weight:800; color:var(--accent-primary);">{{ number_format($op->total_bundles) }}</td>
                <td style="font-family:var(--font-mono); font-weight:800;">{{ number_format($op->total_pcs) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
                <td colspan="13" style="text-align:right; color:var(--accent-primary);">TOTAL</td>
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
