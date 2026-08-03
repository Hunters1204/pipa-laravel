@extends('layouts.app')

@section('title', $warehouse->name)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/warehouse.css') }}">
@endpush

@section('content')
<div class="wh-page-header">
    <a href="{{ route('dashboard', ['filter' => $filter ?? 'today']) }}" class="btn-back">
        ← Kembali
    </a>
    <div class="wh-page-title">
        {{ $warehouse->name }}
        @php
            $filterName = ['today' => 'Hari Ini', 'yesterday' => 'Kemarin', 'month' => 'Bulan Ini', 'all' => 'Semua Waktu'][$filter ?? 'today'] ?? 'Hari Ini';
        @endphp
        <span class="filter-badge">({{ $filterName }})</span>
    </div>
</div>

<div class="card progress-card">
    <div class="progress-text">
        <span>Progress Fisik</span>
        <span class="progress-value">{{ $stats['counted'] }}/{{ $stats['total'] }} Blok ({{ $stats['pct'] }}%)</span>
    </div>
    <div class="progress-bar-bg">
        <div class="progress-bar-fill" style="width: {{ $stats['pct'] }}%;"></div>
    </div>
</div>

<div class="block-row-container">
    @foreach($groupedBlocks as $letter => $blocks)
        <div class="block-group">
            <div class="block-group-title">
                BARIS {{ $letter }}
            </div>
            <div class="block-grid">
                @foreach($blocks as $b)
                    @php
                        $itemCount = $b->stock_opnames_count;
                        $hasData = $itemCount > 0;
                        $pcs = $hasData ? $b->pcs_sum : 0;
                    @endphp
                    <a href="{{ route('opname.create', ['warehouse' => $warehouse->id, 'block' => $b->code]) }}" 
                       class="block-item {{ $hasData ? 'has-data' : '' }}">
                        <span class="block-code">
                            {{ $b->code }}
                        </span>
                        <span class="block-subtitle">
                            {{ $hasData ? number_format($pcs).' pcs ('.$itemCount.' jenis)' : 'Belum' }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
@endsection
