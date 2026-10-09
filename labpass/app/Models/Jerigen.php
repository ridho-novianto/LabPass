<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jerigen extends Model
{
    use HasFactory;

    protected $table = 'jerigen';

    protected $fillable = [
        'kode',
        'label',
        'warna',
        'jenis_limbah',
        'keterangan',
    ];
}