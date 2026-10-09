<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materi extends Model
{
    use HasFactory;

    protected $table = 'materi';

    protected $fillable = [
        'mata_praktikum_id',
        'judul',
        'deskripsi',
        'file_path',
        'tingkat_bahaya',
        'dibuat_oleh',
    ];

    public function mataPraktikum()
    {
        return $this->belongsTo(MataPraktikum::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function apd()
    {
        return $this->belongsToMany(Apd::class, 'materi_apd');
    }

    public function bahan()
    {
        return $this->belongsToMany(Bahan::class, 'materi_bahan')
            ->withPivot(['jumlah', 'satuan']);
    }

    public function alat()
    {
        return $this->belongsToMany(Alat::class, 'materi_alat')
            ->withPivot('jumlah');
    }

    public function limbah()
    {
        return $this->hasMany(MateriLimbah::class);
    }
}