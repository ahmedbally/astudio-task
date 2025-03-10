<?php

namespace App\Actions\Attribute;

use App\Models\Attribute;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateAttributeAction
{
    use AsAction;

    public function handle(Attribute $attribute, array $data): Attribute
    {
        $attribute->update($data);

        return $attribute;
    }
}
