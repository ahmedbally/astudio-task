<?php

namespace App\Providers;

use App\Models\Project;
use App\Models\Timesheet;
use App\Policies\ProjectPolicy;
use App\Policies\TimesheetPolicy;
use App\Support\OAuth\PassportProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Passport\Passport;
use Mockery\Generator\StringManipulation\Pass\Pass;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
        Project::class => ProjectPolicy::class,
        Timesheet::class => TimesheetPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Password::defaults(function () {
            return Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols();
        });
        Passport::enablePasswordGrant();
        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
    }

    public function register(): void
    {
        $this->app->bind(PassportProvider::class, function ($app) {
            return new PassportProvider([
                'clientId' => config('oauth.client_id'),
                'clientSecret' => config('oauth.client_secret'),
                'redirectUri' => config('app.url'),
                'baseUrl' => config('oauth.base_url'),
                'scopes' => explode(' ', config('oauth.scopes')),
            ]);
        });
    }
}
