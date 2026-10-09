<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MateriApd extends Model
{
    use HasFactory;

    protected $table = 'materi_apd';

    protected $fillable = [
        'materi_id',
        'apd_id',
    ];

    public function materi()
    {
        return $this->belongsTo(Materi::class);
    }

    public function apd()
    {
        return $this->belongsTo(Apd::class);
    }
}