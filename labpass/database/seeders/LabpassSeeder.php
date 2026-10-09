<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Enums\StatusKehadiran;
use App\Enums\StatusDispensasi;
use App\Enums\TingkatBahaya;
use App\Models\Apd;
use App\Models\Bahan;
use App\Models\Jerigen;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\MataPraktikum;
use App\Models\Materi;
use App\Models\Sesi;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LabpassSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create demo users
        $dosen = User::create([
            'name' => 'Dosen Demo',
            'email' => 'dosen@labpass.test',
            'password' => Hash::make('password'),
            'role' => Role::DOSEN->value,
        ]);

        $aslab = User::create([
            'name' => 'Aslab Demo',
            'email' => 'aslab@labpass.test',
            'password' => Hash::make('password'),
            'role' => Role::ASLAB->value,
        ]);

        // Create APD items
        $apdItems = [
            ['kode' => 'jas_lab', 'nama' => 'Jas Laboratorium'],
            ['kode' => 'masker', 'nama' => 'Masker'],
            ['kode' => 'kacamata', 'nama' => 'Kacamata Pelindung'],
            ['kode' => 'sarung_tangan', 'nama' => 'Sarung Tangan'],
        ];

        foreach ($apdItems as $item) {
            Apd::create($item);
        }

        // Create bahan
        $bahans = [
            ['nama' => 'HCl', 'rumus' => 'HCl', 'tingkat_bahaya' => TingkatBahaya::TINGGI->value, 'simbol_bahaya' => 'C', 'penanganan' => 'Gunakan dalam fume hood'],
            ['nama' => 'NaOH', 'rumus' => 'NaOH', 'tingkat_bahaya' => TingkatBahaya::TINGGI->value, 'simbol_bahaya' => 'C', 'penanganan' => 'Gunakan dalam fume hood'],
            ['nama' => 'H2SO4', 'rumus' => 'H2SO4', 'tingkat_bahaya' => TingkatBahaya::TINGGI->value, 'simbol_bahaya' => 'C', 'penanganan' => 'Gunakan dalam fume hood'],
            ['nama' => 'Etanol', 'rumus' => 'C2H5OH', 'tingkat_bahaya' => TingkatBahaya::SEDANG->value, 'simbol_bahaya' => 'F', 'penanganan' => 'Gunakan dalam fume hood'],
            ['nama' => 'Asam Asetat', 'rumus' => 'CH3COOH', 'tingkat_bahaya' => TingkatBahaya::SEDANG->value, 'simbol_bahaya' => 'Xi', 'penanganan' => 'Gunakan dalam fume hood'],
            ['nama' => 'Natrium Klorida', 'rumus' => 'NaCl', 'tingkat_bahaya' => TingkatBahaya::RENDAH->value, 'simbol_bahaya' => 'Xn', 'penanganan' => 'Tidak memerlukan perlindungan khusus'],
            ['nama' => 'Glukosa', 'rumus' => 'C6H12O6', 'tingkat_bahaya' => TingkatBahaya::RENDAH->value, 'simbol_bahaya' => 'Xn', 'penanganan' => 'Tidak memerlukan perlindungan khusus'],
            ['nama' => 'Kalsium Karbonat', 'rumus' => 'CaCO3', 'tingkat_bahaya' => TingkatBahaya::RENDAH->value, 'simbol_bahaya' => 'Xn', 'penanganan' => 'Tidak memerlukan perlindungan khusus'],
        ];

        foreach ($bahans as $bahan) {
            Bahan::create($bahan);
        }

        // Create alat
        $alatItems = [
            ['nama' => 'Pipet', 'keterangan' => 'Pipet volumetrik 10 ml'],
            ['nama' => 'Buret', 'keterangan' => 'Buret 25 ml'],
            ['nama' => 'Erlenmeyer', 'keterangan' => 'Erlenmeyer 250 ml'],
        ];

        foreach ($alatItems as $item) {
            \App\Models\Alat::create($item);
        }

        // Create jerigen
        $jerigenItems = [
            ['kode' => 'JR-A', 'label' => 'Limbah Asam-Basa', 'warna' => '#FF0000', 'jenis_limbah' => 'asam-basa', 'keterangan' => 'Untuk limbah asam dan basa'],
            ['kode' => 'JR-B', 'label' => 'Limbah Organik', 'warna' => '#00FF00', 'jenis_limbah' => 'organik', 'keterangan' => 'Untuk limbah organik'],
            ['kode' => 'JR-C', 'label' => 'Limbah Logam', 'warna' => '#0000FF', 'jenis_limbah' => 'logam', 'keterangan' => 'Untuk limbah logam berat'],
            ['kode' => 'JR-D', 'label' => 'Limbah Padat', 'warna' => '#FFFF00', 'jenis_limbah' => 'padat', 'keterangan' => 'Untuk limbah padat'],
        ];

        foreach ($jerigenItems as $item) {
            Jerigen::create($item);
        }

        // Create mata praktikum
        $mataPraktikum = MataPraktikum::create([
            'kode' => 'KIM-001',
            'nama' => 'Kimia Dasar',
        ]);

        // Create kelas
        $kelas = Kelas::create([
            'mata_praktikum_id' => $mataPraktikum->id,
            'nama' => 'Kimia Dasar A',
            'semester' => 'Ganjil 2024',
        ]);

        // Create materi
        $materi = Materi::create([
            'mata_praktikum_id' => $mataPraktikum->id,
            'judul' => 'Titrasi Asam Basa',
            'deskripsi' => 'Praktikum tentang titrasi asam basa menggunakan HCl dan NaOH',
            'tingkat_bahaya' => TingkatBahaya::TINGGI->value,
            'dibuat_oleh' => $dosen->id,
        ]);

        // Link APD to materi (using direct inserts to avoid foreign key issues)
        $materi->apd()->attach([1, 2, 3, 4]); // jas_lab, masker, kacamata, sarung_tangan

        // Add bahan to materi
        $materi->bahan()->attach([
            1 => ['jumlah' => 50, 'satuan' => 'ml'], // HCl
            2 => ['jumlah' => 50, 'satuan' => 'ml'], // NaOH
        ]);

        // Add alat to materi
        $materi->alat()->attach([
            1 => ['jumlah' => 1], // Pipet
            2 => ['jumlah' => 1], // Buret
            3 => ['jumlah' => 1], // Erlenmeyer
        ]);

        // Add materi limbah
        $materiLimbah = $materi->limbah()->create([
            'nama' => 'Cairan A',
            'kandungan' => 'Sisa larutan HCl dan NaOH hasil titrasi',
            'jerigen_id' => 1, // JR-A
            'catatan' => 'Campuran asam dan basa',
        ]);

        // Create mahasiswa
        $mahasiswa = Mahasiswa::create([
            'nim' => '123456789',
            'nama' => 'Mahasiswa Demo',
        ]);

        // Create sesi
        $sesi = Sesi::create([
            'kelas_id' => $kelas->id,
            'materi_id' => $materi->id,
            'aslab_id' => $aslab->id,
            'tanggal' => now()->today(),
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '10:00:00',
            'ruangan' => 'LAB-01',
            'dibuat_oleh' => $dosen->id,
        ]);
    }
}
