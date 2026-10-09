<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kehadiran extends Model
{
    use HasFactory;

    protected $table = 'kehadiran';

    protected $fillable = [
        'sesi_id',
        'mahasiswa_id',
        'token',
        'status',
        'apd_terdeteksi',
        'apd_kurang',
        'snapshot_path',
        'evaluasi_risiko',
        'jam_masuk',
        'jam_keluar',
    ];

    protected $casts = [
        'apd_terdeteksi' => 'array',
        'apd_kurang' => 'array',
        'evaluasi_risiko' => 'array',
        'jam_masuk' => 'datetime',
        'jam_keluar' => 'datetime',
    ];

    public function sesi()
    {
        return $this->belongsTo(Sesi::class);
    }

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function dispensasi()
    {
        return $this->hasOne(Dispensasi::class);
    }

    public function pembuanganLimbah()
    {
        return $this->hasMany(PembuanganLimbah::class);
    }

    public function tokenLog()
    {
        return $this->hasMany(TokenLog::class);
    }
}