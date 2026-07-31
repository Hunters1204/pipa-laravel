@extends('layouts.app')

@section('title', 'Spesifikasi Pipa')

@push('styles')
<style>
    .master-tabs {
        display: flex;
        overflow-x: auto;
        gap: 4px;
        padding-bottom: 2px;
        margin-bottom: var(--space-lg);
        scrollbar-width: none;
    }
    .master-tabs::-webkit-scrollbar { display: none; }

    .tab-btn {
        flex-shrink: 0;
        padding: 7px 14px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        border: 1px solid var(--border-medium);
        background: var(--bg-primary);
        color: var(--text-tertiary);
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
    }
    .tab-btn.active {
        background: var(--accent-primary);
        color: #000;
        border-color: var(--accent-primary);
    }

    .tab-panel { display: none; }
    .tab-panel.active { display: block; }

    .master-list {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-bottom: var(--space-lg);
    }

    .master-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--bg-primary);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-md);
        padding: var(--space-sm) var(--space-md);
    }

    .master-item-label {
        font-weight: 700;
        font-size: 0.9rem;
        color: var(--text-primary);
    }
    .master-item-sub {
        font-size: 0.7rem;
        color: var(--text-tertiary);
        margin-top: 1px;
    }

    .btn-del {
        background: var(--danger-soft);
        color: var(--danger);
        border: 1px solid rgba(239, 68, 68, 0.3);
        padding: 5px 10px;
        border-radius: var(--radius-sm);
        font-size: 0.75rem;
        cursor: pointer;
        flex-shrink: 0;
    }

    .add-form {
        background: rgba(245, 158, 11, 0.05);
        border: 1px dashed var(--border-accent);
        border-radius: var(--radius-md);
        padding: var(--space-md);
    }

    .add-form-title {
        font-size: 0.72rem;
        font-weight: 800;
        color: var(--accent-primary);
        text-transform: uppercase;
        margin-bottom: var(--space-sm);
    }

    .field-group {
        display: flex;
        gap: var(--space-sm);
        margin-bottom: var(--space-sm);
    }

    .field-group input, .field-group select {
        flex: 1;
        padding: var(--space-sm) var(--space-md);
        background: var(--bg-input);
        border: 1px solid var(--border-medium);
        border-radius: var(--radius-md);
        color: #fff;
        font-size: 0.88rem;
    }

    .btn-add {
        width: 100%;
        padding: var(--space-sm);
        background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
        color: #000;
        border: none;
        border-radius: var(--radius-md);
        font-weight: 800;
        font-size: 0.88rem;
        cursor: pointer;
    }

    .weight-table-wrap {
        overflow-x: auto;
        border-radius: var(--radius-md);
        border: 1px solid var(--border-subtle);
        margin-bottom: var(--space-lg);
    }
    .weight-table {
        width: 100%;
        min-width: 400px;
        border-collapse: collapse;
        font-size: 0.75rem;
    }
    .weight-table th {
        background: rgba(245,158,11,0.12);
        padding: 7px 10px;
        text-align: left;
        font-size: 0.65rem;
        font-weight: 800;
        color: var(--accent-primary);
        text-transform: uppercase;
        white-space: nowrap;
    }
    .weight-table td {
        padding: 7px 10px;
        border-bottom: 1px solid var(--border-subtle);
        color: var(--text-primary);
        white-space: nowrap;
    }
    .weight-table tr:last-child td { border-bottom: none; }

    .badge-grade { display: inline-block; padding: 2px 7px; border-radius: 4px; font-size: 0.65rem; font-weight: 800; }
    .badge-ga { background: rgba(34,197,94,0.15); color: var(--success); border: 1px solid rgba(34,197,94,0.3); }
    .badge-gb { background: rgba(245,158,11,0.15); color: var(--accent-primary); border: 1px solid rgba(245,158,11,0.3); }
</style>
@endpush

@section('content')

<div style="display:flex; align-items:center; gap:8px; margin-bottom:var(--space-lg);">
    <span style="font-size:1.3rem;">⚙️</span>
    <div>
        <div style="font-weight:800; font-size:1.05rem; color:var(--accent-primary);">SPESIFIKASI PIPA</div>
        <div style="font-size:0.68rem; color:var(--text-tertiary);">Kelola data master ukuran, grade, class & kategori</div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">✓ {{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert" style="background:var(--danger-soft); color:var(--danger); border:1px solid rgba(239,68,68,0.3);">
        ⚠️ {{ $errors->first() }}
    </div>
@endif

{{-- TABS NAV --}}
<div class="master-tabs" id="masterTabs">
    <button class="tab-btn active" onclick="switchTab('size', this)">📏 Ukuran Pipa</button>
    <button class="tab-btn" onclick="switchTab('grade', this)">🏷️ Grade</button>
    <button class="tab-btn" onclick="switchTab('class', this)">🔠 Class</button>
    <button class="tab-btn" onclick="switchTab('category', this)">📦 Kategori</button>
</div>

