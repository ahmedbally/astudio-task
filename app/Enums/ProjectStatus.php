<?php

namespace App\Enums;

enum ProjectStatus: int
{
    case PENDING = 0;

    case ACTIVE = 1;

    case INACTIVE = 2;
}
