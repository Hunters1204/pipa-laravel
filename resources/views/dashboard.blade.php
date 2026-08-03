@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
<div class="card">
    <div class="dashboard-header">
        <div class="dashboard-title-wrapper">
            <div class="dashboard-title">
                Ringkasan Fisik
            </div>
            <form id="filterForm" action="{{ route('dashboard') }}" method="GET" class="filter-form">
                <select name="filter" onchange="document.getElementById('filterForm').submit()" class="filter-select">
                    <option value="today" {{ $filter === 'today' ? 'selected' : '' }}>Hari Ini</option>
                    <option value="yesterday" {{ $filter === 'yesterday' ? 'selected' : '' }}>Kemarin</option>
                    <option value="month" {{ $filter === 'month' ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>Semua Waktu</option>
                </select>
            </form>
        </div>
        <div class="live-clock-wrapper">
            <span class="live-clock-icon">🕒</span>
            <span id="liveClock" class="live-clock-text">--:--:--</span>
        </div>
    </div>
    <div class="summary-grid">
        <div class="summary-card" onclick="showBundleBreakdown()" title="Lihat rincian Bundle & Kategori Pipa">
            <div class="summary-value bundle">
                {{ number_format($totalBundles) }}
            </div>
            <div class="summary-label">BUNDLE 🔍</div>
        </div>
        <div class="summary-card" onclick="showPcsBreakdown()" title="Lihat detail Pcs per Gudang">
            <div class="summary-value pcs">
                {{ number_format($totalPcs) }}
            </div>
            <div class="summary-label">PCS 🔍</div>
        </div>
        <div class="summary-card" onclick="showOpnameBreakdown()" title="Lihat siapa yang input Opname per Gudang">
            <div class="summary-value opname">
                {{ number_format($totalOpnames) }}
            </div>
            <div class="summary-label">OPNAME 🔍</div>
        </div>
    </div>
</div>

<h3 class="section-heading">
    PILIH GUDANG
</h3>

@foreach($warehouses as $wh)
    @php
        $st = $warehouseStats[$wh->id] ?? ['counted' => 0, 'total' => 36, 'pct' => 0];
    @endphp
    <a href="{{ route('warehouse.show', ['id' => $wh->id, 'filter' => $filter]) }}" class="card warehouse-card">
        <div class="wh-header">
            <div class="wh-name">
                🏭 {{ $wh->name }}
            </div>
            <div class="wh-stats">
                {{ $st['counted'] }}/{{ $st['total'] }} Blok
            </div>
        </div>
        <div class="wh-desc">
            {{ $wh->description }}
        </div>
        
        <div class="progress-bar-bg">
            <div class="progress-bar-fill" style="width: {{ $st['pct'] }}%;"></div>
        </div>
    </a>
@endforeach

<div class="mt-xl mb-md">
    <h3 class="section-heading">
        📈 TREN OPNAME (7 HARI TERAKHIR)
    </h3>
    <div class="card p-md">
        <canvas id="trendChart" height="200"></canvas>
    </div>
</div>

<div class="mt-xl mb-xl">
    <h3 class="section-heading">
        ⚡ AKTIVITAS TERBARU
    </h3>
    <div class="card p-md">
        @if(count($recentActivities) > 0)
            <div class="activity-list">
                @foreach($recentActivities as $act)
                    <div class="activity-item">
                        <div>
                            <div class="activity-user">
                                👷 {{ $act->petugas_name ?? 'Tidak Diketahui' }}
                            </div>
                            <div class="activity-desc">
                                Menginput <strong class="activity-highlight">{{ $act->total_bundles }} Bundle</strong> ({{ optional($act->pipeSize)->name ?? '-' }}) di {{ optional(optional($act->block)->warehouse)->name ?? '-' }} Blok {{ optional($act->block)->code ?? '-' }}
                            </div>
                        </div>
                        <div class="activity-time">
                            {{ $act->created_at->diffForHumans() }}
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="activity-empty">
                Belum ada aktivitas opname.
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@php
    $bundleBreakdown = [];
    $pcsBreakdown = [];
    foreach($warehouseStats as $id => $stat) {
        if(isset($stat['name'])) {
            $bundleBreakdown[] = [
                'name' => $stat['name'],
                'bundles' => $stat['total_bundles']
            ];
            $pcsBreakdown[] = [
                'name' => $stat['name'],
                'pcs' => $stat['total_pcs']
            ];
        }
    }
@endphp
<script>
    const bundleData = @json($bundleBreakdown);
    const topCategoriesData = @json($topCategories ?? []);
    const pcsData = @json($pcsBreakdown);
    const opnameData = @json($opnameUsers ?? []);
    const reportIndexRoute = "{{ route('report.index') }}";
    const chartData = @json($chartData);
</script>
<script src="{{ asset('js/dashboard.js') }}"></script>
@endpush
@endsection
