@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="card">
    <div style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-tertiary); font-weight: 700; margin-bottom: var(--space-xs);">
        Ringkasan Fisik
    </div>
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--space-sm); margin-top: var(--space-md); text-align: center;">
        <div style="background: var(--bg-primary); padding: var(--space-md); border-radius: var(--radius-md);">
            <div style="font-size: 1.3rem; font-weight: 800; font-family: var(--font-mono); color: var(--accent-primary);">
                {{ number_format($totalBundles) }}
            </div>
            <div style="font-size: 0.65rem; color: var(--text-tertiary); font-weight: 600;">BUNDLE</div>
        </div>
        <div style="background: var(--bg-primary); padding: var(--space-md); border-radius: var(--radius-md);">
            <div style="font-size: 1.3rem; font-weight: 800; font-family: var(--font-mono); color: var(--text-primary);">
                {{ number_format($totalPcs) }}
            </div>
            <div style="font-size: 0.65rem; color: var(--text-tertiary); font-weight: 600;">PCS</div>
        </div>
        <div style="background: var(--bg-primary); padding: var(--space-md); border-radius: var(--radius-md);">
            <div style="font-size: 1.1rem; font-weight: 800; font-family: var(--font-mono); color: var(--success);">
                {{ number_format($totalWeight, 0, ',', '.') }}
            </div>
            <div style="font-size: 0.65rem; color: var(--text-tertiary); font-weight: 600;">TOTAL (KG)</div>
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
    <a href="{{ route('warehouse.show', $wh->id) }}" class="card" style="display: block; text-decoration: none; color: inherit; transition: transform 0.2s;">
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
@endsection
