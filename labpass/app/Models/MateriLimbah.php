<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MateriLimbah extends Model
{
    use HasFactory;

    protected $table = 'materi_limbah';

    protected $fillable = [
        'materi_id',
        'nama',
        'kandungan',
        'jerigen_id',
        'catatan',
    ];

    public function materi()
    {
        return $this->belongsTo(Materi::class);
    }

    public function jerigen()
    {
        return $this->belongsTo(Jerigen::class);
    }
}