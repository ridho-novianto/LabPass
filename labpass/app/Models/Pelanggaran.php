<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggaran extends Model
{
    use HasFactory;

    protected $table = 'pelanggaran';

    protected $fillable = [
        'kehadiran_id',
        'jenis',
        'uraian',
        'status',
        'disahkan_oleh',
        'disahkan_pada',
    ];

    protected $casts = [
        'disahkan_pada' => 'datetime',
    ];

    public function kehadiran()
    {
        return $this->belongsTo(Kehadiran::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'disahkan_oleh');
    }
}