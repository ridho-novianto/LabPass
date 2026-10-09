<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataPraktikum extends Model
{
    use HasFactory;

    protected $table = 'mata_praktikum';

    protected $fillable = [
        'kode',
        'nama',
    ];
}