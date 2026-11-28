(() => {
    const STORAGE_KEY = 'stiksafe_lang';

    const EXACT = {
        // Navigation & Global Footer
        'Beranda': 'Home',
        'Fitur': 'Features',
        'Tentang': 'About',
        'Masuk': 'Sign in',
        'Mulai scan': 'Start scan',
        'Scan': 'Scan',
        'Riwayat': 'History',
        'Notifikasi': 'Notifications',
        'Profil': 'Profile',
        'Menu Admin': 'Admin Menu',
        'Dashboard': 'Dashboard',
        'Scan Log': 'Scan Log',
        'Pengguna': 'Users',
        'Feedback': 'Feedback',
        'Kirim Notifikasi': 'Send Notification',
        'Audit Log': 'Audit Log',
        'Setelan': 'Settings',
        'STIKSAFE-67 · SMK N 1 MAS UBUD · Pemeriksaan Keamanan File & URL':
            'STIKSAFE-67 · SMK N 1 MAS UBUD · File & URL Security Inspection',
        'Analisis statis · Gunakan hasil sebagai panduan awal':
            'Static analysis · Use results as an initial guide',

        // Landing Page
        'Kenali risiko.': 'Know the risks.',
        'Lindungi data.': 'Protect your data.',
        'Periksa indikasi ancaman file melalui analisis keamanan berbasis machine learning. Tanpa menjalankan file. Hasil pemeriksaan membantu Anda mengambil langkah lebih aman.':
            'Check file threat indications through machine learning security analysis. Without executing files. Inspection results help you take safer actions.',
        'Mulai pemeriksaan': 'Start inspection',
        'Masuk ke akun': 'Sign in to account',
        'STATUS: ML TERHUBUNG': 'STATUS: ML CONNECTED',
        'STATUS: ML OFFLINE': 'STATUS: ML OFFLINE',
        'Pemeriksaan berlapis': 'Layered inspection',
        'Indikasi awal untuk membantu mengenali file berisiko sebelum dibuka.':
            'Early indications to help identify risky files before opening them.',
        'Analisis statis': 'Static analysis',
        'Periksa karakteristik file tanpa mengeksekusinya di perangkat Anda.':
            'Inspect file characteristics without executing them on your device.',
        'Klasifikasi risiko': 'Risk classification',
        'Hasil model memberi indikasi aman, mencurigakan, berbahaya, atau belum diketahui.':
            'Model output indicates safe, suspicious, malicious, or unknown status.',
        'Riwayat pemindaian': 'Scan history',
        'Simpan dan tinjau hasil pemeriksaan melalui akun Anda.':
            'Save and review inspection results through your account.',
        'Tentang STIKSAFE': 'About STIKSAFE',
        'STIKSAFE-67 adalah alat bantu pemeriksaan awal keamanan file, dikembangkan untuk lingkungan SMK Negeri 1 Mas Ubud.':
            'STIKSAFE-67 is an early file security inspection tool, developed for SMK Negeri 1 Mas Ubud.',
        'Hasil analisis merupakan prediksi model, bukan jaminan keamanan mutlak. Jangan membuka file yang tidak dikenal.':
            'Analysis results are model predictions, not an absolute security guarantee. Do not open unknown files.',
        'Coba STIKSAFE': 'Try STIKSAFE',

        // About Page
        'TENTANG STIKSAFE-67': 'ABOUT STIKSAFE-67',
        'Keamanan file': 'File security',
        'dimulai dari pemeriksaan.': 'starts with inspection.',
        'Alat bantu pemeriksaan awal keamanan berkas dan tautan secara statis.':
            'An early static security inspection tool for files and links.',
        'STIKSAFE-67 membantu pengguna mengenali indikasi awal risiko file dan URL melalui analisis statis. Berkas tidak pernah dijalankan dan tautan tidak pernah dikunjungi selama pemeriksaan.':
            'STIKSAFE-67 helps users recognize early file and URL risk indicators through static analysis. Files are never executed and links are never visited during inspection.',
        'Platform ini dikembangkan sebagai proyek keamanan digital di':
            'This platform was developed as a digital security project at',
        ', memadukan model machine learning EMBER2024 untuk program Windows (':
            ', combining the EMBER2024 machine learning model for Windows programs (',
        '), aturan statis untuk dokumen (PDF, Office, ZIP, gambar, skrip), serta pemeriksaan URL 3 lapis.':
            '), static rules for documents (PDF, Office, ZIP, images, scripts), and 3-layer URL inspection.',
        'Catatan keterbatasan:': 'Limitation note:',
        'Hasil klasifikasi merupakan panduan awal berbasis model dan aturan, bukan jaminan keamanan mutlak. Tetap berhati-hati sebelum membuka atau membagikan berkas dari sumber tidak dikenal.':
            'Classification results are initial model- and rule-based guidance, not an absolute security guarantee. Always be careful before opening or sharing files from unknown sources.',
        'Mulai gunakan STIKSAFE →': 'Start using STIKSAFE →',
        'Kembali ke Beranda': 'Back to Home',
        'Kembali ke beranda →': 'Back to home →',

        // Auth Pages (Login, Register, Forgot, Reset, Verify)
        'Masuk ke': 'Sign in to',
        'Lanjutkan pemeriksaan file dan pantau riwayat keamanan Anda.':
            'Continue file inspections and monitor your security history.',
        'KATA SANDI': 'PASSWORD',
        'Masuk ke dashboard →': 'Sign in to dashboard →',
        'Lupa kata sandi?': 'Forgot password?',
        'Belum punya akun?': "Don't have an account?",
        'Buat akun baru': 'Create new account',
        'Sudah punya akun?': 'Already have an account?',
        'Sudah punya akun': 'Already have an account',
        'Simpan riwayat scan dan akses hasil pemeriksaan Anda.': 'Save your scan history and access your inspection results.',
        'Saya menyetujui syarat penggunaan layanan STIKSAFE-67': 'I agree to the STIKSAFE-67 terms of service',
        'NAMA LENGKAP': 'FULL NAME',
        'KONFIRMASI KATA SANDI': 'CONFIRM PASSWORD',
        'Minimal 12 karakter.': 'Minimum 12 characters.',
        'Daftar sekarang →': 'Register now →',
        'Daftar akun': 'Register account',
        'Buat akun': 'Create account',
        'Buat akun →': 'Create account →',
        'Atur ulang': 'Reset',
        'kata sandi.': 'password.',
        'Masukkan email akun. Jika terdaftar, kami kirim tautan untuk membuat kata sandi baru.':
            'Enter your account email. If registered, we will send a link to create a new password.',
        'EMAIL AKUN': 'ACCOUNT EMAIL',
        'Kirim tautan →': 'Send link →',
        'Kembali masuk': 'Back to sign in',
        'Kembali': 'Back',
        'KONFIRMASI AKSI': 'CONFIRM ACTION',
        'Pemeriksaan awal keamanan file dan tautan berbasis machine learning tanpa menjalankan file di perangkat Anda.':
            'Early machine learning file and link security inspection without running files on your device.',
        'Analisis statis EMBER2024 & aturan dokumen': 'EMBER2024 static analysis & document rules',
        'Pemeriksaan URL 3 lapis tanpa membuka tautan': '3-layer URL inspection without visiting links',
        'Riwayat pemeriksaan tersimpan per akun': 'Inspection history saved per account',

        // User Dashboard
        'Periksa file atau URL mencurigakan, pantau hasil, lalu ambil tindakan dengan informasi yang jelas.':
            'Inspect suspicious files or URLs, monitor results, and take action with clear insights.',
        '+ Mulai Scan Baru': '+ Start New Scan',
        'Lihat riwayat': 'View history',
        'RINGKASAN AKTIVITAS': 'ACTIVITY SUMMARY',
        'Statistik scan': 'Scan statistics',
        'Semua waktu': 'All time',
        'TOTAL SCAN': 'TOTAL SCANS',
        'File yang pernah diperiksa': 'Files inspected so far',
        'AMAN': 'SAFE',
        'Tidak terdeteksi ancaman': 'No threats detected',
        'MENCURIGAKAN': 'SUSPICIOUS',
        'Butuh perhatian': 'Needs attention',
        'BERBAHAYA': 'MALICIOUS',
        'Ancaman terdeteksi': 'Threats detected',
        'dalam antrean / diproses': 'queued / processing',
        'gagal / dibatalkan / ditolak': 'failed / cancelled / rejected',
        'Detail riwayat →': 'History details →',
        'Scan terbaru': 'Recent scans',
        'Aktivitas pemeriksaan file terbaru dalam akun Anda.': 'Recent file inspection activity in your account.',
        'Semua riwayat': 'All history',
        'Belum ada scan': 'No scans yet',
        'Mulai dengan memindai file atau URL pertama Anda.': 'Start by scanning your first file or URL.',
        '+ Scan sekarang': '+ Scan now',
        'WAKTU': 'TIME',
        'HASIL': 'RESULT',
        'Lihat →': 'View →',
        'Detail →': 'Details →',

        // Scan Create (File & URL)
        'Pemeriksaan Keamanan': 'Security Inspection',
        'Periksa potensi risiko file atau link mencurigakan secara aman tanpa perlu menjalankannya.':
            'Safely check potential risks of suspicious files or links without executing them.',
        'Pindai Berkas (File)': 'Scan File',
        'Periksa URL (Link)': 'Check URL (Link)',
        'Unggah Berkas': 'Upload File',
        'Analisis ciri statis PE binary & aturan format dokumen (PDF, Office, Skrip).':
            'Static PE binary feature analysis & document format rules (PDF, Office, Scripts).',
        'Maks 25 MB': 'Max 25 MB',
        'Tarik & lepaskan file di sini': 'Drag & drop file here',
        'atau': 'or',
        'telusuri file dari perangkat': 'browse file from device',
        'Mulai Pemindaian File →': 'Start File Scan →',
        'Pemeriksaan Tautan (URL)': 'URL Inspection',
        'Analisis struktur teks, port non-standar, host IP, dan daftar hitam reputasi.':
            'Analyze text structure, non-standard ports, IP hosts, and reputation blocklists.',
        '3 Lapis Analisis': '3-Layer Analysis',
        'ALAMAT URL LENGKAP': 'FULL URL ADDRESS',
        'Aman & Terisolasi:': 'Safe & Isolated:',
        'Tautan': 'Links are',
        'tidak pernah dikunjungi': 'never visited',
        'oleh sistem. Analisis dilakukan secara statis melalui heuristik domain, IP, dan model klasifikasi URL.':
            'by the system. Analysis is performed statically via domain heuristics, IP checks, and the URL classification model.',
        'Analisis URL Sekarang →': 'Analyze URL Now →',
        'SPESIFIKASI PEMINDAIAN': 'SCAN SPECIFICATIONS',
        'MODEL DETEKSI': 'DETECTION MODEL',
        'EMBER2024 (PE) & Aturan': 'EMBER2024 (PE) & Rules',
        'KEAMANAN INTAKE': 'INTAKE SECURITY',
        'Karantina Terisolasi': 'Isolated Quarantine',
        'BATASAN BERKAS': 'FILE LIMIT',
        'Maksimum 25 MB': 'Maximum 25 MB',
        'FORMAT YANG DIDUKUNG': 'SUPPORTED FORMATS',
        'Model ML': 'ML Model',
        'Pemeriksa Aturan': 'Rule Inspector',
        'Skrip & SVG': 'Scripts & SVG',

        // Scan Results (File & URL)
        'LAPORAN ANALISIS FILE': 'FILE ANALYSIS REPORT',
        'LAPORAN ANALISIS URL': 'URL ANALYSIS REPORT',
        'Hasil Pemeriksaan': 'Inspection Result',
        'Hasil Pemeriksaan URL': 'URL Inspection Result',
        'Analisis statis dilakukan tanpa menjalankan berkas Anda.': 'Static analysis was performed without executing your file.',
        'URL tidak pernah dikunjungi; hanya struktur teks dan daftar hitam yang dianalisis.':
            'URL was never visited; only text structure and blocklists were analyzed.',
        '+ Scan Baru': '+ New Scan',
        '+ Periksa URL Lain': '+ Check Another URL',
        'SEDANG DIPROSES': 'PROCESSING',
        'Pemindaian sedang berlangsung': 'Scan in progress',
        'Hasil akan muncul otomatis tanpa perlu me-refresh halaman.': 'Results will appear automatically without refreshing the page.',
        'File dikarantina': 'File quarantined',
        'ML Service aktif': 'ML Service active',
        'Batalkan scan': 'Cancel scan',
        'HASIL PEMERIKSAAN': 'INSPECTION VERDICT',
        'Aman': 'Safe',
        'Mencurigakan': 'Suspicious',
        'Berbahaya': 'Malicious',
        'Indikasi netral': 'Neutral indication',
        'Tidak diketahui': 'Unknown',
        'Antrean': 'Queued',
        'Diproses': 'Processing',
        'Selesai': 'Completed',
        'Gagal': 'Failed',
        'Dibatalkan': 'Cancelled',
        'Ditolak': 'Rejected',
        'Menunggu antrean': 'Queued',
        'Sedang dianalisis': 'Analyzing',
        'Tidak terdeteksi ancaman oleh pemindai kami.': 'No threats detected by our scanner.',
        'Ditemukan ciri yang memerlukan perhatian. Jangan langsung membuka file ini.':
            'Indicators requiring attention were found. Do not open this file right away.',
        'Ancaman terdeteksi. Jangan buka atau bagikan file ini.': 'Threat detected. Do not open or share this file.',
        'Pemeriksaan dasar selesai; belum dapat memastikan risiko file ini.':
            'Basic inspection completed; unable to determine the exact risk of this file.',
        'Ancaman terdeteksi. Jangan kunjungi URL ini.': 'Threat detected. Do not visit this URL.',
        'Ditemukan ciri yang memerlukan perhatian sebelum membuka tautan ini.':
            'Indicators requiring caution were found before opening this link.',
        'Tidak ada indikasi ancaman yang dikenali; tetap berhati-hati.': 'No threat indications recognized; stay cautious.',
        'Skala 0–1': 'Scale 0–1',
        'Engine pemrosesan': 'Processing engine',
        'Identitas pemeriksaan': 'Inspection ID',
        'INDIKATOR & ALASAN': 'INDICATORS & REASONS',
        'Analisis selesai tanpa kode peringatan khusus.': 'Analysis completed with no special warning codes.',
        'Tidak ada kode alasan khusus.': 'No specific reason codes.',
        'Hasil merupakan prediksi model dan pemeriksaan aturan awal, bukan jaminan keamanan mutlak. Versi preprocessing:':
            'Results are initial model predictions and rule checks, not an absolute security guarantee. Preprocessing version:',
        'TIDAK SELESAI': 'INCOMPLETE',
        'Pemindaian tidak selesai': 'Scan did not complete',
        'FEEDBACK TERKIRIM': 'FEEDBACK SUBMITTED',
        'Terima kasih atas masukan Anda.': 'Thank you for your feedback.',
        'BANTU TINGKATKAN MODEL': 'HELP IMPROVE THE MODEL',
        'Seharusnya aman': 'Should be safe',
        'Seharusnya berbahaya': 'Should be malicious',
        'Catatan lainnya': 'Other notes',
        'Kirim feedback': 'Send feedback',
        'Scan File / URL Lain': 'Scan Another File / URL',
        'YA': 'YES',
        'TIDAK': 'NO',
        'Tipe tidak diketahui': 'Unknown type',

        // Reason Descriptions (File & URL)
        'Ekstensi ganda terdeteksi — indikasi penyamaran file.': 'Double extension detected — possible file masquerading.',
        'File executable menyamar sebagai dokumen atau gambar.': 'Executable file disguised as a document or image.',
        'Karakter ekstensi tidak wajar atau disembunyikan.': 'Unusual or hidden extension characters.',
        'Isi file tidak sesuai dengan ekstensi namanya.': 'File content does not match its filename extension.',
        'Pemeriksaan dasar selesai; tidak ditemukan ciri berisiko.': 'Basic inspection complete; no risk indicators found.',
        'PDF berisi JavaScript tertanam.': 'PDF contains embedded JavaScript.',
        'PDF menjalankan aksi otomatis saat dibuka.': 'PDF triggers automatic actions on open.',
        'PDF mencoba meluncurkan aplikasi eksternal.': 'PDF attempts to launch an external application.',
        'PDF memuat berkas tertanam di dalamnya.': 'PDF contains embedded files.',
        'Arsip berisi berkas executable.': 'Archive contains executable files.',
        'Arsip berisi berkas berekstensi ganda.': 'Archive contains double-extension files.',
        'Arsip berisi file executable.': 'Archive contains an executable file.',
        'Dokumen Office berisi makro aktif.': 'Office document contains active macros.',
        'Gambar memiliki muatan tersembunyi di bagian akhir file.': 'Image contains a hidden trailing payload.',
        'Berkas shortcut Windows (.lnk) berisiko tinggi pada unggahan web.': 'Windows shortcut (.lnk) file poses high risk on web uploads.',
        'Ditemukan pola skrip berisiko.': 'Risky script pattern detected.',
        'URL cocok dengan daftar hitam ancaman.': 'URL matches a threat blocklist.',
        'URL menggunakan alamat IP langsung sebagai host.': 'URL uses a direct IP address as host.',
        'URL menggunakan port jaringan tidak standar.': 'URL uses a non-standard network port.',
        'URL mengandung informasi pengguna (@) yang sering dipakai penyamaran.': 'URL contains userinfo (@) often used for spoofing.',
        'URL mengarah pada unduhan file executable.': 'URL points to an executable file download.',
        'Host menggunakan karakter Punycode (xn--).': 'Host uses Punycode characters (xn--).',
        'URL memiliki terlalu banyak subdomain.': 'URL has too many subdomains.',
        'URL menggunakan layanan pemendek tautan.': 'URL uses a link shortener service.',
        'URL mengandung kata kunci mencurigakan.': 'URL contains suspicious keywords.',
        'Tidak ditemukan indikasi ancaman yang dikenali.': 'No recognized threat indicators found.',

        // History & Notifications
        'RIWAYAT PRIBADI': 'PERSONAL HISTORY',
        'Riwayat scan.': 'Scan history.',
        'Cari dan tinjau hasil pemeriksaan sebelumnya.': 'Search and review previous inspection results.',
        'CARI FILE / ID': 'SEARCH FILE / ID',
        'CARI ID / NAMA FILE': 'SEARCH ID / FILENAME',
        'URUTAN': 'SORT',
        'Terbaru': 'Newest',
        'Terlama': 'Oldest',
        'DARI TANGGAL': 'FROM DATE',
        'SAMPAI TANGGAL': 'TO DATE',
        'Terapkan filter': 'Apply filters',
        'Terapkan': 'Apply',
        'Atur ulang': 'Reset',
        'Semua status': 'All statuses',
        'Semua hasil': 'All results',
        'Tidak ada hasil ditemukan': 'No results found',
        'Coba ubah atau atur ulang filter pencarian Anda.': 'Try changing or resetting your search filters.',
        'Belum ada riwayat scan': 'No scan history yet',
        '+ Mulai Scan Pertama': '+ Start First Scan',
        'Nama file': 'Filename',
        'Waktu upload': 'Upload time',
        'Aksi': 'Actions',
        'Lihat detail': 'View details',
        'Hapus': 'Delete',
        '← Sebelumnya': '← Previous',
        'Berikutnya →': 'Next →',
        'Notifikasi.': 'Notifications.',
        'Pembaruan keamanan dan aktivitas pemindaian Anda.': 'Your security updates and scan activity.',
        'Tandai semua dibaca': 'Mark all as read',
        'Belum ada notifikasi.': 'No notifications yet.',
        'Lihat scan →': 'View scan →',
        'Tandai dibaca': 'Mark as read',

        // Profile
        'PENGATURAN AKUN': 'ACCOUNT SETTINGS',
        'Profil Anda.': 'Your Profile.',
        'Kelola identitas, foto profil, tampilan, dan keamanan akun.':
            'Manage your identity, profile photo, appearance, and account security.',
        'TAMPILAN & BAHASA': 'APPEARANCE & LANGUAGE',
        'TAMPILAN WEBSITE': 'WEBSITE APPEARANCE',
        'Ganti mode terang/gelap dan bahasa tampilan.': 'Switch light/dark theme and display language.',
        'Ganti mode terang atau gelap.': 'Switch between light and dark mode.',
        'Aktifkan dark mode': 'Enable dark mode',
        'Beralih ke mode terang': 'Switch to light mode',
        'Nama Lengkap': 'Full Name',
        'Alamat Email': 'Email Address',
        'Bergabung Sejak': 'Member Since',
        'Edit Profil': 'Edit Profile',
        'Foto profil, ubah nama & ganti kata sandi': 'Profile photo, change name & password',
        'FOTO PROFIL': 'PROFILE PHOTO',
        'Ganti Foto Profil': 'Change Profile Photo',
        'Pilih gambar (JPG, PNG, atau WebP, maks. 2 MB).': 'Choose an image (JPG, PNG, or WebP, max 2 MB).',
        'Unggah Foto': 'Upload Photo',
        'IDENTITAS': 'IDENTITY',
        'Ubah nama': 'Change name',
        'Perbarui nama lengkap yang ditampilkan di akun Anda.': 'Update the full name displayed on your account.',
        'Simpan nama': 'Save name',
        'KEAMANAN': 'SECURITY',
        'Ganti kata sandi': 'Change password',
        'Gunakan kata sandi unik untuk menjaga keamanan akun Anda.': 'Use a unique password to keep your account secure.',
        'KATA SANDI SAAT INI': 'CURRENT PASSWORD',
        'KATA SANDI BARU': 'NEW PASSWORD',
        'Mengganti kata sandi dapat mengakhiri sesi aktif di perangkat lain.':
            'Changing your password may end active sessions on other devices.',
        'Perbarui kata sandi': 'Update password',
        'SESI & AKSES': 'SESSION & ACCESS',
        'Selesai menggunakan akun ini? Keluar atau beralih ke akun lain.':
            'Done using this account? Sign out or switch accounts.',
        'Ganti akun': 'Switch account',

        // Admin Dashboard & Pages
        'Dashboard admin': 'Admin Dashboard',
        'Pantau antrean, aktivitas scan, akun, dan kesehatan sistem dari satu tempat.':
            'Monitor queues, scan activity, accounts, and system health in one place.',
        'Kirim notifikasi': 'Send notification',
        'Kirim notifikasi.': 'Send notification.',
        'Audit log': 'Audit log',
        'Status pemindaian': 'Scan status',
        'Buka scan log': 'Open scan log',
        'Status scan': 'Scan status',
        'Distribusi hasil': 'Result distribution',
        'Akun pengguna': 'User accounts',
        'Kelola →': 'Manage →',
        'Total akun terdaftar': 'Total registered accounts',
        'Belum ada data akun.': 'No account data yet.',
        'Antrean aktif': 'Active queue',
        'job menunggu worker': 'jobs waiting for worker',
        'Pantau antrean': 'Monitor queue',
        'Scan 30 hari terakhir': 'Scans in last 30 days',
        'Belum ada scan dalam 30 hari terakhir.': 'No scans in the last 30 days.',
        'Tanggal': 'Date',
        'Jumlah scan': 'Scan count',

        // Admin Scans
        'Pemantauan scan.': 'Scan monitor.',
        'Telusuri status, hasil, dan error dari seluruh pemindaian.': 'Browse statuses, results, and errors from all scans.',
        'Tidak ada scan yang cocok.': 'No matching scans.',
        'DAFTAR SCAN LOG': 'SCAN LOG LIST',
        'Hapus yang dipilih': 'Delete selected',
        'Pemilik': 'Owner',
        'Dibuat': 'Created',
        'Detail scan admin.': 'Admin scan details.',
        'Coba Ulang Scan': 'Retry Scan',
        'Audit lengkap proses pemeriksaan dan percobaannya.': 'Full audit of the inspection process and its attempts.',
        'Informasi scan': 'Scan information',
        'ID scan': 'Scan ID',
        'Skor model': 'Model score',
        'Versi model': 'Model version',
        'Kode error': 'Error code',
        'Pesan error': 'Error message',
        'Jumlah percobaan': 'Attempt count',
        'Daftar percobaan': 'Attempts list',
        'Belum ada percobaan.': 'No attempts yet.',
        'Pemicu': 'Trigger',
        'Mulai': 'Started',
        'Durasi': 'Duration',
        '← Daftar scan': '← Scan list',

        // Admin Users
        'Manajemen Pengguna': 'User Management',
        'Kelola akun, status, dan hak akses pengguna.': 'Manage user accounts, statuses, and permissions.',
        'CARI AKUN': 'SEARCH ACCOUNT',
        'Semua Role': 'All Roles',
        'Semua Status': 'All Statuses',
        'Nama': 'Name',
        'Email diverifikasi': 'Email verified',
        'Terdaftar': 'Registered',
        'Detail': 'Details',
        'Tidak ada pengguna ditemukan.': 'No users found.',
        '← Kembali ke Daftar Pengguna': '← Back to Users List',
        'PROFIL PENGGUNA': 'USER PROFILE',
        'Informasi Akun': 'Account Information',
        'Verifikasi email': 'Email verification',
        'Terverifikasi': 'Verified',
        'Belum diverifikasi': 'Unverified',
        'Belum': 'Not yet',
        'Sudah': 'Verified',
        'Bergabung': 'Joined',
        'Status saat ini': 'Current status',
        'KONTROL AKSES': 'ACCESS CONTROL',
        'Status, Role & Sesi': 'Status, Role & Sessions',
        'Ubah Status': 'Change Status',
        'Alasan (wajib jika Suspended)': 'Reason (required if Suspended)',
        'Simpan Status': 'Save Status',
        'Role Pengguna': 'User Role',
        'Ubah Role': 'Change Role',
        'Cabut Semua Sesi API (Force Logout)': 'Revoke All API Sessions (Force Logout)',
        'Akun milik sendiri tidak dapat diubah status/rolenya dari halaman admin ini.':
            'Your own account status/role cannot be modified from this admin page.',
        'AKTIVITAS PEMINDAIAN': 'SCAN ACTIVITY',
        'Scan Terbaru (10)': 'Recent Scans (10)',
        'Belum ada scan.': 'No scans yet.',

        // Admin Feedback, Notifications, Audit Logs, Settings
        'Feedback User.': 'User Feedback.',
        'Tinjau laporan salah klasifikasi yang dikirimkan setelah scan selesai.':
            'Review misclassification reports submitted after scans complete.',
        'Semua': 'All',
        'Belum ditinjau': 'Not reviewed',
        'Sudah ditinjau': 'Reviewed',
        'JENIS': 'TYPE',
        'Belum ada feedback untuk filter ini.': 'No feedback for this filter.',
        'Laporan feedback pengguna': 'User feedback reports',
        'Waktu': 'Time',
        'Komentar': 'Comment',
        'Tandai ditinjau': 'Mark reviewed',
        'Pesan masuk ke pusat notifikasi akun penerima. Aksi ini mengirim segera.':
            'Messages are delivered immediately to recipients\' notification centers.',
        'PENERIMA': 'RECIPIENTS',
        'Semua user': 'All users',
        'Semua admin': 'All admins',
        'Seluruh akun (User & Admin)': 'All accounts (Users & Admins)',
        'JUDUL': 'TITLE',
        'PESAN': 'MESSAGE',
        'Batal': 'Cancel',
        'Jejak audit sistem': 'System audit trail',
        'Catatan historis tindakan dan kejadian sistem.': 'Historical record of system actions and events.',
        'Semua Aksi': 'All Actions',
        'Status Akun Diubah': 'Account Status Changed',
        'Role Akun Diubah': 'Account Role Changed',
        'Scan Dicoba Ulang': 'Scan Retried',
        'Semua Tingkat (Severity)': 'All Severities',
        'Aktor': 'Actor',
        'Belum ada jejak audit.': 'No audit logs yet.',
        'ADMIN / SETELAN': 'ADMIN / SETTINGS',
        'Pengaturan Sistem & Status ML': 'System Settings & ML Status',
        'Konfigurasi batas aplikasi, retensi, timeout, dan kesehatan layanan Machine Learning.':
            'Configure application limits, retention, timeouts, and Machine Learning service health.',
        'Status Layanan ML': 'ML Service Status',
        'Koneksi ke ML Service (': 'Connection to ML Service (',
        'OFFLINE — Fallback Aktif': 'OFFLINE — Fallback Active',
        'ONLINE — Terhubung': 'ONLINE — Connected',
        'ONLINE — Siap': 'ONLINE — Ready',
        'ML service tidak dapat dijangkau.': 'ML service is unreachable.',
        'Sistem secara otomatis beralih ke': 'The system automatically switches to',
        'agar scan tetap berfungsi normal.': 'so scanning continues to function normally.',
        'Formulir Pengaturan': 'Settings Form',
        'Max Ukuran File Upload (MB)': 'Max File Upload Size (MB)',
        'Batas Waktu Total Scan (menit)': 'Total Scan Timeout (minutes)',
        'Retensi File di Karantina (hari)': 'Quarantine File Retention (days)',
        'Timeout HTTP ML Service (detik)': 'ML Service HTTP Timeout (seconds)',
        'Simpan Pengaturan': 'Save Settings',
    };

    const PLACEHOLDERS = {
        'Nama file atau ID': 'Filename or ID',
        'Komentar opsional': 'Optional comment',
        'https://contoh-situs.com/halaman': 'https://example-site.com/page',
        'Contoh: Pelanggaran TOS...': 'Example: TOS violation...',
        'Judul notifikasi...': 'Notification title...',
        'Tulis isi pesan notifikasi di sini...': 'Write notification message here...',
        'Nama atau email...': 'Name or email...',
    };

    const textOrig = new WeakMap();
    const phOrig = new WeakMap();

    function getLang() {
        return localStorage.getItem(STORAGE_KEY) === 'en' ? 'en' : 'id';
    }

    function translateString(str, lang) {
        if (lang !== 'en') return str;
        const trimmed = str.trim();
        if (!trimmed) return str;

        if (EXACT[trimmed]) {
            return str.replace(trimmed, EXACT[trimmed]);
        }

        // Dynamic patterns
        if (trimmed.startsWith('Halo, ')) {
            return str.replace('Halo, ', 'Hello, ');
        }
        const selMatch = trimmed.match(/^(\d+)\s+dipilih$/);
        if (selMatch) {
            return str.replace(trimmed, `${selMatch[1]} selected`);
        }
        const kodeMatch = trimmed.match(/^(\d+)\s+kode$/);
        if (kodeMatch) {
            return str.replace(trimmed, `${kodeMatch[1]} codes`);
        }
        const pageMatch = trimmed.match(/^Halaman\s+(\d+)\s+dari\s+(\d+)$/);
        if (pageMatch) {
            return str.replace(trimmed, `Page ${pageMatch[1]} of ${pageMatch[2]}`);
        }
        if (trimmed.startsWith('Alasan: ')) {
            return str.replace('Alasan: ', 'Reason: ');
        }
        if (trimmed.startsWith('Sudah (')) {
            return str.replace('Sudah (', 'Verified (');
        }
        if (trimmed.startsWith('Koneksi ke ML Service (')) {
            return str.replace('Koneksi ke ML Service (', 'Connection to ML Service (');
        }
        if (trimmed.startsWith('Ukuran: ') && trimmed.includes('Klik untuk ganti file')) {
            return str.replace('Ukuran: ', 'Size: ').replace('Klik untuk ganti file', 'Click to change file');
        }
        return str;
    }

    function apply(lang = getLang()) {
        document.documentElement.lang = lang;

        const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, {
            acceptNode(node) {
                const parent = node.parentElement;
                if (!parent) return NodeFilter.FILTER_REJECT;
                const tag = parent.tagName;
                if (tag === 'SCRIPT' || tag === 'STYLE' || tag === 'NOSCRIPT' || tag === 'CODE') {
                    return NodeFilter.FILTER_REJECT;
                }
                if (parent.classList.contains('material-symbols-outlined')) {
                    return NodeFilter.FILTER_REJECT;
                }
                return node.nodeValue && node.nodeValue.trim() ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT;
            }
        });

        let node;
        while ((node = walker.nextNode())) {
            if (!textOrig.has(node)) {
                textOrig.set(node, node.nodeValue);
            }
            const curTrim = node.nodeValue.trim();
            if (EXACT[curTrim]) {
                textOrig.set(node, node.nodeValue);
            }
            const base = textOrig.get(node);
            const next = lang === 'en' ? translateString(base, 'en') : base;
            if (node.nodeValue !== next) {
                node.nodeValue = next;
            }
        }

        document.querySelectorAll('input[placeholder], textarea[placeholder]').forEach((el) => {
            if (!phOrig.has(el)) {
                phOrig.set(el, el.getAttribute('placeholder'));
            }
            const base = phOrig.get(el);
            if (lang === 'en' && PLACEHOLDERS[base]) {
                el.setAttribute('placeholder', PLACEHOLDERS[base]);
            } else if (base !== null) {
                el.setAttribute('placeholder', base);
            }
        });

        document.querySelectorAll('[data-lang-code]').forEach((el) => {
            el.textContent = lang.toUpperCase();
        });
        document.querySelectorAll('[data-lang-label]').forEach((el) => {
            el.textContent = lang === 'en' ? 'Language: English (EN)' : 'Bahasa: Indonesia (ID)';
        });
        document.querySelectorAll('[data-lang-toggle]').forEach((btn) => {
            btn.setAttribute('aria-pressed', lang === 'en' ? 'true' : 'false');
            btn.setAttribute('title', lang === 'en' ? 'Ganti ke Bahasa Indonesia' : 'Switch to English');
        });
    }

    function setLang(lang) {
        const normalized = lang === 'en' ? 'en' : 'id';
        localStorage.setItem(STORAGE_KEY, normalized);
        apply(normalized);
    }

    function toggle() {
        setLang(getLang() === 'en' ? 'id' : 'en');
    }

    window.StiksafeI18n = { getLang, setLang, toggle, apply };

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-lang-toggle]');
        if (!btn) return;
        toggle();
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => apply());
    } else {
        apply();
    }
})();
