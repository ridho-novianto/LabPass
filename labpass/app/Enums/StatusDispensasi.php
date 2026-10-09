<?php

namespace App\Enums;

enum StatusDispensasi: string
{
    case MENUNGGU = 'menunggu';
    case DISUJUI = 'disetujui';
    case DITOLAK = 'ditolak';
}