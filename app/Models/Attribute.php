<?php

namespace App\Models;

use App\Enums\AttributeType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'type',
        'options',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'type' => AttributeType::class,
        ];
    }
}
