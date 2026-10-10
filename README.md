# LabPass - Gate Keamanan & Akses Laboratorium Pintar Berbasis AI

> Project Hackathon: Sistem otomatisasi verifikasi APD, briefing bahaya K3L berbasis LLM, dan validasi pembuangan limbah laboratorium.

---

## Tentang Project Ini

Masalah klasik di laboratorium praktikum kampus:
1. **Pemeriksaan APD manual sering lolos**: Aslab atau dosen gak selalu sempat ngecek satu per satu apakah tiap mahasiswa beneran pakai jas lab, sarung tangan, masker, dan kacamata safety dengan benar.
2. **Mahasiswa jarang baca modul K3L**: Mahasiswa sering langsung masuk praktikum tanpa paham sifat bahaya bahan kimia yang dipakai (misal reagen korosif asam/basa kuat).
3. **Limbah praktikum dibuang sembarangan**: Sisa cairan kimia sering asal tuang ke wastafel atau salah jerigen penampungan, yang bikin bahaya fatal bagi lingkungan dan fasilitas lab.

**LabPass** hadir sebagai "safety gatekeeper" terpadu:
- Mahasiswa scan APD di kamera kios (deteksi YOLOv8 real-time).
- Dosen/aslab memantau antrean dan acc akses dari dashboard.
- Mahasiswa dapat ringkasan otomatis bahaya K3L & panduan limbah dari AI (LiteLLM Qwen Flash).
- Sebelum sesi selesai, mahasiswa wajib verifikasi pembuangan limbah ke jerigen yang sesuai kodenya.

---

## Fitur Utama

### 1. Kios Scan APD (Computer Vision YOLOv8)
- Input NIM dan nama, lalu sistem ambil foto lewat webcam atau upload foto.
- Model YOLOv8 mendeteksi kelengkapan 4 elemen APD: **Jas Lab**, **Sarung Tangan**, **Kacamata Pelindung**, dan **Masker**.
- Menghasilkan visualisasi bounding box dan checklist hasil deteksi secara instan.

### 2. Panel Dosen & Aslab (Real-time Gate Approval)
- Antrean verifikasi masuk secara live.
- Dosen bisa melihat foto bukti scan mahasiswa beserta status deteksinya sebelum memberi izin (ACC/Tolak).
- Manajemen data materi praktikum, upload modul PDF, jadwal sesi, dan inventaris jerigen limbah.

### 3. AI Auto-Summary Modul Praktikum (LiteLLM Qwen Flash)
- Dosen cukup upload modul PDF (misal: *Titrasi Asam Basa*).
- AI otomatis membedah modul dan menghasilkan:
  - Ringkasan eksekutif praktikum.
  - Poin kritis bahaya bahan kimia (K3L).
  - Rekomendasi APD spesifik.
  - Prosedur alur penanganan limbah cairan.
- Hasil ringkasan tampil di layar praktikan tanpa perlu buka dokumen panjang berlembar-lembar.

### 4. Safe Waste Disposal Gate (Checkout Limbah)
- Praktikum belum bisa selesai kalau mahasiswa belum konfirmasi pembuangan sisa reagen ke jerigen yang tepat (kode jerigen, jenis limbah, dan volume).
- Mencegah kontaminasi silang dan pembuangan liar ke wastafel.

### 5. Metrik Pemantauan Kuota Token AI
- Monitoring pemakaian token tim secara transparan langsung di dashboard admin.
- Melacak token masuk (prompt), token keluar (completion), sisa alokasi kuota 10.000.000 token, dan efisiensi cache sistem.

---

## Tech Stack

- **Backend**: Laravel 12 (PHP 8.3)
- **Database**: MySQL
- **Computer Vision**: Python 3.12, Ultralytics YOLOv8, OpenCV, PyTorch
- **Generative AI**: LiteLLM API Hackathon (`qwen3-coder-flash` / `qwen3.8-flash`)
- **Frontend**: Blade Templating, Tailwind CSS, Alpine.js
- **Reverse Proxy / Hosting Demo**: Cloudflare Tunnel & Apache2

---

## Struktur Folder

```text
LabPass/
├── best.pt                    # Weights model YOLOv8 deteksi APD
├── inference_checklist.py     # Script Python inference YOLOv8
├── sample_test.jpg            # Sample gambar demo untuk uji coba cepat
├── labpass/                   # Source code utama Laravel 12
│   ├── app/
│   │   ├── Http/Controllers/  # Controller (Verifikasi, Admin, Kios, Praktikum)
│   │   ├── Models/            # Model Eloquent (Kehadiran, Materi, Sesi, Limbah, dll)
│   │   └── Services/          # Service AI (RisikoEvaluator.php)
│   ├── database/migrations/   # Skema database
│   ├── resources/views/       # Tampilan antarmuka Blade
│   └── routes/web.php         # Rute aplikasi
└── README.md
```

---

## Cara Menjalankan Project

### 1. Kloning Repositori
```bash
git clone https://github.com/ridho-novianto/LabPass.git
cd LabPass
```

### 2. Setup Backend Python (YOLOv8)
```bash
# Buat virtual environment
python3 -m venv venv
source venv/bin/activate

# Install dependensi
pip install ultralytics torch opencv-python-headless
```

### 3. Setup Laravel
```bash
cd labpass

# Install dependensi PHP
composer install

# Salin konfigurasi environment
cp .env.example .env

# Generate APP_KEY
php artisan key:generate

# Buat symbolic link storage
php artisan storage:link
```

### 4. Konfigurasi `.env`
Buka file `labpass/.env` dan sesuaikan database serta konfigurasi AI:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=labpass
DB_USERNAME=root
DB_PASSWORD=

# Konfigurasi LLM API
LLM_BASE_URL=https://litellm-hackathon.digdaya.ai/v1
LLM_API_KEY=your_api_key_here
LLM_MODEL=qwen3-coder-flash
```

> **Tips Model**: Untuk ganti model AI, cukup ubah nilai `LLM_MODEL` (pilihan: `qwen3-coder-flash`, `qwen3.8-flash`, `deepseek-v4.1-flash`) lalu jalankan `php artisan config:clear`.

### 5. Migrasi & Seed Database
```bash
php artisan migrate --seed
```

### 6. Jalankan Server
```bash
# Terminal 1: Laravel Web Server
php artisan serve

# (Opsional jika menggunakan Cloudflare Tunnel untuk demo hosting)
cloudflared tunnel run
```

Akses web di browser:
- **Kios Scan Mahasiswa**: `http://localhost:8000/` atau `/verifikasi`
- **Dashboard Admin / Dosen**: `http://localhost:8000/admin` (Akun demo: cek seeder `UserSeeder.php`)

---

## Catatan Teknis & Keamanan

- **Domain / Cloudflare Tunnel**: Konfigurasi proxy di `bootstrap/app.php` sudah diset `trustProxies(at: '*')` dan skema otomatis menyesuaikan HTTPS header, sehingga tidak akan terkena isu *Mixed Content* saat diakses via tunnel publik.
- **API Key Guard**: Kunci API LLM tersimpan eksklusif di dalam file `.env` lokal dan telah dimasukkan ke dalam `.gitignore` agar tidak bocor ke repositori publik.
- **Token Optimization**: Hasil rangkuman modul AI disimpan sementara di cache Laravel untuk menghemat kuota token tim ketika modul yang sama diakses berulang kali.
