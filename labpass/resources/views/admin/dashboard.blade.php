<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LabPass - Control Center Dosen & Aslab</title>
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
      x-data="adminApp()">

    <!-- Clean White Navbar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center font-bold text-white shadow-sm">
                    LP
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-base font-extrabold text-slate-900 tracking-tight">LabPass</span>
                        <span class="text-[11px] font-semibold uppercase px-2 py-0.5 rounded-full {{ $activeUser->role === 'dosen' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                            {{ strtoupper($activeUser->role) }} PANEL
                        </span>
                    </div>
                    <p class="text-xs text-slate-500">Verifikasi APD & Monitoring</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="text-xs font-semibold text-slate-700 hidden sm:inline">
                    {{ $activeUser->name }}
                </span>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 transition">
                        Keluar
                    </button>
                </form>
                <a href="{{ route('verifikasi.index') }}" class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white transition shadow-sm">
                    Portal Mahasiswa &rarr;
                </a>
            </div>
        </div>
    </header>

    <!-- Main Workspace -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8 flex-1 w-full space-y-6">
        
        <!-- Flash Alert -->
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between">
                <span>✓ {{ session('success') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">&times;</button>
            </div>
        @endif
        @if(session('info'))
            <div class="p-4 rounded-xl bg-slate-100 border border-slate-300 text-slate-800 text-xs font-semibold flex items-center justify-between">
                <span>ℹ {{ session('info') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-slate-600 hover:text-slate-900">&times;</button>
            </div>
        @endif

        <!-- Quick Metrics -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Antrean Verifikasi YOLO</span>
                <div class="text-2xl font-extrabold text-amber-600 font-mono mt-1">{{ $stats['antrean'] }}</div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Praktikan Aktif di Lab</span>
                <div class="text-2xl font-extrabold text-emerald-600 font-mono mt-1">{{ $stats['aktif'] }}</div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Limbah Jerigen Dibuang</span>
                <div class="text-2xl font-extrabold text-indigo-600 font-mono mt-1">{{ $stats['limbah_dibuang'] }}</div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Total Modul Materi</span>
                <div class="text-2xl font-extrabold text-slate-900 font-mono mt-1">{{ $stats['total_materi'] }}</div>
            </div>
        </div>

        <!-- Tab Controls -->
        <div class="flex border-b border-slate-200 gap-6 text-sm font-bold overflow-x-auto">
            <button @click="tab = 'approval'" class="pb-3 border-b-2 transition flex items-center gap-2 whitespace-nowrap"
                    :class="tab === 'approval' ? 'border-amber-600 text-amber-700' : 'border-transparent text-slate-500 hover:text-slate-900'">
                <span>Antrean Persetujuan YOLO</span>
                @if($antreanVerifikasi->count() > 0)
                    <span class="px-2 py-0.5 rounded-full text-xs font-mono bg-amber-100 text-amber-800">{{ $antreanVerifikasi->count() }}</span>
                @endif
            </button>
            <button @click="tab = 'monitoring'" class="pb-3 border-b-2 transition whitespace-nowrap"
                    :class="tab === 'monitoring' ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-slate-500 hover:text-slate-900'">
                Monitoring Kehadiran & Status Limbah Live
            </button>
            @if($activeUser->role === 'dosen')
                <button @click="tab = 'materi'" class="pb-3 border-b-2 transition flex items-center gap-2 whitespace-nowrap"
                        :class="tab === 'materi' ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-slate-500 hover:text-slate-900'">
                    <span>Kelola & Upload Materi</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-mono bg-indigo-50 text-indigo-700 border border-indigo-200">{{ $materiList->count() }}</span>
                </button>
                <button @click="tab = 'sesi'" class="pb-3 border-b-2 transition whitespace-nowrap"
                        :class="tab === 'sesi' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-slate-500 hover:text-slate-900'">
                    Publikasikan Jadwal Sesi
                </button>
            @endif
        </div>

        <!-- TAB 1: ANTREAN VERIFIKASI YOLO -->
        <div x-show="tab === 'approval'" x-transition class="space-y-4">
            @if($antreanVerifikasi->isEmpty())
                <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center text-slate-500">
                    <p class="text-sm font-semibold">Tidak ada mahasiswa yang menunggu verifikasi saat ini.</p>
                    <p class="text-xs text-slate-400 mt-1">Data hasil pemindaian kamera YOLO mahasiswa akan otomatis muncul di sini.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($antreanVerifikasi as $item)
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-base font-bold text-slate-900">{{ $item->mahasiswa->nama }}</h3>
                                    <p class="text-xs text-slate-500 font-mono">NIM: {{ $item->mahasiswa->nim }}</p>
                                </div>
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200">
                                    MENUNGGU ACC
                                </span>
                            </div>

                            <!-- YOLO Annotated Box Preview -->
                            <div class="relative rounded-xl overflow-hidden border border-slate-200 bg-slate-950 aspect-video flex items-center justify-center">
                                @if($item->snapshot_path)
                                    <img src="{{ $item->snapshot_path }}" alt="Scan YOLO" class="w-full h-full object-contain">
                                @else
                                    <span class="text-xs text-slate-400">Tidak ada snapshot</span>
                                @endif
                            </div>

                            <!-- Checklist APD YOLO -->
                            <div class="space-y-2">
                                <span class="text-xs font-bold text-slate-700 block">Status Deteksi APD AI YOLO:</span>
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    @php
                                        $detectedArr = $item->apd_terdeteksi ?? [];
                                        $checkApd = function($key) use ($detectedArr) {
                                            if (!is_array($detectedArr)) return false;
                                            foreach ($detectedArr as $val) {
                                                $norm = strtolower(str_replace(['_', '-'], ' ', (string)$val));
                                                if ($key === 'jas_lab' && str_contains($norm, 'jas')) return true;
                                                if ($key === 'masker' && str_contains($norm, 'mask')) return true;
                                                if ($key === 'kacamata' && (str_contains($norm, 'kacamata') || str_contains($norm, 'glass'))) return true;
                                                if ($key === 'sarung_tangan' && (str_contains($norm, 'sarung') || str_contains($norm, 'glove'))) return true;
                                            }
                                            return false;
                                        };
                                        $hasJas = $checkApd('jas_lab');
                                        $hasMasker = $checkApd('masker');
                                        $hasKacamata = $checkApd('kacamata');
                                        $hasSarung = $checkApd('sarung_tangan');
                                    @endphp
                                    <div class="p-2 rounded-lg border {{ $hasJas ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' }} flex items-center gap-1.5 font-medium">
                                        <span>{{ $hasJas ? '✓' : '✗' }}</span>
                                        <span>Jas Lab</span>
                                    </div>
                                    <div class="p-2 rounded-lg border {{ $hasMasker ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' }} flex items-center gap-1.5 font-medium">
                                        <span>{{ $hasMasker ? '✓' : '✗' }}</span>
                                        <span>Masker</span>
                                    </div>
                                    <div class="p-2 rounded-lg border {{ $hasKacamata ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' }} flex items-center gap-1.5 font-medium">
                                        <span>{{ $hasKacamata ? '✓' : '✗' }}</span>
                                        <span>Kacamata</span>
                                    </div>
                                    <div class="p-2 rounded-lg border {{ $hasSarung ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' }} flex items-center gap-1.5 font-medium">
                                        <span>{{ $hasSarung ? '✓' : '✗' }}</span>
                                        <span>Sarung Tangan</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-2 pt-2 border-t border-slate-100">
                                <form action="{{ route('admin.kehadiran.tolak', $item->id) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full py-2.5 px-3 rounded-xl border border-slate-200 hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200 text-slate-700 text-xs font-semibold transition">
                                        Tolak Akses
                                    </button>
                                </form>
                                <form action="{{ route('admin.kehadiran.approve', $item->id) }}" method="POST" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="sesi_id" value="{{ $sesiList->first()->id ?? 1 }}">
                                    <button type="submit" class="w-full py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition shadow-sm">
                                        Setujui Akses (ACC) &rarr;
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- TAB 2: MONITORING KEHADIRAN & LIMBAH -->
        <div x-show="tab === 'monitoring'" x-transition class="space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Log Aktivitas & Status Pembuangan Limbah</h3>
                        <p class="text-xs text-slate-500">Pantau secara langsung apakah mahasiswa telah membuang limbah ke jerigen sebelum keluar laboratorium.</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200">
                            <tr>
                                <th class="p-4">Mahasiswa / NIM</th>
                                <th class="p-4">Sesi & Materi</th>
                                <th class="p-4">Status Verifikasi</th>
                                <th class="p-4">Status Limbah Jerigen</th>
                                <th class="p-4">Waktu Masuk / Keluar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($riwayatKehadiran as $r)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="p-4">
                                        <div class="font-bold text-slate-900">{{ $r->mahasiswa->nama }}</div>
                                        <div class="font-mono text-slate-500 text-[11px]">{{ $r->mahasiswa->nim }}</div>
                                    </td>
                                    <td class="p-4">
                                        <div class="font-semibold text-slate-800">{{ $r->sesi->materi->judul ?? 'Titrasi Asam Basa' }}</div>
                                        <div class="text-slate-500 text-[11px]">Kelas: {{ $r->sesi->kelas->nama ?? 'Kimia Dasar A' }}</div>
                                    </td>
                                    <td class="p-4">
                                        @if(in_array($r->status, ['disetujui', 'lulus', 'praktikum_berjalan']))
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                ACC / DISETUJUI
                                            </span>
                                        @elseif($r->status === 'selesai')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                SELESAI PRAKTIKUM
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                DITOLAK
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-4">
                                        @if($r->pembuanganLimbah->isNotEmpty())
                                            <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 font-bold flex items-center gap-1.5 w-fit">
                                                <span>Dibuang ke JR-A Merah</span>
                                                <span class="font-mono text-[11px] font-normal">({{ $r->pembuanganLimbah->first()->volume_ml }} ml)</span>
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-lg bg-amber-50 text-amber-800 border border-amber-200 font-semibold">
                                                Belum Dibuang
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-4 font-mono text-[11px] text-slate-500">
                                        <div>Masuk: {{ $r->jam_masuk ? $r->jam_masuk->format('H:i:s') : '-' }}</div>
                                        <div>Keluar: {{ $r->jam_keluar ? $r->jam_keluar->format('H:i:s') : 'Masih di Lab' }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="p-6 text-center text-slate-500">Belum ada aktivitas mahasiswa.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 3: KELOLA & UPLOAD MATERI (FITUR UTAMA PANEL DOSEN) -->
        @if($activeUser->role === 'dosen')
            <div x-show="tab === 'materi'" x-transition class="space-y-6">
                
                <!-- Subtab Switcher & Info -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-bold text-slate-900">Manajemen Modul & Upload Berkas Materi Dosen</h3>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    AI
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">
                                Unggah berkas dokumen modul (PDF), tentukan APD wajib, takaran bahan kimia, serta pemetaan limbah jerigen.
                            </p>
                        </div>
                <div class="flex items-center gap-2 bg-slate-100 p-1.5 rounded-xl text-xs font-semibold">
                    <button type="button" @click="materiMode = 'list'"
                            :class="materiMode === 'list' ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                            class="px-3.5 py-2 rounded-lg transition">
                        Materi ({{ $materiList->count() }})
                    </button>
                    <button type="button" @click="materiMode = 'create'"
                            :class="materiMode === 'create' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                            class="px-3.5 py-2 rounded-lg transition">
                        + Unggah
                    </button>
                </div>
                    </div>
                </div>

                <!-- SUBTAB 1: DAFTAR MATERI AKTIF -->
                <div x-show="materiMode === 'list'" x-transition class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @forelse($materiList as $m)
                            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4 flex flex-col justify-between">
                                <div class="space-y-3">
                                    <!-- Header Badge -->
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <span class="text-[10px] font-mono uppercase font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200">
                                                {{ $m->mataPraktikum->nama ?? 'Kimia Dasar' }}
                                            </span>
                                            <h4 class="text-base font-extrabold text-slate-900 mt-1.5">{{ $m->judul }}</h4>
                                        </div>
                                        <span class="text-[11px] font-bold uppercase px-2.5 py-0.5 rounded-full {{ $m->tingkat_bahaya === 'tinggi' ? 'bg-rose-50 text-rose-700 border border-rose-200' : ($m->tingkat_bahaya === 'sedang' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200') }}">
                                            Bahaya: {{ $m->tingkat_bahaya }}
                                        </span>
                                    </div>

                                    @if($m->deskripsi)
                                        <p class="text-xs text-slate-600 line-clamp-2">{{ $m->deskripsi }}</p>
                                    @endif

                                    <!-- Status File Modul Dokumen -->
                                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="font-bold text-slate-700 flex items-center gap-1.5">
                                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                Dokumen Modul PDF:
                                            </span>
                                            @if($m->file_path)
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">
                                                    Berkas Tersedia
                                                </span>
                                            @else
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-amber-100 text-amber-800">
                                                    Belum Ada Berkas
                                                </span>
                                            @endif
                                        </div>

                                        @if($m->file_path)
                                            <div class="flex items-center gap-2 pt-1">
                                                <a href="{{ asset($m->file_path) }}" target="_blank"
                                                   class="text-xs font-bold text-indigo-600 hover:text-indigo-800 hover:underline flex items-center gap-1">
                                                    <span>Lihat / Unduh Dokumen PDF</span> &rarr;
                                                </a>
                                            </div>
                                        @endif

                                        <!-- Quick Replace/Upload Form -->
                                        <form action="{{ route('admin.materi.upload-file', $m->id) }}" method="POST" enctype="multipart/form-data" class="pt-2 border-t border-slate-200/60 flex items-center gap-2">
                                            @csrf
                                            <input type="file" name="file_materi" accept=".pdf,.doc,.docx,.txt" required
                                                   class="text-[11px] text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:font-semibold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300 w-full">
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-[11px] font-semibold shrink-0">
                                                {{ $m->file_path ? 'Ganti' : 'Upload' }}
                                            </button>
                                        </form>
                                    </div>

                                    <!-- APD Wajib & Limbah Tags -->
                                    <div class="grid grid-cols-2 gap-2 text-[11px]">
                                        <div class="p-2.5 rounded-lg bg-white border border-slate-200">
                                            <span class="text-slate-400 block font-semibold mb-1">APD Wajib ({{ $m->apd->count() }}):</span>
                                            <div class="flex flex-wrap gap-1">
                                                @forelse($m->apd as $a)
                                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 text-[10px] font-semibold">{{ $a->nama }}</span>
                                                @empty
                                                    <span class="text-slate-400 italic">Belum diset</span>
                                                @endforelse
                                            </div>
                                        </div>
                                        <div class="p-2.5 rounded-lg bg-white border border-slate-200">
                                            <span class="text-slate-400 block font-semibold mb-1">Jerigen Limbah:</span>
                                            <div class="flex flex-wrap gap-1">
                                                @forelse($m->limbah as $l)
                                                    <span class="px-1.5 py-0.5 rounded bg-rose-50 text-rose-700 border border-rose-200 text-[10px] font-mono font-bold">
                                                        {{ $l->jerigen->kode ?? 'JR-A' }}
                                                    </span>
                                                @empty
                                                    <span class="text-slate-400 italic">Belum diset</span>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                                <button type="button" @click="openAiPreview({{ $m->id }}, '{{ addslashes($m->judul) }}')"
                                        class="py-2 px-3.5 rounded-xl border border-indigo-200 bg-indigo-50/50 hover:bg-indigo-100/70 text-indigo-700 text-xs font-bold transition flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20"><path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14H8V9a4 4 0 114 4v1z"></path></svg>
                                    <span>Pratinjau</span>
                                </button>

                                    <form action="{{ route('admin.materi.destroy', $m->id) }}" method="POST" onsubmit="return confirm('Hapus materi praktikum ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus Materi">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-2 bg-white rounded-2xl border border-slate-200 p-8 text-center text-slate-500">
                                <p class="text-sm font-semibold">Belum ada materi praktikum yang diunggah.</p>
                                <button type="button" @click="materiMode = 'create'" class="mt-3 px-4 py-2 rounded-xl bg-indigo-600 text-white font-bold text-xs hover:bg-indigo-500">
                                    + Unggah Materi Pertama
                                </button>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- SUBTAB 2: FORM TAMBAH MATERI BARU & UPLOAD MODUL -->
                <div x-show="materiMode === 'create'" x-transition class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
                    <div>
                        <h4 class="text-base font-extrabold text-slate-900">Form Tambah Materi Praktikum Baru</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Lengkapi parameter keselamatan, upload berkas modul penuntun (PDF), dan petakan wadah limbah jerigen.</p>
                    </div>

                    <form action="{{ route('admin.materi.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <!-- Bagian 1: Identitas & File Modul -->
                        <div class="space-y-4">
                            <span class="text-xs font-bold text-indigo-700 uppercase tracking-wider block">1. Identitas Materi & Berkas Penuntun</span>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Judul Materi Praktikum <span class="text-rose-500">*</span></label>
                                    <input type="text" name="judul" required placeholder="Contoh: Titrasi Redoks & Penetapan Kadar Fe"
                                           class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Mata Praktikum <span class="text-rose-500">*</span></label>
                                    <select name="mata_praktikum_id" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                        @foreach($mataPraktikumList as $mp)
                                            <option value="{{ $mp->id }}">{{ $mp->nama }} ({{ $mp->kode }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Tingkat Bahaya K3L <span class="text-rose-500">*</span></label>
                                    <select name="tingkat_bahaya" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                        <option value="tinggi">Tinggi (Bahan Korosif, Beracun, atau Eksoterm)</option>
                                        <option value="sedang">Sedang (Bahan Iritatif atau Pelarut Organik Ringan)</option>
                                        <option value="rendah">Rendah (Garam Netral, Akuades, Bahan Non-Toksik)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Upload Berkas Modul Praktikum (PDF / Doc) <span class="text-indigo-600">(Dianjurkan)</span></label>
                                    <input type="file" name="file_materi" accept=".pdf,.doc,.docx,.txt"
                                           class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-700 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                    <span class="text-[11px] text-slate-400 mt-1 block">Format didukung: PDF, DOCX (Maks. 20MB). Berkas akan langsung dapat dibaca oleh mahasiswa.</span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi & Petunjuk Dosen</label>
                                <textarea name="deskripsi" rows="2" placeholder="Tuliskan gambaran umum praktikum, sasaran analisis, atau catatan instruksional khusus..."
                                          class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-xs text-slate-900 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
                            </div>
                        </div>

                        <!-- Bagian 2: APD Wajib -->
                        <div class="space-y-3 pt-4 border-t border-slate-100">
                            <span class="text-xs font-bold text-indigo-700 uppercase tracking-wider block">2. Standar APD Wajib Laboratorium</span>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                @foreach($apdList as $apd)
                                    <label class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-indigo-50/40 cursor-pointer flex items-center gap-2.5 transition">
                                        <input type="checkbox" name="apd_ids[]" value="{{ $apd->id }}" checked
                                               class="w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500">
                                        <span class="text-xs font-semibold text-slate-800">{{ $apd->nama }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Bagian 3: Bahan Kimia & Takaran -->
                        <div class="space-y-3 pt-4 border-t border-slate-100" x-data="bahanSection()">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-indigo-700 uppercase tracking-wider">3. Bahan Kimia & Reagen</span>
                                <button type="button" @click="addBahan" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                                    + Tambah Bahan
                                </button>
                            </div>

                            <template x-for="(item, index) in items" :key="index">
                                <div class="grid grid-cols-12 gap-3 items-center">
                                    <div class="col-span-6 sm:col-span-5">
                                        <select :name="'bahan[' + index + '][id]'" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900">
                                            @foreach($bahanList as $b)
                                                <option value="{{ $b->id }}">{{ $b->nama }} ({{ $b->rumus }}) - {{ $b->tingkat_bahaya }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-span-3 sm:col-span-3">
                                        <input type="number" :name="'bahan[' + index + '][jumlah]'" x-model="item.jumlah" placeholder="Jumlah" required
                                               class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 font-mono">
                                    </div>
                                    <div class="col-span-2 sm:col-span-3">
                                        <input type="text" :name="'bahan[' + index + '][satuan]'" x-model="item.satuan" placeholder="Satuan (ml/gr)" required
                                               class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900">
                                    </div>
                                    <div class="col-span-1 text-center">
                                        <button type="button" @click="removeBahan(index)" class="text-slate-400 hover:text-rose-600 text-sm font-bold">&times;</button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Bagian 4: Alat Laboratorium -->
                        <div class="space-y-3 pt-4 border-t border-slate-100" x-data="alatSection()">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-indigo-700 uppercase tracking-wider">4. Peralatan Praktikum</span>
                                <button type="button" @click="addAlat" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                                    + Tambah Alat
                                </button>
                            </div>

                            <template x-for="(item, index) in items" :key="index">
                                <div class="grid grid-cols-12 gap-3 items-center">
                                    <div class="col-span-7 sm:col-span-8">
                                        <select :name="'alat[' + index + '][id]'" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900">
                                            @foreach($alatList as $a)
                                                <option value="{{ $a->id }}">{{ $a->nama }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-span-4 sm:col-span-3">
                                        <input type="number" :name="'alat[' + index + '][jumlah]'" x-model="item.jumlah" placeholder="Jumlah (unit)" required
                                               class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 font-mono">
                                    </div>
                                    <div class="col-span-1 text-center">
                                        <button type="button" @click="removeAlat(index)" class="text-slate-400 hover:text-rose-600 text-sm font-bold">&times;</button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Bagian 5: Pemetaan Limbah Jerigen -->
                        <div class="space-y-3 pt-4 border-t border-slate-100" x-data="limbahSection()">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-rose-700 uppercase tracking-wider">5. Prosedur Limbah & Jerigen Penampung (Wajib K3L)</span>
                                <button type="button" @click="addLimbah" class="text-xs font-bold text-rose-600 hover:text-rose-800">
                                    + Tambah Alur Limbah
                                </button>
                            </div>

                            <template x-for="(item, index) in items" :key="index">
                                <div class="p-3.5 rounded-xl border border-rose-200 bg-rose-50/30 space-y-3">
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <div>
                                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Sisa Reaksi</label>
                                            <input type="text" :name="'limbah[' + index + '][nama]'" x-model="item.nama" required placeholder="Contoh: Cairan A (Sisa Titrasi)"
                                                   class="w-full bg-white border border-slate-300 rounded-xl px-3 py-1.5 text-xs text-slate-900">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Kandungan Senyawa</label>
                                            <input type="text" :name="'limbah[' + index + '][kandungan]'" x-model="item.kandungan" required placeholder="Contoh: HCl + NaOH + Phenolphthalein"
                                                   class="w-full bg-white border border-slate-300 rounded-xl px-3 py-1.5 text-xs text-slate-900">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Wadah Jerigen Tujuan</label>
                                            <select :name="'limbah[' + index + '][jerigen_id]'" class="w-full bg-white border border-slate-300 rounded-xl px-3 py-1.5 text-xs text-slate-900">
                                                @foreach($jerigenList as $j)
                                                    <option value="{{ $j->id }}">{{ $j->kode }} - {{ $j->label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <button type="button" @click="removeLimbah(index)" class="text-xs text-rose-600 hover:text-rose-800 font-semibold">
                                            Hapus Pemetaan Ini
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                            <button type="button" @click="materiMode = 'list'" class="px-5 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                Batal
                            </button>
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition shadow-sm flex items-center gap-2">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>Simpan & Publikasikan Materi dengan AI</span>
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        @endif

        <!-- TAB 4: PUBLIKASIKAN JADWAL SESI -->
        @if($activeUser->role === 'dosen')
            <div x-show="tab === 'sesi'" x-transition class="max-w-2xl">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-4">
                    <h3 class="text-base font-bold text-slate-900">Publikasikan Sesi Praktikum Hari Ini</h3>
                    <p class="text-xs text-slate-500">Tentukan kelas apa, materi apa, dan rentang jam mulai hingga selesai praktikum.</p>

                    <form action="{{ route('admin.sesi.store') }}" method="POST" class="space-y-4 pt-2">
                        @csrf
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Kelas Praktikum</label>
                                <select name="kelas_id" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900">
                                    @foreach($kelasList as $kelas)
                                        <option value="{{ $kelas->id }}">{{ $kelas->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Materi Praktikum</label>
                                <select name="materi_id" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900">
                                    @foreach($materiList as $m)
                                        <option value="{{ $m->id }}">{{ $m->judul }} (Bahaya: {{ $m->tingkat_bahaya }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal</label>
                                <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 font-mono">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Jam Mulai</label>
                                <input type="time" name="jam_mulai" value="08:00" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 font-mono">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Jam Selesai</label>
                                <input type="time" name="jam_selesai" value="10:00" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 font-mono">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Ruangan Lab</label>
                                <input type="text" name="ruangan" value="LAB-01 Kimia" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Aslab Pendamping</label>
                                <select name="aslab_id" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900">
                                    <option value="">-- Pilih Aslab --</option>
                                    @foreach($aslabList as $aslab)
                                        <option value="{{ $aslab->id }}">{{ $aslab->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-2.5 px-4 rounded-xl text-xs transition shadow-sm mt-2">
                            Simpan & Publikasikan Jadwal Sesi
                        </button>
                    </form>
                </div>
            </div>
        @endif

        <!-- BUKTI METRIK PEMAKAIAN TOKEN AI (TOKEN TRACKER & EFFICIENCY AUDIT) -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-600 flex items-center justify-center font-bold text-white shadow-sm font-mono text-xs">
                        AI
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-extrabold text-slate-900 tracking-tight">Bukti Metrik Pemakaian Token AI</h3>
                            <span class="text-[10px] font-mono font-bold px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">
                                LIVE TOKEN TRACKER
                            </span>
                        </div>
                        <p class="text-xs text-slate-500">Transparansi audit pemakaian LLM, efisiensi caching, dan optimasi biaya token K3L.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-xs font-mono font-semibold text-slate-600">Sinkron Database `token_log`</span>
                </div>
            </div>

            <!-- 4 Metric KPI Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <!-- 1. Total Token -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-1">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Total Token Terpakai</span>
                    <div class="text-2xl font-extrabold text-slate-900 font-mono">{{ number_format($tokenMetrics['total_token']) }}</div>
                    <span class="text-[10px] text-slate-400 block">Prompt in + Completion out</span>
                </div>

                <!-- 2. Panggilan AI -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-1">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Jumlah Transaksi AI</span>
                    <div class="text-2xl font-extrabold text-indigo-600 font-mono">{{ $tokenMetrics['total_calls'] }} <span class="text-xs font-normal">panggilan</span></div>
                    <span class="text-[10px] text-slate-400 block">Evaluasi risiko & materi</span>
                </div>

                <!-- 3. Persentase Cache -->
                <div class="p-4 rounded-2xl bg-emerald-50/50 border border-emerald-200 space-y-1">
                    <span class="text-[11px] font-bold text-emerald-800 uppercase tracking-wide">Efisiensi Cache</span>
                    <div class="text-2xl font-extrabold text-emerald-700 font-mono">{{ $tokenMetrics['cache_percentage'] }}%</div>
                    <span class="text-[10px] text-emerald-600 font-semibold block">0 token saat cache hit</span>
                </div>

                <!-- 4. Rata-rata Token -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-1">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Rata-rata / Transaksi</span>
                    <div class="text-2xl font-extrabold text-slate-900 font-mono">{{ $tokenMetrics['avg_token'] }} <span class="text-xs font-normal">tokens</span></div>
                    <span class="text-[10px] text-slate-400 block">Optimasi JSON ringkas</span>
                </div>
            </div>

            <!-- Tabel Bukti Transaksi Token Log -->
            <div class="space-y-3">
                <span class="text-xs font-bold text-slate-700 block">Log Bukti Transaksi Token Terbaru (Audit Trail):</span>
                <div class="rounded-2xl border border-slate-200 overflow-hidden">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200">
                            <tr>
                                <th class="p-3.5">Waktu Transaksi</th>
                                <th class="p-3.5">Jenis Operasi AI</th>
                                <th class="p-3.5">Terkait Mahasiswa / Modul</th>
                                <th class="p-3.5">Prompt In</th>
                                <th class="p-3.5">Completion Out</th>
                                <th class="p-3.5">Total Token</th>
                                <th class="p-3.5">Sumber Eksekusi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700 font-mono text-[11px]">
                            @forelse($tokenMetrics['logs'] as $log)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="p-3.5 text-slate-500">{{ $log->created_at ? $log->created_at->format('H:i:s d/m/Y') : '-' }}</td>
                                    <td class="p-3.5 font-sans font-bold text-slate-800">
                                        {{ $log->jenis === 'evaluasi_risiko' ? 'Evaluasi Risiko K3L' : 'Ringkasan Modul Materi' }}
                                    </td>
                                    <td class="p-3.5 font-sans">
                                        {{ $log->kehadiran->mahasiswa->nama ?? 'Sistem Modul Penuntun' }}
                                    </td>
                                    <td class="p-3.5 text-indigo-600">{{ $log->token_masuk }}</td>
                                    <td class="p-3.5 text-indigo-600">{{ $log->token_keluar }}</td>
                                    <td class="p-3.5 font-bold text-slate-900">{{ $log->token_masuk + $log->token_keluar }}</td>
                                    <td class="p-3.5 font-sans">
                                        @if($log->dari_cache)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1 w-fit">
                                                <span>[CACHE]</span> Hemat 100%
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 flex items-center gap-1 w-fit">
                                                <span>[API]</span> LiteLLM Direct
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="p-4 text-center text-slate-400">Belum ada riwayat transaksi token.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>

    <!-- MODAL PREVIEW AI SUMMARY UNTUK DOSEN -->
    <div x-show="showAiModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-3xl border border-slate-200 max-w-2xl w-full p-6 sm:p-8 shadow-2xl space-y-5 max-h-[90vh] overflow-y-auto">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-bold text-sm">
                        AI
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Pratinjau Ringkasan Cerdas AI</h3>
                        <p class="text-xs text-slate-500" x-text="'Materi: ' + currentPreviewTitle"></p>
                    </div>
                </div>
                <button type="button" @click="showAiModal = false" class="text-slate-400 hover:text-slate-800 text-lg font-bold">&times;</button>
            </div>

            <!-- Loading State -->
            <div x-show="aiLoading" class="p-8 text-center text-slate-500 space-y-2">
                <div class="animate-spin w-6 h-6 border-2 border-indigo-600 border-t-transparent rounded-full mx-auto"></div>
                <p class="text-xs">Menyusun ringkasan K3L & parameter materi...</p>
            </div>

            <!-- Content State -->
            <div x-show="!aiLoading" class="space-y-4 text-xs">
                <div class="p-4 rounded-2xl bg-indigo-50/50 border border-indigo-100 space-y-1.5">
                    <span class="text-[11px] font-bold text-indigo-700 uppercase tracking-wider block">Ringkasan Eksekutif Praktikum</span>
                    <p class="text-slate-800 leading-relaxed font-medium" x-text="aiData.ringkasan_eksekutif"></p>
                </div>

                <div class="p-4 rounded-2xl bg-rose-50/40 border border-rose-100 space-y-2">
                    <span class="text-[11px] font-bold text-rose-700 uppercase tracking-wider block">Poin Kritis Keselamatan (K3L)</span>
                    <ul class="list-disc list-inside space-y-1 text-slate-700">
                        <template x-for="item in (aiData.fokus_keselamatan || [])">
                            <li x-text="item"></li>
                        </template>
                    </ul>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-1.5">
                    <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider block">Standar APD Prioritas</span>
                    <p class="text-slate-700" x-text="aiData.apd_prioritas"></p>
                </div>

                <div class="p-4 rounded-2xl bg-amber-50/40 border border-amber-200/60 space-y-1.5">
                    <span class="text-[11px] font-bold text-amber-800 uppercase tracking-wider block">Protokol Penanganan Limbah</span>
                    <p class="text-amber-900 font-medium" x-text="aiData.penanganan_limbah"></p>
                </div>

                <div class="p-4 rounded-2xl bg-emerald-50/40 border border-emerald-100 space-y-1.5">
                    <span class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider block">Tips Sukses Presisi Praktikum</span>
                    <p class="text-emerald-900" x-text="aiData.tips_sukses"></p>
                </div>
            </div>

            <div class="pt-2 text-right">
                <button type="button" @click="showAiModal = false" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800">
                    Tutup Pratinjau
                </button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500">
        <p>LabPass AI &copy; 2026 — Modul Dosen & Aslab Terpadu dengan Manajemen Materi & Ringkasan AI.</p>
    </footer>

    <script>
        function adminApp() {
            return {
                tab: 'approval',
                materiMode: 'list',
                showAiModal: false,
                aiLoading: false,
                currentPreviewTitle: '',
                aiData: {},

                async openAiPreview(materiId, title) {
                    this.currentPreviewTitle = title;
                    this.showAiModal = true;
                    this.aiLoading = true;
                    try {
                        const res = await fetch('/admin/materi/' + materiId + '/ai-preview');
                        this.aiData = await res.json();
                    } catch (e) {
                        this.aiData = { ringkasan_eksekutif: 'Gagal memuat ringkasan AI.' };
                    } finally {
                        this.aiLoading = false;
                    }
                }
            }
        }

        function bahanSection() {
            return {
                items: [
                    { jumlah: 50, satuan: 'ml' },
                    { jumlah: 50, satuan: 'ml' }
                ],
                addBahan() { this.items.push({ jumlah: 10, satuan: 'ml' }); },
                removeBahan(index) { if (this.items.length > 1) this.items.splice(index, 1); }
            }
        }

        function alatSection() {
            return {
                items: [
                    { jumlah: 1 },
                    { jumlah: 1 }
                ],
                addAlat() { this.items.push({ jumlah: 1 }); },
                removeAlat(index) { if (this.items.length > 1) this.items.splice(index, 1); }
            }
        }

        function limbahSection() {
            return {
                items: [
                    { nama: 'Cairan Sisa Reaksi', kandungan: 'Sisa titran dan analit' }
                ],
                addLimbah() { this.items.push({ nama: 'Cairan Pencucian', kandungan: 'Bilas akuades & sisa reagen' }); },
                removeLimbah(index) { if (this.items.length > 1) this.items.splice(index, 1); }
            }
        }
    </script>
</body>
</html>
