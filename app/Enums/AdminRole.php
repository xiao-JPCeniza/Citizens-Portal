<?php

namespace App\Enums;

enum AdminRole: string
{
    case SuperAdmin = 'Super Admin';
    case Administrator = 'Administrator';
    case Verifier = 'Verifier';
}