{{-- ══════════════════════════════════════════════════════════════ --}}
{{-- TAB: UKURAN PIPA --}}
{{-- ══════════════════════════════════════════════════════════════ --}}
<div class="tab-panel active" id="tab-size">
    <div class="master-list">
        @forelse($sizes as $s)
            <div class="master-item">
                <div>
                    <div class="master-item-label" style="font-family:var(--font-mono); color:var(--accent-primary);">{{ $s->size_label }}</div>
                    <div class="master-item-sub">{{ $s->pcs_per_bundle }} pcs/bundle</div>
                </div>
                <button type="button" class="btn-del" onclick="deleteMaster('{{ route('master.size.destroy', $s->id) }}', 'ukuran {{ $s->size_label }}')">🗑️</button>
            </div>
        @empty
            <div style="text-align:center; padding:var(--space-xl); color:var(--text-tertiary); font-size:0.82rem;">Belum ada data ukuran pipa.</div>
        @endforelse
    </div>

    <div class="add-form">
        <div class="add-form-title">➕ Tambah Ukuran Pipa</div>
        <form action="{{ route('master.size.store') }}" method="POST">
            @csrf
            <div class="field-group">
                <input type="text" name="size_label" placeholder='Ukuran (cth: 2")' required>
                <input type="number" name="pcs_per_bundle" placeholder="Pcs/Bundle" min="1" required style="max-width:130px;">
            </div>
            <button type="submit" class="btn-add">+ Tambah Ukuran</button>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════ --}}
{{-- TAB: GRADE --}}
{{-- ══════════════════════════════════════════════════════════════ --}}
<div class="tab-panel" id="tab-grade">
    <div class="master-list">
        @forelse($types as $t)
            <div class="master-item">
                <div>
                    <div class="master-item-label">
                        <span class="badge-grade {{ $t->code === 'G-A' ? 'badge-ga' : 'badge-gb' }}">{{ $t->code }}</span>
                        <span style="margin-left:6px; font-weight:500; color:var(--text-secondary);">{{ $t->name }}</span>
                    </div>
                </div>
                <button type="button" class="btn-del" onclick="deleteMaster('{{ route('master.grade.destroy', $t->id) }}', 'grade {{ $t->code }}')">🗑️</button>
            </div>
        @empty
            <div style="text-align:center; padding:var(--space-xl); color:var(--text-tertiary); font-size:0.82rem;">Belum ada data grade.</div>
        @endforelse
    </div>

    <div class="add-form">
        <div class="add-form-title">➕ Tambah Grade</div>
        <form action="{{ route('master.grade.store') }}" method="POST">
            @csrf
            <div class="field-group">
                <input type="text" name="code" placeholder="Kode (cth: G-C)" required style="max-width:110px;">
                <input type="text" name="name" placeholder="Nama (cth: Grade C)" required>
            </div>
            <button type="submit" class="btn-add">+ Tambah Grade</button>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════ --}}
{{-- TAB: CLASS --}}
{{-- ══════════════════════════════════════════════════════════════ --}}
<div class="tab-panel" id="tab-class">
    <div class="master-list">
        @forelse($classes as $cl)
            <div class="master-item">
                <div>
                    <div class="master-item-label" style="font-family:var(--font-mono);">{{ $cl->name }}</div>
                    <div class="master-item-sub">Kode: {{ $cl->code }}</div>
                </div>
                <button type="button" class="btn-del" onclick="deleteMaster('{{ route('master.class.destroy', $cl->id) }}', 'class {{ $cl->name }}')">🗑️</button>
            </div>
        @empty
            <div style="text-align:center; padding:var(--space-xl); color:var(--text-tertiary); font-size:0.82rem;">Belum ada data class.</div>
        @endforelse
    </div>

    <div class="add-form">
        <div class="add-form-title">➕ Tambah Class</div>
        <form action="{{ route('master.class.store') }}" method="POST">
            @csrf
            <div class="field-group">
                <input type="text" name="code" placeholder="Kode (cth: SCH80)" required style="max-width:110px;">
                <input type="text" name="name" placeholder="Nama (cth: SCH 80)" required>
            </div>
            <button type="submit" class="btn-add">+ Tambah Class</button>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════ --}}
{{-- TAB: KATEGORI --}}
{{-- ══════════════════════════════════════════════════════════════ --}}
<div class="tab-panel" id="tab-category">
    <div class="master-list">
        @forelse($categories as $cat)
            <div class="master-item">
                <div>
                    <div class="master-item-label">{{ $cat->name }}</div>
                    <div class="master-item-sub">Kode: {{ $cat->code }}</div>
                </div>
                <button type="button" class="btn-del" onclick="deleteMaster('{{ route('master.category.destroy', $cat->id) }}', 'kategori ini')">🗑️</button>
            </div>
        @empty
            <div style="text-align:center; padding:var(--space-xl); color:var(--text-tertiary); font-size:0.82rem;">Belum ada data kategori.</div>
        @endforelse
    </div>

    <div class="add-form">
        <div class="add-form-title">➕ Tambah Kategori</div>
        <form action="{{ route('master.category.store') }}" method="POST">
            @csrf
            <div class="field-group">
                <input type="text" name="code" placeholder="Kode (cth: hollow)" required style="max-width:110px;">
                <input type="text" name="name" placeholder="Nama (cth: PIPA HOLLOW)" required>
            </div>
            <button type="submit" class="btn-add">+ Tambah Kategori</button>
        </form>
    </div>
</div>



@push('scripts')
<script>
    const ACTIVE_TAB = '{{ session("success_tab", "size") }}';

    function switchTab(name, el) {
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('tab-' + name).classList.add('active');
        el.classList.add('active');
    }

    // Restore active tab after form submit
    document.addEventListener('DOMContentLoaded', function() {
        if (ACTIVE_TAB && ACTIVE_TAB !== '') {
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
                        <button onclick="this.closest('[style*=fixed]').style.display='none'" style="position:absolute;top:8px;right:10px;background:none;border:none;color:#9ca3af;font-size:1.3rem;cursor:pointer;line-height:1;">✕</button>
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
</script>
@endpush
@endsection
