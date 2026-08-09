let currentPcsPerBundle = 0; // Value set via initial fetch or data-attribute
// ── Delete Opname ────────────────────────────────────────────
let pendingDeleteId = null;

function deleteOpname(id) {
    pendingDeleteId = id;
    // Show custom confirm modal
    let overlay = document.getElementById('deleteConfirmOverlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'deleteConfirmOverlay';
        overlay.innerHTML = `
            <div style="position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:99999;display:flex;align-items:center;justify-content:center;">
                <div style="background:#1a2234;border:1px solid rgba(239,68,68,0.4);border-radius:16px;padding:24px;max-width:320px;width:90%;text-align:center;">
                    <div style="font-size:2rem;margin-bottom:8px;">🗑️</div>
                    <div style="font-weight:800;color:#fff;font-size:1rem;margin-bottom:6px;">Hapus Item Ini?</div>
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
            pendingDeleteId = null;
        });

        document.getElementById('btnDeleteConfirm').addEventListener('click', function() {
            if (!pendingDeleteId) return;
            overlay.style.display = 'none';
            executeDelete(pendingDeleteId);
        });
    }
    overlay.style.display = '';
}

function executeDelete(id) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/opname/' + id;
    
    const token = document.createElement('input');
    token.type = 'hidden';
    token.name = '_token';
    token.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    form.appendChild(token);

    const method = document.createElement('input');
    method.type = 'hidden';
    method.name = '_method';
    method.value = 'DELETE';
    form.appendChild(method);

    document.body.appendChild(form);
    form.submit();
}

// ── Quick Select Spec ─────────────────────────────────────────
function clearQuickSelect() {
    document.querySelectorAll('.quick-spec-btn').forEach(el => {
        el.style.background = 'rgba(245,158,11,0.15)';
        el.style.color = 'var(--accent-primary)';
        el.style.border = '1px solid rgba(245,158,11,0.4)';
        if (el.dataset.originalText) {
            el.innerHTML = el.dataset.originalText;
        }
    });
}

function applySpec(categoryId, sizeId, typeId, classId, bdlPerRow, rows, adjust, loose, btnElement) {
    document.getElementById('pipeCategory').value = categoryId;
    document.getElementById('pipeSize').value = sizeId;
    document.getElementById('pipeType').value = typeId;
    document.getElementById('pipeClass').value = classId || '';
    
    document.getElementById('totalBdlPerRow').value = bdlPerRow || '';
    document.getElementById('totalRows').value = rows || '';
    document.getElementById('totalModeAdjust').value = adjust || '';
    document.getElementById('totalModeLoose').value = loose || '';
    
    clearQuickSelect();
    if (btnElement) {
        if (!btnElement.dataset.originalText) {
            btnElement.dataset.originalText = btnElement.innerHTML;
        }
        btnElement.style.background = 'var(--accent-primary)';
        btnElement.style.color = '#111827';
        btnElement.style.border = '1px solid var(--accent-primary)';
        btnElement.innerHTML = '✅ ' + btnElement.dataset.originalText.trim();
    }

    const card = document.getElementById('specCard');
    if (card) {
        card.style.background = 'rgba(245,158,11,0.15)';
        setTimeout(() => card.style.background = 'var(--bg-primary)', 300);
    }

    fetchPipeData();
}

// ── Pipe Data Fetch ──────────────────────────────────────────
async function fetchPipeData() {
    const sizeId = document.getElementById('pipeSize').value;
    try {
        const resp = await fetch(`/api/pipe-info/${sizeId}`);
        const data = await resp.json();
        currentPcsPerBundle = data.pcs_per_bundle || 0;
        calculate();
    } catch (e) { console.error(e); }
}

// ── Main Calculate ───────────────────────────────────────────
function calculate() {
    // Total mode
    const tBdlRow = parseInt(document.getElementById('totalBdlPerRow').value) || 0;
    const tRows = parseInt(document.getElementById('totalRows').value) || 0;
    const tAdj = parseInt(document.getElementById('totalModeAdjust').value) || 0;
    const tLoose = parseInt(document.getElementById('totalModeLoose').value) || 0;
    
    const tAutoBdl = tBdlRow * tRows;
    const tBundles = Math.max(0, tAutoBdl + tAdj);
    const tPcs = tBundles * currentPcsPerBundle + tLoose;
    
    const autoBdlDisplay = document.getElementById('totalAutoBundle');
    const modeDisplay = document.getElementById('totalModeDisplay');
    const grandBundles = document.getElementById('grandTotalBundles');
    const grandPcs = document.getElementById('grandTotalPcs');

    if(autoBdlDisplay) autoBdlDisplay.textContent = tAutoBdl;
    if(modeDisplay) modeDisplay.textContent = tPcs.toLocaleString('id-ID') + ' pcs';
    if(grandBundles) grandBundles.textContent = tBundles.toLocaleString('id-ID');
    if(grandPcs) grandPcs.textContent = tPcs.toLocaleString('id-ID');
}

// ── Custom Alert Modal ───────────────────────────────────────
function showAlert(msg) {
    const overlay = document.createElement('div');
    overlay.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.7);z-index:9999;display:flex;align-items:center;justify-content:center;padding:20px;';
    overlay.addEventListener('click', function(e) { if (e.target === overlay) overlay.remove(); });
    overlay.innerHTML = `
        <div style="background:#1e1e2e;border:2px solid #ef4444;border-radius:12px;padding:24px;max-width:320px;width:100%;text-align:center;position:relative;">
            <div style="font-size:2rem;margin-bottom:8px;">⚠️</div>
            <div style="color:#fff;font-weight:800;font-size:1rem;margin-bottom:8px;">${msg}</div>
            <button onclick="this.closest('[style*=fixed]').remove()" style="margin-top:8px;background:#ef4444;color:#fff;border:none;padding:10px 24px;border-radius:8px;font-weight:800;font-size:0.9rem;cursor:pointer;width:100%;">OK</button>
        </div>`;
    document.body.appendChild(overlay);
}

// ── AI Pipe Counter ──────────────────────────────────────────
let cropper = null;
let activeSide = null;

function openCamera(side, useCapture = true) {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    if (useCapture) {
        input.capture = 'environment';
    }
    input.style.display = 'none';
    document.body.appendChild(input);

    input.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(ev) {
            const base64 = ev.target.result;
            
            activeSide = side;
            document.getElementById('cropImage').src = base64;
            document.getElementById('cropModal').style.display = 'flex';
            
            if (cropper) cropper.destroy();
            
            const imageElement = document.getElementById('cropImage');
            cropper = new Cropper(imageElement, {
                viewMode: 1,
                dragMode: 'crop',
                autoCropArea: 0.9,
                responsive: true,
                restore: false,
                guides: true,
                center: true,
                highlight: false,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false,
            });
        };
        reader.readAsDataURL(file);
        document.body.removeChild(input);
    });

    input.click();
}

// Event Listeners setup (wrapped to ensure DOM is ready)
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('opnameForm');
    if(form) {
        currentPcsPerBundle = parseInt(form.getAttribute('data-pcs-per-bundle')) || 0;
        
        form.addEventListener('submit', function (e) {
            const grandTotalBundles = document.getElementById('grandTotalBundles');
            const grandTotalPcs = document.getElementById('grandTotalPcs');
            const bundles = parseInt(grandTotalBundles ? grandTotalBundles.textContent.replace(/[^0-9]/g, '') : 0) || 0;
            const pcs = parseInt(grandTotalPcs ? grandTotalPcs.textContent.replace(/[^0-9]/g, '') : 0) || 0;

            if (bundles === 0 && pcs === 0) {
                e.preventDefault();
                showAlert('Jumlah bundle atau pcs belum diisi!<br><small>Masukkan jumlah pipa terlebih dahulu.</small>');
                return false;
            }
        });
    }

    // Bind History Modal
    const btnShowHistory = document.getElementById('btnShowHistory');
    if (btnShowHistory) {
        btnShowHistory.addEventListener('click', () => {
            document.getElementById('historyModal').style.display = 'flex';
        });
    }
    const btnCloseHistory = document.getElementById('btnCloseHistory');
    if (btnCloseHistory) {
        btnCloseHistory.addEventListener('click', () => {
            document.getElementById('historyModal').style.display = 'none';
        });
    }

    // Bind Delete Opname
    document.querySelectorAll('.btn-delete-opname').forEach(btn => {
        btn.addEventListener('click', function() {
            deleteOpname(this.getAttribute('data-id'));
        });
    });

    // Bind Quick Select Spec
    document.querySelectorAll('.quick-spec-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            applySpec(
                this.getAttribute('data-category'),
                this.getAttribute('data-size'),
                this.getAttribute('data-type'),
                this.getAttribute('data-class'),
                this.getAttribute('data-bdl'),
                this.getAttribute('data-rows'),
                this.getAttribute('data-adjust'),
                this.getAttribute('data-loose'),
                this
            );
        });
    });

    // Bind Camera AI
    const btnOpenCameraTotal = document.getElementById('btnOpenCameraTotal');
    if (btnOpenCameraTotal) {
        btnOpenCameraTotal.addEventListener('click', () => {
            openCamera('total', true);
        });
    }

    const btnUploadImageTotal = document.getElementById('btnUploadImageTotal');
    if (btnUploadImageTotal) {
        btnUploadImageTotal.addEventListener('click', () => {
            openCamera('total', false);
        });
    }

    document.querySelectorAll('input[type="number"]').forEach(el => el.addEventListener('input', calculate));
    
    const pipeSize = document.getElementById('pipeSize');
    if(pipeSize) pipeSize.addEventListener('change', fetchPipeData);
    
    const pipeType = document.getElementById('pipeType');
    if(pipeType) pipeType.addEventListener('change', fetchPipeData);
    
    document.querySelectorAll('select').forEach(el => el.addEventListener('change', clearQuickSelect));

    const btnCancelCrop = document.getElementById('btnCancelCrop');
    if(btnCancelCrop) {
        btnCancelCrop.addEventListener('click', function() {
            document.getElementById('cropModal').style.display = 'none';
            if (cropper) cropper.destroy();
            cropper = null;
            activeSide = null;
        });
    }

    const btnConfirmCrop = document.getElementById('btnConfirmCrop');
    if(btnConfirmCrop) {
        btnConfirmCrop.addEventListener('click', function() {
            if (!cropper || !activeSide) return;
            
            const btn = document.getElementById('btnConfirmCrop');
            const origText = btn.innerHTML;
            btn.innerHTML = 'Memproses...';
            btn.disabled = true;

            setTimeout(() => {
                const canvas = cropper.getCroppedCanvas({
                    maxWidth: 1024,
                    maxHeight: 1024
                });
                const croppedBase64 = canvas.toDataURL('image/jpeg', 0.7);
                
                document.getElementById('cropModal').style.display = 'none';
                cropper.destroy();
                cropper = null;
                
                const side = activeSide;
                activeSide = null;

                btn.innerHTML = origText;
                btn.disabled = false;

                const previewDiv = document.getElementById(side + 'AiPreview');
                const previewImg = document.getElementById(side + 'AiImg');
                if (previewDiv && previewImg) {
                    previewImg.src = croppedBase64;
                    previewDiv.style.display = 'block';
                }

                const resultDiv = document.getElementById(side + 'AiResult');
                const resultText = document.getElementById(side + 'AiText');
                if(resultDiv) resultDiv.style.display = 'block';
                if(resultText) resultText.innerHTML = '⏳ Menghitung pipa... (AI sedang menganalisis foto)';

                countPipesWithAI(croppedBase64, side);
            }, 50);
        });
    }

    // Initial load
    fetchPipeData();

    // Enforce strictly numeric input
    document.querySelectorAll('.row-calc-input, .loose-input').forEach(input => {
        input.setAttribute('inputmode', 'numeric');
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });
});

async function countPipesWithAI(base64Image, side) {
    const resultDiv = document.getElementById(side + 'AiResult');
    const resultText = document.getElementById(side + 'AiText');
    const boxesContainer = document.getElementById(side + 'AiBoxes');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    if (boxesContainer) boxesContainer.innerHTML = '';

    try {
        const resp = await fetch('/api/count-pipes', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ image: base64Image }),
        });

        const data = await resp.json();

        if (data.success) {
            let inputField;
            if (side === 'left') inputField = document.getElementById('leftLoose');
            else if (side === 'right') inputField = document.getElementById('rightLoose');
            else inputField = document.getElementById('totalModeLoose');

            if(inputField) inputField.value = data.count;

            const confColors = { high: '#22c55e', medium: '#f59e0b', low: '#ef4444' };
            const confLabels = { high: 'Tinggi', medium: 'Sedang', low: 'Rendah' };
            const confColor = confColors[data.confidence] || '#f59e0b';
            const confLabel = confLabels[data.confidence] || data.confidence;

            if(resultText) {
                resultText.innerHTML = `🤖 AI: <strong>${data.count} pcs</strong> ` +
                    `<span style="background:${confColor}; color:#000; padding:1px 6px; border-radius:4px; font-size:0.65rem; font-weight:800;">Akurasi ${confLabel}</span>` +
                    (data.notes ? `<br><span style="color:var(--text-tertiary); font-size:0.65rem;">${data.notes}</span>` : '');
            }

            if (data.boxes && Array.isArray(data.boxes) && boxesContainer) {
                data.boxes.forEach(box => {
                    if (box.length === 4) {
                        const ymin = box[0] / 10;
                        const xmin = box[1] / 10;
                        const ymax = box[2] / 10;
                        const xmax = box[3] / 10;
                        
                        const boxEl = document.createElement('div');
                        boxEl.style.position = 'absolute';
                        boxEl.style.top = ymin + '%';
                        boxEl.style.left = xmin + '%';
                        boxEl.style.height = (ymax - ymin) + '%';
                        boxEl.style.width = (xmax - xmin) + '%';
                        boxEl.style.border = '2px solid #22c55e';
                        boxEl.style.borderRadius = '50%'; 
                        boxEl.style.backgroundColor = 'rgba(34, 197, 94, 0.2)';
                        boxEl.style.boxShadow = '0 0 4px rgba(0,0,0,0.5)';
                        boxesContainer.appendChild(boxEl);
                    }
                });
            }

            calculate();
        } else {
            if(resultText) {
                const errMsg = data.error || data.message || 'Gagal menganalisis foto (Mungkin ukuran foto terlalu besar/server error).';
                resultText.innerHTML = `❌ ${errMsg}`;
                resultText.style.color = '#ef4444';
            }
        }
    } catch (err) {
        if(resultText) {
            resultText.innerHTML = `❌ Error: ${err.message}`;
            resultText.style.color = '#ef4444';
        }
    }
}
