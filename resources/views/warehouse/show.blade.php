@extends('layouts.app')

@section('title', $warehouse->name)

@section('content')
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-md);">
    <a href="{{ route('dashboard', ['filter' => $filter ?? 'today']) }}" style="color: var(--text-secondary); text-decoration: none; font-size: 0.85rem; font-weight: 600;">
        ← Kembali
    </a>
    <div style="font-weight: 800; font-size: 1rem; color: var(--accent-primary);">
        {{ $warehouse->name }}
        @php
            $filterName = ['today' => 'Hari Ini', 'yesterday' => 'Kemarin', 'month' => 'Bulan Ini', 'all' => 'Semua Waktu'][$filter ?? 'today'] ?? 'Hari Ini';
        @endphp
        <span style="font-size: 0.7rem; color: var(--text-tertiary); font-weight: normal; margin-left: 6px;">({{ $filterName }})</span>
    </div>
</div>

<div class="card" style="padding: var(--space-md); margin-bottom: var(--space-md);">
    <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 6px;">
        <span>Progress Fisik</span>
        <span style="font-family: var(--font-mono); font-weight: 700;">{{ $stats['counted'] }}/{{ $stats['total'] }} Blok ({{ $stats['pct'] }}%)</span>
    </div>
    <div style="background: var(--bg-primary); height: 8px; border-radius: 4px; overflow: hidden;">
        <div style="width: {{ $stats['pct'] }}%; height: 100%; background: linear-gradient(90deg, var(--accent-primary), var(--success));"></div>
    </div>
</div>

<div style="display: flex; flex-direction: column; gap: var(--space-md);">
    @foreach($groupedBlocks as $letter => $blocks)
        <div style="background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: var(--space-md);">
            <div style="font-size: 0.75rem; font-weight: 800; color: var(--accent-primary); margin-bottom: var(--space-sm);">
                BARIS {{ $letter }}
            </div>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--space-sm);">
                @foreach($blocks as $b)
                    @php
                        $itemCount = $b->stockOpnames->count();
                        $hasData = $itemCount > 0;
                        $pcs = $hasData ? $b->stockOpnames->sum('total_pcs') : 0;
                    @endphp
                    <a href="{{ route('opname.create', ['warehouse' => $warehouse->id, 'block' => $b->code]) }}" 
                       style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: var(--space-md); border-radius: var(--radius-md); text-decoration: none; border: 1px solid {{ $hasData ? 'rgba(34, 197, 94, 0.4)' : 'var(--border-subtle)' }}; background: {{ $hasData ? 'rgba(34, 197, 94, 0.1)' : 'var(--bg-primary)' }};">
                        <span style="font-weight: 800; font-size: 1rem; color: {{ $hasData ? 'var(--success)' : 'var(--text-primary)' }}; font-family: var(--font-mono);">
                            {{ $b->code }}
                        </span>
                        <span style="font-size: 0.62rem; color: {{ $hasData ? 'var(--success)' : 'var(--text-tertiary)' }}; margin-top: 2px;">
                            {{ $hasData ? number_format($pcs).' pcs ('.$itemCount.' jenis)' : 'Belum' }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
@endsection
