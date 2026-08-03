@extends('layouts.app')

@section('title', 'Spesifikasi Pipa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/master.css') }}">
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
</script>
<script src="{{ asset('js/master.js') }}"></script>
@endpush
@endsection
