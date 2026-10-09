<?php

namespace App\Services;

use App\Enums\StatusKehadiran;
use App\Models\Kehadiran;
use App\Models\Materi;
use App\Models\TokenLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class RisikoEvaluator
{
    public function evaluate(array $data): array
    {
        $cacheKey = $this->generateCacheKey($data);
        
        // Try to get from cache first
        $cachedResult = Cache::get($cacheKey);
        if ($cachedResult !== null) {
            try {
                TokenLog::create([
                    'kehadiran_id' => $data['kehadiran_id'] ?? null,
                    'jenis' => 'evaluasi_risiko',
                    'token_masuk' => 65,
                    'token_keluar' => 80,
                    'dari_cache' => true,
                ]);
            } catch (\Exception $e) {}

            return [
                'risiko' => $cachedResult['risiko'],
                'alasan' => $cachedResult['alasan'],
                'saran' => $cachedResult['saran'],
                'dari_cache' => true,
            ];
        }

        try {
            $result = $this->evaluateViaLLM($data);
            
            // Cache the result
            Cache::put($cacheKey, $result, 3600); // 1 hour
            
            return array_merge($result, ['dari_cache' => false]);
        } catch (\Exception $e) {
            // Fallback to simple rule-based evaluation
            $fallbackResult = $this->evaluateFallback($data);
            
            // Cache the fallback result
            Cache::put($cacheKey, $fallbackResult, 3600); // 1 hour
            
            return array_merge($fallbackResult, ['dari_cache' => false]);
        }
    }

    private function evaluateViaLLM(array $data): array
    {
        $model = config('services.llm.model', 'gpt-3.5-turbo');
        $baseUrl = config('services.llm.base_url', 'https://api.openai.com/v1');
        $apiKey = config('services.llm.api_key', '');

        if (empty($apiKey)) {
            throw new \Exception('LLM API key not configured');
        }

        $prompt = $this->generatePrompt($data);

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
            'Content-Type' => 'application/json',
        ])->timeout(8)->post("{$baseUrl}/chat/completions", [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Anda adalah asisten AI yang ahli dalam mengevaluasi risiko keselamatan laboratorium. Berikan jawaban dalam format JSON.',
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'response_format' => ['type' => 'json_object'],
            'max_tokens' => 150,
            'temperature' => 0.2,
        ]);

        if (!$response->successful()) {
            throw new \Exception('LLM request failed');
        }

        $result = $response->json();
        $content = $result['choices'][0]['message']['content'];

        $parsed = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON response from LLM');
        }

        return [
            'risiko' => $parsed['risiko'] ?? 'tidak_diketahui',
            'alasan' => $parsed['alasan'] ?? 'Tidak dapat menentukan risiko',
            'saran' => $parsed['saran'] ?? 'Tidak ada saran',
        ];
    }

    private function generatePrompt(array $data): string
    {
        $materi = $data['materi'] ?? '';
        $bahaya = $data['bahaya'] ?? '';
        $bahan = implode(', ', $data['bahan'] ?? []);
        $kurang = implode(', ', $data['kurang'] ?? []);

        return "
Evaluasi risiko keselamatan laboratorium berdasarkan informasi berikut:
- Materi praktikum: {$materi}
- Tingkat bahaya: {$bahaya}
- Bahan yang digunakan: {$bahan}
- APD yang kurang: {$kurang}

Berikan penilaian risiko (tinggi, sedang, rendah), alasan singkat, dan saran tindakan dalam format JSON:
{
  \"risiko\": \"\",
  \"alasan\": \"\",
  \"saran\": \"\"
}";
    }

    private function evaluateFallback(array $data): array
    {
        $bahaya = $data['bahaya'] ?? '';
        $kurangCount = count($data['kurang'] ?? []);

        if ($bahaya === 'tinggi' && $kurangCount >= 2) {
            $risiko = 'tinggi';
            $alasan = 'Tingkat bahaya tinggi dengan banyak APD yang kurang';
            $saran = 'Pastikan semua APD wajib dipakai sebelum praktikum';
        } elseif ($bahaya === 'tinggi' && $kurangCount === 1) {
            $risiko = 'sedang';
            $alasan = 'Tingkat bahaya tinggi dengan satu APD kurang';
            $saran = 'Pastikan APD yang kurang dipakai sebelum praktikum';
        } else {
            $risiko = 'rendah';
            $alasan = 'Tingkat bahaya rendah atau APD lengkap';
            $saran = 'Lanjutkan praktikum dengan hati-hati';
        }

        return [
            'risiko' => $risiko,
            'alasan' => $alasan,
            'saran' => $saran,
        ];
    }

    private function generateCacheKey(array $data): string
    {
        $materiId = $data['materi_id'] ?? '';
        $kurang = $data['kurang'] ?? [];
        sort($kurang);
        
        return "risk_eval_{$materiId}_" . md5(implode('_', $kurang));
    }

    /**
     * AI Executive Summary & Safety Briefing for Student Module
     */
    public function summarizeMateri(Materi $materi): array
    {
        $materi->loadMissing(['bahan', 'alat', 'apd', 'limbah.jerigen', 'mataPraktikum']);
        $cacheKey = "materi_ai_summary_{$materi->id}";

        $cachedResult = Cache::get($cacheKey);
        if ($cachedResult !== null && is_array($cachedResult)) {
            try {
                TokenLog::create([
                    'jenis' => 'ringkasan_materi',
                    'token_masuk' => 120,
                    'token_keluar' => 200,
                    'dari_cache' => true,
                ]);
            } catch (\Exception $e) {}

            return array_merge($cachedResult, ['dari_cache' => true]);
        }

        try {
            $result = $this->summarizeViaLLM($materi);
            Cache::put($cacheKey, $result, 86400); // 24 jam
            return array_merge($result, ['dari_cache' => false]);
        } catch (\Exception $e) {
            $fallback = $this->summarizeFallback($materi);
            Cache::put($cacheKey, $fallback, 86400);
            return array_merge($fallback, ['dari_cache' => false]);
        }
    }

    private function summarizeViaLLM(Materi $materi): array
    {
        $apiKey = config('services.llm.api_key', '');
        if (empty($apiKey)) {
            throw new \Exception('LLM API key empty');
        }

        $model = config('services.llm.model', 'gpt-3.5-turbo');
        $baseUrl = config('services.llm.base_url', 'https://api.openai.com/v1');

        $bahanList = $materi->bahan->map(fn($b) => "{$b->nama} ({$b->pivot->jumlah} {$b->pivot->satuan}, Bahaya: {$b->tingkat_bahaya})")->implode(', ');
        $apdList = $materi->apd->pluck('nama')->implode(', ');
        $limbahList = $materi->limbah->map(fn($l) => "{$l->nama} -> Jerigen {$l->jerigen->kode} ({$l->jerigen->label})")->implode(', ');

        $prompt = "Anda adalah asisten AI K3L Laboratorium Kampus. Buatkan ringkasan eksekutif dan panduan keselamatan untuk modul praktikum berikut:
Judul: {$materi->judul}
Deskripsi: {$materi->deskripsi}
Tingkat Bahaya: {$materi->tingkat_bahaya}
Bahan Reagen: {$bahanList}
APD Wajib: {$apdList}
Aturan Limbah: {$limbahList}

Format JSON wajib:
{
  \"ringkasan_eksekutif\": \"1-2 kalimat ringkasan tujuan utama dan prosedur kunci praktikum\",
  \"fokus_keselamatan\": [\"Poin bahaya 1\", \"Poin bahaya 2\", \"Poin bahaya 3\"],
  \"apd_prioritas\": \"Penjelasan singkat mengapa APD yang ditentukan wajib dikenakan saat praktikum ini\",
  \"penanganan_limbah\": \"Instruksi khusus pembuangan limbah ke jerigen yang tepat\",
  \"tips_sukses\": \"Tips singkat agar praktikum aman dan presisi\"
}";

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
            'Content-Type' => 'application/json',
        ])->timeout(8)->post("{$baseUrl}/chat/completions", [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => 'Kembalikan respons valid JSON saja.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'response_format' => ['type' => 'json_object'],
            'max_tokens' => 500,
            'temperature' => 0.3,
        ]);

        if (!$response->successful()) {
            throw new \Exception('LLM API error');
        }

        $content = $response->json('choices.0.message.content');
        $parsed = json_decode($content, true);
        if (!$parsed || empty($parsed['ringkasan_eksekutif'])) {
            throw new \Exception('Invalid JSON');
        }

        return $parsed;
    }

    private function summarizeFallback(Materi $materi): array
    {
        $bahanNames = $materi->bahan->pluck('nama')->toArray();
        $bahanStr = implode(', ', $bahanNames);
        $apdNames = $materi->apd->pluck('nama')->implode(', ') ?: 'Jas Lab, Kacamata Pelindung, Masker, Sarung Tangan';

        $hasCorrosive = false;
        $hasFlammable = false;
        foreach ($materi->bahan as $b) {
            $rumus = strtoupper($b->rumus ?? '');
            if (in_array($rumus, ['HCL', 'NAOH', 'H2SO4', 'HNO3'])) {
                $hasCorrosive = true;
            }
            if (in_array($rumus, ['C2H5OH', 'CH3OH', 'ACETONE'])) {
                $hasFlammable = true;
            }
        }

        $fokus = [];
        if ($hasCorrosive) {
            $fokus[] = 'Bahan asam/basa kuat bersifat sangat korosif dan iritatif terhadap jaringan kulit serta membran mukosa mata.';
            $fokus[] = 'Reaksi netralisasi dapat menimbulkan pelepasan panas (eksoterm). Lakukan penambahan reagen secara perlahan melalui buret.';
        }
        if ($hasFlammable) {
            $fokus[] = 'Terdapat pelarut organik mudah terbakar. Jauhkan dari sumber api terbuka dan pemanas langsung.';
        }
        if (empty($fokus)) {
            $fokus[] = 'Pastikan reagen kimia ditakar sesuai konsentrasi yang telah ditentukan dalam instruksi penuntun.';
            $fokus[] = 'Gunakan lemari asam (fume hood) jika timbul uap atau bau gas menyengat selama reaksi.';
        }
        $fokus[] = 'Dilarang memipet larutan reagen kimia menggunakan mulut secara langsung (gunakan filler/pipet volumetrik).';

        $limbahItems = [];
        foreach ($materi->limbah as $l) {
            $kode = $l->jerigen->kode ?? 'JR-A';
            $label = $l->jerigen->label ?? 'Limbah Laboratorium';
            $limbahItems[] = "{$l->nama} wajib ditampung ke Jerigen {$kode} ({$label})";
        }
        $limbahText = !empty($limbahItems) 
            ? implode('. ', $limbahItems) . '. Dilarang membuang cairan sisa titrasi ke wastafel air!'
            : 'Seluruh sisa larutan kimia wajib dituangkan ke jerigen limbah bertanda khusus di sudut ruangan.';

        return [
            'ringkasan_eksekutif' => "Modul {$materi->judul} melatih mahasiswa melakukan pengukuran kuantitatif larutan menggunakan reagen {$bahanStr}. Prosedur membutuhkan ketelitian pengamatan titik ekuivalen dan kepatuhan penuh terhadap SOP keselamatan laboratorium.",
            'fokus_keselamatan' => $fokus,
            'apd_prioritas' => "Wajib mengenakan {$apdNames}. Jas lab melindungi tubuh dari cipratan reagen, sarung tangan memproteksi kontak langsung kulit, dan kacamata mencegah risiko bahaya aerosol/percikan mata.",
            'penanganan_limbah' => $limbahText,
            'tips_sukses' => 'Bilas buret dengan sedikit larutan titran sebelum pengisian penuh. Pastikan tidak ada gelembung udara pada ujung buret untuk menjamin akurasi volume pembacaan meniskus.',
        ];
    }
}
