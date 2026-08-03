@extends('layouts.app')

@section('title', 'Laporan Stock Opname')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/report.css') }}">
@endpush

@section('content')
<div class="report-header">
    <h3 class="report-title">📋 LAPORAN FISIK</h3>
</div>

<div class="card export-card">
    <div class="export-label">Unduh Laporan Excel (Per Gudang):</div>
    <div class="export-buttons">
        <a href="{{ route('report.export', ['opname_date' => $selectedDate]) }}" class="btn btn-export-all">
            📥 Semua Gudang
        </a>
        @foreach($warehouses as $wh)
        <a href="{{ route('report.export', ['warehouse_id' => $wh->id, 'opname_date' => $selectedDate]) }}" class="btn btn-export-wh">
            📥 {{ $wh->name }}
        </a>
        @endforeach
    </div>
</div>

{{-- FILTER --}}
<div class="card export-card">
    <form action="{{ route('report.index') }}" method="GET">
        <div class="filter-form-wrap">
            <select name="warehouse_id" class="filter-select">
                <option value="">-- Semua Gudang --</option>
                @foreach($warehouses as $wh)
                    <option value="{{ $wh->id }}" {{ $selectedWarehouse == $wh->id ? 'selected' : '' }}>
                        {{ $wh->name }}
                    </option>
                @endforeach
            </select>
            <select name="opname_date" class="filter-select">
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
<div class="card export-card" style="margin-bottom: var(--space-md);">
    <div class="summary-grid">
        <div>
            <div class="summary-val summary-val-records">{{ number_format($summary['total_records']) }}</div>
            <div class="summary-label">RECORD</div>
        </div>
        <div>
            <div class="summary-val summary-val-bundles">{{ number_format($summary['total_bundles']) }}</div>
            <div class="summary-label">BUNDLE</div>
        </div>
        <div>
            <div class="summary-val summary-val-pcs">{{ number_format($summary['total_pcs']) }}</div>
            <div class="summary-label">PCS</div>
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
            <tr class="main-row" data-detail="{{ json_encode($detailData) }}" title="Klik untuk melihat detail lengkap">
                <td class="text-tertiary">{{ $i + 1 }}</td>
                <td class="font-mono text-secondary">
                    {{ $op->created_at ? $op->created_at->format('d/m/Y H:i:s') : '-' }}
                </td>
                <td>{{ $op->petugas_name }}</td>
                <td class="text-secondary">{{ optional($op->block)->warehouse->name ?? '-' }}</td>
                <td class="font-mono font-bold text-accent">{{ optional($op->block)->code ?? '-' }}</td>
                <td class="font-mono text-tertiary">{{ optional($op->block)->sloc_code ?? '-' }}</td>
                <td class="text-secondary">{{ optional($op->pipeCategory)->name ?? '-' }}</td>
                <td class="font-mono font-semibold">{{ optional($op->pipeSize)->size_label ?? '-' }}</td>
                <td>
                    @if($op->pipeType)
                        <span class="badge {{ $op->pipeType->code === 'G-A' ? 'badge-ga' : 'badge-gb' }}">{{ $op->pipeType->code }}</span>
                    @else <span class="text-tertiary">-</span> @endif
                </td>
                <td>
                    @if($op->pipeClass)
                        <span class="badge badge-class">{{ $op->pipeClass->name }}</span>
                    @else <span class="text-tertiary">-</span> @endif
                </td>
                <td class="text-secondary">{{ optional($op->pipeSize)->pcs_per_bundle ?? 0 }}</td>
                <td class="font-mono font-bold text-accent">{{ number_format($op->total_bundles) }}</td>
                <td class="font-mono font-bold">{{ number_format($op->total_pcs) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
                <td colspan="11" style="text-align:right; color:var(--accent-primary);">TOTAL</td>
                <td class="font-mono text-accent">{{ number_format($summary['total_bundles']) }}</td>
                <td class="font-mono">{{ number_format($summary['total_pcs']) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
@else
    <div class="empty-state">
        <div class="empty-state-icon">📭</div>
        Belum ada data opname untuk filter yang dipilih.
    </div>
@endif
@endsection

@push('scripts')
<script src="{{ asset('js/report.js') }}"></script>
@endpush
