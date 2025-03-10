<?php

namespace App\Actions\Attribute;

use App\Models\Attribute;
use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateAttributeAction
{
    use AsAction;

    public function handle(array $data): Attribute
    {
        return Attribute::create($data);
    }
}
