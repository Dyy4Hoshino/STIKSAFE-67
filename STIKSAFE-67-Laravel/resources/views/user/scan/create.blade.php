@extends('layouts.app')
@section('title', 'Scan File & URL | STIKSAFE-67')

@section('content')
<div class="scan-workspace">
    {{-- Subtle 3D Disperse-Converge Backdrop behind top header --}}
    <div class="scan-3d-backdrop" aria-hidden="true">
        <canvas id="scan-bg-3d"></canvas>
    </div>

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
.scan-workspace { position: relative; width: 100%; }
.scan-3d-backdrop {
    position: absolute;
    top: -32px;
    left: 50%;
    width: 100vw;
    transform: translateX(-50%);
    height: 230px;
    pointer-events: none;
    z-index: 0;
    overflow: hidden;
    mask-image: linear-gradient(to bottom, #000 62%, transparent 100%);
    -webkit-mask-image: linear-gradient(to bottom, #000 62%, transparent 100%);
}
#scan-bg-3d {
    width: 100%;
    height: 100%;
    display: block;
}
.scan-header {
    position: relative;
    z-index: 1;
    margin-bottom: 28px;
    padding-top: 4px;
    max-width: 600px;
}
.scan-header h1 { margin: 6px 0 6px; }
.scan-header p { line-height: 1.6; }

.scan-grid {
    position: relative;
    z-index: 1;
    display: grid;
    grid-template-columns: minmax(0, 1.45fr) minmax(300px, 0.85fr);
    gap: 24px;
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
    .scan-3d-backdrop { height: 175px; top: -18px; opacity: 0.85; }
    .scan-header { margin-bottom: 20px; }
    .scan-grid { grid-template-columns: 1fr; gap: 18px; }
    .scan-aside-col { gap: 14px; }
}
@media (max-width: 600px) {
    .scan-3d-backdrop { height: 145px; opacity: 0.72; }
    .scan-header { margin-bottom: 16px; }
    .scan-card { padding: 18px; }
    .panel-head { flex-wrap: wrap; gap: 10px; margin-bottom: 16px; padding-bottom: 14px; }
    .drop-zone { min-height: 185px; padding: 24px 16px; }
    .tab-btn { font-size: 12.5px; padding: 9px 10px; gap: 6px; }
    .aside-card { padding: 18px; }
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

(() => {
    const canvas = document.getElementById('scan-bg-3d');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let w = 0, h = 0, dpr = Math.min(window.devicePixelRatio || 1, 2);

    function resize() {
        const rect = canvas.getBoundingClientRect();
        w = Math.max(1, rect.width);
        h = Math.max(1, rect.height);
        canvas.width = Math.round(w * dpr);
        canvas.height = Math.round(h * dpr);
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    }
    resize();
    window.addEventListener('resize', resize);

    // Dispersed 3D molecular lattice using wide radial disperse trajectories + smooth rotation
    const N = 64;
    const golden = Math.PI * (3 - Math.sqrt(5));
    const particles = [];
    for (let i = 0; i < N; i++) {
        const y = 1 - (i / (N - 1)) * 2;
        const r = Math.sqrt(Math.max(0, 1 - y * y));
        const theta = golden * i;
        const bx = Math.cos(theta) * r;
        const by = y;
        const bz = Math.sin(theta) * r;

        const s1 = Math.sin(i * 12.9898) * 43758.5453;
        const s2 = Math.sin(i * 78.233) * 19341.123;
        const s3 = Math.sin(i * 39.425) * 28411.771;
        const rnd1 = (s1 - Math.floor(s1)) * 2 - 1;
        const rnd2 = (s2 - Math.floor(s2)) * 2 - 1;
        const rnd3 = (s3 - Math.floor(s3)) * 2 - 1;

        particles.push({
            bx, by, bz,
            dx: bx * 2.2 + rnd1 * 1.45,
            dy: by * 1.6 + rnd2 * 0.95,
            dz: bz * 2.0 + rnd3 * 1.1,
            phase: i * 0.45,
            accent: i % 5 === 0,
            size: i % 5 === 0 ? 2.4 : 1.6
        });
    }

    let t = 0;
    function render() {
        ctx.clearRect(0, 0, w, h);
        const isDark = document.body.classList.contains('dark-mode');

        // Gentle airy disperse breathing spanning full width of top backdrop
        const spread = 0.88 + Math.sin(t * 0.42) * 0.12;

        const cx = w * 0.5;
        const cy = h * 0.46;
        const scaleX = Math.max(120, w * 0.22);
        const scaleY = Math.max(42, h * 0.3);
        const fov = 5.2;

        const rotY = t * 0.24;
        const rotX = 0.22 + Math.sin(t * 0.18) * 0.1;
        const cosY = Math.cos(rotY), sinY = Math.sin(rotY);
        const cosX = Math.cos(rotX), sinX = Math.sin(rotX);

        const projected = particles.map((p) => {
            const drift = Math.sin(t * 0.7 + p.phase) * 0.04;
            const x0 = p.bx + (p.dx - p.bx) * spread;
            const y0 = p.by + (p.dy - p.by) * spread + drift;
            const z0 = p.bz + (p.dz - p.bz) * spread;

            const x1 = x0 * cosY + z0 * sinY;
            const z1 = -x0 * sinY + z0 * cosY;
            const y2 = y0 * cosX - z1 * sinX;
            const z2 = y0 * sinX + z1 * cosX;

            const persp = fov / (fov - z2);
            return {
                x: cx + x1 * scaleX * persp,
                y: cy + y2 * scaleY * persp,
                z: z2,
                persp,
                accent: p.accent,
                size: p.size
            };
        });

        // Subtle molecular bonds between nearby 3D nodes across full width
        const maxDist = w > 720 ? 110 : 70;
        const lineOpacityBase = isDark ? 0.19 : 0.14;
        ctx.lineWidth = 0.85;
        for (let i = 0; i < projected.length; i++) {
            const a = projected[i];
            for (let j = i + 1; j < projected.length; j++) {
                const b = projected[j];
                const d = Math.hypot(a.x - b.x, a.y - b.y);
                if (d < maxDist) {
                    const alpha = (1 - d / maxDist) * lineOpacityBase;
                    ctx.beginPath();
                    ctx.moveTo(a.x, a.y);
                    ctx.lineTo(b.x, b.y);
                    ctx.strokeStyle = isDark
                        ? `rgba(148, 163, 184, ${alpha.toFixed(3)})`
                        : `rgba(86, 94, 116, ${alpha.toFixed(3)})`;
                    ctx.stroke();
                }
            }
        }

        // Draw depth-sorted 3D nodes
        projected.sort((a, b) => a.z - b.z);
        for (const p of projected) {
            const depthAlpha = Math.max(0.14, Math.min(0.75, (p.z + 1.5) / 2.6));
            const alpha = depthAlpha * (isDark ? 0.72 : 0.56);
            ctx.beginPath();
            ctx.arc(p.x, p.y, Math.max(1.1, p.size * p.persp), 0, Math.PI * 2);
            if (p.accent) {
                ctx.fillStyle = isDark
                    ? `rgba(255, 123, 0, ${Math.min(0.85, alpha * 1.25).toFixed(3)})`
                    : `rgba(249, 115, 22, ${Math.min(0.75, alpha * 1.15).toFixed(3)})`;
            } else {
                ctx.fillStyle = isDark
                    ? `rgba(148, 163, 184, ${alpha.toFixed(3)})`
                    : `rgba(86, 94, 116, ${alpha.toFixed(3)})`;
            }
            ctx.fill();
        }

        if (!reducedMotion) {
            t += 0.009;
            requestAnimationFrame(render);
        }
    }

    render();
})();
</script>
@endsection
