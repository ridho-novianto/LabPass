<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MateriBahan extends Model
{
    use HasFactory;

    protected $table = 'materi_bahan';

    protected $fillable = [
        'materi_id',
        'bahan_id',
        'jumlah',
        'satuan',
    ];

    public function materi()
    {
        return $this->belongsTo(Materi::class);
    }

    public function bahan()
    {
        return $this->belongsTo(Bahan::class);
    }
}