<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LabPass AI - Terminal Kios Keselamatan Laboratorium</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col justify-between selection:bg-emerald-500 selection:text-black"
      x-data="kiosApp()">

    <!-- Industrial Top Bar -->
    <header class="border-b border-slate-800 bg-slate-900/90 backdrop-blur sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-emerald-600 flex items-center justify-center font-black text-slate-950 text-base tracking-tighter shadow">
                    LP
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-bold text-white tracking-tight">LabPass</span>
                        <span class="text-[10px] uppercase font-mono px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Kios</span>
                    </div>
                    <p class="text-[11px] text-slate-400">Verifikasi APD & Akses Laboratorium</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="hidden sm:flex items-center gap-2 px-3 py-1 rounded-md bg-slate-950 border border-slate-800 text-xs font-mono text-slate-300">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>ONLINE • LAB-01</span>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="text-xs px-3 py-1.5 rounded-lg border border-slate-700 bg-slate-800 hover:bg-slate-700 text-slate-200 transition font-medium">
                    Panel Dosen &rarr;
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="max-w-5xl mx-auto px-4 py-8 flex-1 w-full">
        <!-- Progress Steps -->
        <div class="mb-8">
            <div class="grid grid-cols-3 gap-2 sm:gap-4 text-center">
                <div class="py-2.5 px-3 rounded-lg border transition-all text-xs font-semibold"
                     :class="step === 1 ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-400' : 'bg-slate-900 border-slate-800 text-slate-400'">
                    1. Identitas & Sesi
                </div>
                <div class="py-2.5 px-3 rounded-lg border transition-all text-xs font-semibold"
                     :class="step === 2 ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-400' : 'bg-slate-900 border-slate-800 text-slate-400'">
                    2. Scan APD Kamera
                </div>
                <div class="py-2.5 px-3 rounded-lg border transition-all text-xs font-semibold"
                     :class="step === 3 ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-400' : 'bg-slate-900 border-slate-800 text-slate-400'">
                    3. Tiket & Akses
                </div>
            </div>
        </div>

        <!-- STEP 1: FORM IDENTITAS & PILIH SESI -->
        <div x-show="step === 1" x-transition class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 max-w-2xl mx-auto shadow-xl">
            <div class="mb-6">
                <h2 class="text-lg font-bold text-white">Check-in Mahasiswa Praktikum</h2>
                <p class="text-xs text-slate-400 mt-1">Masukkan identitas Anda dan pilih sesi praktikum aktif hari ini yang telah diatur oleh Dosen.</p>
            </div>

            <form @submit.prevent="goToStep2" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Nomor Induk Mahasiswa (NIM)</label>
                    <input type="text" x-model="form.nim" required placeholder="Contoh: 3122500012"
                           class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-emerald-500 font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Nama Lengkap Mahasiswa</label>
                    <input type="text" x-model="form.nama" required placeholder="Contoh: Muhammad Ridho"
                           class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Pilih Sesi Praktikum Hari Ini</label>
                    <select x-model="form.sesi_id" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-emerald-500">
                        <option value="">-- Pilih Sesi Praktikum Aktif --</option>
                        @foreach($sesiList as $sesi)
                            <option value="{{ $sesi->id }}">
                                {{ $sesi->materi->judul ?? 'Praktikum' }} — Kelas: {{ $sesi->kelas->nama ?? '-' }} ({{ substr($sesi->jam_mulai,0,5) }} - {{ substr($sesi->jam_selesai,0,5) }} WIB)
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1.5">
                        * Materi, takaran bahan kimia, serta instruksi jerigen limbah akan otomatis disinkronkan sesuai sesi yang Anda pilih.
                    </p>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold py-3 px-4 rounded-xl text-sm transition shadow-lg shadow-emerald-500/20 flex items-center justify-center gap-2">
                        <span>Lanjut ke Verifikasi APD</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>
            </form>
        </div>

        <!-- STEP 2: WEBCAM SCAN APD -->
        <div x-show="step === 2" x-transition class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 max-w-3xl mx-auto shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-bold text-white">Verifikasi APD</h2>
                    <p class="text-xs text-slate-400">Posisikan tubuh Anda di depan kamera untuk verifikasi kepatuhan APD.</p>
                </div>
                <button @click="step = 1" class="text-xs text-slate-400 hover:text-white underline">
                    &larr; Ubah Identitas
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                <!-- Camera View -->
                <div class="md:col-span-7 bg-slate-950 rounded-xl border border-slate-800 aspect-video relative overflow-hidden flex flex-col items-center justify-center">
                    <video id="webcamVideo" autoplay playsinline class="w-full h-full object-cover" :class="cameraActive ? '' : 'hidden'"></video>
                    
                    <div x-show="!cameraActive" class="text-center p-4">
                        <div class="w-12 h-12 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center mx-auto mb-2 text-slate-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        </div>
                        <p class="text-xs text-slate-400">Kamera Standby</p>
                        <button type="button" @click="startCamera()" class="mt-2 text-xs px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-emerald-400 font-medium border border-slate-700">
                            Nyalakan Webcam
                        </button>
                    </div>

                    <!-- Scan overlay animation -->
                    <div x-show="scanning" class="absolute inset-0 bg-emerald-500/10 flex items-center justify-center pointer-events-none">
                        <div class="w-full h-1 bg-emerald-400 absolute top-0 animate-bounce"></div>
                        <span class="bg-slate-900/90 text-emerald-400 px-3 py-1 rounded-full text-xs font-mono font-bold border border-emerald-500/30">
                            MEMINDAI...
                        </span>
                    </div>
                </div>

                <!-- APD Checklist & Simulation Switch -->
                <div class="md:col-span-5 space-y-4">
                    <div class="bg-slate-950 p-4 rounded-xl border border-slate-800">
                        <h3 class="text-xs font-bold text-slate-200 uppercase tracking-wider mb-2">Standar APD Wajib</h3>
                        <ul class="text-xs space-y-2 text-slate-300">
                            <li class="flex items-center gap-2"><span class="text-emerald-400">✓</span> Jas Laboratorium</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-400">✓</span> Masker</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-400">✓</span> Kacamata</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-400">✓</span> Sarung Tangan</li>
                        </ul>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[11px] font-semibold text-slate-400 uppercase">Uji Coba:</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" @click="form.mode_apd = 'lengkap'"
                                    class="py-2 px-2.5 rounded-lg border text-xs font-medium text-center transition"
                                    :class="form.mode_apd === 'lengkap' ? 'bg-emerald-500/20 border-emerald-500 text-emerald-300' : 'bg-slate-950 border-slate-800 text-slate-400'">
                                Lengkap
                            </button>
                            <button type="button" @click="form.mode_apd = 'kurang'"
                                    class="py-2 px-2.5 rounded-lg border text-xs font-medium text-center transition"
                                    :class="form.mode_apd === 'kurang' ? 'bg-amber-500/20 border-amber-500 text-amber-300' : 'bg-slate-950 border-slate-800 text-slate-400'">
                                Kurang
                            </button>
                        </div>
                    </div>

                    <button type="button" @click="runScan()" :disabled="scanning"
                            class="w-full bg-emerald-500 hover:bg-emerald-400 disabled:opacity-50 text-slate-950 font-bold py-3 px-4 rounded-xl text-sm transition shadow-lg shadow-emerald-500/20 flex items-center justify-center gap-2">
                        <span x-text="scanning ? 'Menganalisis...' : 'Ambil Foto & Verifikasi'"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- STEP 3: HASIL VERIFIKASI -->
        <div x-show="step === 3" x-transition class="max-w-xl mx-auto">
            
            <!-- CASE 1: LULUS (ACCESS GRANTED) -->
            <div x-show="result.status === 'lulus'" class="bg-slate-900 border border-emerald-500/40 rounded-2xl p-6 sm:p-8 text-center shadow-2xl">
                <div class="w-16 h-16 rounded-full bg-emerald-500/10 border border-emerald-500/40 flex items-center justify-center mx-auto mb-4 text-emerald-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <span class="text-xs font-mono font-bold px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                    VERIFIKASI LOLOS • ACCESS GRANTED
                </span>
                <h2 class="text-xl font-extrabold text-white mt-3" x-text="form.nama"></h2>
                <p class="text-xs text-slate-400 font-mono mt-1">NIM: <span x-text="form.nim"></span></p>

                <div class="my-6 p-4 rounded-xl bg-slate-950 border border-slate-800 text-left">
                    <h4 class="text-xs font-bold text-slate-300 uppercase mb-2">Hasil Deteksi APD:</h4>
                    <div class="flex flex-wrap gap-1.5">
                        <template x-for="apd in result.terdeteksi" :key="apd">
                            <span class="text-xs bg-emerald-950/60 border border-emerald-600/40 text-emerald-300 px-2.5 py-1 rounded-md font-medium">
                                ✓ <span x-text="apd"></span>
                            </span>
                        </template>
                    </div>
                </div>

                <a :href="result.redirect" class="w-full bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold py-3 px-4 rounded-xl text-sm transition shadow-lg shadow-emerald-500/25 flex items-center justify-center gap-2">
                    <span>Masuk ke Halaman Praktikum & Bahan &rarr;</span>
                </a>
            </div>

            <!-- CASE 2: DITOLAK / KURANG APD (MINTA DISPENSASI) -->
            <div x-show="result.status === 'ditolak' || result.status === 'dispensasi_diajukan'" class="bg-slate-900 border border-amber-500/40 rounded-2xl p-6 sm:p-8 shadow-2xl">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 rounded-full bg-amber-500/10 border border-amber-500/40 flex items-center justify-center mx-auto mb-4 text-amber-400">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <span class="text-xs font-mono font-bold px-2.5 py-1 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/30">
                        APD BELUM LENGKAP
                    </span>
                    <h2 class="text-lg font-bold text-white mt-2">Akses Masuk Ditahan</h2>
                    <p class="text-xs text-slate-400 mt-1">Anda tidak dapat masuk ke laboratorium sebelum APD lengkap atau disetujui Dosen.</p>
                </div>

                <!-- Missing APD items -->
                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 mb-6">
                    <h4 class="text-xs font-bold text-rose-400 uppercase mb-2">APD Yang Belum Terdeteksi:</h4>
                    <div class="flex flex-wrap gap-1.5">
                        <template x-for="k in result.kurang" :key="k">
                            <span class="text-xs bg-rose-950/60 border border-rose-600/40 text-rose-300 px-2.5 py-1 rounded-md font-semibold">
                                ✗ <span x-text="k"></span>
                            </span>
                        </template>
                    </div>
                </div>

                <!-- Form Dispensasi -->
                <div x-show="result.status === 'ditolak'">
                    <h3 class="text-sm font-bold text-white mb-2">Ajukan Permohonan Dispensasi ke Dosen</h3>
                    <p class="text-xs text-slate-400 mb-3">Tulis alasan Anda. Permohonan akan langsung masuk ke panel Dosen untuk di-ACC secara manual.</p>
                    
                    <textarea x-model="alasanDispensasi" rows="3" placeholder="Contoh: Kacamata safety tertinggal di loker, meminjam kacamata cadangan aslab..."
                              class="w-full bg-slate-950 border border-slate-700 rounded-xl p-3 text-xs text-white focus:outline-none focus:border-amber-500"></textarea>

                    <div class="flex gap-2 mt-4">
                        <button type="button" @click="step = 2" class="px-4 py-2.5 rounded-xl border border-slate-700 text-xs text-slate-300 hover:bg-slate-800">
                            Scan Ulang
                        </button>
                        <button type="button" @click="kirimDispensasi()" :disabled="!alasanDispensasi.trim()"
                                class="flex-1 bg-amber-500 hover:bg-amber-400 disabled:opacity-50 text-slate-950 font-bold py-2.5 px-4 rounded-xl text-xs transition">
                            Kirim ke Dosen Pengampu &rarr;
                        </button>
                    </div>
                </div>

                <!-- Waiting ACC status -->
                <div x-show="result.status === 'dispensasi_diajukan'" class="text-center p-4 bg-slate-950 rounded-xl border border-amber-500/30">
                    <div class="inline-block w-6 h-6 border-2 border-amber-400 border-t-transparent rounded-full animate-spin mb-2"></div>
                    <h4 class="text-sm font-bold text-white">Menunggu Persetujuan Dosen...</h4>
                    <p class="text-xs text-slate-400 mt-1">Halaman ini otomatis membuka materi setelah Dosen menekan tombol ACC di dashboard.</p>
                    <p class="text-[11px] font-mono text-amber-400 mt-2">Polling status aktif setiap 3 detik</p>
                </div>
            </div>

        </div>
    </main>

    <!-- Industrial Footer -->
    <footer class="border-t border-slate-800 py-4 bg-slate-950 text-center text-xs text-slate-500">
        <p>LabPass &copy; 2026 — Sistem Verifikasi APD</p>
    </footer>

    <script>
        function kiosApp() {
            return {
                step: 1,
                cameraActive: false,
                scanning: false,
                form: {
                    nim: '3122500012',
                    nama: 'Muhammad Ridho',
                    sesi_id: '{{ $sesiList->first()->id ?? 1 }}',
                    mode_apd: 'lengkap'
                },
                result: {
                    status: '',
                    token: '',
                    terdeteksi: [],
                    kurang: [],
                    redirect: null
                },
                alasanDispensasi: '',
                pollingTimer: null,

                goToStep2() {
                    this.step = 2;
                    this.startCamera();
                },

                startCamera() {
                    const video = document.getElementById('webcamVideo');
                    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                        navigator.mediaDevices.getUserMedia({ video: true })
                            .then(stream => {
                                video.srcObject = stream;
                                this.cameraActive = true;
                            })
                            .catch(err => {
                                console.log('Camera access fallback simulation');
                                this.cameraActive = false;
                            });
                    }
                },

                async runScan() {
                    this.scanning = true;
                    try {
                        const response = await fetch("{{ route('kios.verifikasi', [], false) }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify(this.form)
                        });
                        const data = await response.json();
                        this.result = data;
                        this.scanning = false;
                        this.step = 3;

                        if (data.status === 'lulus' && data.redirect) {
                            setTimeout(() => {
                                window.location.href = data.redirect;
                            }, 1800);
                        }
                    } catch (e) {
                        this.scanning = false;
                        alert("Terjadi kesalahan koneksi server.");
                    }
                },

                async kirimDispensasi() {
                    try {
                        const res = await fetch("{{ route('kios.minta-dispensasi', [], false) }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                token: this.result.token,
                                alasan: this.alasanDispensasi
                            })
                        });
                        const data = await res.json();
                        this.result.status = 'dispensasi_diajukan';

                        // Start Polling every 3s
                        this.pollingTimer = setInterval(async () => {
                            const pollRes = await fetch(`/kios/cek-status/${this.result.token}`);
                            const pollData = await pollRes.json();
                            if (pollData.status === 'dispensasi_disetujui' && pollData.redirect) {
                                clearInterval(this.pollingTimer);
                                window.location.href = pollData.redirect;
                            }
                        }, 3000);
                    } catch (e) {
                        alert("Gagal mengirim dispensasi.");
                    }
                }
            }
        }
    </script>
</body>
</html>
