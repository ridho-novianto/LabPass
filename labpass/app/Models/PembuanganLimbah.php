<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembuanganLimbah extends Model
{
    use HasFactory;

    protected $table = 'pembuangan_limbah';

    protected $fillable = [
        'kehadiran_id',
        'materi_limbah_id',
        'volume_ml',
        'dibuang_pada',
    ];

    protected $casts = [
        'volume_ml' => 'decimal:2',
        'dibuang_pada' => 'datetime',
    ];

    public function kehadiran()
    {
        return $this->belongsTo(Kehadiran::class);
    }

    public function materiLimbah()
    {
        return $this->belongsTo(MateriLimbah::class);
    }
}