@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xs);">
        <div style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-tertiary); font-weight: 700;">
            Ringkasan Fisik
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

@push('scripts')
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
    
    function showBundleBreakdown() {
        if (!bundleData || bundleData.length === 0) return;
        const overlay = document.createElement('div');
        overlay.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.8);z-index:9999;display:flex;align-items:center;justify-content:center;padding:20px; backdrop-filter:blur(4px);';
        overlay.addEventListener('click', function(e) { if (e.target === overlay) overlay.remove(); });
        
        let listHtml = '';
        let total = 0;
        bundleData.forEach(item => {
            listHtml += `
                <div style="display:flex; justify-content:space-between; padding:12px; border-bottom:1px solid rgba(255,255,255,0.05);">
                    <div style="color:var(--text-secondary);font-weight:700;">🏭 ${item.name}</div>
                    <div style="font-family:var(--font-mono); font-weight:800; color:#fff;">${new Intl.NumberFormat('en-US').format(item.bundles)} <span style="font-size:0.7rem;font-weight:400;color:var(--text-tertiary);">BDL</span></div>
                </div>
            `;
            total += item.bundles;
        });

        let topCatsHtml = '';
        if (topCategoriesData && topCategoriesData.length > 0) {
            topCategoriesData.forEach((cat, index) => {
                const medal = index === 0 ? '🥇' : (index === 1 ? '🥈' : '🥉');
                topCatsHtml += `
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 12px; background:rgba(255,255,255,0.02); border-radius:6px; margin-bottom:6px; border:1px solid rgba(255,255,255,0.05);">
                        <div style="color:var(--text-secondary); font-size:0.85rem; font-weight:700;">${medal} ${cat.name}</div>
                        <div style="font-family:var(--font-mono); font-weight:800; color:var(--accent-primary); font-size:0.9rem;">${new Intl.NumberFormat('en-US').format(cat.bundles)} <span style="font-size:0.6rem;font-weight:400;color:var(--text-tertiary);">BDL</span></div>
                    </div>
                `;
            });
            topCatsHtml = `
                <div style="margin-top:20px; margin-bottom:12px;">
                    <div style="font-size:0.75rem; color:var(--text-tertiary); text-transform:uppercase; font-weight:800; margin-bottom:8px; letter-spacing:0.5px;">🔥 Top 3 Kategori Hari Ini</div>
                    ${topCatsHtml}
                </div>
            `;
        }

        const content = `
            <div style="background:#1e1e2e;border:1px solid rgba(255,255,255,0.1);border-radius:12px;padding:24px;max-width:400px;width:100%;box-shadow:0 10px 40px rgba(0,0,0,0.5); max-height:90vh; overflow-y:auto;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                    <h3 style="font-weight:800;color:var(--accent-primary);margin:0;font-size:1.1rem;">📊 Rincian Bundle</h3>
                    <button onclick="this.closest('[style*=fixed]').remove()" style="background:none;border:none;color:var(--text-secondary);font-size:1.5rem;cursor:pointer;line-height:1;">&times;</button>
                </div>
                
                <div style="background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid rgba(255,255,255,0.05); margin-bottom:12px;">
                    ${listHtml}
                </div>
                <div style="display:flex; justify-content:space-between; padding:12px; background:rgba(245,158,11,0.1); border:1px solid rgba(245,158,11,0.3); border-radius:8px;">
                    <div style="color:var(--accent-primary); font-weight:800; font-size:0.8rem; text-transform:uppercase;">Total Keseluruhan</div>
                    <div style="font-family:var(--font-mono); font-weight:800; color:var(--accent-primary);">${new Intl.NumberFormat('en-US').format(total)} <span style="font-size:0.7rem;font-weight:400;">BDL</span></div>
                </div>

                ${topCatsHtml}
            </div>
        `;
        overlay.innerHTML = content;
        document.body.appendChild(overlay);
    }

    const pcsData = @json($pcsBreakdown);
    function showPcsBreakdown() {
        if (!pcsData || pcsData.length === 0) return;
        const overlay = document.createElement('div');
        overlay.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.8);z-index:9999;display:flex;align-items:center;justify-content:center;padding:20px; backdrop-filter:blur(4px);';
        overlay.addEventListener('click', function(e) { if (e.target === overlay) overlay.remove(); });
        
        let listHtml = '';
        let total = 0;
        pcsData.forEach(item => {
            listHtml += `
                <div style="display:flex; justify-content:space-between; padding:12px; border-bottom:1px solid rgba(255,255,255,0.05);">
                    <div style="color:var(--text-secondary);font-weight:700;">🏭 ${item.name}</div>
                    <div style="font-family:var(--font-mono); font-weight:800; color:#fff;">${new Intl.NumberFormat('en-US').format(item.pcs)} <span style="font-size:0.7rem;font-weight:400;color:var(--text-tertiary);">PCS</span></div>
                </div>
            `;
            total += item.pcs;
        });

        const content = `
            <div style="background:#1e1e2e;border:1px solid rgba(255,255,255,0.1);border-radius:12px;padding:24px;max-width:400px;width:100%;box-shadow:0 10px 40px rgba(0,0,0,0.5);">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                    <h3 style="font-weight:800;color:var(--text-primary);margin:0;font-size:1.1rem;">📊 Rincian Pcs per Gudang</h3>
                    <button onclick="this.closest('[style*=fixed]').remove()" style="background:none;border:none;color:var(--text-secondary);font-size:1.5rem;cursor:pointer;line-height:1;">&times;</button>
                </div>
                <div style="background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid rgba(255,255,255,0.05); margin-bottom:16px;">
                    ${listHtml}
                </div>
                <div style="display:flex; justify-content:space-between; padding:12px; background:rgba(245,158,11,0.1); border:1px solid rgba(245,158,11,0.3); border-radius:8px;">
                    <div style="color:var(--accent-primary); font-weight:800; font-size:0.8rem; text-transform:uppercase;">Total Keseluruhan</div>
                    <div style="font-family:var(--font-mono); font-weight:800; color:var(--accent-primary);">${new Intl.NumberFormat('en-US').format(total)} <span style="font-size:0.7rem;font-weight:400;">PCS</span></div>
                </div>
            </div>
        `;
        overlay.innerHTML = content;
        document.body.appendChild(overlay);
    }

    const opnameData = @json($opnameUsers ?? []);
    function showOpnameBreakdown() {
        if (!opnameData || Object.keys(opnameData).length === 0) return;
        const overlay = document.createElement('div');
        overlay.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.8);z-index:9999;display:flex;align-items:center;justify-content:center;padding:20px; backdrop-filter:blur(4px);';
        overlay.addEventListener('click', function(e) { if (e.target === overlay) overlay.remove(); });
        
        let listHtml = '';
        Object.keys(opnameData).forEach(whName => {
            let usersHtml = '';
            Object.keys(opnameData[whName]).forEach(user => {
                usersHtml += `<div style="display:flex; justify-content:space-between; align-items:center; padding:4px 0;">
                    <div style="color:var(--text-secondary); font-size:0.85rem;">👷 ${user}</div>
                    <div style="font-family:var(--font-mono); font-weight:700; color:#fff; font-size:0.85rem;">${opnameData[whName][user]} <span style="font-size:0.6rem;font-weight:400;color:var(--text-tertiary);">x</span></div>
                </div>`;
            });

            listHtml += `
                <div style="padding:12px; border-bottom:1px solid rgba(255,255,255,0.05);">
                    <div style="color:var(--accent-primary); font-weight:800; font-size:0.9rem; margin-bottom:8px;">🏭 ${whName}</div>
                    ${usersHtml}
                </div>
            `;
        });

        const content = `
            <div style="background:#1e1e2e;border:1px solid rgba(255,255,255,0.1);border-radius:12px;padding:24px;max-width:400px;width:100%;box-shadow:0 10px 40px rgba(0,0,0,0.5); max-height:80vh; overflow-y:auto; display:flex; flex-direction:column;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-shrink:0;">
                    <h3 style="font-weight:800;color:var(--success);margin:0;font-size:1.1rem;">📝 Petugas Opname Hari Ini</h3>
                    <button onclick="this.closest('[style*=fixed]').remove()" style="background:none;border:none;color:var(--text-secondary);font-size:1.5rem;cursor:pointer;line-height:1;">&times;</button>
                </div>
                <div style="background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid rgba(255,255,255,0.05); margin-bottom:16px; overflow-y:auto;">
                    ${listHtml}
                </div>
                <div style="flex-shrink:0;">
                    <a href="{{ route('report.index') }}" style="display:block; width:100%; text-align:center; padding:12px; background:linear-gradient(135deg, var(--accent-primary), var(--accent-secondary)); color:#000; text-decoration:none; font-weight:800; border-radius:8px; font-size:0.9rem; border:none; cursor:pointer;">
                        Lihat Seluruh Laporan 📊
                    </a>
                </div>
            </div>
        `;
        overlay.innerHTML = content;
        document.body.appendChild(overlay);
    }

    function updateClock() {
        const now = new Date();
        const optionsDate = { day: '2-digit', month: 'short', year: 'numeric' };
        const optionsTime = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
        const dateStr = now.toLocaleDateString('id-ID', optionsDate);
        const timeStr = now.toLocaleTimeString('id-ID', optionsTime).replace(/\./g, ':');
        document.getElementById('liveClock').textContent = dateStr + ' ' + timeStr;
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>
@endpush
@endsection
