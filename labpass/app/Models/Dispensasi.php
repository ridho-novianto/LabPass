<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispensasi extends Model
{
    use HasFactory;

    protected $table = 'dispensasi';

    protected $fillable = [
        'kehadiran_id',
        'alasan',
        'status',
        'diputuskan_oleh',
        'catatan',
        'diputuskan_pada',
    ];

    protected $casts = [
        'diputuskan_pada' => 'datetime',
    ];

    public function kehadiran()
    {
        return $this->belongsTo(Kehadiran::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'diputuskan_oleh');
    }
}