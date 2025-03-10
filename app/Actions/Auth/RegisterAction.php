<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Lorisleiva\Actions\Concerns\AsAction;

class RegisterAction
{
    use AsAction;

    public function handle(array $data) {
        $user = User::create($data);

        event(new Registered($user));

        return $user;
    }
}
