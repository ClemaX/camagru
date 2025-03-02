<?php

namespace App\Enumerations;

enum Role: int
{
	case GUEST = 0;
	case USER = 1;
	case ADMIN = 2;
}
