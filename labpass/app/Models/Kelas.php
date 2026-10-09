<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';

    protected $fillable = [
        'mata_praktikum_id',
        'nama',
        'semester',
    ];

    public function mataPraktikum()
    {
        return $this->belongsTo(MataPraktikum::class);
    }
}