@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xs);">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-tertiary); font-weight: 700;">
                Ringkasan Fisik
            </div>
            <form id="filterForm" action="{{ route('dashboard') }}" method="GET" style="margin:0;">
                <select name="filter" onchange="document.getElementById('filterForm').submit()" style="background: rgba(255,255,255,0.1); color: var(--text-primary); border: 1px solid var(--border-subtle); border-radius: 6px; padding: 4px 8px; font-size: 0.75rem; font-weight: 600; cursor: pointer; outline: none;">
                    <option value="today" {{ $filter === 'today' ? 'selected' : '' }} style="color:#000;">Hari Ini</option>
                    <option value="yesterday" {{ $filter === 'yesterday' ? 'selected' : '' }} style="color:#000;">Kemarin</option>
                    <option value="month" {{ $filter === 'month' ? 'selected' : '' }} style="color:#000;">Bulan Ini</option>
                    <option value="all" {{ $filter === 'all' ? 'selected' : '' }} style="color:#000;">Semua Waktu</option>
                </select>
            </form>
        </div>
        <div style="background: rgba(255,255,255,0.05); padding: 4px 10px; border-radius: 6px; border: 1px solid var(--border-subtle); display: flex; align-items: center; gap: 6px;">
            <span style="font-size: 0.9rem;">🕒</span>
            <span id="liveClock" style="font-family: var(--font-mono); font-weight: 700; font-size: 0.75rem; color: var(--accent-primary);">--:--:--</span>
        </div>
    </div>
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--space-sm); margin-top: var(--space-md); text-align: center;">
        <div style="background: var(--bg-primary); padding: var(--space-md); border-radius: var(--radius-md); cursor: pointer;" onclick="showBundleBreakdown()" title="Lihat rincian Bundle & Kategori Pipa">
            <div style="font-size: 1.3rem; font-weight: 800; font-family: var(--font-mono); color: var(--accent-primary);">
                {{ number_format($totalBundles) }}
            </div>
            <div style="font-size: 0.65rem; color: var(--text-tertiary); font-weight: 600;">BUNDLE 🔍</div>
        </div>
        <div style="background: var(--bg-primary); padding: var(--space-md); border-radius: var(--radius-md); cursor: pointer;" onclick="showPcsBreakdown()" title="Lihat detail Pcs per Gudang">
            <div style="font-size: 1.3rem; font-weight: 800; font-family: var(--font-mono); color: var(--text-primary);">
                {{ number_format($totalPcs) }}
            </div>
            <div style="font-size: 0.65rem; color: var(--text-tertiary); font-weight: 600;">PCS 🔍</div>
        </div>
        <div style="background: var(--bg-primary); padding: var(--space-md); border-radius: var(--radius-md); cursor: pointer;" onclick="showOpnameBreakdown()" title="Lihat siapa yang input Opname per Gudang">
            <div style="font-size: 1.3rem; font-weight: 800; font-family: var(--font-mono); color: var(--success);">
                {{ number_format($totalOpnames) }}
            </div>
            <div style="font-size: 0.65rem; color: var(--text-tertiary); font-weight: 600;">OPNAME 🔍</div>
        </div>
    </div>
</div>

<h3 style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: var(--space-md); font-weight: 700;">
    PILIH GUDANG
</h3>

@foreach($warehouses as $wh)
    @php
        $st = $warehouseStats[$wh->id] ?? ['counted' => 0, 'total' => 36, 'pct' => 0];
    @endphp
    <a href="{{ route('warehouse.show', ['id' => $wh->id, 'filter' => $filter]) }}" class="card" style="display: block; text-decoration: none; color: inherit; transition: transform 0.2s;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-sm);">
            <div style="font-weight: 800; font-size: 1.1rem; color: var(--text-primary);">
                🏭 {{ $wh->name }}
            </div>
            <div style="font-size: 0.8rem; font-family: var(--font-mono); color: var(--accent-primary); font-weight: 700;">
                {{ $st['counted'] }}/{{ $st['total'] }} Blok
            </div>
        </div>
        <div style="font-size: 0.75rem; color: var(--text-tertiary); margin-bottom: var(--space-md);">
            {{ $wh->description }}
        </div>
        
        <div style="background: var(--bg-primary); height: 8px; border-radius: 4px; overflow: hidden;">
            <div style="width: {{ $st['pct'] }}%; height: 100%; background: linear-gradient(90deg, var(--accent-primary), var(--success)); border-radius: 4px;"></div>
        </div>
    </a>
@endforeach

<div style="margin-top: var(--space-xl); margin-bottom: var(--space-md);">
    <h3 style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: var(--space-md); font-weight: 700;">
        📈 TREN OPNAME (7 HARI TERAKHIR)
    </h3>
    <div class="card" style="padding: var(--space-md);">
        <canvas id="trendChart" height="200"></canvas>
    </div>
</div>

<div style="margin-top: var(--space-xl); margin-bottom: var(--space-xl);">
    <h3 style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: var(--space-md); font-weight: 700;">
        ⚡ AKTIVITAS TERBARU
    </h3>
    <div class="card" style="padding: var(--space-md);">
        @if(count($recentActivities) > 0)
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @foreach($recentActivities as $act)
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <div>
                            <div style="font-weight: 700; color: var(--text-primary); font-size: 0.9rem; margin-bottom: 4px;">
                                👷 {{ $act->petugas_name ?? 'Tidak Diketahui' }}
                            </div>
                            <div style="font-size: 0.75rem; color: var(--text-tertiary);">
                                Menginput <strong style="color:var(--accent-primary);">{{ $act->total_bundles }} Bundle</strong> ({{ optional($act->pipeSize)->name ?? '-' }}) di {{ optional(optional($act->block)->warehouse)->name ?? '-' }} Blok {{ optional($act->block)->code ?? '-' }}
                            </div>
                        </div>
                        <div style="font-size: 0.7rem; color: var(--text-secondary); font-family: var(--font-mono); background: rgba(255,255,255,0.05); padding: 4px 8px; border-radius: 4px; white-space: nowrap;">
                            {{ $act->created_at->diffForHumans() }}
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div style="text-align: center; color: var(--text-tertiary); font-size: 0.8rem; padding: 20px 0;">
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
