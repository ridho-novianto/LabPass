<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sesi;
use App\Models\Mahasiswa;
use App\Models\Kehadiran;
use App\Models\Dispensasi;
use Illuminate\Support\Str;

class KiosController extends Controller
{
    public function index()
    {
        $sesiList = Sesi::with(['kelas', 'materi.apd', 'materi.bahan', 'materi.alat', 'materi.limbah.jerigen'])
            ->orderBy('id', 'desc')
            ->get();
        return view('kios.index', compact('sesiList'));
    }

    public function verifikasi(Request $request)
    {
        $request->validate([
            'nim' => 'required|string',
            'nama' => 'required|string',
            'sesi_id' => 'required|exists:sesi,id',
            'mode_apd' => 'required|in:lengkap,kurang'
        ]);

        $mahasiswa = Mahasiswa::firstOrCreate(
            ['nim' => trim($request->nim)],
            ['nama' => trim($request->nama)]
        );

        $sesi = Sesi::with('materi.apd')->findOrFail($request->sesi_id);
        $wajibApd = $sesi->materi->apd->pluck('nama')->toArray();

        if (empty($wajibApd)) {
            $wajibApd = ['Jas Laboratorium', 'Masker', 'Kacamata Pelindung', 'Sarung Tangan'];
        }

        if ($request->mode_apd === 'lengkap') {
            $terdeteksi = $wajibApd;
            $kurang = [];
            $status = 'lulus';
        } else {
            $kurang = ['Kacamata Pelindung'];
            $terdeteksi = array_values(array_diff($wajibApd, $kurang));
            $status = 'ditolak';
        }

        $token = (string) Str::ulid();

        $kehadiran = Kehadiran::updateOrCreate(
            ['sesi_id' => $sesi->id, 'mahasiswa_id' => $mahasiswa->id],
            [
                'token' => $token,
                'status' => $status,
                'apd_terdeteksi' => $terdeteksi,
                'apd_kurang' => $kurang,
                'jam_masuk' => now(),
            ]
        );

        return response()->json([
            'status' => $kehadiran->status,
            'token' => $kehadiran->token,
            'terdeteksi' => $terdeteksi,
            'kurang' => $kurang,
            'redirect' => $kehadiran->status === 'lulus' ? route('sesi.show', ['token' => $kehadiran->token]) : null
        ]);
    }

    public function mintaDispensasi(Request $request)
    {
        $request->validate([
            'token' => 'required|exists:kehadiran,token',
            'alasan' => 'required|string'
        ]);

        $kehadiran = Kehadiran::where('token', $request->token)->firstOrFail();
        $kehadiran->update(['status' => 'dispensasi_diajukan']);

        Dispensasi::updateOrCreate(
            ['kehadiran_id' => $kehadiran->id],
            [
                'alasan' => $request->alasan,
                'status' => 'menunggu'
            ]
        );

        return response()->json([
            'status' => 'dispensasi_diajukan',
            'message' => 'Dispensasi terkirim ke panel Dosen. Menunggu ACC manual.'
        ]);
    }

    public function cekStatus($token)
    {
        $kehadiran = Kehadiran::where('token', $token)->first();
        if (!$kehadiran) {
            return response()->json(['status' => 'not_found'], 404);
        }

        return response()->json([
            'status' => $kehadiran->status,
            'redirect' => in_array($kehadiran->status, ['lulus', 'dispensasi_disetujui']) 
                ? route('sesi.show', ['token' => $kehadiran->token]) 
                : null
        ]);
    }
}
