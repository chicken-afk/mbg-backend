<?php

namespace App\Enums;

enum RoleEnum: int
{
    case ADMIN = 1;
    case STAFF = 2;
    case SUPERADMIN = 3;

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::STAFF => 'Staff',
            self::SUPERADMIN => 'Super Admin',
        };
    }
}
