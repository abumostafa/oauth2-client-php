<?php

namespace EasyBib\OAuth2\Client;

use EasyBib\OAuth2\Client\AuthorizationCodeGrant\TokenResponse;
use Symfony\Component\HttpFoundation\Session\Session;

class TokenStore
{
    /**
     * This is a persistent store for token data, which does not necessarily
     * strictly correspond to a user's PHP session
     *
     * @var Session
     */
    private $session;

    /**
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        if ($this->isExpired()) {
            return null;
        }

        return $this->get('token');
    }

    /**
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->get('refresh_token');
    }

    /**
     * @return bool
     */
    public function isRefreshable()
    {
        return $this->get('token') && $this->get('refresh_token');
    }

    /**
     * @param TokenResponse $tokenResponse
     */
    public function updateFromTokenResponse(TokenResponse $tokenResponse)
    {
        $this->session->replace([
            'token' => $tokenResponse->getToken(),
            'refresh_token' => $tokenResponse->getRefreshToken(),
            'expires_at' => $this->expirationTimeFor($tokenResponse),
        ]);
    }

    /**
     * @param TokenResponse $tokenResponse
     * @return int
     */
    private function expirationTimeFor(TokenResponse $tokenResponse)
    {
        return time() + $tokenResponse->getExpiresIn();
    }

    /**
     * @return bool
     */
    private function isExpired()
    {
        $expiresAt = $this->get('expires_at');

        return $expiresAt && $expiresAt < time();
    }

    /**
     * @param $name
     * @return mixed
     */
    private function get($name)
    {
        return $this->session->get($name);
    }
}
