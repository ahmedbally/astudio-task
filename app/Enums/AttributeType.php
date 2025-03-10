<?php

namespace App\Enums;

enum AttributeType: string
{
    case TEXT  = 'text';
    case DATE  = 'date';
    case NUMBER  = 'number';
    case SELECT  = 'select';
}
