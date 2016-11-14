<?php

namespace GPS\Tests\AppBundle\Controller\Api;

use GPS\AppBundle\Testing;

class ResourceLinkControllerTest extends Testing\ControllerTest
{
    use Testing\ResetFixturesHelperTrait, Testing\FixtureHelperTrait;
    
    public function testResourceLinkCRUDActions()
    {
        
        // ensure anonymous user gets only published content
        $c = $this->createAuthClient('user2@example.com', 'user2');
        $res = $this->callApi($c, 'GET', '/api/content/links?types=article&tags=Language%20Study');
        $this->assertTrue(isset($res['links']));
        $this->assertSame(1, count($res['links']));
        
        
        foreach ($res['links'] as $link) {
            $this->assertTrue($link['published']);
            $this->assertTrue(in_array('Language Study', $link['tags']));
            $this->assertSame('article', $link['type']);
        }
        
        // general user can't create/modify/delete
        $res = $this->call($c, 'POST', '/api/content/links');
        $this->assertSame(403, $c->getResponse()->getStatusCode());
        $res = $this->call($c, 'PUT', '/api/content/links/1234');
        $this->assertSame(403, $c->getResponse()->getStatusCode());
        $res = $this->call($c, 'DELETE', '/api/content/links/1234');
        $this->assertSame(403, $c->getResponse()->getStatusCode());
        
        // admin is ok, create/modify/delete link
        $c = $this->createAuthClient('admin@example.com', 'admin');
        
        // get non-existing (404)
        $res = $this->call($c, 'PUT', '/api/content/links/1234123421341234', ['type'=> 'article']);
        $this->assertSame(404, $res->getStatusCode());
        
        // create & get
        $res = $this->callApi($c, 'POST', '/api/content/links', ['type' => "article", 'description' => 'foo bar']);
        $this->assertSame(201, $c->getResponse()->getStatusCode());
        $this->assertSame('article', $res['link']['type']);
        $this->assertSame('foo bar', $res['link']['description']);
        $id = $res['link']['id'];
        $res = $this->call($c, 'GET', '/api/content/links/'.$id);
        $this->assertSame(200, $res->getStatusCode());
        
        // modify
        $res = $this->callApi($c, 'PUT', '/api/content/links/'.$id, ['description' => "wat wat"]);
        $this->assertSame(200, $c->getResponse()->getStatusCode());
        $this->assertSame("wat wat", $res['link']['description']);
        
        // delete
        $res = $this->callApi($c, 'DELETE', '/api/content/links/'.$id);
        $this->assertSame(200, $c->getResponse()->getStatusCode());

        // get deleted
        $res = $this->call($c, 'GET', '/api/content/links/'.$id, ['description' => "wat wat"]);
        $this->assertSame(404, $res->getStatusCode());
    }
    
}
