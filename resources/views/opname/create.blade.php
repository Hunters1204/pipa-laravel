@extends('layouts.app')

@section('title', 'Blok ' . $block->code)

@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
    <style>
        #cropModal {
            position: fixed; inset: 0; background: rgba(0,0,0,0.95); z-index: 10000;
            display: none; flex-direction: column; padding: 20px;
        }
        #cropContainer {
            flex: 1; display: flex; align-items: center; justify-content: center;
            overflow: hidden; margin-bottom: 20px;
            background: #000; border-radius: 12px;
        }
        #cropImage { max-width: 100%; max-height: 100%; }
        .crop-actions { display: flex; gap: 12px; padding-bottom: max(20px, env(safe-area-inset-bottom)); }
    </style>
@endpush

@section('content')
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-md);">
        <a href="{{ route('warehouse.show', $warehouse->id) }}"
            style="color: var(--text-secondary); text-decoration: none; font-size: 0.85rem; font-weight: 600;">
            ← Peta Blok
        </a>
        <div style="font-weight: 800; font-size: 1rem; color: var(--accent-primary);">
            Blok {{ $block->code }} <span
                style="font-size: 0.68rem; color: var(--text-tertiary); font-family: var(--font-mono);">({{ $block->sloc_code }})</span>
        </div>
    </div>

    {{-- ╔══════════════════════════════════════════════════════╗ --}}
    {{-- ║ INPUT HARI INI ║ --}}
    {{-- ╚══════════════════════════════════════════════════════╝ --}}
    @if($todayOpnames->count() > 0)
        <div class="card"
            style="border:1px solid rgba(34,197,94,0.4); background:rgba(34,197,94,0.05); margin-bottom:var(--space-md);">
            <div
                style="font-size:0.72rem; font-weight:800; color:var(--success); text-transform:uppercase; margin-bottom:var(--space-sm);">
                📦 Input Hari Ini ({{ date('d/m/Y') }}) — {{ $todayOpnames->count() }} Jenis
            </div>

            <div style="display:flex; flex-direction:column; gap:8px;">
                @foreach($todayOpnames as $item)
                    <div
                        style="background:var(--bg-primary); border:1px solid var(--border-subtle); border-radius:var(--radius-md); padding:var(--space-sm) var(--space-md);">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:6px;">
                            <div>
                                <span style="font-weight:800; font-size:0.9rem;">
                                    {{ optional($item->pipeSize)->size_label }}
                                    <span style="color:var(--accent-primary);">{{ optional($item->pipeType)->code }}</span>
                                    @if($item->pipeClass)<span style="color:var(--text-tertiary); font-size:0.75rem;">/
                                    {{ $item->pipeClass->name }}</span>@endif
                                </span>
                                <div style="font-size:0.68rem; color:var(--text-tertiary); margin-top:1px;">
                                    {{ optional($item->pipeCategory)->name }} · 👷 {{ $item->petugas_name }}</div>
                            </div>
                            <div style="display:flex; gap:6px; align-items:center;">
                                <a href="{{ route('opname.edit', $item->id) }}" style="color:var(--accent-primary); text-decoration:none; font-size:0.9rem; padding:4px 8px; border:1px solid var(--accent-primary); border-radius:var(--radius-sm); background:rgba(245,158,11,0.1); display:flex; align-items:center;" title="Edit">✏️</a>
                                <button type="button" onclick="deleteOpname({{ $item->id }})" style="color:#ef4444; border:1px solid rgba(239,68,68,0.4); padding:4px 8px; border-radius:var(--radius-sm); background:rgba(239,68,68,0.1); font-size:0.9rem; cursor:pointer; display:flex; align-items:center;" title="Hapus">🗑️</button>
                            </div>
                        </div>
                        {{-- Hasil: Bundle · Pcs · Tgl --}}
                        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:4px; text-align:center;">
                            <div style="background:rgba(245,158,11,0.1); padding:5px; border-radius:6px;">
                                <div
                                    style="font-size:1.05rem; font-weight:800; font-family:var(--font-mono); color:var(--accent-primary);">
                                    {{ number_format($item->total_bundles) }}</div>
                                <div style="font-size:0.58rem; color:var(--text-tertiary); font-weight:700;">BUNDLE</div>
                            </div>
                            <div style="background:rgba(255,255,255,0.05); padding:5px; border-radius:6px;">
                                <div style="font-size:1.05rem; font-weight:800; font-family:var(--font-mono); color:#fff;">
                                    {{ number_format($item->total_pcs) }}</div>
                                <div style="font-size:0.58rem; color:var(--text-tertiary); font-weight:700;">PCS</div>
                            </div>
                            <div style="background:rgba(255,255,255,0.03); padding:5px; border-radius:6px;">
                                <div
                                    style="font-size:0.72rem; font-weight:700; font-family:var(--font-mono); color:var(--text-secondary); padding-top:3px;">
                                    {{ $item->created_at ? $item->created_at->format('d/m/Y H:i:s') : '-' }}</div>
                                <div style="font-size:0.58rem; color:var(--text-tertiary); font-weight:700;">WAKTU INPUT</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Total blok hari ini --}}
            <div
                style="margin-top:var(--space-md); padding-top:var(--space-sm); border-top:1px dashed var(--border-subtle); display:grid; grid-template-columns:1fr 1fr; text-align:center; gap:8px;">
                <div>
                    <div style="font-size:1.2rem; font-weight:800; font-family:var(--font-mono); color:var(--accent-primary);">
                        {{ $todayOpnames->sum('total_bundles') }}</div>
                    <div style="font-size:0.6rem; color:var(--text-tertiary);">TOTAL BUNDLE HARI INI</div>
                </div>
                <div>
                    <div style="font-size:1.2rem; font-weight:800; font-family:var(--font-mono); color:#fff;">
                        {{ number_format($todayOpnames->sum('total_pcs')) }}</div>
                    <div style="font-size:0.6rem; color:var(--text-tertiary);">TOTAL PCS HARI INI</div>
                </div>
            </div>
        </div>
    @endif

    {{-- ╔══════════════════════════════════════════════════════╗ --}}
    {{-- ║ RIWAYAT HARI SEBELUMNYA ║ --}}
    {{-- ╚══════════════════════════════════════════════════════╝ --}}
    @if($historyOpnames->count() > 0)
        <div class="card"
            style="border:1px solid rgba(255,255,255,0.06); background:rgba(0,0,0,0.2); margin-bottom:var(--space-md);">
            <div
                style="font-size:0.72rem; font-weight:800; color:var(--text-tertiary); text-transform:uppercase; margin-bottom:var(--space-sm);">
                🗂️ Riwayat Inputan Sebelumnya
            </div>
            @foreach($historyOpnames as $date => $items)
                <div style="margin-bottom:10px;">
                    <div
                        style="font-size:0.68rem; font-weight:800; color:var(--text-secondary); background:var(--bg-primary); padding:3px 8px; border-radius:4px; margin-bottom:4px; display:inline-block;">
                        📅 {{ $date ? date('d M Y', strtotime($date)) : 'N/A' }}
                    </div>
                    @foreach($items as $item)
                        <div
                            style="background:rgba(255,255,255,0.03); border:1px solid var(--border-subtle); padding:5px var(--space-md); border-radius:var(--radius-sm); margin-bottom:3px; display:flex; justify-content:space-between; align-items:center; font-size:0.76rem;">
                            <span style="color:var(--text-secondary);">
                                {{ optional($item->pipeSize)->size_label }}
                                <strong style="color:var(--accent-primary);">{{ optional($item->pipeType)->code }}</strong>
                                @if($item->pipeClass)/ {{ $item->pipeClass->name }}@endif
                                <span style="color:var(--text-tertiary);">({{ optional($item->pipeCategory)->name }})</span>
                            </span>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <span
                                    style="font-family:var(--font-mono); color:var(--text-secondary); font-weight:700;">{{ $item->total_bundles }}
                                    bdl / {{ number_format($item->total_pcs) }} pcs</span>
                                <a href="{{ route('opname.edit', $item->id) }}" style="color:var(--accent-primary); text-decoration:none; font-size:0.8rem; padding:3px 6px; border:1px solid var(--accent-primary); border-radius:var(--radius-sm); background:rgba(245,158,11,0.1);" title="Edit">✏️</a>
                                <button type="button" onclick="deleteOpname({{ $item->id }})" style="color:#ef4444; border:1px solid rgba(239,68,68,0.4); padding:3px 6px; border-radius:var(--radius-sm); background:rgba(239,68,68,0.1); font-size:0.8rem; cursor:pointer;" title="Hapus">🗑️</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    @endif

    {{-- ╔══════════════════════════════════════════════════════╗ --}}
    {{-- ║ FORM INPUT ║ --}}
    {{-- ╚══════════════════════════════════════════════════════╝ --}}
    <form action="{{ isset($editOpname) ? route('opname.update', $editOpname->id) : route('opname.store') }}" method="POST" id="opnameForm">
        @csrf
        @if(isset($editOpname))
            @method('PUT')
        @endif
        <input type="hidden" name="block_id" value="{{ $block->id }}">
        <input type="hidden" name="input_mode" id="inputModeField" value="total">

        {{-- ── Spesifikasi Pipa ─────────────────────────────────── --}}
        <div class="card" id="specCard" style="transition: background 0.3s;">
            <div
                style="font-size:0.72rem; font-weight:800; color:var(--accent-primary); text-transform:uppercase; margin-bottom:var(--space-sm);">
                ➕ Spesifikasi Pipa
            </div>

            {{-- ── Quick Select Spesifikasi ─────────────────────────────────── --}}
            @if(isset($existingSpecs) && $existingSpecs->count() > 0 && !isset($editOpname))
            <div style="margin-bottom:var(--space-md); padding-bottom:10px; border-bottom:1px dashed var(--border-subtle);">
                <div style="font-size:0.65rem; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; margin-bottom:8px;">
                    ⚡ Pilih Cepat (Tersedia di blok ini):
                </div>
                <div style="display:flex; gap:8px; overflow-x:auto; padding-bottom:4px; -webkit-overflow-scrolling:touch;">
                    @foreach($existingSpecs as $spec)
                        <button type="button" 
                                onclick="applySpec({{ $spec->pipe_category_id }}, {{ $spec->pipe_size_id }}, {{ $spec->pipe_type_id }}, {{ $spec->pipe_class_id ?: 'null' }})"
                                style="flex-shrink:0; background:rgba(245,158,11,0.15); border:1px solid rgba(245,158,11,0.4); color:var(--accent-primary); padding:6px 14px; border-radius:20px; font-size:0.75rem; font-weight:800; cursor:pointer; transition:all 0.2s;">
                            {{ optional($spec->pipeSize)->size_label }} {{ optional($spec->pipeType)->code }} @if($spec->pipeClass) / {{ $spec->pipeClass->name }} @endif
                        </button>
                    @endforeach
                </div>
            </div>
            @endif

            <div style="font-size:0.65rem; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; margin-bottom:8px;">
                Atau Input Manual:
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-sm); margin-bottom:var(--space-sm);">
                <div>
                    <label
                        style="font-size:0.65rem; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; display:block; margin-bottom:3px;">Ukuran
                        Pipa</label>
                    <select name="pipe_size_id" id="pipeSize"
                        style="width:100%; padding:9px var(--space-md); background:var(--bg-input); border:1px solid var(--border-medium); border-radius:var(--radius-md); color:#fff; font-weight:700; font-size:0.88rem;">
                        @foreach($sizes as $sz)
                            <option value="{{ $sz->id }}">{{ $sz->size_label }} ({{ $sz->pcs_per_bundle }}/bdl)</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label
                        style="font-size:0.65rem; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; display:block; margin-bottom:3px;">Grade</label>
                    <select name="pipe_type_id" id="pipeType"
                        style="width:100%; padding:9px var(--space-md); background:var(--bg-input); border:1px solid var(--border-medium); border-radius:var(--radius-md); color:#fff; font-weight:700; font-size:0.88rem;">
                        @foreach($types as $tp)
                            <option value="{{ $tp->id }}">{{ $tp->code }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-sm);">
                <div>
                    <label
                        style="font-size:0.65rem; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; display:block; margin-bottom:3px;">Class</label>
                    <select name="pipe_class_id" id="pipeClass"
                        style="width:100%; padding:9px var(--space-md); background:var(--bg-input); border:1px solid var(--border-medium); border-radius:var(--radius-md); color:#fff; font-weight:700; font-size:0.88rem;">
                        <option value="">— Pilih Class —</option>
                        @foreach($classes as $cl)
                            <option value="{{ $cl->id }}">{{ $cl->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label
                        style="font-size:0.65rem; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; display:block; margin-bottom:3px;">Kategori</label>
                    <select name="pipe_category_id" id="pipeCategory"
                        style="width:100%; padding:9px var(--space-md); background:var(--bg-input); border:1px solid var(--border-medium); border-radius:var(--radius-md); color:#fff; font-weight:700; font-size:0.88rem;">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- ── MODE TOTAL LANGSUNG ───────────────────────────────── --}}
        <div id="modeTotalSection">
            <div class="card" style="border:1px solid rgba(99,102,241,0.3); background:rgba(99,102,241,0.05);">
                <div
                    style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--space-sm);">
                    <span style="font-weight:800; font-size:0.85rem; color:#a5b4fc;">⬛ TOTAL (Kanan + Kiri)</span>
                    <span id="totalModeDisplay"
                        style="font-family:var(--font-mono); font-size:0.78rem; font-weight:700; color:var(--text-secondary);">0
                        pcs</span>
                </div>
                <div class="row-calc">
                    <div class="row-calc-row">
                        <div class="row-calc-field">
                            <label>Bdl/Baris</label>
                            <input type="number" name="total_bdl_per_row" class="row-calc-input" id="totalBdlPerRow" placeholder="0" min="0"
                                style="border-color:rgba(99,102,241,0.4);" value="{{ isset($editOpname) ? $editOpname->left_bdl_per_row : '' }}">
                        </div>
                        <span class="row-calc-op">×</span>
                        <div class="row-calc-field">
                            <label>Baris Atas</label>
                            <input type="number" name="total_rows" class="row-calc-input" id="totalRows" placeholder="0" min="0"
                                style="border-color:rgba(99,102,241,0.4);" value="{{ isset($editOpname) ? $editOpname->left_rows : '' }}">
                        </div>
                        <span class="row-calc-op">=</span>
                        <div class="row-calc-field">
                            <label>Bundle</label>
                            <div class="row-calc-total" id="totalAutoBundle"
                                style="border-color:rgba(99,102,241,0.4); color:#a5b4fc;">0</div>
                        </div>
                    </div>
                </div>
                <div style="margin-top:var(--space-md); display:flex; align-items:center; justify-content:space-between;">
                    <label style="font-size:0.73rem; font-weight:600; color:var(--text-secondary);">📦 Tambahan Bundle:</label>
                    <input type="number" name="total_adjust" class="loose-input" id="totalModeAdjust" placeholder="0" style="border-color:rgba(99,102,241,0.4);" value="{{ isset($editOpname) ? $editOpname->left_adjust : '' }}">
                </div>
                <div style="margin-top:8px;">
                    <div style="display:flex; align-items:center; justify-content:space-between;">
                        <label style="font-size:0.73rem; font-weight:600; color:var(--text-secondary);">🔩 Pcs Lepas:</label>
                        <div style="display:flex; align-items:center; gap:6px;">
                            <input type="number" name="total_loose" class="loose-input" id="totalModeLoose" placeholder="0" min="0" style="border-color:rgba(99,102,241,0.4);" value="{{ isset($editOpname) ? $editOpname->left_loose : '' }}">
                            <button type="button" onclick="openCamera('total')" style="background:linear-gradient(135deg,#6366f1,#8b5cf6); color:#fff; border:none; padding:8px 10px; border-radius:var(--radius-md); font-size:0.8rem; cursor:pointer; white-space:nowrap; font-weight:700;">📷 AI</button>
                        </div>
                    </div>
                    <div id="totalAiResult" style="display:none; margin-top:6px; padding:6px 10px; border-radius:8px; background:rgba(99,102,241,0.15); border:1px solid rgba(99,102,241,0.3); font-size:0.72rem;">
                        <span id="totalAiText" style="color:#a5b4fc; font-weight:700;"></span>
                    </div>
                    <div id="totalAiPreview" style="display:none; margin-top:6px; position:relative; width:100%;">
                        <img id="totalAiImg" style="width:100%; height:auto; display:block; border-radius:8px; border:1px solid var(--border-subtle);">
                        <div id="totalAiBoxes" style="position:absolute; top:0; left:0; width:100%; height:100%; pointer-events:none;"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── HASIL AKHIR ──────────────────────────────────────── --}}
        <div class="card" style="border:1px solid var(--border-accent); background:rgba(245,158,11,0.05);">
            <div
                style="font-size:0.72rem; font-weight:800; color:var(--accent-primary); text-transform:uppercase; text-align:center; margin-bottom:var(--space-sm);">
                📊 HASIL ITEM INI
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:var(--space-xs); text-align:center;">
                <div
                    style="background:rgba(245,158,11,0.1); padding:var(--space-sm); border-radius:var(--radius-md); border:1px solid rgba(245,158,11,0.2);">
                    <div id="grandTotalBundles"
                        style="font-size:1.5rem; font-weight:800; font-family:var(--font-mono); color:var(--accent-primary);">
                        0</div>
                    <div style="font-size:0.6rem; color:var(--text-tertiary); font-weight:700;">BUNDLE</div>
                </div>
                <div
                    style="background:rgba(255,255,255,0.05); padding:var(--space-sm); border-radius:var(--radius-md); border:1px solid var(--border-subtle);">
                    <div id="grandTotalPcs"
                        style="font-size:1.5rem; font-weight:800; font-family:var(--font-mono); color:#fff;">0</div>
                    <div style="font-size:0.6rem; color:var(--text-tertiary); font-weight:700;">PCS</div>
                </div>
                <div
                    style="background:rgba(255,255,255,0.03); padding:var(--space-sm); border-radius:var(--radius-md); border:1px solid var(--border-subtle);">
                    <div
                        style="font-size:0.78rem; font-weight:700; font-family:var(--font-mono); color:var(--text-secondary); padding-top:6px;">
                        {{ date('d/m/Y H:i:s') }}</div>
                    <div style="font-size:0.6rem; color:var(--text-tertiary); font-weight:700;">WAKTU INPUT</div>
                </div>
            </div>

        </div>

        {{-- ── TOMBOL AKSI ──────────────────────────────────────── --}}
        <div style="display:flex; flex-direction:column; gap:8px; margin-bottom:var(--space-xl);">
            <button type="submit" name="action" value="add_more" class="btn"
                style="background:var(--bg-tertiary); color:var(--accent-primary); border:1px solid var(--border-accent);">
                ➕ Simpan & Tambah Pipa Lain
            </button>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                <button type="submit" name="action" value="save" class="btn btn-success">
                    💾 Simpan & Selesai
                </button>
                <button type="submit" name="action" value="next" class="btn btn-primary">
                    Lanjut Blok ➡️
                </button>
            </div>
        </div>
    </form>

    {{-- ── CROP MODAL ────────────────────────────────────────── --}}
    <div id="cropModal">
        <div style="color:#fff; font-weight:800; font-size:1.1rem; margin-bottom:4px; text-align:center;">Potong Area Pipa</div>
        <div style="color:#9ca3af; font-size:0.75rem; text-align:center; margin-bottom:12px;">Crop hanya pada bagian pipa yang lepas / tidak di-bundle.</div>
        <div id="cropContainer">
            <img id="cropImage" src="">
        </div>
        <div class="crop-actions">
            <button type="button" id="btnCancelCrop" class="btn" style="flex:1; background:#374151; color:#fff;">❌ Batal</button>
            <button type="button" id="btnConfirmCrop" class="btn btn-primary" style="flex:2;">✂️ Selesai & Hitung</button>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
        <script>
            let currentPcsPerBundle = {{ optional($sizes->first())->pcs_per_bundle ?? 0 }};
            // (Kanan-Kiri Mode removed)


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
                        <div style="position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:9998;display:flex;align-items:center;justify-content:center;">
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
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch('/opname/' + id, {
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
                    showAlert('Gagal menghapus: ' + err.message);
                });
            }


            // ── Quick Select Spec ─────────────────────────────────────────
            function applySpec(categoryId, sizeId, typeId, classId) {
                document.getElementById('pipeCategory').value = categoryId;
                document.getElementById('pipeSize').value = sizeId;
                document.getElementById('pipeType').value = typeId;
                document.getElementById('pipeClass').value = classId || '';
                
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
                
                document.getElementById('totalAutoBundle').textContent = tAutoBdl;
                document.getElementById('totalModeDisplay').textContent = tPcs.toLocaleString('id-ID') + ' pcs';

                document.getElementById('grandTotalBundles').textContent = tBundles.toLocaleString('id-ID');
                document.getElementById('grandTotalPcs').textContent = tPcs.toLocaleString('id-ID');
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

            // ── Form Validation Alert ────────────────────────────────────
            document.getElementById('opnameForm').addEventListener('submit', function (e) {
                const bundles = parseInt(document.getElementById('grandTotalBundles').textContent.replace(/[^0-9]/g, '')) || 0;
                const pcs = parseInt(document.getElementById('grandTotalPcs').textContent.replace(/[^0-9]/g, '')) || 0;

                if (bundles === 0 && pcs === 0) {
                    e.preventDefault();
                    showAlert('Jumlah bundle atau pcs belum diisi!<br><small>Masukkan jumlah pipa terlebih dahulu.</small>');
                    return false;
                }

                // Form valid
            });

            // ── Event Listeners ──────────────────────────────────────────
            document.querySelectorAll('input[type="number"]').forEach(el => el.addEventListener('input', calculate));
            document.getElementById('pipeSize').addEventListener('change', fetchPipeData);
            document.getElementById('pipeType').addEventListener('change', fetchPipeData);

            // Initial load
            fetchPipeData();

            // ── AI Pipe Counter ──────────────────────────────────────────
            let cropper = null;
            let activeSide = null;

            function openCamera(side) {
                const input = document.createElement('input');
                input.type = 'file';
                input.accept = 'image/*';
                input.capture = 'environment';
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

            document.getElementById('btnCancelCrop').addEventListener('click', function() {
                document.getElementById('cropModal').style.display = 'none';
                if (cropper) cropper.destroy();
                cropper = null;
                activeSide = null;
            });

            document.getElementById('btnConfirmCrop').addEventListener('click', function() {
                if (!cropper || !activeSide) return;
                
                // Set button to loading state
                const btn = document.getElementById('btnConfirmCrop');
                const origText = btn.innerHTML;
                btn.innerHTML = 'Memproses...';
                btn.disabled = true;

                // Use setTimeout to allow UI to update before heavy canvas operation
                setTimeout(() => {
                    const canvas = cropper.getCroppedCanvas();
                    const croppedBase64 = canvas.toDataURL('image/jpeg', 0.8);
                    
                    document.getElementById('cropModal').style.display = 'none';
                    cropper.destroy();
                    cropper = null;
                    
                    const side = activeSide;
                    activeSide = null;

                    // Reset button
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
                    resultDiv.style.display = 'block';
                    resultText.innerHTML = '⏳ Menghitung pipa... (AI sedang menganalisis foto)';

                    countPipesWithAI(croppedBase64, side);
                }, 50);
            });

            async function countPipesWithAI(base64Image, side) {
                const resultDiv = document.getElementById(side + 'AiResult');
                const resultText = document.getElementById(side + 'AiText');
                const boxesContainer = document.getElementById(side + 'AiBoxes');
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                // Clear old boxes
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
                        // Determine the correct input field
                        let inputField;
                        if (side === 'left') inputField = document.getElementById('leftLoose');
                        else if (side === 'right') inputField = document.getElementById('rightLoose');
                        else inputField = document.getElementById('totalModeLoose');

                        // Auto-fill the count
                        inputField.value = data.count;

                        // Confidence badge colors
                        const confColors = {
                            high: '#22c55e',
                            medium: '#f59e0b',
                            low: '#ef4444'
                        };
                        const confLabels = {
                            high: 'Tinggi',
                            medium: 'Sedang',
                            low: 'Rendah'
                        };
                        const confColor = confColors[data.confidence] || '#f59e0b';
                        const confLabel = confLabels[data.confidence] || data.confidence;

                        resultText.innerHTML = `🤖 AI: <strong>${data.count} pcs</strong> ` +
                            `<span style="background:${confColor}; color:#000; padding:1px 6px; border-radius:4px; font-size:0.65rem; font-weight:800;">Akurasi ${confLabel}</span>` +
                            (data.notes ? `<br><span style="color:var(--text-tertiary); font-size:0.65rem;">${data.notes}</span>` : '');

                        // Draw bounding boxes
                        if (data.boxes && Array.isArray(data.boxes)) {
                            data.boxes.forEach(box => {
                                // box format: [ymin, xmin, ymax, xmax] scaled 0-1000
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
                                    boxEl.style.borderRadius = '50%'; // Make it a circle/ellipse for pipes!
                                    boxEl.style.backgroundColor = 'rgba(34, 197, 94, 0.2)';
                                    boxEl.style.boxShadow = '0 0 4px rgba(0,0,0,0.5)';
                                    boxesContainer.appendChild(boxEl);
                                }
                            });
                        }

                        // Trigger recalculation
                        calculate();
                    } else {
                        resultText.innerHTML = `❌ ${data.error || 'Gagal menganalisis foto.'}`;
                        resultText.style.color = '#ef4444';
                    }
                } catch (err) {
                    resultText.innerHTML = `❌ Error: ${err.message}`;
                    resultText.style.color = '#ef4444';
                }
            }
        </script>
    @endpush
@endsection