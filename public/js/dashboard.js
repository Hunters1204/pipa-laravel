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
                <a href="${reportIndexRoute}" style="display:block; width:100%; text-align:center; padding:12px; background:linear-gradient(135deg, var(--accent-primary), var(--accent-secondary)); color:#000; text-decoration:none; font-weight:800; border-radius:8px; font-size:0.9rem; border:none; cursor:pointer;">
                    Lihat Seluruh Laporan 📊
                </a>
            </div>
        </div>
    `;
    overlay.innerHTML = content;
    document.body.appendChild(overlay);
}

function updateClock() {
    const liveClock = document.getElementById('liveClock');
    if (!liveClock) return;
    const now = new Date();
    const optionsDate = { day: '2-digit', month: 'short', year: 'numeric' };
    const optionsTime = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
    const dateStr = now.toLocaleDateString('id-ID', optionsDate);
    const timeStr = now.toLocaleTimeString('id-ID', optionsTime).replace(/\./g, ':');
    liveClock.textContent = dateStr + ' ' + timeStr;
}

window.addEventListener('DOMContentLoaded', () => {
    setInterval(updateClock, 1000);
    updateClock();

    // Chart.js Initialization
    const chartCanvas = document.getElementById('trendChart');
    if (chartCanvas && typeof Chart !== 'undefined' && typeof chartData !== 'undefined') {
        const ctx = chartCanvas.getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Total Bundle',
                    data: chartData.data,
                    backgroundColor: '#3b82f6',
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#9ca3af', precision: 0 } },
                    x: { grid: { display: false }, ticks: { color: '#9ca3af' } }
                }
            }
        });
    }
});
