<?php

namespace App\Actions\Attribute;

use App\Models\Attribute;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Lorisleiva\Actions\Concerns\AsAction;

class GetAttributesAction
{
    use AsAction;

    public function handle(): LengthAwarePaginator
    {
        return Attribute::paginate();
    }
}
