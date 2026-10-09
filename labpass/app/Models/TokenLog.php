<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TokenLog extends Model
{
    use HasFactory;

    protected $table = 'token_log';

    protected $fillable = [
        'kehadiran_id',
        'jenis',
        'token_masuk',
        'token_keluar',
        'dari_cache',
    ];

    public function kehadiran()
    {
        return $this->belongsTo(Kehadiran::class);
    }
}