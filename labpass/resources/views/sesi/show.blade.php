<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sesi Praktikum - {{ $kehadiran->sesi->materi->judul ?? 'Praktikum' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col justify-between"
      x-data="sesiPraktikumApp()">

    <!-- Header Praktikum -->
    <header class="border-b border-slate-800 bg-slate-900/90 backdrop-blur sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-emerald-600 flex items-center justify-center font-bold text-slate-950">
                    LP
                </div>
                <div>
                    <h1 class="text-sm font-bold text-white flex items-center gap-2">
                        {{ $kehadiran->sesi->materi->judul ?? 'Praktikum' }}
                        <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">SESI AKTIF</span>
                    </h1>
                    <p class="text-xs text-slate-400">Kelas: {{ $kehadiran->sesi->kelas->nama ?? '-' }} • Ruang: {{ $kehadiran->sesi->ruangan }}</p>
                </div>
            </div>

            <!-- Student Profile badge -->
            <div class="flex items-center gap-3">
                <div class="text-right">
                    <div class="text-xs font-bold text-white">{{ $kehadiran->mahasiswa->nama }}</div>
                    <div class="text-[11px] font-mono text-slate-400">NIM: {{ $kehadiran->mahasiswa->nim }}</div>
                </div>
                <button type="button" @click="openModal = true" class="text-xs font-bold px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white transition shadow-lg shadow-rose-600/20">
                    Selesai & Buang Limbah &rarr;
                </button>
            </div>
        </div>
    </header>

    <!-- Main Workspace -->
    <main class="max-w-5xl mx-auto px-4 py-8 flex-1 w-full">
        <!-- Tabs Nav -->
        <div class="flex border-b border-slate-800 gap-6 mb-8 text-sm font-semibold">
            <button @click="activeTab = 'bahan'" 
                    class="pb-3 border-b-2 transition"
                    :class="activeTab === 'bahan' ? 'border-emerald-500 text-emerald-400' : 'border-transparent text-slate-400 hover:text-white'">
                Bahan Kimia ({{ $kehadiran->sesi->materi->bahan->count() }})
            </button>
            <button @click="activeTab = 'alat'" 
                    class="pb-3 border-b-2 transition"
                    :class="activeTab === 'alat' ? 'border-emerald-500 text-emerald-400' : 'border-transparent text-slate-400 hover:text-white'">
                Alat Praktikum ({{ $kehadiran->sesi->materi->alat->count() }})
            </button>
            <button @click="activeTab = 'limbah'" 
                    class="pb-3 border-b-2 transition flex items-center gap-2"
                    :class="activeTab === 'limbah' ? 'border-emerald-500 text-emerald-400' : 'border-transparent text-slate-400 hover:text-white'">
                <span>Panduan Jerigen Limbah</span>
                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
            </button>
        </div>

        <!-- TAB 1: BAHAN KIMIA -->
        <div x-show="activeTab === 'bahan'" x-transition class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($kehadiran->sesi->materi->bahan as $b)
                    <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition">
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="text-xs font-mono text-emerald-400 font-bold bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/20">
                                    {{ $b->rumus ?? $b->nama }}
                                </span>
                                <h3 class="text-base font-bold text-white mt-2">{{ $b->nama }}</h3>
                            </div>
                            <span class="text-xs font-bold px-2 py-1 rounded bg-rose-500/10 text-rose-400 border border-rose-500/20 uppercase">
                                {{ $b->tingkat_bahaya }}
                            </span>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-800 flex justify-between text-xs text-slate-400">
                            <span>Takaran Disiapkan:</span>
                            <span class="font-bold text-white font-mono">{{ $b->pivot->jumlah }} {{ $b->pivot->satuan }}</span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-2">
                            Penanganan: Gunakan lemari asam dan hindari kontak langsung dengan kulit.
                        </p>
                    </div>
                @empty
                    <p class="text-xs text-slate-400">Belum ada bahan kimia terdaftar untuk materi ini.</p>
                @endforelse
            </div>
        </div>

        <!-- TAB 2: ALAT PRAKTIKUM -->
        <div x-show="activeTab === 'alat'" x-transition>
            <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-950/80 text-slate-400 font-semibold border-b border-slate-800">
                        <tr>
                            <th class="p-4">Nama Peralatan</th>
                            <th class="p-4">Jumlah Wajib</th>
                            <th class="p-4">Status Pengambilan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800 text-slate-200">
                        @forelse($kehadiran->sesi->materi->alat as $a)
                            <tr>
                                <td class="p-4 font-semibold text-white">{{ $a->nama }}</td>
                                <td class="p-4 font-mono">{{ $a->pivot->jumlah }} Unit</td>
                                <td class="p-4"><span class="text-emerald-400 font-medium">✓ Tersedia di Meja Praktikum</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="p-4 text-slate-400">Tidak ada alat terdaftar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 3: LIMBAH JERIGEN (PENTING) -->
        <div x-show="activeTab === 'limbah'" x-transition class="space-y-6">
            <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 text-xs text-amber-200 flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <div>
                    <h4 class="font-bold text-amber-300">Wajib Ditaati (Standar Keselamatan Laboratorium)</h4>
                    <p class="mt-0.5 text-amber-200/80">Sisa reaksi kimia TIDAK BOLEH dibuang ke wastafel cuci piring/tangan! Masukkan cairan ke dalam jerigen bertanda khusus sebelum meninggalkan laboratorium.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @forelse($kehadiran->sesi->materi->limbah as $l)
                    <div class="bg-slate-900 border-2 border-rose-500/40 rounded-2xl p-6 relative overflow-hidden shadow-xl">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-xs font-mono font-bold px-2 py-1 rounded bg-rose-500 text-white">
                                JERIGEN TUJUAN: {{ $l->jerigen->kode ?? 'JR-A' }}
                            </span>
                            <span class="text-xs font-bold text-rose-400">{{ $l->jerigen->label ?? 'Limbah Asam-Basa' }}</span>
                        </div>

                        <h3 class="text-lg font-extrabold text-white">{{ $l->nama }}</h3>
                        <div class="mt-3 p-3 bg-slate-950 rounded-xl border border-slate-800 text-xs">
                            <span class="text-slate-400 block text-[11px] mb-1">Kandungan Senyawa:</span>
                            <span class="font-semibold text-slate-200">{{ $l->kandungan }}</span>
                        </div>

                        <div class="mt-4 flex items-center justify-between text-xs text-slate-400">
                            <span>Warna Penanda Jerigen:</span>
                            <div class="flex items-center gap-2">
                                <span class="w-3.5 h-3.5 rounded-full bg-red-600 border border-white/20"></span>
                                <span class="font-bold text-white">MERAH (Asam/Basa)</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400">Tidak ada data limbah.</p>
                @endforelse
            </div>
        </div>
    </main>

    <!-- CHECKOUT MODAL: PEMBUANGAN LIMBAH -->
    <div x-show="openModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4" x-cloak>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 max-w-lg w-full shadow-2xl">
            <h3 class="text-lg font-bold text-white">Verifikasi Pembuangan Limbah Cairan</h3>
            <p class="text-xs text-slate-400 mt-1">Anda wajib mengonfirmasi telah membuang sisa praktikum ke jerigen yang sesuai sebelum check-out.</p>

            <form @submit.prevent="submitCheckout" class="mt-6 space-y-4">
                @foreach($kehadiran->sesi->materi->limbah as $l)
                    <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-white">{{ $l->nama }} &rarr; {{ $l->jerigen->kode ?? 'JR-A' }}</span>
                            <span class="text-[10px] font-mono text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded">JERIGEN MERAH</span>
                        </div>
                        <label class="flex items-center gap-2 text-xs text-slate-300 cursor-pointer">
                            <input type="checkbox" required class="w-4 h-4 rounded text-emerald-500 bg-slate-900 border-slate-700">
                            <span>Saya telah membuang seluruh cairan ke dalam jerigen <strong>{{ $l->jerigen->kode ?? 'JR-A' }}</strong>.</span>
                        </label>
                        <div>
                            <label class="block text-[11px] text-slate-400 mb-1">Perkiraan Volume Dibuang (ml):</label>
                            <input type="number" required min="1" value="50" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-1.5 text-xs text-white font-mono">
                        </div>
                    </div>
                @endforeach

                <div class="flex gap-3 pt-2">
                    <button type="button" @click="openModal = false" class="px-4 py-2.5 rounded-xl border border-slate-700 text-xs text-slate-300 hover:bg-slate-800">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold py-2.5 px-4 rounded-xl text-xs transition">
                        Konfirmasi Selesai & Keluar Lab
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="border-t border-slate-800 py-4 bg-slate-950 text-center text-xs text-slate-500">
        <p>LabPass AI &copy; 2026 — Sesi Praktikum Mahasiswa. Patuhi keselamatan kerja setiap saat.</p>
    </footer>

    <script>
        function sesiPraktikumApp() {
            return {
                activeTab: 'bahan',
                openModal: false,
                async submitCheckout() {
                    const res = await fetch("{{ route('sesi.checkout', ['token' => $kehadiran->token]) }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({
                            limbah: [
                                @foreach($kehadiran->sesi->materi->limbah as $l)
                                { id: {{ $l->id }}, volume: 50 },
                                @endforeach
                            ]
                        })
                    });
                    const data = await res.json();
                    alert("Praktikum selesai dicatat! Limbah telah diverifikasi.");
                    window.location.href = "{{ route('kios.index') }}";
                }
            }
        }
    </script>
</body>
</html>
