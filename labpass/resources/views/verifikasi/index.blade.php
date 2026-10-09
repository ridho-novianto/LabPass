<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LabPass - Portal Verifikasi Keselamatan & APD Laboratorium</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col justify-between selection:bg-emerald-500 selection:text-white"
      x-data="verifikasiApp()">

    <!-- Clean White Navbar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 flex items-center justify-center font-bold text-white shadow-sm shadow-emerald-600/30">
                    LP
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-base font-extrabold text-slate-900 tracking-tight">LabPass</span>
                        <span class="text-[11px] font-semibold uppercase px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Safety Gate
                        </span>
                    </div>
                    <p class="text-xs text-slate-500">Verifikasi APD & Akses Laboratorium</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('login') }}" class="text-xs font-semibold px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 transition">
                    Login Dosen & Aslab &rarr;
                </a>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 py-8 flex-1 w-full">
        
        <!-- Header Title Banner -->
        <div class="mb-8 text-center max-w-xl mx-auto">
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200 inline-block mb-2">
                Pemeriksaan APD K3L
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                Verifikasi Masuk Laboratorium
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 mt-2">
                Silakan isi data diri Anda dan arahkan badan ke kamera. Sistem akan memvalidasi kepatuhan APD (Jas Lab, Sarung Tangan, Kacamata, Masker).
            </p>
        </div>

        <!-- Main Card Form -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
            
            <!-- 1. Data Diri Mahasiswa -->
            <div>
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wide flex items-center gap-2 mb-4">
                    <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs">1</span>
                    Identitas Praktikan
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor Induk Mahasiswa (NIM)</label>
                        <input type="text" x-model="nim" required placeholder="Contoh: 3122500012"
                               class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Lengkap Mahasiswa</label>
                        <input type="text" x-model="nama" required placeholder="Contoh: Muhammad Ridho"
                               class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>
            </div>

            <hr class="border-slate-100">

            <!-- 2. Foto & Kamera Scan APD -->
            <div>
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wide flex items-center gap-2 mb-4">
                    <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs">2</span>
                    Verifikasi APD
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
                    
                    <!-- Viewport / Kamera Area -->
                    <div class="md:col-span-7 bg-slate-900 rounded-2xl aspect-[4/3] relative overflow-hidden flex flex-col items-center justify-center shadow-inner">
                        <video id="webcam" autoplay playsinline class="w-full h-full object-cover" :class="cameraOn ? '' : 'hidden'"></video>
                        <canvas id="canvas" class="hidden"></canvas>
                        
                        <!-- Snapshot preview if captured -->
                        <img :src="capturedImage" x-show="capturedImage && !cameraOn" class="w-full h-full object-cover" />

                        <!-- Placeholder state -->
                        <div x-show="!cameraOn && !capturedImage" class="text-center p-6 text-slate-300">
                            <div class="w-14 h-14 rounded-2xl bg-slate-800 flex items-center justify-center mx-auto mb-3 text-slate-400">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            </div>
                            <p class="text-sm font-bold text-white">Kamera Standby</p>
                            <p class="text-xs text-slate-400 mt-1 max-w-xs">Gunakan webcam browser untuk scan langsung atau gunakan opsi unggah foto.</p>
                            <button type="button" @click="startCamera()" class="mt-4 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition shadow">
                                Aktifkan Kamera Webcam
                            </button>
                        </div>

                        <!-- Processing overlay -->
                        <div x-show="isProcessing" class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm flex flex-col items-center justify-center text-white z-20">
                            <div class="w-10 h-10 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin mb-3"></div>
                            <p class="text-sm font-bold">Menjalankan Model YOLOv8...</p>
                            <p class="text-xs text-emerald-300 mt-1 font-mono">Menganalisis Bounding Box APD</p>
                        </div>
                    </div>

                    <!-- Action Controls & Checklist -->
                    <div class="md:col-span-5 space-y-4">
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wide mb-2.5">
                                Kriteria Verifikasi:
                            </h3>
                            <ul class="text-xs space-y-2 text-slate-700">
                                <li class="flex items-center gap-2"><span class="text-emerald-600 font-bold">✓</span> Jas Laboratorium</li>
                                <li class="flex items-center gap-2"><span class="text-emerald-600 font-bold">✓</span> Kacamata Pelindung</li>
                                <li class="flex items-center gap-2"><span class="text-emerald-600 font-bold">✓</span> Sarung Tangan</li>
                                <li class="flex items-center gap-2"><span class="text-emerald-600 font-bold">✓</span> Masker Wajah</li>
                            </ul>
                        </div>

                        <!-- Camera Action buttons -->
                        <div class="space-y-2">
                            <button type="button" x-show="cameraOn" @click="takeSnapshot()"
                                    class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-2.5 px-4 rounded-xl text-xs transition shadow-sm flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path></svg>
                                Ambil Foto dari Kamera
                            </button>

                            <div class="pt-1">
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Atau Unggah Foto File:</label>
                                <input type="file" @change="handleFileUpload($event)" accept="image/*"
                                       class="block w-full text-xs text-slate-600 file:mr-2.5 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                            </div>

                            <button type="button" @click="useSampleTest()" class="text-[11px] text-emerald-700 font-semibold hover:underline block pt-1">
                                [Opsi Cepat]: Gunakan Foto Demo Sample (Sample APD)
                            </button>
                        </div>

                        <!-- Main Submit Button -->
                        <div class="pt-3">
                            <button type="button" @click="submitScan()" :disabled="isProcessing || (!capturedImage && !fileUpload && !nim)"
                                    class="w-full bg-slate-900 hover:bg-slate-800 disabled:opacity-50 text-white font-extrabold py-3.5 px-4 rounded-xl text-sm transition shadow-md flex items-center justify-center gap-2">
                                <span>Verifikasi & Kirim ke Dosen &rarr;</span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>

        </div>

    </main>

    <!-- Clean Footer -->
    <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500">
        <p>LabPass &copy; 2026 — Sistem Verifikasi APD</p>
    </footer>

    <script>
        function verifikasiApp() {
            return {
                nim: '3122500012',
                nama: 'Muhammad Ridho',
                cameraOn: false,
                capturedImage: null,
                fileUpload: null,
                isProcessing: false,

                startCamera() {
                    const video = document.getElementById('webcam');
                    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                        navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480 } })
                            .then(stream => {
                                video.srcObject = stream;
                                this.cameraOn = true;
                            })
                            .catch(err => {
                                alert("Akses kamera tidak diizinkan atau tidak ditemukan.");
                            });
                    }
                },

                takeSnapshot() {
                    const video = document.getElementById('webcam');
                    const canvas = document.getElementById('canvas');
                    canvas.width = video.videoWidth || 640;
                    canvas.height = video.videoHeight || 480;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                    this.capturedImage = canvas.toDataURL('image/jpeg', 0.9);
                    this.cameraOn = false;
                },

                handleFileUpload(e) {
                    const file = e.target.files[0];
                    if (file) {
                        this.fileUpload = file;
                        const reader = new FileReader();
                        reader.onload = (event) => {
                            this.capturedImage = event.target.result;
                            this.cameraOn = false;
                        };
                        reader.readAsDataURL(file);
                    }
                },

                useSampleTest() {
                    this.capturedImage = '/storage/scans/sample_test.jpg';
                    this.fileUpload = null;
                },

                async submitScan() {
                    if (!this.nim.trim() || !this.nama.trim()) {
                        alert("Harap isi NIM dan Nama terlebih dahulu.");
                        return;
                    }

                    this.isProcessing = true;
                    const formData = new FormData();
                    formData.append('nim', this.nim);
                    formData.append('nama', this.nama);
                    formData.append('_token', '{{ csrf_token() }}');

                    if (this.fileUpload) {
                        formData.append('foto', this.fileUpload);
                    } else if (this.capturedImage && this.capturedImage.startsWith('data:image')) {
                        formData.append('webcam_image', this.capturedImage);
                    }

                    try {
                        const res = await fetch("{{ route('verifikasi.scan', [], false) }}", {
                            method: "POST",
                            body: formData
                        });
                        if (!res.ok) {
                            const errText = await res.text();
                            console.error("HTTP error scan:", res.status, errText);
                            throw new Error("HTTP " + res.status);
                        }
                        const data = await res.json();
                        this.isProcessing = false;
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            alert("Pemindaian selesai namun tidak ada respons pengalihan.");
                        }
                    } catch (e) {
                        this.isProcessing = false;
                        console.error("Scan error detail:", e);
                        alert("Gagal memproses pemindaian APD. Detail: " + (e.message || "Periksa koneksi"));
                    }
                }
            }
        }
    </script>
</body>
</html>
