<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kehadiran;
use App\Models\PembuanganLimbah;

class SesiMahasiswaController extends Controller
{
    public function show($token)
    {
        $kehadiran = Kehadiran::with([
            'mahasiswa',
            'sesi.kelas',
            'sesi.materi.bahan',
            'sesi.materi.alat',
            'sesi.materi.limbah.jerigen',
            'pembuanganLimbah'
        ])->where('token', $token)->firstOrFail();

        if (!in_array($kehadiran->status, ['lulus', 'dispensasi_disetujui'])) {
            return redirect()->route('kios.index')->with('error', 'Akses belum diizinkan oleh sistem atau Dosen.');
        }

        return view('sesi.show', compact('kehadiran'));
    }

    public function checkout(Request $request, $token)
    {
        $request->validate([
            'limbah' => 'required|array',
            'limbah.*.id' => 'required|exists:materi_limbah,id',
            'limbah.*.volume' => 'required|numeric|min:1'
        ]);

        $kehadiran = Kehadiran::where('token', $token)->firstOrFail();

        foreach ($request->limbah as $item) {
            PembuanganLimbah::create([
                'kehadiran_id' => $kehadiran->id,
                'materi_limbah_id' => $item['id'],
                'volume_ml' => $item['volume'],
                'dibuang_pada' => now(),
            ]);
        }

        $kehadiran->update(['jam_keluar' => now()]);

        return response()->json([
            'status' => 'success',
            'message' => 'Limbah berhasil diverifikasi dan praktikum selesai dicatat.'
        ]);
    }
}
