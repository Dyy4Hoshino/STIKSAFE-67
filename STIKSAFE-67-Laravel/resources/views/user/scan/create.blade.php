@extends('layouts.app')
@section('title', 'Scan File & URL | STIKSAFE-67')

@section('content')
<div class="scan-page">
    <div class="scan-header">
        <span class="eyebrow">DETECTION ENGINE / V12 STAGING</span>
        <h1>Pemeriksaan Keamanan</h1>
        <p class="muted">Periksa potensi risiko file atau link mencurigakan secara aman tanpa perlu menjalankannya.</p>
    </div>

    <!-- TAB SWITCHER -->
    <div class="tab-container">
        <button type="button" class="tab-btn active" id="tab-file-btn" onclick="switchTab('file')">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="12" y1="18" x2="12" y2="12"></line><line x1="9" y1="15" x2="15" y2="15"></line></svg>
            Pindai Berkas (File)
        </button>
        <button type="button" class="tab-btn" id="tab-url-btn" onclick="switchTab('url')">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
            Periksa URL (Link)
        </button>
    </div>

    <!-- PANEL 1: SCAN FILE -->
    <div class="panel tab-content" id="content-file">
        <div class="panel-head">
            <div>
                <h2>Unggah Berkas</h2>
                <p class="muted">Analisis ciri statis PE binary & aturan format dokumen (PDF, Office, Skrip).</p>
            </div>
            <span class="badge-tag">Maks 25 MB</span>
        </div>

        <form method="post" action="{{ route('scan.store') }}" enctype="multipart/form-data" id="scan-form">
            @csrf
            <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">
            
            <label for="file" id="drop-area" class="drop-zone">
                <input id="file" name="file" type="file" required class="file-hidden">
                <div class="drop-icon-wrap" id="drop-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                </div>
                <strong id="drop-title" class="drop-title">Tarik & lepaskan file di sini</strong>
                <span class="muted drop-sub" id="file-name">atau <span style="color:#FF6700;text-decoration:underline">telusuri file dari perangkat</span></span>
            </label>
            @error('file')<p class="error" style="margin-top:10px">{{ $message }}</p>@enderror

            <div class="scan-features-grid">
                <div class="feature-item">
                    <span class="feat-label">MODEL DETEKSI</span>
                    <strong class="feat-val">EMBER2024 (PE) & Aturan</strong>
                </div>
                <div class="feature-item">
                    <span class="feat-label">KEAMANAN INTAKE</span>
                    <strong class="feat-val">Karantina Terisolasi</strong>
                </div>
                <div class="feature-item">
                    <span class="feat-label">BATASAN BERKAS</span>
                    <strong class="feat-val">Maksimum 25 MB</strong>
                </div>
            </div>

            <div class="actions form-actions">
                <button class="btn primary btn-submit" type="submit">
                    Mulai Pemindaian File →
                </button>
            </div>
        </form>
    </div>

    <!-- PANEL 2: SCAN URL -->
    <div class="panel tab-content" id="content-url" style="display:none">
        <div class="panel-head">
            <div>
                <h2>Pemeriksaan Tautan (URL)</h2>
                <p class="muted">Analisis struktur teks, port non-standar, host IP, dan daftar hitam reputasi.</p>
            </div>
            <span class="badge-tag">3 Lapis Analisis</span>
        </div>

        <form method="post" action="{{ route('scan.url') }}">
            @csrf
            <div class="field" style="margin-top:8px">
                <label for="url">ALAMAT URL LENGKAP</label>
                <div class="url-input-wrap">
                    <span class="url-prefix">URL</span>
                    <input id="url" name="url" type="url" required placeholder="https://contoh-situs.com/halaman" value="{{ old('url') }}" class="url-input">
                </div>
            </div>
            @error('url')<p class="error" style="margin-top:8px">{{ $message }}</p>@enderror

            <div class="url-notice">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6ee7b7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <p style="margin:0;font-size:12.5px;color:#cbd5e1;line-height:1.5">
                    <strong>Aman & Terisolasi:</strong> Tautan <em>tidak pernah dikunjungi</em> oleh sistem. Analisis dilakukan secara statis melalui heuristik domain, IP, dan model klasifikasi URL.
                </p>
            </div>

            <div class="actions form-actions">
                <button class="btn primary btn-submit" type="submit">
                    Analisis URL Sekarang →
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .scan-page {
        width: min(100%, 760px);
        margin: 0 auto;
        padding: 6px 0 40px;
    }
    .scan-header {
        text-align: center;
        margin-bottom: 26px;
    }
    .scan-header h1 {
        margin: 8px 0 6px;
    }
    .scan-header p {
        margin: 0;
        font-size: 14px;
    }

    /* TAB SELECTOR */
    .tab-container {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        background: rgba(15, 23, 42, 0.6);
        padding: 6px;
        border-radius: 12px;
        border: 1px solid rgba(148, 163, 184, 0.14);
    }
    .tab-btn {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 18px;
        border: none;
        border-radius: 8px;
        background: transparent;
        color: #94a3b8;
        font-family: inherit;
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    .tab-btn:hover {
        color: #f8fafc;
        background: rgba(255, 255, 255, 0.04);
    }
    .tab-btn.active {
        background: #FF6700;
        color: #111827;
        box-shadow: 0 4px 14px rgba(255, 103, 0, 0.25);
    }

    /* CARD & HEAD */
    .panel-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 14px;
        margin-bottom: 22px;
        border-bottom: 1px solid rgba(148, 163, 184, 0.12);
        padding-bottom: 16px;
    }
    .panel-head h2 {
        margin: 0 0 4px;
        font-size: 20px;
    }
    .panel-head p {
        margin: 0;
        font-size: 13px;
    }
    .badge-tag {
        font-size: 11px;
        font-weight: 700;
        font-family: 'JetBrains Mono', monospace;
        color: #FF8A38;
        background: rgba(255, 103, 0, 0.1);
        border: 1px solid rgba(255, 103, 0, 0.25);
        padding: 4px 10px;
        border-radius: 20px;
        white-space: nowrap;
    }

    /* DROP ZONE */
    .drop-zone {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 220px;
        padding: 32px 20px;
        text-align: center;
        border: 2px dashed rgba(70, 130, 180, 0.45);
        border-radius: 14px;
        background: rgba(11, 17, 32, 0.55);
        cursor: pointer;
        transition: all 0.25s ease;
    }
    .drop-zone:hover, .drop-zone.dragover {
        border-color: #FF6700;
        background: rgba(255, 103, 0, 0.06);
    }
    .file-hidden {
        display: none;
    }
    .drop-icon-wrap {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: rgba(255, 103, 0, 0.12);
        color: #FF6700;
        display: grid;
        place-items: center;
        margin-bottom: 14px;
        transition: transform 0.2s ease;
    }
    .drop-zone:hover .drop-icon-wrap {
        transform: translateY(-3px);
    }
    .drop-title {
        font-size: 16px;
        color: #f8fafc;
        margin-bottom: 6px;
    }
    .drop-sub {
        font-size: 13px;
    }

    /* FEATURES GRID */
    .scan-features-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin: 22px 0 16px;
    }
    .feature-item {
        background: rgba(11, 17, 32, 0.7);
        border: 1px solid rgba(148, 163, 184, 0.12);
        padding: 12px 14px;
        border-radius: 10px;
    }
    .feat-label {
        display: block;
        font-size: 10.5px;
        font-family: 'JetBrains Mono', monospace;
        color: #91a0b4;
        margin-bottom: 4px;
    }
    .feat-val {
        font-size: 12.5px;
        color: #f8fafc;
        font-weight: 600;
    }

    /* URL INPUT */
    .url-input-wrap {
        display: flex;
        align-items: stretch;
        margin-top: 4px;
        border: 1px solid rgba(70, 130, 180, 0.35);
        border-radius: 8px;
        overflow: hidden;
        background: #0b1120;
    }
    .url-prefix {
        display: flex;
        align-items: center;
        padding: 0 14px;
        background: rgba(15, 23, 42, 0.85);
        border-right: 1px solid rgba(70, 130, 180, 0.25);
        color: #91a0b4;
        font-size: 12px;
        font-weight: 700;
        font-family: 'JetBrains Mono', monospace;
    }
    .url-input {
        flex: 1;
        border: none !important;
        border-radius: 0 !important;
        padding: 12px 14px !important;
        font-size: 13.5px !important;
    }
    .url-notice {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        margin-top: 18px;
        padding: 14px 16px;
        background: rgba(16, 185, 129, 0.06);
        border: 1px solid rgba(16, 185, 129, 0.2);
        border-radius: 10px;
    }

    /* ACTIONS */
    .form-actions {
        margin-top: 24px;
        padding-top: 18px;
        border-top: 1px solid rgba(148, 163, 184, 0.12);
    }
    .btn-submit {
        width: 100%;
        padding: 13px;
        font-size: 14px;
        font-weight: 700;
        text-align: center;
    }

    @media (max-width: 600px) {
        .scan-features-grid {
            grid-template-columns: 1fr;
        }
        .tab-btn {
            font-size: 12px;
            padding: 10px;
        }
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

(function() {
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
            dropArea.style.borderColor = '#10B981';
            dropArea.style.background = 'rgba(16, 185, 129, 0.07)';
            dropIcon.innerHTML = '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
            dropIcon.style.background = 'rgba(16, 185, 129, 0.15)';
        } else {
            dropTitle.textContent = 'Tarik & lepaskan file di sini';
            fileName.innerHTML = 'atau <span style="color:#FF6700;text-decoration:underline">telusuri file dari perangkat</span>';
            dropArea.style.borderColor = 'rgba(70, 130, 180, 0.45)';
            dropArea.style.background = 'rgba(11, 17, 32, 0.55)';
            dropIcon.innerHTML = '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>';
            dropIcon.style.background = 'rgba(255, 103, 0, 0.12)';
        }
    }

    fileInput.addEventListener('change', function() {
        updateLabel(this.files[0] || null);
    });

    ['dragenter', 'dragover'].forEach(function(evt) {
        dropArea.addEventListener(evt, function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropArea.classList.add('dragover');
        });
    });

    ['dragleave', 'drop'].forEach(function(evt) {
        dropArea.addEventListener(evt, function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropArea.classList.remove('dragover');
        });
    });

    dropArea.addEventListener('drop', function(e) {
        const dt = e.dataTransfer;
        if (dt.files.length) {
            fileInput.files = dt.files;
            updateLabel(dt.files[0]);
        }
    });

    @if($errors->has('url'))
        switchTab('url');
    @endif
})();
</script>
@endsection
