<?php

namespace App\Support\OAuth;

use GuzzleHttp\Exception\GuzzleException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class PassportProvider extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * @var string
     */
    protected string $baseUrl;

    /**
     * Constructs an OAuth 2.0 service provider.
     *
     * @param array $options An array of options to set on this provider.
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        // Get the base URL from the options or config
        $this->baseUrl = $options['baseUrl'] ?? config('oauth.base_url');
    }

    /**
     * Returns the base URL for authorizing a client.
     *
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return $this->baseUrl . '/oauth/authorize';
    }

    /**
     * Returns the base URL for requesting an access token.
     *
     * @param array $params
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->baseUrl . '/oauth/token';
    }

    /**
     * Returns the URL for requesting the resource owner's details.
     *
     * @param AccessToken $token
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->baseUrl . '/api/user';
    }

    /**
     * Returns the default scopes used by this provider.
     *
     * @return array
     */
    protected function getDefaultScopes()
    {
        return [];
    }

    /**
     * Checks a provider response for errors.
     *
     * @param ResponseInterface $response
     * @param array|string $data Parsed response data
     * @throws IdentityProviderException
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if ($response->getStatusCode() >= 400) {
            $error = $data['error'] ?? $response->getReasonPhrase();
            $errorDesc = $data['error_description'] ?? 'Unknown error occurred';

            throw new IdentityProviderException(
                $errorDesc,
                $response->getStatusCode(),
                $response
            );
        }
    }

    /**
     * Generates a resource owner object from a successful resource owner
     * details request.
     *
     * @param array $response
     * @param AccessToken $token
     * @return PassportResourceOwner
     */
    protected function createResourceOwner(array $response, AccessToken $token): PassportResourceOwner
    {
        return new PassportResourceOwner($response);
    }

    /**
     * Requests and returns the resource owner of given access token.
     *
     * @param AccessToken $token
     * @return PassportResourceOwner
     * @throws IdentityProviderException
     * @throws GuzzleException
     */
    public function getResourceOwner(AccessToken $token): PassportResourceOwner
    {
        $response = $this->fetchResourceOwnerDetails($token);

        return $this->createResourceOwner($response, $token);
    }
}
