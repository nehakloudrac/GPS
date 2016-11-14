<?php

namespace GPS\AppBundle\Util;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * A generic cache warmer used to pre-cache many application-level values.  For now every method
 * provides some specific functinality.  These are all located in one service for convenience
 * at the moment.  It can be split apart later if there is a need.
 */
class CacheWarmers implements CacheWarmerInterface
{
    private $container;
    
    public function __construct($container)
    {
        $this->container = $container;
    }
    
    /**
     * Just add method calls into this for anyting that should happen when the cache is warmed.
     * 
     * @inheritDoc
     */
    public function warmUp($cacheDir)
    {
        // TODO:... warmup cache with whatever
    }
    
    /**
     * @inheritDoc
     */
    public function isOptional()
    {
        return true;
    }
        
}
