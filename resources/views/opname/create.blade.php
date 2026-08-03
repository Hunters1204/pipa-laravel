@extends('layouts.app')

@section('title', 'Blok ' . $block->code)

@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/opname.css') }}">
@endpush

@section('content')
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-md);">
        <a href="{{ route('warehouse.show', $warehouse->id) }}"
            style="color: var(--text-secondary); text-decoration: none; font-size: 0.85rem; font-weight: 600; flex: 1;">
            ← Peta Blok
        </a>
        <div style="font-weight: 800; font-size: 1rem; color: var(--accent-primary); text-align: center; flex: 1; white-space: nowrap;">
            Blok {{ $block->code }} <span
                style="font-size: 0.68rem; color: var(--text-tertiary); font-family: var(--font-mono);">({{ $block->sloc_code }})</span>
        </div>
        <div style="flex: 1; text-align: right;">
            @if($historyOpnames->count() > 0)
                <button type="button" id="btnShowHistory" style="background: rgba(255,255,255,0.1); border: 1px solid var(--border-subtle); color: var(--text-secondary); padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; cursor: pointer; transition: background 0.2s;">
                    🕒 Histori
                </button>
            @endif
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
                                <button type="button" class="btn-delete-opname" data-id="{{ $item->id }}" style="color:#ef4444; border:1px solid rgba(239,68,68,0.4); padding:4px 8px; border-radius:var(--radius-sm); background:rgba(239,68,68,0.1); font-size:0.9rem; cursor:pointer; display:flex; align-items:center;" title="Hapus">🗑️</button>
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
    {{-- ║ MODAL RIWAYAT HARI SEBELUMNYA ║ --}}
    {{-- ╚══════════════════════════════════════════════════════╝ --}}
    @if($historyOpnames->count() > 0)
    <div id="historyModal" style="position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 9999; display: none; align-items: center; justify-content: center; padding: 20px; backdrop-filter: blur(4px);">
        <div style="background: #1e1e2e; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 24px; max-width: 500px; width: 100%; box-shadow: 0 10px 40px rgba(0,0,0,0.5); max-height: 80vh; display: flex; flex-direction: column;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="font-weight: 800; color: var(--text-primary); margin: 0; font-size: 1.1rem;">🗂️ Riwayat Inputan Sebelumnya</h3>
                <button type="button" id="btnCloseHistory" style="background: none; border: none; color: var(--text-secondary); font-size: 1.5rem; cursor: pointer; line-height: 1;">&times;</button>
            </div>
            
            <div style="overflow-y: auto; padding-right: 8px;">
                @foreach($historyOpnames as $date => $items)
                    <div style="margin-bottom:16px;">
                        <div
                            style="font-size:0.75rem; font-weight:800; color:var(--text-secondary); background:rgba(255,255,255,0.05); padding:4px 10px; border-radius:6px; margin-bottom:8px; display:inline-block; border: 1px solid rgba(255,255,255,0.05);">
                            📅 {{ $date ? date('d M Y', strtotime($date)) : 'N/A' }}
                        </div>
                        @foreach($items as $item)
                            <div
                                style="background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.05); padding:10px var(--space-md); border-radius:8px; margin-bottom:8px; display:flex; justify-content:space-between; align-items:center; font-size:0.85rem;">
                                <span style="color:var(--text-secondary);">
                                    {{ optional($item->pipeSize)->size_label }}
                                    <strong style="color:var(--accent-primary);">{{ optional($item->pipeType)->code }}</strong>
                                    @if($item->pipeClass)/ {{ $item->pipeClass->name }}@endif
                                    <div style="color:var(--text-tertiary); font-size: 0.7rem; margin-top: 2px;">{{ optional($item->pipeCategory)->name }} · 👷 {{ $item->petugas_name }}</div>
                                </span>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <span style="font-family:var(--font-mono); color:#fff; font-weight:800;">
                                        {{ $item->total_bundles }} <span style="font-size:0.65rem; color:var(--text-tertiary); font-weight:400;">bdl</span> / 
                                        {{ number_format($item->total_pcs) }} <span style="font-size:0.65rem; color:var(--text-tertiary); font-weight:400;">pcs</span>
                                    </span>
                                    <button type="button" class="btn-delete-opname" data-id="{{ $item->id }}"
                                        style="background:rgba(239, 68, 68, 0.1); border:1px solid rgba(239, 68, 68, 0.3); color:#ef4444; border-radius:6px; padding:4px 8px; cursor:pointer; font-size:0.8rem;"
                                        title="Hapus Data">
                                        🗑️
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- ╔══════════════════════════════════════════════════════╗ --}}
    {{-- ║ FORM INPUT ║ --}}
    {{-- ╚══════════════════════════════════════════════════════╝ --}}
    <form action="{{ isset($editOpname) ? route('opname.update', $editOpname->id) : route('opname.store') }}" method="POST" id="opnameForm" data-pcs-per-bundle="{{ optional($sizes->first())->pcs_per_bundle ?? 0 }}">
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
                                class="quick-spec-btn"
                                data-category="{{ $spec->pipe_category_id }}"
                                data-size="{{ $spec->pipe_size_id }}"
                                data-type="{{ $spec->pipe_type_id }}"
                                data-class="{{ $spec->pipe_class_id ?: 'null' }}"
                                data-bdl="{{ $spec->left_bdl_per_row ?? 0 }}"
                                data-rows="{{ $spec->left_rows ?? 0 }}"
                                data-adjust="{{ $spec->left_adjust ?? 0 }}"
                                data-loose="{{ $spec->total_loose ?? 0 }}"
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
                            <option value="{{ $sz->id }}" {{ (isset($editOpname) && $editOpname->pipe_size_id == $sz->id) ? 'selected' : '' }}>
                                {{ $sz->size_label }} ({{ $sz->pcs_per_bundle }}/bdl)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label
                        style="font-size:0.65rem; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; display:block; margin-bottom:3px;">Grade</label>
                    <select name="pipe_type_id" id="pipeType"
                        style="width:100%; padding:9px var(--space-md); background:var(--bg-input); border:1px solid var(--border-medium); border-radius:var(--radius-md); color:#fff; font-weight:700; font-size:0.88rem;">
                        @foreach($types as $tp)
                            <option value="{{ $tp->id }}" {{ (isset($editOpname) && $editOpname->pipe_type_id == $tp->id) ? 'selected' : '' }}>
                                {{ $tp->code }}
                            </option>
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
                            <option value="{{ $cl->id }}" {{ (isset($editOpname) && $editOpname->pipe_class_id == $cl->id) ? 'selected' : '' }}>
                                {{ $cl->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label
                        style="font-size:0.65rem; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; display:block; margin-bottom:3px;">Kategori</label>
                    <select name="pipe_category_id" id="pipeCategory"
                        style="width:100%; padding:9px var(--space-md); background:var(--bg-input); border:1px solid var(--border-medium); border-radius:var(--radius-md); color:#fff; font-weight:700; font-size:0.88rem;">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ (isset($editOpname) && $editOpname->pipe_category_id == $cat->id) ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
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
                            <button type="button" id="btnOpenCameraTotal" style="background:linear-gradient(135deg,#6366f1,#8b5cf6); color:#fff; border:none; padding:8px 10px; border-radius:var(--radius-md); font-size:0.8rem; cursor:pointer; white-space:nowrap; font-weight:700;">📷 Foto</button>
                            <button type="button" id="btnUploadImageTotal" style="background:linear-gradient(135deg,#f59e0b,#d97706); color:#fff; border:none; padding:8px 10px; border-radius:var(--radius-md); font-size:0.8rem; cursor:pointer; white-space:nowrap; font-weight:700;">📁 Upload</button>
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
        <script src="{{ asset('js/opname.js') }}"></script>
    @endpush
@endsection