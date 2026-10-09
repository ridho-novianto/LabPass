<?php

namespace App\Enums;

enum StatusKehadiran: string
{
    case LULUS = 'lulus';
    case DITOLAK = 'ditolak';
    case MENUNGGU_DISPENSASI = 'menunggu_dispensasi';
    case DISPENSASI_DISUJUI = 'dispensasi_disetujui';
    case DISPENSASI_DITOLAK = 'dispensasi_ditolak';
}