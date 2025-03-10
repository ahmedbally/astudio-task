<?php

namespace App\Support\OAuth;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class PassportResourceOwner implements ResourceOwnerInterface
{
    /**
     * @var array
     */
    protected $response;

    /**
     * Creates new resource owner.
     *
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;
    }

    /**
     * Returns the identifier of the resource owner.
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->response['id'] ?? null;
    }

    /**
     * Returns the email of the resource owner.
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->response['email'] ?? null;
    }

    /**
     * Returns the first name of the resource owner.
     *
     * @return string|null
     */
    public function getFirstName()
    {
        return $this->response['first_name'] ?? null;
    }

    /**
     * Returns the last name of the resource owner.
     *
     * @return string|null
     */
    public function getLastName()
    {
        return $this->response['last_name'] ?? null;
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }
}
