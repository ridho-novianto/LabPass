<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Sesi;
use App\Models\Kelas;
use App\Models\Materi;
use App\Models\Kehadiran;
use App\Models\PembuanganLimbah;
use App\Models\Apd;
use App\Models\Bahan;
use App\Models\Alat;
use App\Models\Jerigen;
use App\Models\MataPraktikum;
use App\Models\TokenLog;
use App\Services\RisikoEvaluator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AdminDashboardController extends Controller
{
    public function loginForm()
    {
        $users = User::all();
        return view('auth.login', compact('users'));
    }

    public function login(Request $request)
    {
        if ($request->filled('user_id')) {
            $user = User::findOrFail($request->user_id);
            Auth::login($user);
            return redirect()->route('admin.dashboard');
        }

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors(['email' => 'Email atau password salah.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function index()
    {
        $activeUser = Auth::user();
        if (!$activeUser) {
            return redirect()->route('login');
        }

        $kelasList = Kelas::all();
        $materiList = Materi::with([
            'mataPraktikum',
            'bahan',
            'alat',
            'apd',
            'limbah.jerigen',
            'createdBy'
        ])->orderBy('id', 'desc')->get();

        $mataPraktikumList = MataPraktikum::orderBy('nama')->get();
        $apdList = Apd::all();
        $bahanList = Bahan::orderBy('nama')->get();
        $alatList = Alat::orderBy('nama')->get();
        $jerigenList = Jerigen::orderBy('kode')->get();

        $aslabList = User::where('role', 'aslab')->get();
        $sesiList = Sesi::with(['kelas', 'materi', 'aslab'])->orderBy('id', 'desc')->get();

        // Antrean verifikasi APD dari YOLO
        $antreanVerifikasi = Kehadiran::with(['mahasiswa', 'sesi.materi'])
            ->where('status', 'menunggu_persetujuan_dosen')
            ->orderBy('id', 'desc')
            ->get();

        // Riwayat kehadiran & status limbah
        $riwayatKehadiran = Kehadiran::with([
            'mahasiswa',
            'sesi.materi',
            'sesi.kelas',
            'pembuanganLimbah.materiLimbah.jerigen'
        ])
        ->where('status', '!=', 'menunggu_persetujuan_dosen')
        ->orderBy('id', 'desc')
        ->get();

        $stats = [
            'antrean' => $antreanVerifikasi->count(),
            'aktif' => $riwayatKehadiran->where('jam_keluar', null)->count(),
            'limbah_dibuang' => $riwayatKehadiran->filter(fn($k) => $k->pembuanganLimbah->isNotEmpty())->count(),
            'total_hari_ini' => $riwayatKehadiran->count() + $antreanVerifikasi->count(),
            'total_materi' => $materiList->count(),
        ];

        // Bukti Metrik Pemakaian Token AI (Token Tracker)
        $tokenLogs = TokenLog::with('kehadiran.mahasiswa')->orderBy('id', 'desc')->get();
        $totalTokens = $tokenLogs->sum(fn($t) => $t->token_masuk + $t->token_keluar);
        $totalCalls = $tokenLogs->count();
        $cacheHits = $tokenLogs->where('dari_cache', true)->count();
        $cachePercentage = $totalCalls > 0 ? round(($cacheHits / $totalCalls) * 100, 1) : 0;
        $avgTokens = $totalCalls > 0 ? round($totalTokens / $totalCalls) : 0;

        $tokenMetrics = [
            'total_token' => $totalTokens,
            'total_calls' => $totalCalls,
            'cache_percentage' => $cachePercentage,
            'avg_token' => $avgTokens,
            'logs' => $tokenLogs->take(8),
        ];

        return view('admin.dashboard', compact(
            'activeUser',
            'kelasList',
            'materiList',
            'mataPraktikumList',
            'apdList',
            'bahanList',
            'alatList',
            'jerigenList',
            'aslabList',
            'sesiList',
            'antreanVerifikasi',
            'riwayatKehadiran',
            'stats',
            'tokenMetrics'
        ));
    }

    public function approveKehadiran(Request $request, $id)
    {
        $kehadiran = Kehadiran::findOrFail($id);
        
        $sesiId = $request->sesi_id ?? Sesi::first()->id ?? 1;

        $kehadiran->update([
            'status' => 'disetujui',
            'sesi_id' => $sesiId,
        ]);

        return back()->with('success', 'Mahasiswa ' . $kehadiran->mahasiswa->nama . ' berhasil di-APPROVE (ACC). Mahasiswa sekarang diarahkan memilih materi praktikum.');
    }

    public function tolakKehadiran($id)
    {
        $kehadiran = Kehadiran::findOrFail($id);
        $kehadiran->update(['status' => 'ditolak']);
        return back()->with('info', 'Verifikasi mahasiswa ' . $kehadiran->mahasiswa->nama . ' telah ditolak.');
    }

    public function storeSesi(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'materi_id' => 'required|exists:materi,id',
            'tanggal' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'ruangan' => 'required|string',
        ]);

        Sesi::create([
            'kelas_id' => $request->kelas_id,
            'materi_id' => $request->materi_id,
            'aslab_id' => $request->aslab_id ?? null,
            'tanggal' => $request->tanggal,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'ruangan' => $request->ruangan,
            'dibuat_oleh' => Auth::id() ?? 1,
        ]);

        return back()->with('success', 'Jadwal sesi kelas & materi berhasil dipublikasikan!');
    }

    /**
     * Upload & Tambah Materi Baru Lengkap (PDF, APD, Bahan, Alat, Limbah)
     */
    public function storeMateri(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'mata_praktikum_id' => 'required|exists:mata_praktikum,id',
            'tingkat_bahaya' => 'required|in:rendah,sedang,tinggi',
            'deskripsi' => 'nullable|string',
            'file_materi' => 'nullable|file|mimes:pdf,doc,docx,txt|max:20480',
        ]);

        $filePath = null;
        if ($request->hasFile('file_materi')) {
            $file = $request->file('file_materi');
            $dir = storage_path('app/public/materi');
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            $filename = 'materi_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $filename);
            $filePath = '/storage/materi/' . $filename;
        }

        $materi = Materi::create([
            'mata_praktikum_id' => $request->mata_praktikum_id,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'file_path' => $filePath,
            'tingkat_bahaya' => $request->tingkat_bahaya,
            'dibuat_oleh' => Auth::id() ?? 1,
        ]);

        // Attach APD
        if ($request->filled('apd_ids')) {
            $materi->apd()->sync($request->apd_ids);
        }

        // Attach Bahan
        if ($request->filled('bahan') && is_array($request->bahan)) {
            $bahanSync = [];
            foreach ($request->bahan as $b) {
                if (!empty($b['id']) && isset($b['jumlah'])) {
                    $bahanSync[$b['id']] = [
                        'jumlah' => $b['jumlah'],
                        'satuan' => $b['satuan'] ?? 'ml'
                    ];
                }
            }
            if (!empty($bahanSync)) {
                $materi->bahan()->sync($bahanSync);
            }
        }

        // Attach Alat
        if ($request->filled('alat') && is_array($request->alat)) {
            $alatSync = [];
            foreach ($request->alat as $a) {
                if (!empty($a['id']) && isset($a['jumlah'])) {
                    $alatSync[$a['id']] = [
                        'jumlah' => $a['jumlah']
                    ];
                }
            }
            if (!empty($alatSync)) {
                $materi->alat()->sync($alatSync);
            }
        }

        // Add Limbah
        if ($request->filled('limbah') && is_array($request->limbah)) {
            foreach ($request->limbah as $l) {
                if (!empty($l['nama']) && !empty($l['jerigen_id'])) {
                    $materi->limbah()->create([
                        'nama' => $l['nama'],
                        'kandungan' => $l['kandungan'] ?? '-',
                        'jerigen_id' => $l['jerigen_id'],
                        'catatan' => $l['catatan'] ?? null,
                    ]);
                }
            }
        }

        // Pre-generate AI Summary cache for fast student view
        try {
            app(RisikoEvaluator::class)->summarizeMateri($materi);
        } catch (\Exception $e) {
            // Non-blocking
        }

        return back()->with('success', 'Materi praktikum "' . $materi->judul . '" beserta dokumen & konfigurasi K3L berhasil disimpan!');
    }

    /**
     * Upload / Ganti File Modul PDF untuk Materi yang Sudah Ada
     */
    public function uploadFileMateri(Request $request, $id)
    {
        $request->validate([
            'file_materi' => 'required|file|mimes:pdf,doc,docx,txt|max:20480',
        ]);

        $materi = Materi::findOrFail($id);

        $file = $request->file('file_materi');
        $dir = storage_path('app/public/materi');
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $filename = 'materi_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
        $file->move($dir, $filename);

        $materi->update([
            'file_path' => '/storage/materi/' . $filename,
        ]);

        // Invalidate previous AI summary cache
        Cache::forget("materi_ai_summary_{$materi->id}");
        try {
            app(RisikoEvaluator::class)->summarizeMateri($materi);
        } catch (\Exception $e) {
            // Non-blocking
        }

        return back()->with('success', 'File dokumen modul untuk "' . $materi->judul . '" berhasil diunggah!');
    }

    /**
     * Hapus Materi Praktikum
     */
    public function destroyMateri($id)
    {
        $materi = Materi::findOrFail($id);
        $judul = $materi->judul;

        // Check if used by sessions
        if ($materi->sesi()->exists()) {
            return back()->with('info', 'Materi "' . $judul . '" tidak dapat dihapus karena sudah terikat dengan sesi praktikum.');
        }

        if ($materi->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($materi->file_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($materi->file_path);
        }

        Cache::forget("materi_ai_summary_{$materi->id}");
        $materi->delete();

        return back()->with('success', 'Materi "' . $judul . '" berhasil dihapus.');
    }

    /**
     * Preview AI Summary untuk Dosen
     */
    public function previewAiSummary($id)
    {
        $materi = Materi::findOrFail($id);
        $summary = app(RisikoEvaluator::class)->summarizeMateri($materi);
        return response()->json($summary);
    }
}
