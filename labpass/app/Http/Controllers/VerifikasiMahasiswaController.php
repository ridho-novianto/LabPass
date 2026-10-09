<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mahasiswa;
use App\Models\Kehadiran;
use App\Models\Sesi;
use App\Models\Materi;
use App\Models\PembuanganLimbah;
use App\Services\RisikoEvaluator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class VerifikasiMahasiswaController extends Controller
{
    public function index()
    {
        return view('verifikasi.index');
    }

    public function scan(Request $request)
    {
        $request->validate([
            'nim' => 'required|string',
            'nama' => 'required|string',
        ]);

        $nim = trim($request->nim);
        $nama = trim($request->nama);

        $mahasiswa = Mahasiswa::firstOrCreate(
            ['nim' => $nim],
            ['nama' => $nama]
        );

        $storageScansDir = storage_path('app/public/scans');
        if (!file_exists($storageScansDir)) {
            mkdir($storageScansDir, 0777, true);
        }

        $timestamp = time() . '_' . Str::random(6);
        $inputPath = $storageScansDir . '/input_' . $timestamp . '.jpg';
        $outputPath = $storageScansDir . '/annotated_' . $timestamp . '.jpg';

        // Check if file uploaded or webcam base64
        if ($request->hasFile('foto')) {
            $request->file('foto')->move($storageScansDir, 'input_' . $timestamp . '.jpg');
        } elseif ($request->filled('webcam_image')) {
            $base64 = $request->webcam_image;
            if (str_contains($base64, ',')) {
                $base64 = explode(',', $base64)[1];
            }
            file_put_contents($inputPath, base64_decode($base64));
        } else {
            // Default fallback to sample_test.jpg
            copy('/home/veinnn/hackathon-pens/LapPass/sample_test.jpg', $inputPath);
        }

        // Run YOLO inference script with Python venv
        $pythonBin = '/home/veinnn/hackathon-pens/LapPass/venv/bin/python';
        $scriptPath = '/home/veinnn/hackathon-pens/LapPass/inference_checklist.py';
        $cmd = escapeshellcmd("$pythonBin $scriptPath " . escapeshellarg($inputPath) . " " . escapeshellarg($outputPath));
        
        $output = shell_exec($cmd);

        $checklist = [];
        $isCompliant = false;

        if ($output) {
            $startIdx = strpos($output, '{');
            $endIdx = strrpos($output, '}');
            if ($startIdx !== false && $endIdx !== false) {
                $jsonStr = substr($output, $startIdx, ($endIdx - $startIdx + 1));
                $parsed = json_decode($jsonStr, true);
                if ($parsed) {
                    $isCompliant = $parsed['is_compliant'] ?? false;
                    $checklist = $parsed['checklist'] ?? [];
                }
            }
        }

        // Extract detected & missing
        $detected = [];
        $missing = [];
        foreach ($checklist as $item) {
            if (!empty($item['detected'])) {
                $detected[] = $item['item'];
            } else {
                $missing[] = $item['item'];
            }
        }

        $token = (string) Str::ulid();
        $annotatedUrl = file_exists($outputPath) 
            ? '/storage/scans/annotated_' . $timestamp . '.jpg' 
            : '/storage/scans/input_' . $timestamp . '.jpg';

        $kehadiran = Kehadiran::create([
            'mahasiswa_id' => $mahasiswa->id,
            'sesi_id' => null, // Sesi akan dipilih / di-assign dosen
            'token' => $token,
            'status' => 'menunggu_persetujuan_dosen',
            'apd_terdeteksi' => $detected,
            'apd_kurang' => $missing,
            'snapshot_path' => $annotatedUrl,
            'jam_masuk' => now(),
        ]);

        return response()->json([
            'status' => 'ok',
            'token' => $token,
            'is_compliant' => $isCompliant,
            'checklist' => $checklist,
            'detected' => $detected,
            'missing' => $missing,
            'annotated_image' => $annotatedUrl,
            'redirect' => route('verifikasi.status', ['token' => $token], false)
        ]);
    }

    public function status($token)
    {
        $kehadiran = Kehadiran::with(['mahasiswa', 'sesi.kelas', 'sesi.materi'])->where('token', $token)->firstOrFail();
        $sesiList = Sesi::with(['kelas', 'materi'])->orderBy('id', 'desc')->get();
        return view('verifikasi.status', compact('kehadiran', 'sesiList'));
    }

    public function checkStatusAjax($token)
    {
        $kehadiran = Kehadiran::with('sesi.materi')->where('token', $token)->first();
        if (!$kehadiran) {
            return response()->json(['status' => 'not_found'], 404);
        }

        return response()->json([
            'status' => $kehadiran->status,
            'is_approved' => in_array($kehadiran->status, ['disetujui', 'lulus', 'dispensasi_disetujui']),
            'sesi_id' => $kehadiran->sesi_id,
            'sesi_nama' => $kehadiran->sesi->materi->judul ?? null,
        ]);
    }

    public function pilihMateri(Request $request, $token)
    {
        $request->validate([
            'sesi_id' => 'required|exists:sesi,id'
        ]);

        $kehadiran = Kehadiran::where('token', $token)->firstOrFail();
        $kehadiran->update([
            'sesi_id' => $request->sesi_id,
            'status' => 'praktikum_berjalan'
        ]);

        return response()->json([
            'status' => 'ok',
            'redirect' => route('praktikum.show', ['token' => $token], false)
        ]);
    }

    public function praktikum($token)
    {
        $kehadiran = Kehadiran::with([
            'mahasiswa',
            'sesi.kelas',
            'sesi.materi.mataPraktikum',
            'sesi.materi.apd',
            'sesi.materi.bahan',
            'sesi.materi.alat',
            'sesi.materi.limbah.jerigen',
            'pembuanganLimbah'
        ])->where('token', $token)->firstOrFail();

        $aiSummary = null;
        if ($kehadiran->sesi && $kehadiran->sesi->materi) {
            try {
                $evaluator = app(RisikoEvaluator::class);
                $aiSummary = $evaluator->summarizeMateri($kehadiran->sesi->materi);
            } catch (\Exception $e) {
                $aiSummary = null;
            }
        }

        return view('verifikasi.praktikum', compact('kehadiran', 'aiSummary'));
    }

    public function getAiSummary($token)
    {
        $kehadiran = Kehadiran::with(['sesi.materi.bahan', 'sesi.materi.alat', 'sesi.materi.apd', 'sesi.materi.limbah.jerigen'])
            ->where('token', $token)
            ->firstOrFail();

        if (!$kehadiran->sesi || !$kehadiran->sesi->materi) {
            return response()->json(['error' => 'Sesi atau materi praktikum tidak ditemukan.'], 404);
        }

        $evaluator = app(RisikoEvaluator::class);
        $summary = $evaluator->summarizeMateri($kehadiran->sesi->materi);

        return response()->json($summary);
    }

    public function checkout(Request $request, $token)
    {
        $request->validate([
            'volume_ml' => 'required|numeric|min:1',
            'limbah_id' => 'required|exists:materi_limbah,id'
        ]);

        $kehadiran = Kehadiran::where('token', $token)->firstOrFail();

        PembuanganLimbah::create([
            'kehadiran_id' => $kehadiran->id,
            'materi_limbah_id' => $request->limbah_id,
            'volume_ml' => $request->volume_ml,
            'dibuang_pada' => now(),
        ]);

        $kehadiran->update(['jam_keluar' => now(), 'status' => 'selesai']);

        return response()->json([
            'status' => 'ok',
            'message' => 'Limbah berhasil dibuang dan praktikum selesai dicatat.'
        ]);
    }
}
