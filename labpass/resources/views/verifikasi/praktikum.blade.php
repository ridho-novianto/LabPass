<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sesi Praktikum - {{ $kehadiran->sesi->materi->judul ?? 'Titrasi Asam Basa' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col justify-between"
      x-data="praktikumApp()">

    <!-- Clean White Navbar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 flex items-center justify-center font-bold text-white shadow-sm shadow-emerald-600/30">
                    LP
                </div>
                <div>
                    <h1 class="text-sm font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                        {{ $kehadiran->sesi->materi->judul ?? 'Praktikum Kimia' }}
                        <span class="text-[10px] font-mono px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                            PRAKTIKUM BERJALAN
                        </span>
                    </h1>
                    <p class="text-xs text-slate-500">
                        Kelas: {{ $kehadiran->sesi->kelas->nama ?? '-' }} • Ruang: {{ $kehadiran->sesi->ruangan ?? 'LAB-01' }} • Mata Praktikum: {{ $kehadiran->sesi->materi->mataPraktikum->nama ?? 'Kimia Dasar' }}
                    </p>
                </div>
            </div>

            <!-- Student Profile & Action button -->
            <div class="flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <span class="text-xs font-bold text-slate-900 block">{{ $kehadiran->mahasiswa->nama }}</span>
                    <span class="text-[11px] font-mono text-slate-500">NIM: {{ $kehadiran->mahasiswa->nim }}</span>
                </div>
                <button type="button" @click="showCheckoutModal = true"
                        class="text-xs font-bold px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white transition shadow-sm">
                    Selesai & Buang Limbah &rarr;
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content Workspace -->
    <main class="max-w-6xl mx-auto px-4 sm:px-6 py-8 flex-1 w-full space-y-6">

        <!-- AI EXECUTIVE SUMMARY & SAFETY BRIEFING (FITUR UTAMA AI LABPASS) -->
        @if($aiSummary)
            <div class="bg-gradient-to-br from-indigo-900 via-slate-900 to-slate-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl space-y-5 border border-indigo-800/40 relative overflow-hidden">
                <!-- Background Accent Glow -->
                <div class="absolute -top-24 -right-24 w-72 h-72 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 relative z-10 border-b border-white/10 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-indigo-500/20 border border-indigo-400/30 flex items-center justify-center font-bold text-indigo-300 text-sm">
                            ✨
                        </div>
                        <div>
                            <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-indigo-300 block">
                                AI LABPASS SAFETY ASSISTANT &bull; {{ $aiSummary['dari_cache'] ?? false ? 'RINGKASAN CEPAT (CACHED)' : 'SINTESIS CERDAS AKTIF' }}
                            </span>
                            <h2 class="text-base font-extrabold text-white tracking-tight">
                                Ringkasan Eksekutif & Briefing Keselamatan Laboratorium
                            </h2>
                        </div>
                    </div>
                    <span class="text-[11px] font-semibold text-slate-300 bg-white/10 px-3 py-1 rounded-full w-fit">
                        Tingkat Bahaya: <strong class="text-amber-300 uppercase">{{ $kehadiran->sesi->materi->tingkat_bahaya ?? 'Tinggi' }}</strong>
                    </span>
                </div>

                <!-- Executive Summary Text -->
                <div class="relative z-10 text-xs sm:text-sm text-slate-200 leading-relaxed font-normal bg-white/5 p-4 rounded-2xl border border-white/5">
                    {{ $aiSummary['ringkasan_eksekutif'] ?? 'Modul ini membutuhkan kepatuhan penuh terhadap SOP laboratorium dan penggunaan APD wajib.' }}
                </div>

                <!-- 3 Pillars Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 relative z-10 text-xs">
                    
                    <!-- 1. Fokus Keselamatan K3L -->
                    <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 space-y-2">
                        <span class="text-[11px] font-bold text-rose-300 uppercase tracking-wide flex items-center gap-1.5">
                            <span>⚠️</span> Bahaya & Poin Kritis K3L
                        </span>
                        <ul class="space-y-1 text-slate-300 list-disc list-inside text-[11.5px] leading-relaxed">
                            @if(!empty($aiSummary['fokus_keselamatan']))
                                @foreach($aiSummary['fokus_keselamatan'] as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            @else
                                <li>Patuhi seluruh standar keamanan praktikum.</li>
                            @endif
                        </ul>
                    </div>

                    <!-- 2. APD Prioritas -->
                    <div class="p-4 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 space-y-2">
                        <span class="text-[11px] font-bold text-indigo-300 uppercase tracking-wide flex items-center gap-1.5">
                            <span>🛡️</span> Protokol APD Wajib
                        </span>
                        <p class="text-slate-300 text-[11.5px] leading-relaxed">
                            {{ $aiSummary['apd_prioritas'] ?? 'Jas laboratorium, kacamata pengaman, masker, dan sarung tangan wajib selalu digunakan.' }}
                        </p>
                    </div>

                    <!-- 3. Penanganan Limbah -->
                    <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 space-y-2">
                        <span class="text-[11px] font-bold text-amber-300 uppercase tracking-wide flex items-center gap-1.5">
                            <span>🛢️</span> Aturan Limbah Jerigen
                        </span>
                        <p class="text-slate-300 text-[11.5px] leading-relaxed">
                            {{ $aiSummary['penanganan_limbah'] ?? 'Buang seluruh cairan sisa reaksi ke jerigen khusus bertanda merah sebelum check-out.' }}
                        </p>
                    </div>

                </div>

                <!-- Tips Praktikum -->
                @if(!empty($aiSummary['tips_sukses']))
                    <div class="relative z-10 pt-1 flex items-start gap-2.5 text-[11px] text-emerald-300 bg-emerald-950/30 p-3 rounded-xl border border-emerald-500/20">
                        <span class="font-bold shrink-0">💡 Tips Presisi Praktikum:</span>
                        <span class="text-slate-200">{{ $aiSummary['tips_sukses'] }}</span>
                    </div>
                @endif
            </div>
        @endif

        <!-- Tab Navigation Bar -->
        <div class="flex border-b border-slate-200 gap-6 text-sm font-bold overflow-x-auto">
            <button @click="tab = 'dokumen'" class="pb-3 border-b-2 transition flex items-center gap-2 whitespace-nowrap"
                    :class="tab === 'dokumen' ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-slate-500 hover:text-slate-900'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>1. Dokumen Modul (PDF Dosen)</span>
                @if($kehadiran->sesi->materi->file_path)
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                @endif
            </button>
            <button @click="tab = 'bahan'" class="pb-3 border-b-2 transition whitespace-nowrap"
                    :class="tab === 'bahan' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-slate-500 hover:text-slate-900'">
                2. Bahan Kimia
            </button>
            <button @click="tab = 'alat'" class="pb-3 border-b-2 transition whitespace-nowrap"
                    :class="tab === 'alat' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-slate-500 hover:text-slate-900'">
                3. Peralatan Lab
            </button>
            <button @click="tab = 'limbah'" class="pb-3 border-b-2 transition flex items-center gap-2 whitespace-nowrap"
                    :class="tab === 'limbah' ? 'border-rose-600 text-rose-700' : 'border-transparent text-slate-500 hover:text-slate-900'">
                <span>4. Prosedur Limbah Cairan (Wajib Jerigen)</span>
                <span class="w-2 h-2 rounded-full bg-rose-600"></span>
            </button>
        </div>

        <!-- TAB 1: DOKUMEN MODUL PRAKTIKUM (PDF DARI DOSEN) -->
        <div x-show="tab === 'dokumen'" x-transition class="space-y-4">
            @if($kehadiran->sesi->materi->file_path)
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-200 flex items-center justify-center text-indigo-700 font-bold">
                                📄
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">{{ $kehadiran->sesi->materi->judul }}.pdf</h3>
                                <p class="text-xs text-slate-500">Berkas Modul Resmi dari Dosen Pengampu &bull; LabPass AI Document Viewer</p>
                            </div>
                        </div>
                        <a href="{{ asset($kehadiran->sesi->materi->file_path) }}" target="_blank"
                           class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs transition shadow-sm flex items-center gap-1.5 w-fit">
                            <span>Unduh / Buka Dokumen Layar Penuh</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                        </a>
                    </div>

                    <!-- Embedded PDF Viewer -->
                    <div class="rounded-2xl overflow-hidden border border-slate-200 bg-slate-100 shadow-inner">
                        <iframe src="{{ asset($kehadiran->sesi->materi->file_path) }}#toolbar=1" class="w-full h-[650px] border-0" title="Dokumen Modul Praktikum">
                            <p class="p-6 text-center text-xs text-slate-600">
                                Browser Anda tidak mendukung pratinjau dokumen langsung. Silakan 
                                <a href="{{ asset($kehadiran->sesi->materi->file_path) }}" class="text-indigo-600 underline font-bold" target="_blank">klik di sini untuk mengunduh modul</a>.
                            </p>
                        </iframe>
                    </div>

                    @if($kehadiran->sesi->materi->deskripsi)
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                            <span class="font-bold text-slate-800 block mb-1">Catatan Tambahan Dosen:</span>
                            <p class="text-slate-600 leading-relaxed">{{ $kehadiran->sesi->materi->deskripsi }}</p>
                        </div>
                    @endif
                </div>
            @else
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 text-center space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center font-bold text-xl mx-auto border border-amber-200">
                        📑
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Dokumen PDF Belum Diunggah Dosen</h3>
                        <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                            Dosen belum melampirkan berkas PDF untuk modul ini. Anda tetap dapat membaca petunjuk bahan kimia, alat, dan prosedur pembuangan limbah pada tab di atas.
                        </p>
                    </div>
                </div>
            @endif
        </div>

        <!-- TAB 2: BAHAN KIMIA -->
        <div x-show="tab === 'bahan'" x-transition class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($kehadiran->sesi->materi->bahan as $b)
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-3">
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="text-xs font-mono font-bold px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ $b->rumus ?? $b->nama }}
                                </span>
                                <h3 class="text-base font-bold text-slate-900 mt-2">{{ $b->nama }}</h3>
                            </div>
                            <span class="text-xs font-bold px-2.5 py-1 rounded bg-rose-50 text-rose-700 border border-rose-200 uppercase">
                                {{ $b->tingkat_bahaya }} (Korosif)
                            </span>
                        </div>
                        <div class="pt-3 border-t border-slate-100 flex justify-between text-xs text-slate-600">
                            <span>Kebutuhan Takaran:</span>
                            <span class="font-bold text-slate-900 font-mono">{{ $b->pivot->jumlah }} {{ $b->pivot->satuan }}</span>
                        </div>
                        <p class="text-xs text-slate-500 bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                            ⚠️ Penanganan K3L: Ambil larutan di dalam lemari asam. Wajib gunakan sarung tangan nitril dan kacamata pengaman.
                        </p>
                    </div>
                @empty
                    <p class="text-xs text-slate-500">Tidak ada bahan kimia terdaftar.</p>
                @endforelse
            </div>
        </div>

        <!-- TAB 3: ALAT PRAKTIKUM -->
        <div x-show="tab === 'alat'" x-transition>
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200">
                        <tr>
                            <th class="p-4">Nama Alat Praktikum</th>
                            <th class="p-4">Jumlah Kebutuhan</th>
                            <th class="p-4">Lokasi & Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($kehadiran->sesi->materi->alat as $a)
                            <tr>
                                <td class="p-4 font-bold text-slate-900">{{ $a->nama }}</td>
                                <td class="p-4 font-mono font-semibold">{{ $a->pivot->jumlah }} Unit</td>
                                <td class="p-4 text-emerald-700 font-semibold">✓ Siap di Meja Praktikum Mahasiswa</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="p-4 text-slate-500">Tidak ada alat terdaftar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 4: LIMBAH JERIGEN (PENTING K3L) -->
        <div x-show="tab === 'limbah'" x-transition class="space-y-6">
            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900 flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <div>
                    <h4 class="font-bold text-amber-900">Peraturan Ketat Pembuangan Sisa Reaksi</h4>
                    <p class="mt-0.5 text-amber-800">
                        Dilarang keras menuangkan limbah cairan kimia ke wastafel air! Setiap cairan sisa praktikum harus dimasukkan ke dalam jerigen bertanda khusus sebelum Anda meninggalkan laboratorium.
                    </p>
                </div>
            </div>

            <!-- Kartu Limbah Cairan -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @forelse($kehadiran->sesi->materi->limbah as $l)
                    <div class="bg-white border-2 border-rose-300 rounded-2xl p-6 shadow-sm space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-mono font-bold px-2.5 py-1 rounded bg-rose-600 text-white">
                                JERIGEN: {{ $l->jerigen->kode ?? 'JR-A' }}
                            </span>
                            <span class="text-xs font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded border border-rose-200">
                                {{ $l->jerigen->label ?? 'Limbah Asam-Basa' }}
                            </span>
                        </div>

                        <div>
                            <span class="text-xs text-slate-500 uppercase font-semibold">Nama Sisa Cairan:</span>
                            <h3 class="text-xl font-extrabold text-slate-900">{{ $l->nama }}</h3>
                        </div>

                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 text-xs">
                            <span class="text-slate-500 block text-[11px] font-semibold mb-1">Kandungan Senyawa:</span>
                            <span class="font-semibold text-slate-800">{{ $l->kandungan }}</span>
                        </div>

                        <div class="pt-2 flex items-center justify-between text-xs text-slate-600 border-t border-slate-100">
                            <span>Tanda Warna Jerigen:</span>
                            <div class="flex items-center gap-2">
                                <span class="w-4 h-4 rounded-full bg-red-600 border border-slate-300"></span>
                                <span class="font-bold text-slate-900">MERAH (Jerigen Asam/Basa)</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-500">Tidak ada limbah terdaftar.</p>
                @endforelse
            </div>
        </div>

    </main>

    <!-- CHECKOUT MODAL: VERIFIKASI PEMBUANGAN LIMBAH -->
    <div x-show="showCheckoutModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 max-w-lg w-full shadow-2xl space-y-5">
            <div>
                <h3 class="text-lg font-extrabold text-slate-900">Verifikasi Pembuangan Limbah Cairan</h3>
                <p class="text-xs text-slate-600 mt-1">Anda wajib memastikan limbah praktikum telah dibuang ke jerigen yang sesuai sebelum check-out.</p>
            </div>

            <form @submit.prevent="submitCheckout" class="space-y-4">
                @php $firstLimbah = $kehadiran->sesi->materi->limbah->first(); @endphp
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-900">
                            {{ $firstLimbah->nama ?? 'Cairan A' }} &rarr; {{ $firstLimbah->jerigen->kode ?? 'JR-A' }}
                        </span>
                        <span class="text-[10px] font-mono font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded border border-rose-200">
                            JERIGEN MERAH
                        </span>
                    </div>

                    <label class="flex items-start gap-2.5 text-xs text-slate-700 cursor-pointer pt-1">
                        <input type="checkbox" required class="w-4 h-4 rounded text-emerald-600 border-slate-300 focus:ring-emerald-500 mt-0.5">
                        <span>Saya mengonfirmasi telah menuangkan sisa <strong>{{ $firstLimbah->nama ?? 'Cairan A' }}</strong> ke dalam wadah <strong>{{ $firstLimbah->jerigen->kode ?? 'JR-A' }} (Warna Merah)</strong>.</span>
                    </label>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Perkiraan Volume Cairan yang Dibuang (ml):</label>
                        <input type="number" x-model="volume" required min="1" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-1.5 text-xs text-slate-900 font-mono">
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" @click="showCheckoutModal = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-2.5 px-4 rounded-xl text-xs transition shadow-sm">
                        Konfirmasi Selesai & Keluar Lab &rarr;
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500">
        <p>LabPass AI &copy; 2026 — Selesai Praktikum & Pembuangan Limbah Terverifikasi dengan Panduan Cerdas AI.</p>
    </footer>

    <script>
        function praktikumApp() {
            return {
                tab: 'dokumen',
                showCheckoutModal: false,
                volume: 45,

                async submitCheckout() {
                    const res = await fetch("{{ route('praktikum.checkout', ['token' => $kehadiran->token]) }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({
                            volume_ml: this.volume,
                            limbah_id: {{ $firstLimbah->id ?? 1 }}
                        })
                    });
                    const data = await res.json();
                    alert("Praktikum selesai dicatat! Terima kasih telah mematuhi SOP K3L.");
                    window.location.href = "{{ route('verifikasi.index') }}";
                }
            }
        }
    </script>
</body>
</html>
