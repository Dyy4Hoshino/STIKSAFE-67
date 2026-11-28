@extends('layouts.app')
@section('title', 'Pemantauan Scan | STIKSAFE-67')
@section('content')
<div class="eyebrow">OPERATIONS / SCAN MONITOR</div>
<h1>Pemantauan scan.</h1>
<p class="muted">Telusuri status, hasil, dan error dari seluruh pemindaian.</p>

<section class="panel" style="margin:22px 0">
    <form method="get" action="{{ route('admin.scans') }}">
        <div class="form-grid">
            <div class="field">
                <label for="q">CARI ID / NAMA FILE</label>
                <input id="q" name="q" type="search" value="{{ request('q') }}">
            </div>
            <div class="field">
                <label for="status">STATUS</label>
                <select id="status" name="filter[status]">
                    <option value="">Semua status</option>
                    @foreach (['queued' => 'Menunggu antrean', 'processing' => 'Sedang dianalisis', 'completed' => 'Selesai', 'failed' => 'Gagal', 'cancelled' => 'Dibatalkan', 'rejected' => 'Ditolak'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('filter.status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="result">HASIL</label>
                <select id="result" name="filter[result]">
                    <option value="">Semua hasil</option>
                    @foreach (['safe' => 'Aman', 'suspicious' => 'Mencurigakan', 'malicious' => 'Berbahaya', 'unknown' => 'Indikasi netral'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('filter.result') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="from">DARI TANGGAL</label>
                <input id="from" name="filter[from]" type="date" value="{{ request('filter.from') }}">
            </div>
            <div class="field">
                <label for="to">SAMPAI TANGGAL</label>
                <input id="to" name="filter[to]" type="date" value="{{ request('filter.to') }}">
            </div>
        </div>
        <div class="actions">
            <button class="btn primary" type="submit">Terapkan</button>
            <a class="btn" href="{{ route('admin.scans') }}">Atur ulang</a>
        </div>
    </form>
</section>

<section class="panel admin-scans-panel">
    @if ($scans->isEmpty())
        <p class="muted" style="margin:0">Tidak ada scan yang cocok.</p>
    @else
        <form method="post" action="{{ route('admin.scans.bulk-destroy') }}" id="bulk-delete-form">
            @csrf
            @method('DELETE')

            <div class="bulk-bar">
                <div class="bulk-info">
                    <span class="eyebrow">DAFTAR SCAN LOG</span>
                    <span class="bulk-count" id="bulk-selected-count">0 dipilih</span>
                </div>
                <button type="submit" class="btn danger bulk-delete-btn" id="bulk-delete-btn" disabled>
                    Hapus yang dipilih
                </button>
            </div>

            <div class="table-wrap">
                <table class="data admin-scans-table">
                    <thead>
                        <tr>
                            <th scope="col" class="col-check">
                                <input type="checkbox" id="select-all-scans" class="scan-checkbox" aria-label="Pilih semua log scan">
                            </th>
                            <th scope="col" class="col-id">ID Scan</th>
                            <th scope="col" class="col-owner">Pemilik</th>
                            <th scope="col">Nama file</th>
                            <th scope="col" class="col-time">Dibuat</th>
                            <th scope="col">Status</th>
                            <th scope="col">Hasil</th>
                            <th scope="col" style="text-align:right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($scans as $scan)
                            @php
                                $st = $scan->status?->value;
                                $lbl = $scan->result_label?->value;
                            @endphp
                            <tr>
                                <td class="col-check">
                                    <input
                                        type="checkbox"
                                        name="scan_ids[]"
                                        value="{{ $scan->id }}"
                                        class="scan-checkbox scan-row-check"
                                        aria-label="Pilih scan {{ $scan->id }}"
                                    >
                                </td>
                                <td class="col-id">
                                    <div class="scan-id-wrap">
                                        <span class="scan-id-full" title="{{ $scan->id }}">{{ $scan->id }}</span>
                                        <button type="button" class="copy-id-btn" data-copy-id="{{ $scan->id }}" title="Salin ID Scan" aria-label="Salin ID {{ $scan->id }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                                        </button>
                                    </div>
                                </td>
                                <td class="col-owner">
                                    <span class="cell-truncate owner-truncate" title="{{ $scan->user?->name ?? '—' }}">{{ $scan->user?->name ?? '—' }}</span>
                                </td>
                                <td>
                                    <span class="cell-truncate file-truncate" title="{{ $scan->original_filename }}">{{ $scan->original_filename }}</span>
                                </td>
                                <td class="col-time muted" style="white-space:nowrap;font-size:11.5px">
                                    @if ($scan->created_at)
                                        <time datetime="{{ $scan->created_at->toISOString() }}">{{ $scan->created_at->timezone(config('app.timezone'))->format('d M H:i') }}</time>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td style="white-space:nowrap">
                                    <span class="scan-pill pill-st-{{ $st ?? 'other' }}">{{ $st ?? '—' }}</span>
                                </td>
                                <td style="white-space:nowrap">
                                    @if ($lbl)
                                        <span class="scan-pill pill-res-{{ $lbl }}">{{ $lbl }}</span>
                                    @else
                                        <span class="muted">—</span>
                                    @endif
                                </td>
                                <td style="text-align:right;white-space:nowrap">
                                    <a class="detail-btn-link" href="{{ route('admin.scans.show', $scan->id) }}">Detail →</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </form>

        <div class="actions" style="justify-content:space-between">
            <span class="muted" style="font-size:12.5px">Halaman {{ $scans->currentPage() }} dari {{ $scans->lastPage() }}</span>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                @if (! $scans->onFirstPage())
                    <a class="btn" href="{{ $scans->previousPageUrl() }}">← Sebelumnya</a>
                @endif
                @if ($scans->hasMorePages())
                    <a class="btn" href="{{ $scans->nextPageUrl() }}">Berikutnya →</a>
                @endif
            </div>
        </div>
    @endif
</section>

<style>
.admin-scans-panel { padding: 18px; }
.bulk-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 14px;
    padding-bottom: 12px;
    border-bottom: 1px solid #eceef0;
}
.bulk-info { display: flex; align-items: center; gap: 10px; }
.bulk-count {
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px;
    font-weight: 600;
    padding: 3px 9px;
    border-radius: 999px;
    background: #f2f4f6;
    color: #565e74;
    border: 1px solid #e6e8ea;
}
.bulk-delete-btn { padding: 7px 13px; font-size: 12.5px; }
.bulk-delete-btn:disabled { opacity: 0.45; cursor: not-allowed; pointer-events: none; }

.admin-scans-table th,
.admin-scans-table td {
    padding: 10px 8px;
    font-size: 12.5px;
}
.col-check { width: 32px; text-align: center; }
.scan-checkbox {
    width: 15px;
    height: 15px;
    accent-color: #f97316;
    cursor: pointer;
    vertical-align: middle;
}

.scan-id-wrap {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.scan-id-full {
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px;
    color: #191c1e;
    white-space: nowrap;
    user-select: all;
}
.copy-id-btn {
    width: 24px;
    height: 24px;
    border-radius: 5px;
    border: 1px solid #e6e8ea;
    background: #f7f9fb;
    color: #565e74;
    display: inline-grid;
    place-items: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: border-color 0.15s, color 0.15s, background-color 0.15s;
}
.copy-id-btn:hover { border-color: #f97316; color: #f97316; background: #fff3ed; }
.copy-id-btn.copied { border-color: #006948; color: #006948; background: #ecfdf5; }
.copy-id-btn svg { width: 13px; height: 13px; stroke-width: 2; }

.cell-truncate {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.owner-truncate { max-width: 96px; }
.file-truncate { max-width: 145px; font-weight: 500; }

.scan-pill {
    display: inline-block;
    padding: 2px 7px;
    border-radius: 999px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 10.5px;
    font-weight: 600;
    border: 1px solid #e6e8ea;
    background: #f2f4f6;
    color: #565e74;
}
.pill-st-completed, .pill-res-safe { background: #ecfdf5; border-color: #a7f3d0; color: #006948; }
.pill-st-queued, .pill-st-processing { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }
.pill-res-suspicious { background: #fffbeb; border-color: #fde68a; color: #b45309; }
.pill-st-failed, .pill-st-cancelled, .pill-st-rejected, .pill-res-malicious { background: #fef2f2; border-color: #fecaca; color: #ba1a1a; }

.detail-btn-link {
    color: #f97316;
    font-weight: 600;
    font-size: 12.5px;
}
.detail-btn-link:hover { text-decoration: underline; }

body.dark-mode .bulk-bar { border-color: #212c3d; }
body.dark-mode .bulk-count { background: #0e141f; border-color: #212c3d; color: #cbd5e1; }
body.dark-mode .scan-id-full { color: #e2e8f0; }
body.dark-mode .copy-id-btn { background: #0e141f; border-color: #2a384e; color: #94a3b8; }
body.dark-mode .copy-id-btn:hover { border-color: #ff7b00; color: #ff7b00; background: #2a1b12; }
body.dark-mode .copy-id-btn.copied { border-color: #164e3a; color: #34d399; background: #0c261d; }
body.dark-mode .scan-pill { background: #1e293b; border-color: #334155; color: #cbd5e1; }
body.dark-mode .pill-st-completed, body.dark-mode .pill-res-safe { background: #0c261d; border-color: #164e3a; color: #34d399; }
body.dark-mode .pill-st-queued, body.dark-mode .pill-st-processing { background: #10203a; border-color: #1e3a8a; color: #93c5fd; }
body.dark-mode .pill-res-suspicious { background: #2b2110; border-color: #5c4317; color: #fbbf24; }
body.dark-mode .pill-st-failed, body.dark-mode .pill-st-cancelled, body.dark-mode .pill-st-rejected, body.dark-mode .pill-res-malicious { background: #2d1215; border-color: #5c2229; color: #fca5a5; }
body.dark-mode .detail-btn-link { color: #ff7b00; }

@media (max-width: 1024px) {
    .col-owner { display: none; }
    .file-truncate { max-width: 120px; }
}
@media (max-width: 768px) {
    .col-time { display: none; }
    .file-truncate { max-width: 115px; }
    .admin-scans-panel { padding: 14px; }
}
</style>

<script>
(() => {
    const selectAll = document.getElementById('select-all-scans');
    const rowChecks = Array.from(document.querySelectorAll('.scan-row-check'));
    const countBadge = document.getElementById('bulk-selected-count');
    const deleteBtn = document.getElementById('bulk-delete-btn');
    const form = document.getElementById('bulk-delete-form');

    function syncBulkState() {
        const checked = rowChecks.filter((c) => c.checked).length;
        if (countBadge) countBadge.textContent = `${checked} dipilih`;
        if (deleteBtn) deleteBtn.disabled = checked === 0;
        if (selectAll && rowChecks.length > 0) {
            selectAll.checked = checked === rowChecks.length;
            selectAll.indeterminate = checked > 0 && checked < rowChecks.length;
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', () => {
            rowChecks.forEach((c) => { c.checked = selectAll.checked; });
            syncBulkState();
        });
    }
    rowChecks.forEach((c) => c.addEventListener('change', syncBulkState));

    if (form) {
        form.addEventListener('submit', (e) => {
            const checked = rowChecks.filter((c) => c.checked).length;
            if (checked === 0) {
                e.preventDefault();
                return;
            }
            if (!window.confirm(`Hapus permanen ${checked} log scan yang dipilih? Tindakan ini tidak dapat dibatalkan.`)) {
                e.preventDefault();
            }
        });
    }

    document.querySelectorAll('[data-copy-id]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const id = btn.dataset.copyId;
            try {
                await navigator.clipboard.writeText(id);
                btn.classList.add('copied');
                const orig = btn.innerHTML;
                btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>';
                setTimeout(() => {
                    btn.classList.remove('copied');
                    btn.innerHTML = orig;
                }, 1400);
            } catch (_) {}
        });
    });
})();
</script>
@endsection
