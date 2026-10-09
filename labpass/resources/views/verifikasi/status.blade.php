<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Verifikasi - LabPass</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col justify-between"
      x-data="statusApp()">

    <!-- Clean White Navbar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 flex items-center justify-center font-bold text-white shadow-sm">
                    LP
                </div>
                <div>
                    <span class="text-base font-extrabold text-slate-900 tracking-tight">LabPass</span>
                    <p class="text-xs text-slate-500">Status Verifikasi APD</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('verifikasi.index', [], false) }}" class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 transition">
                    &larr; Scan Ulang
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-4 py-8 flex-1 w-full space-y-6">
        
        <!-- Student Header Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <span class="text-xs font-mono font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200">
                        TOKEN: {{ substr($kehadiran->token, 0, 10) }}...
                    </span>
                    <h1 class="text-xl font-bold text-slate-900 mt-2">{{ $kehadiran->mahasiswa->nama }}</h1>
                    <p class="text-xs text-slate-500 font-mono">NIM: {{ $kehadiran->mahasiswa->nim }}</p>
                </div>
                <div class="text-right">
                    <span class="text-xs text-slate-400 block">Waktu Pemindaian:</span>
                    <span class="text-xs font-mono font-bold text-slate-700">{{ $kehadiran->created_at->format('H:i:s d M Y') }}</span>
                </div>
            </div>

            <!-- YOLO Detection Result Banner & Photo -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
                
                <!-- Bounding Box Image from YOLO -->
                <div class="md:col-span-5 bg-slate-100 rounded-xl border border-slate-200 overflow-hidden">
                    <span class="block text-[11px] font-bold text-slate-600 p-2.5 bg-slate-50 border-b border-slate-200">
                        Foto Anotasi Bounding Box YOLO:
                    </span>
                    <img src="{{ $kehadiran->snapshot_path }}" alt="Hasil Scan APD" class="w-full h-auto object-cover" />
                </div>

                <!-- Checklist APD Items -->
                <div class="md:col-span-7 space-y-3">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Hasil Evaluasi Sensor K3L:</h3>
                    
                    <div class="space-y-2">
                        @php
                            $detectedList = $kehadiran->apd_terdeteksi ?? [];
                            $checkApdUser = function($key) use ($detectedList) {
                                if (!is_array($detectedList)) return false;
                                foreach ($detectedList as $val) {
                                    $norm = strtolower(str_replace(['_', '-'], ' ', (string)$val));
                                    if ($key === 'jas_lab' && str_contains($norm, 'jas')) return true;
                                    if ($key === 'masker' && str_contains($norm, 'mask')) return true;
                                    if ($key === 'kacamata' && (str_contains($norm, 'kacamata') || str_contains($norm, 'glass'))) return true;
                                    if ($key === 'sarung_tangan' && (str_contains($norm, 'sarung') || str_contains($norm, 'glove'))) return true;
                                }
                                return false;
                            };
                            $items = [
                                ['key' => 'jas_lab', 'label' => 'Jas Lab'],
                                ['key' => 'masker', 'label' => 'Masker'],
                                ['key' => 'kacamata', 'label' => 'Kacamata Pelindung'],
                                ['key' => 'sarung_tangan', 'label' => 'Sarung Tangan'],
                            ];
                        @endphp

                        @foreach($items as $apd)
                            @php $isFound = $checkApdUser($apd['key']); @endphp
                            <div class="p-2.5 rounded-xl border text-xs flex items-center justify-between {{ $isFound ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' }}">
                                <span class="font-semibold">{{ $apd['label'] }}</span>
                                <span class="font-bold text-[11px]">{{ $isFound ? '✓ TERDETEKSI' : '✗ TIDAK TERDETEKSI' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>

        <!-- WAITING STATE (MENUNGGU ACC DOSEN) -->
        <div x-show="!isApproved" class="bg-white rounded-2xl border border-amber-200 shadow-sm p-6 sm:p-8 text-center space-y-4">
            <div class="w-12 h-12 rounded-full bg-amber-50 border border-amber-200 flex items-center justify-center mx-auto text-amber-600">
                <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            
            <h2 class="text-lg font-bold text-slate-900">Menunggu Persetujuan & Verifikasi Dosen</h2>
            <p class="text-xs sm:text-sm text-slate-600 max-w-md mx-auto">
                Hasil pindaian APD Anda telah dikirimkan ke Dosen. Begitu Dosen memberikan izin, Anda dapat memilih materi praktikum.
            </p>

            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-50 border border-amber-200 text-xs font-mono text-amber-800">
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                <span>Auto-refresh sinkronisasi aktif (2.5 detik)</span>
            </div>
        </div>

        <!-- APPROVED STATE (PILIH MATERI KELAS & AI PROCESS) -->
        <div x-show="isApproved" x-transition class="bg-white rounded-2xl border border-emerald-200 shadow-sm p-6 sm:p-8 space-y-6">
            
            <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center gap-3">
                <span class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold shrink-0">✓</span>
                <div>
                    <h3 class="text-xs font-bold text-emerald-900">Izin Masuk Laboratorium Diberikan (APPROVED)</h3>
                    <p class="text-[11px] text-emerald-700">Dosen pengampu telah meng-ACC kehadiran Anda. Silakan pilih materi praktikum hari ini.</p>
                </div>
            </div>

            <!-- Form Pilih Materi Dropdown -->
            <div x-show="!aiProcessing" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-900 mb-1.5">
                        Pilih Materi Praktikum Sesuai Kelas Anda:
                    </label>
                    <select x-model="selectedSesi" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">-- Pilih Materi Praktikum --</option>
                        @foreach($sesiList as $s)
                            <option value="{{ $s->id }}">
                                {{ $s->materi->judul ?? 'Praktikum' }} — Kelas: {{ $s->kelas->nama ?? '-' }} ({{ substr($s->jam_mulai,0,5) }} - {{ substr($s->jam_selesai,0,5) }} WIB)
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="button" @click="startAiProcessing()" :disabled="!selectedSesi"
                        class="w-full bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white font-bold py-3.5 px-4 rounded-xl text-sm transition shadow-sm flex items-center justify-center gap-2">
                    <span>Mulai Praktikum &rarr;</span>
                </button>
            </div>

            <!-- AI PROCESS INDICATOR (ANIMASI PROSES AI) -->
            <div x-show="aiProcessing" class="py-6 space-y-5 text-center">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-200 flex items-center justify-center mx-auto text-indigo-600 animate-spin">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </div>

                <div>
                    <h3 class="text-base font-bold text-slate-900" x-text="aiStepText"></h3>
                    <p class="text-xs text-slate-500 mt-1">Memproses data praktikum...</p>
                </div>

                <!-- Progress bar -->
                <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden max-w-md mx-auto border border-slate-200">
                    <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-500" :style="'width: ' + aiProgress + '%'"></div>
                </div>

                <div class="text-xs font-mono font-bold text-indigo-600" x-text="aiProgress + '% PROSES SELESAI'"></div>
            </div>

        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500">
        <p>LabPass &copy; 2026 — Sistem Verifikasi APD</p>
    </footer>

    <script>
        function statusApp() {
            return {
                isApproved: {{ in_array($kehadiran->status, ['disetujui', 'lulus', 'dispensasi_disetujui', 'praktikum_berjalan']) ? 'true' : 'false' }},
                selectedSesi: '{{ $kehadiran->sesi_id ?? $sesiList->first()->id ?? 1 }}',
                aiProcessing: false,
                aiProgress: 0,
                aiStepText: 'Membaca modul materi & lembar keselamatan K3L...',
                pollTimer: null,

                init() {
                    if (!this.isApproved) {
                        this.pollTimer = setInterval(async () => {
                            const res = await fetch("{{ route('verifikasi.status-ajax', ['token' => $kehadiran->token], false) }}");
                            const data = await res.json();
                            if (data.is_approved) {
                                this.isApproved = true;
                                clearInterval(this.pollTimer);
                            }
                        }, 2500);
                    }
                },

                startAiProcessing() {
                    this.aiProcessing = true;
                    this.aiProgress = 20;

                    setTimeout(() => {
                        this.aiProgress = 55;
                        this.aiStepText = 'Menganalisis reaksi bahan kimia (HCl & NaOH) serta tingkat bahaya...';
                    }, 700);

                    setTimeout(() => {
                        this.aiProgress = 85;
                        this.aiStepText = 'Menyiapkan checklist alat praktikum & pemetaan jerigen limbah...';
                    }, 1400);

                    setTimeout(async () => {
                        this.aiProgress = 100;
                        this.aiStepText = 'Ringkasan bahan & limbah siap!';

                        // Save selected session & redirect to praktikum
                        const res = await fetch("{{ route('verifikasi.pilih-materi', ['token' => $kehadiran->token], false) }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({ sesi_id: this.selectedSesi })
                        });
                        const data = await res.json();
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        }
                    }, 2100);
                }
            }
        }
    </script>
</body>
</html>
