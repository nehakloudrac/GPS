<?php

namespace GPS\AppBundle\Service;

use Doctrine\Common\Cache\Cache;

/**
 * Token managers create and store randomized tokens for one-time use.  Tokens expire after
 * a timelimit if not used.  These are used primarily for password resets and account
 * verification after registration.
 *
 * @author Evan Villemez
 */
class TokenManager
{
    /**
     * @var string\Doctrine\Common\Cache\Cache
     */
    protected $cache;

    /**
     * Prefix to use for token keys
     *
     * @var string
     */
    protected $cacheKePrefix;

    /**
     * Constructor, needs a cache mechanism and secret to use for token generation
     *
     * @param CacheInterface $cache  Cache backend used for storing tokens.
     * @param string         $secret Secret used in generating token strings.
     */
    public function __construct(Cache $cache, $cacheKePrefix = 'tokens_', $ttl = 3600)
    {
        $this->cache = $cache;
        $this->cacheKeyPrefix = $cacheKePrefix;
        $this->ttl = $ttl;
    }

    /**
     * Return boolean if a token has been set for a given id.
     *
     * @param  string  $token
     * @return boolean
     */
    public function hasToken($token)
    {
        return $this->cache->contains($this->getTokenCacheKey($token));
    }

    /**
     * Retrieve the token.
     *
     * @param  string $token
     * @return string or boolean false if it doesn't exist
     */
    public function getTokenData($token)
    {
        return $this->cache->fetch($this->getTokenCacheKey($token));
    }

    /**
     * Create, store, and return a new one-time-use token.
     *
     * @param  array $data  Any metadata to associate with the token
     * @return string
     */
    public function createToken($data = [])
    {
        $token = $this->generateToken($data);
        $this->cache->save($this->getTokenCacheKey($token), $data, $this->ttl);

        return $token;
    }

    /**
     * Attempt to use and remove a given token for a given id.  Throws exception if any problems are detected.
     *
     * @param  string                   $token
     * @throws InvalidArgumentException if a token does not exist, was expired.
     * @return mixed                    Associated token data
     */
    public function useToken($token)
    {
        //does token exist?
        if (!$this->hasToken($token)) {
            throw new \InvalidArgumentException("The token does not exist.");
        }

        //can we actually get it? (not expired)
        if (!$data = $this->getTokenData($token)) {
            throw new \InvalidArgumentException("The token could not be retrived or was expired.");
        }

        //remove it
        $this->removeToken($token);

        return $data;
    }

    /**
     * Remove a stored upload token for a given id
     *
     * @param  string $token
     * @return void
     */
    public function removeToken($token)
    {
        return $this->cache->delete($this->getTokenCacheKey($token));
    }

    protected function generateToken()
    {
        return sha1(uniqid());
    }

    protected function getTokenCacheKey($token)
    {
        return $this->cacheKeyPrefix.$token;
    }
}