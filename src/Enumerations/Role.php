<?php

namespace App\Enumerations;

enum Role: string
{
    case ADMIN = 'ADMIN';
    case USER = 'USER';
    case GUEST = 'GUEST';
}
