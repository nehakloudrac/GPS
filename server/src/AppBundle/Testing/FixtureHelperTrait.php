<?php

namespace GPS\AppBundle\Testing;

trait FixtureHelperTrait
{
    protected function resetMongo($useCache = true)
    {
        static::createClient()
            ->getContainer()
            ->get('gps.fixtures')
            ->resetMongo($useCache)
        ;
    }
    
    protected function fetchFixtures()
    {
        return static::createClient()
            ->getContainer()
            ->get('gps.fixtures')
            ->load()
        ;
    }
}
