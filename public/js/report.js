document.addEventListener('DOMContentLoaded', () => {
    // Bind table row clicks
    document.querySelectorAll('.main-row').forEach(row => {
        row.addEventListener('click', function() {
            const dataStr = this.getAttribute('data-detail');
            if (dataStr) {
                try {
                    const data = JSON.parse(dataStr);
                    showDetailModal(data);
                } catch (e) {
                    console.error("Invalid JSON for row detail", e);
                }
            }
        });
    });

    // Bind filter auto-submit
    document.querySelectorAll('.filter-select').forEach(select => {
        select.addEventListener('change', function() {
            if (this.form) {
                this.form.submit();
            }
        });
    });
});

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
