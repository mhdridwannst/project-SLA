<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';       // Pemilik workspace perusahaan (bisa kelola tim & aturan SLA)
    case AGENT = 'agent';       // Teknisi/Support yang menangani tiket
    case CUSTOMER = 'customer'; // Klien/Karyawan yang melaporkan masalah

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Workspace Admin',
            self::AGENT => 'Support Agent',
            self::CUSTOMER => 'Customer / Reporter',
        };
    }
}
