<?php

namespace GPS\Tests\AppBundle\Controller\Api;

use GPS\AppBundle\Testing;

class PartnerControllerTest extends Testing\ControllerTest
{
    use Testing\ResetFixturesHelperTrait, Testing\FixtureHelperTrait;
    
    public function testPartnerCRUDActions()
    {
        
        // ensure anonymous user gets only public referrers
        $c = $this->createAuthClient('user2@example.com', 'user2');
        // $res = $this->callApi($c, 'GET', '/api/partners');
        // $this->assertTrue(isset($res['links']));
        // $this->assertSame(4, count($res['links']));
        // foreach ($res['partners'] as $item) {
        //     $this->assertTrue($item['isEnabled']);
        //     $this->assertTrue($item['isPublicPartner']);
        // }
        
        // general user can't create/modify/delete
        $res = $this->call($c, 'POST', '/api/partners');
        $this->assertSame(403, $c->getResponse()->getStatusCode());
        $res = $this->call($c, 'PUT', '/api/partners/1234');
        $this->assertSame(403, $c->getResponse()->getStatusCode());
        $res = $this->call($c, 'DELETE', '/api/partners/1234');
        $this->assertSame(403, $c->getResponse()->getStatusCode());
        
        // admin is ok, create/modify/delete link
        $c = $this->createAuthClient('admin@example.com', 'admin');
        
        // get non-existing (404)
        $res = $this->call($c, 'PUT', '/api/partners/1234123421341234', ['description'=> 'foo']);
        $this->assertSame(404, $res->getStatusCode());
        
        // create & get
        $res = $this->callApi($c, 'POST', '/api/partners', ['key' => "foo", 'name' => 'Foooo Inc.', 'description' => 'foo bar']);
        $this->assertSame(201, $c->getResponse()->getStatusCode());
        $this->assertSame('foo', $res['partner']['key']);
        $this->assertSame('foo bar', $res['partner']['description']);
        $id = $res['partner']['id'];
        $res = $this->call($c, 'GET', '/api/partners/'.$id);
        $this->assertSame(200, $res->getStatusCode());
        
        // modify
        $res = $this->callApi($c, 'PUT', '/api/partners/'.$id, ['description' => "wat wat"]);
        $this->assertSame(200, $c->getResponse()->getStatusCode());
        $this->assertSame("wat wat", $res['partner']['description']);
        
        // delete
        $res = $this->callApi($c, 'DELETE', '/api/partners/'.$id);
        $this->assertSame(200, $c->getResponse()->getStatusCode());

        // get deleted
        $res = $this->call($c, 'GET', '/api/partners/'.$id);
        $this->assertSame(404, $res->getStatusCode());
        
        // incomplete until the index tests are added, but I need to merge
        // multiple branches before I add the fixtures for partners
        $this->markTestIncomplete();
    }
    
}