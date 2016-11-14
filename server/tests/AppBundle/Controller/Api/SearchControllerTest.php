<?php

namespace GPS\Testing\AppBundle\Controller\Api;

use GPS\AppBundle\Testing;

class SearchControllerTest extends Testing\ControllerTest
{
    /**
     * @group search
     */
    public function testUnsureAuth()
    {
        // anon users blocked
        $c = static::createClient();
        $res = $this->call($c, 'GET', '/api/admin/search/candidates');
        $this->assertSame(401, $res->getStatusCode());
        
        // regular users blocked
        $c = $this->createAuthClient('user@example.com', 'user');
        $res = $this->call($c, 'GET', '/api/admin/search/candidates');
        $this->assertSame(401, $res->getStatusCode());
        
        // admin users ok
        $c = $this->createAuthClient('admin@example.com', 'admin');
        $res = $this->call($c, 'GET', '/api/admin/search/candidates');
        $this->assertSame(200, $res->getStatusCode());
    }
    
    /**
     * @group search
     */
    public function testQueryCandidates()
    {
        $c = $this->createAuthClient('admin@example.com', 'admin');
        $res = $this->call($c, 'GET', '/api/admin/search/candidates');
        $this->assertSame(200, $res->getStatusCode());
        $out = json_decode($res->getContent(), true);
        $this->assertTrue(isset($out['result']));
        $this->assertTrue(isset($out['result']['total']));
    }
    
}
