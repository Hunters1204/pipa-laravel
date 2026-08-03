function switchTab(name, el) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    el.classList.add('active');
}

// Restore active tab after form submit
document.addEventListener('DOMContentLoaded', function() {
    if (typeof ACTIVE_TAB !== 'undefined' && ACTIVE_TAB !== '') {
        const panel = document.getElementById('tab-' + ACTIVE_TAB);
        if (panel) {
            const idx = ['size','grade','class','category'].indexOf(ACTIVE_TAB);
            const btn = document.querySelectorAll('.tab-btn')[idx];
            if (btn) switchTab(ACTIVE_TAB, btn);
        }
    }
});

// ── Custom Delete Modal ───────────────────────────────────────
let pendingDeleteUrl = null;

function deleteMaster(url, itemName) {
    pendingDeleteUrl = url;
    let overlay = document.getElementById('deleteConfirmOverlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'deleteConfirmOverlay';
        overlay.innerHTML = `
            <div style="position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:9998;display:flex;align-items:center;justify-content:center;">
                <div style="background:#1a2234;border:1px solid rgba(239,68,68,0.4);border-radius:16px;padding:24px;max-width:320px;width:90%;text-align:center;position:relative;">
                    <div style="font-size:2rem;margin-bottom:8px;">🗑️</div>
                    <div style="font-weight:800;color:#fff;font-size:1rem;margin-bottom:6px;">Hapus Item Ini?</div>
                    <div id="deleteItemName" style="font-size:0.8rem;color:#f3f4f6;margin-bottom:8px;font-weight:600;"></div>
                    <div style="font-size:0.8rem;color:#9ca3af;margin-bottom:20px;">Data yang sudah dihapus tidak bisa dikembalikan.</div>
                    <div style="display:flex;gap:8px;">
                        <button id="btnDeleteCancel" style="flex:1;padding:10px;border-radius:8px;border:1px solid rgba(255,255,255,0.15);background:#111827;color:#9ca3af;font-weight:700;cursor:pointer;font-size:0.85rem;">Batal</button>
                        <button id="btnDeleteConfirm" style="flex:1;padding:10px;border-radius:8px;border:none;background:#ef4444;color:#fff;font-weight:700;cursor:pointer;font-size:0.85rem;">Ya, Hapus</button>
                    </div>
                </div>
            </div>`;
        document.body.appendChild(overlay);

        document.getElementById('btnDeleteCancel').addEventListener('click', function() {
            overlay.style.display = 'none';
            pendingDeleteUrl = null;
        });

        document.getElementById('btnDeleteConfirm').addEventListener('click', function() {
            if (!pendingDeleteUrl) return;
            overlay.style.display = 'none';
            executeDelete(pendingDeleteUrl);
        });
    }
    document.getElementById('deleteItemName').textContent = 'Anda akan menghapus ' + itemName + '.';
    overlay.style.display = '';
}

function executeDelete(url) {
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': token,
            'Accept': 'text/html'
        },
        body: '_token=' + encodeURIComponent(token) + '&_method=DELETE',
        redirect: 'follow'
    }).then(function(response) {
        window.location.reload();
    }).catch(function(err) {
        alert('Gagal menghapus: ' + err.message);
    });
}
