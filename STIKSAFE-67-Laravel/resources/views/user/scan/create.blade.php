@extends('layouts.app')
@section('title', 'Scan File & URL | STIKSAFE-67')

@section('content')
<div class="scan-workspace">
    {{-- Left-Aligned Header — exact original copy --}}
    <div class="scan-header">
        <span class="eyebrow">DETECTION ENGINE / V12 STAGING</span>
        <h1>Pemeriksaan Keamanan</h1>
        <p class="muted" style="margin:0">Periksa potensi risiko file atau link mencurigakan secara aman tanpa perlu menjalankannya.</p>
    </div>

    {{-- Asymmetric 2-Column Workspace (NOT narrow centered!) --}}
    <div class="scan-grid">
        <div class="scan-main-col">
            <div class="tab-container" role="tablist">
                <button type="button" class="tab-btn active" id="tab-file-btn" onclick="switchTab('file')">
                    <span class="material-symbols-outlined" style="font-size:18px">upload_file</span>
                    <span>Pindai Berkas (File)</span>
                </button>
                <button type="button" class="tab-btn" id="tab-url-btn" onclick="switchTab('url')">
                    <span class="material-symbols-outlined" style="font-size:18px">link</span>
                    <span>Periksa URL (Link)</span>
                </button>
            </div>

            {{-- PANEL 1: SCAN FILE --}}
            <div class="panel scan-card" id="content-file">
                <div class="panel-head">
                    <div>
                        <h2>Unggah Berkas</h2>
                        <p class="muted">Analisis ciri statis PE binary &amp; aturan format dokumen (PDF, Office, Skrip).</p>
                    </div>
                    <span class="badge-tag">Maks 25 MB</span>
                </div>

                <form method="post" action="{{ route('scan.store') }}" enctype="multipart/form-data" id="scan-form">
                    @csrf
                    <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">

                    <label for="file" id="drop-area" class="drop-zone">
                        <input id="file" name="file" type="file" required class="file-hidden">
                        <div class="drop-icon-wrap" id="drop-icon">
                            <span class="material-symbols-outlined" style="font-size:26px">cloud_upload</span>
                        </div>
                        <strong id="drop-title" class="drop-title">Tarik &amp; lepaskan file di sini</strong>
                        <span class="muted drop-sub" id="file-name">atau <span class="drop-link">telusuri file dari perangkat</span></span>
                    </label>
                    @error('file')<p class="error" style="margin-top:10px">{{ $message }}</p>@enderror

                    <div class="form-actions">
                        <button class="btn primary btn-submit" type="submit">Mulai Pemindaian File →</button>
                    </div>
                </form>
            </div>

            {{-- PANEL 2: SCAN URL --}}
            <div class="panel scan-card" id="content-url" style="display:none">
                <div class="panel-head">
                    <div>
                        <h2>Pemeriksaan Tautan (URL)</h2>
                        <p class="muted">Analisis struktur teks, port non-standar, host IP, dan daftar hitam reputasi.</p>
                    </div>
                    <span class="badge-tag">3 Lapis Analisis</span>
                </div>

                <form method="post" action="{{ route('scan.url') }}">
                    @csrf
                    <div class="field" style="margin-top:4px">
                        <label for="url">ALAMAT URL LENGKAP</label>
                        <div class="url-input-wrap">
                            <span class="url-prefix">URL</span>
                            <input id="url" name="url" type="url" required placeholder="https://contoh-situs.com/halaman" value="{{ old('url') }}" class="url-input">
                        </div>
                    </div>
                    @error('url')<p class="error" style="margin-top:8px">{{ $message }}</p>@enderror

                    <div class="url-notice">
                        <span class="material-symbols-outlined" style="font-size:18px;color:#006948;flex-shrink:0">info</span>
                        <p><strong>Aman &amp; Terisolasi:</strong> Tautan <em>tidak pernah dikunjungi</em> oleh sistem. Analisis dilakukan secara statis melalui heuristik domain, IP, dan model klasifikasi URL.</p>
                    </div>

                    <div class="form-actions">
                        <button class="btn primary btn-submit" type="submit">Analisis URL Sekarang →</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Right Side Information Rail (Balances Desktop Viewport) --}}
        <aside class="scan-aside-col" aria-label="Informasi pemeriksaan">
            <div class="panel aside-card">
                <span class="eyebrow">SPESIFIKASI PEMINDAIAN</span>
                <div class="aside-specs">
                    <div class="aside-spec-item">
                        <span class="aside-spec-icon"><span class="material-symbols-outlined" style="font-size:18px">memory</span></span>
                        <div>
                            <span class="feat-label">MODEL DETEKSI</span>
                            <strong class="feat-val">EMBER2024 (PE) &amp; Aturan</strong>
                        </div>
                    </div>
                    <div class="aside-spec-item">
                        <span class="aside-spec-icon aside-icon-safe"><span class="material-symbols-outlined" style="font-size:18px">lock</span></span>
                        <div>
                            <span class="feat-label">KEAMANAN INTAKE</span>
                            <strong class="feat-val">Karantina Terisolasi</strong>
                        </div>
                    </div>
                    <div class="aside-spec-item">
                        <span class="aside-spec-icon"><span class="material-symbols-outlined" style="font-size:18px">hard_drive</span></span>
                        <div>
                            <span class="feat-label">BATASAN BERKAS</span>
                            <strong class="feat-val">Maksimum 25 MB</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel aside-card">
                <span class="eyebrow">FORMAT YANG DIDUKUNG</span>
                <div class="format-list">
                    <div class="format-group">
                        <small>Model ML</small>
                        <div class="chip-wrap">
                            <span>.EXE</span><span>.DLL</span>
                        </div>
                    </div>
                    <div class="format-group">
                        <small>Pemeriksa Aturan</small>
                        <div class="chip-wrap">
                            <span>PDF</span><span>DOCX / XLSX / PPTX</span><span>ZIP</span><span>JPG / PNG / GIF</span><span>RTF / LNK</span><span>Skrip &amp; SVG</span>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>

