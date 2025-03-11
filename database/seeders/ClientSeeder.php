<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Laravel\Passport\ClientRepository;

class ClientSeeder extends Seeder
{
    public function run(ClientRepository $clientRepository): void
    {
        if (! $client = $clientRepository->forUser(null)->first()) {
            $client = $clientRepository->createPasswordGrantClient(null, 'Password Grant Client', url(''), 'users');
        }

        Artisan::call('env:set', ['key' => 'OAUTH_CLIENT_ID', 'value' => $client->id]);
        Artisan::call('env:set', ['key' => 'OAUTH_CLIENT_SECRET', 'value' => $client->secret]);
        Artisan::call('optimize');
    }
}
