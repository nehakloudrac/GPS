<?php

namespace GPS\AppBundle\Testing;

trait ResetFixturesHelperTrait
{
    public function setUp()
    {
        $this->resetMongo(true);
    }

    public function tearDown()
    {
        $this->resetMongo(true);
    }    
}
