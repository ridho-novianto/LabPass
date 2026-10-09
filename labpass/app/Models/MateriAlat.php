<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MateriAlat extends Model
{
    use HasFactory;

    protected $table = 'materi_alat';

    protected $fillable = [
        'materi_id',
        'alat_id',
        'jumlah',
    ];

    public function materi()
    {
        return $this->belongsTo(Materi::class);
    }

    public function alat()
    {
        return $this->belongsTo(Alat::class);
    }
}