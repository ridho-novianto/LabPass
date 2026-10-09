<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sesi extends Model
{
    use HasFactory;

    protected $table = 'sesi';

    protected $fillable = [
        'kelas_id',
        'materi_id',
        'aslab_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'ruangan',
        'dibuat_oleh',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function materi()
    {
        return $this->belongsTo(Materi::class);
    }

    public function aslab()
    {
        return $this->belongsTo(User::class, 'aslab_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function kehadiran()
    {
        return $this->hasMany(Kehadiran::class);
    }

    public static function berlangsung()
    {
        return static::whereDate('tanggal', today())
            ->whereTime('jam_mulai', '<=', now())
            ->whereTime('jam_selesai', '>', now());
    }
}