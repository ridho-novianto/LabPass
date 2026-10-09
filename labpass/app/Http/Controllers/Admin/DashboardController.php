<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sesi;
use App\Models\Kelas;
use App\Models\Materi;
use App\Models\Kehadiran;
use App\Models\Dispensasi;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function loginForm()
    {
        $users = User::all();
        return view('auth.login', compact('users'));
    }

    public function login(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        Auth::login($user);
        return redirect()->route('admin.dashboard');
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('kios.index');
    }

    public function index()
    {
        $activeUser = Auth::user() ?? User::where('role', 'dosen')->first();

        $sesiList = Sesi::with(['kelas', 'materi', 'aslab'])->orderBy('id', 'desc')->get();
        $kelasList = Kelas::all();
        $materiList = Materi::all();
        $aslabList = User::where('role', 'aslab')->get();

        $kehadiranList = Kehadiran::with([
            'mahasiswa', 
            'sesi.materi', 
            'sesi.kelas', 
            'dispensasi', 
            'pembuanganLimbah.materiLimbah.jerigen'
        ])->orderBy('id', 'desc')->get();

        $pendingDispensasi = Dispensasi::with(['kehadiran.mahasiswa', 'kehadiran.sesi.materi'])
            ->where('status', 'menunggu')
            ->get();

        $stats = [
            'total_hadir' => $kehadiranList->count(),
            'lulus' => $kehadiranList->where('status', 'lulus')->count(),
            'dispensasi' => $pendingDispensasi->count(),
            'limbah_selesai' => $kehadiranList->filter(fn($k) => $k->pembuanganLimbah->isNotEmpty())->count(),
        ];

        return view('admin.dashboard', compact(
            'activeUser',
            'sesiList',
            'kelasList',
            'materiList',
            'aslabList',
            'kehadiranList',
            'pendingDispensasi',
            'stats'
        ));
    }

    public function approveDispensasi($id)
    {
        $dispensasi = Dispensasi::findOrFail($id);
        $dispensasi->update([
            'status' => 'disetujui',
            'diputuskan_oleh' => Auth::id() ?? 1,
            'diputuskan_pada' => now(),
            'catatan' => 'Disetujui oleh Dosen PJ Praktikum'
        ]);

        $dispensasi->kehadiran->update(['status' => 'dispensasi_disetujui']);

        return back()->with('success', 'Dispensasi disetujui. Mahasiswa sekarang dapat masuk ke materi.');
    }

    public function rejectDispensasi($id)
    {
        $dispensasi = Dispensasi::findOrFail($id);
        $dispensasi->update([
            'status' => 'ditolak',
            'diputuskan_oleh' => Auth::id() ?? 1,
            'diputuskan_pada' => now(),
            'catatan' => 'Ditolak: APD wajib tidak boleh diabaikan untuk materi berisiko tinggi.'
        ]);

        $dispensasi->kehadiran->update(['status' => 'ditolak']);

        return back()->with('info', 'Dispensasi ditolak.');
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

        return back()->with('success', 'Jadwal sesi praktikum berhasil ditambahkan!');
    }
}
