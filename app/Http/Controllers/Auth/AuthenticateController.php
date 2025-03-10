<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Support\OAuth\PassportProvider;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Passport;
use Laravel\Passport\TokenRepository;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Throwable;

class AuthenticateController extends Controller
{
    public function __construct(
        protected PassportProvider $passportProvider,
        protected TokenRepository $tokenRepository,
    )
    {
    }

    public function store(LoginRequest $request): JsonResponse
    {
        try {
            $token = app(PassportProvider::class)->getAccessToken('password', [
                'username' => $request->validated('email'),
                'password' => $request->validated('password'),
            ]);
        } catch (IdentityProviderException $e) {
            throw ValidationException::withMessages([
                'email' => [trans('auth.failed')],
            ]);
        } catch (Throwable $e) {
            Log::error($e->getMessage(), $e->getTrace());

            return api()
                ->error('server_error', __('Internal server error'))
                ->respond(JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }


        return api()
            ->success([
                'access_token' => $token->getToken(),
                'token_type' => 'bearer',
                'expires_in' => $token->getExpires(),
                'refresh_token' => $token->getRefreshToken(),
            ])
            ->respond();
    }


    public function destroy(Request $request): JsonResponse
    {
        $request->user()->token()->revoke();

        return api()
            ->success(message: trans('auth.logout'))
            ->respond();
    }
}