<style>
.scan-workspace { width: 100%; }
.scan-header { margin-bottom: 24px; }
.scan-header h1 { margin: 6px 0 4px; }

.scan-grid {
    display: grid;
    grid-template-columns: 1.45fr 0.85fr;
    gap: 22px;
    align-items: start;
}

.tab-container {
    display: flex;
    gap: 6px;
    padding: 4px;
    margin-bottom: 16px;
    border-radius: 10px;
    background: #eceef0;
}
.tab-btn {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 14px;
    border: none;
    border-radius: 7px;
    background: transparent;
    color: #565e74;
    font-family: inherit;
    font-size: 13.5px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s;
}
.tab-btn:hover { color: #191c1e; }
.tab-btn.active {
    background: #ffffff;
    color: #191c1e;
    font-weight: 600;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
}
.tab-btn.active .material-symbols-outlined { color: #a33900; }

.scan-card { padding: 26px; }
.panel-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 14px;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid #eceef0;
}
.panel-head h2 { margin: 0 0 4px; font-size: 19px; }
.panel-head p { margin: 0; font-size: 13px; }
.badge-tag {
    font-size: 11px;
    font-weight: 600;
    font-family: 'JetBrains Mono', monospace;
    color: #a33900;
    background: #fff3ed;
    border: 1px solid #ffdbce;
    padding: 4px 10px;
    border-radius: 999px;
    white-space: nowrap;
}

.drop-zone {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 220px;
    padding: 32px 20px;
    text-align: center;
    border: 1.5px dashed #cbd5e1;
    border-radius: 10px;
    background: #f7f9fb;
    cursor: pointer;
    transition: border-color 0.15s, background-color 0.15s;
}
.drop-zone:hover, .drop-zone.dragover { border-color: #a33900; background: #fff9f6; }
.drop-zone.selected { border-color: #006948; border-style: solid; background: #ecfdf5; }
.file-hidden { display: none; }
.drop-icon-wrap {
    width: 52px;
    height: 52px;
    border-radius: 12px;
    background: #ffffff;
    border: 1px solid #e6e8ea;
    color: #a33900;
    display: grid;
    place-items: center;
    margin-bottom: 14px;
}
.drop-title { font-size: 15.5px; color: #191c1e; margin-bottom: 5px; }
.drop-sub { font-size: 13px; }
.drop-link { color: #a33900; font-weight: 600; text-decoration: underline; }

.url-input-wrap {
    display: flex;
    align-items: stretch;
    border: 1px solid #d8dadc;
    border-radius: 7px;
    overflow: hidden;
    background: #ffffff;
}
.url-input-wrap:focus-within { border-color: #a33900; box-shadow: 0 0 0 3px rgba(163, 57, 0, 0.1); }
.url-prefix {
    display: flex;
    align-items: center;
    padding: 0 14px;
    background: #f2f4f6;
    border-right: 1px solid #e6e8ea;
    color: #565e74;
    font: 700 11.5px 'JetBrains Mono', monospace;
}
.url-input { flex: 1; border: none !important; box-shadow: none !important; border-radius: 0 !important; padding: 11px 14px !important; font-family: 'JetBrains Mono', monospace !important; font-size: 13px !important; }
.url-notice {
    display: flex;
    gap: 10px;
    align-items: flex-start;
    margin-top: 16px;
    padding: 13px 15px;
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    border-radius: 8px;
}
.url-notice p { margin: 0; font-size: 12.5px; color: #005137; line-height: 1.55; }

.form-actions { margin-top: 22px; padding-top: 18px; border-top: 1px solid #eceef0; }
.btn-submit { width: 100%; padding: 12px 18px; font-size: 14px; }

/* Right Rail */
.scan-aside-col { display: flex; flex-direction: column; gap: 16px; }
.aside-card { padding: 20px; }
.aside-specs { display: grid; gap: 14px; margin-top: 14px; }
.aside-spec-item { display: flex; align-items: center; gap: 12px; }
.aside-spec-icon { width: 34px; height: 34px; border-radius: 8px; background: #f2f4f6; color: #a33900; display: grid; place-items: center; flex-shrink: 0; }
.aside-icon-safe { color: #006948; }
.feat-label { display: block; font: 600 10px 'JetBrains Mono', monospace; color: #565e74; }
.feat-val { font-size: 13px; color: #191c1e; font-weight: 600; }

.format-list { display: grid; gap: 14px; margin-top: 14px; }
.format-group small { display: block; font-size: 12px; font-weight: 600; color: #565e74; margin-bottom: 6px; }
.chip-wrap { display: flex; flex-wrap: wrap; gap: 6px; }
.chip-wrap span { padding: 3px 8px; border-radius: 5px; background: #f2f4f6; border: 1px solid #e6e8ea; font: 500 11px 'JetBrains Mono', monospace; color: #191c1e; }

@media (max-width: 900px) {
    .scan-grid { grid-template-columns: 1fr; }
}
@media (max-width: 600px) {
    .scan-card { padding: 18px; }
    .tab-btn { font-size: 12.5px; padding: 9px 10px; }
}
</style>

<script>
function switchTab(type) {
    const fileTab = document.getElementById('tab-file-btn');
    const urlTab = document.getElementById('tab-url-btn');
    const fileContent = document.getElementById('content-file');
    const urlContent = document.getElementById('content-url');

    if (type === 'file') {
        fileTab.classList.add('active');
        urlTab.classList.remove('active');
        fileContent.style.display = 'block';
        urlContent.style.display = 'none';
    } else {
        urlTab.classList.add('active');
        fileTab.classList.remove('active');
        urlContent.style.display = 'block';
        fileContent.style.display = 'none';
    }
}

(() => {
    const dropArea = document.getElementById('drop-area');
    const fileInput = document.getElementById('file');
    const fileName = document.getElementById('file-name');
    const dropTitle = document.getElementById('drop-title');
    const dropIcon = document.getElementById('drop-icon');

    function updateLabel(file) {
        if (file) {
            const sizeMB = (file.size / 1024 / 1024).toFixed(2);
            dropTitle.textContent = file.name;
            fileName.textContent = 'Ukuran: ' + sizeMB + ' MB · Klik untuk ganti file';
            dropArea.classList.add('selected');
            dropIcon.innerHTML = '<span class="material-symbols-outlined" style="font-size:26px;color:#006948">check_circle</span>';
        } else {
            dropTitle.textContent = 'Tarik & lepaskan file di sini';
            fileName.innerHTML = 'atau <span class="drop-link">telusuri file dari perangkat</span>';
            dropArea.classList.remove('selected');
            dropIcon.innerHTML = '<span class="material-symbols-outlined" style="font-size:26px">cloud_upload</span>';
        }
    }

    fileInput.addEventListener('change', function () {
        updateLabel(this.files[0] || null);
    });

    ['dragenter', 'dragover'].forEach((evt) => {
        dropArea.addEventListener(evt, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropArea.classList.add('dragover');
        });
    });

    ['dragleave', 'drop'].forEach((evt) => {
        dropArea.addEventListener(evt, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropArea.classList.remove('dragover');
        });
    });

    dropArea.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        if (dt.files.length) {
            fileInput.files = dt.files;
            updateLabel(dt.files[0]);
        }
    });

    const params = new URLSearchParams(window.location.search);
    if (params.get('tab') === 'url') {
        switchTab('url');
    }
    @if($errors->has('url'))
        switchTab('url');
    @endif
})();
</script>
@endsection
