<?php

namespace GPS\AppBundle\Testing;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ControllerTest extends WebTestCase {
    use
        AuthHelperTrait, 
        FixtureHelperTrait, 
        ApiHelperTrait
    ;
    
    protected function getContainer()
    {
        return static::createClient()->getKernel()->getContainer();
    }
}