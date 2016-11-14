<?php

namespace GPS\Tests\AppBundle\Service;

use GPS\AppBundle\Service\TokenManager;
use Doctrine\Common\Cache\ArrayCache;

class TokenManagerTest extends \PHPUnit_Framework_TestCase
{
    protected function createTM()
    {
        return new TokenManager(new ArrayCache());
    }
    
    public function testCreateRetrieveAndDeleteToken()
    {
        $tm = $this->createTM();
        
        $tokenData = ['foo' => 'bar'];
        $token = $tm->createToken($tokenData);
        $this->assertTrue(is_string($token));
        $this->assertTrue($tm->hasToken($token));

        $data = $tm->getTokenData($token);
        $this->assertSame($data, $tokenData);

        $tm->removeToken($token);
        $this->assertFalse($tm->hasToken($token));
    }
    
    public function testUseToken()
    {
        $tm = $this->createTM();
        
        $this->setExpectedException('InvalidArgumentException');
        $tm->useToken('foo');
        
        $tokenData = ['foo' => 'bar'];
        $token = $tm->createToken($token);
        $this->assertTrue($tm->hasToken($token));
        
        $data = $tm->useToken($token);
        $this->assertSame($data, $tokenData);
        $this->assertFalse($tm->hasToken($token));
    }
}
