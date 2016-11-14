<?php

namespace GPS\AppBundle\Testing;

trait AuthHelperTrait
{
    protected function createAuthClient($email, $password)
    {
        return static::createClient([], [
            'PHP_AUTH_USER' => $email,
            'PHP_AUTH_PW' => $password
        ]);
    }
    
    protected function createApiKeyClient($apiKey)
    {
        throw new \RuntimeException("Not yet implemented.");
    }
}
