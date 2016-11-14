<?php
namespace GPS\Testing\AppBundle\Controller\Api;

use GPS\AppBundle\Testing;

class AdminControllerTest extends Testing\ControllerTest
{
    use Testing\ResetFixturesHelperTrait;

    public function testUserAdminCommentsActions()
    {
        // ensure anonymous is blocked
        $c = static::createClient();
        $res = $this->call($c, 'GET', '/api/admin/users/223/comments');
        $this->assertSame(401, $res->getStatusCode());
        
        // admin is ok, get comments
        $c = $this->createAuthClient('admin@example.com', 'admin');
        $data = $this->callApi($c, 'GET', '/api/admin/users/cccccccccccccccccccccccc/comments');
        $this->assertSame(200, $c->getResponse()->getStatusCode());
        $this->assertSame(0, count($data['comments']));

        // non existing user
        $data = $this->callApi($c, 'GET', '/api/admin/users/86/comments');
        $this->assertSame(404, $c->getResponse()->getStatusCode());
        
        // create comment
        $data = $this->callApi($c, 'POST', '/api/admin/users/cccccccccccccccccccccccc/comments', ['text' => "A test."]);
        $this->assertSame(201, $c->getResponse()->getStatusCode());
        $this->assertTrue(isset($data['comment']['id']));
        $this->assertSame("A test.", $data['comment']['text']);
        
        // get comments
        $data = $this->callApi($c, 'GET', '/api/admin/users/cccccccccccccccccccccccc/comments');
        $this->assertSame(200, $c->getResponse()->getStatusCode());
        $this->assertSame(1, count($data['comments']));
        $this->assertSame("A test.", $data['comments'][0]['text']);
        $commentId = $data['comments'][0]['id'];
        
        // delete comment
        $data = $this->callApi($c, 'DELETE', '/api/admin/users/cccccccccccccccccccccccc/comments/'.$commentId);
        $this->assertSame(200, $c->getResponse()->getStatusCode());
        
        // get comments
        $data = $this->callApi($c, 'GET', '/api/admin/users/cccccccccccccccccccccccc/comments');
        $this->assertSame(200, $c->getResponse()->getStatusCode());
        $this->assertSame(0, count($data['comments']));
        
    }
    
    public function testGetUserDetailsAction()
    {
        $c = $this->createAuthClient('admin@example.com', 'admin');
        $res = $this->call($c, 'GET', '/api/admin/users/cccccccccccccccccccccccc/details');
        $this->assertSame(200, $res->getStatusCode());
    }
    
    public function testGetUserOverviewAction()
    {
        $c = $this->createAuthClient('admin@example.com', 'admin');
        $data = $this->callApi($c, 'GET', '/api/admin/overview/users?email=gmail');
        $this->assertTrue(count($data['results']) > 0);
    }
    
    public function testGetProfileOverviewAction()
    {
        $c = $this->createAuthClient('admin@example.com', 'admin');
        $data = $this->callApi($c, 'GET', '/api/admin/overview/profiles?languages=rus');
        $this->assertTrue(count($data['results']) > 0);
    }
    
    public function testGetOverviewCountsAction()
    {
        $c = $this->createAuthClient('admin@example.com', 'admin');
        $data = $this->callApi($c, 'GET', '/api/admin/overview/counts');
        $this->assertTrue(count($data['total']) > 0);
    }
    
    public function testGetOverviewFieldsAction()
    {
        $c = $this->createAuthClient('admin@example.com', 'admin');
        $data = $this->callApi($c, 'GET', '/api/admin/overview/facets/industries');
        $this->assertTrue(count($data['data']) > 0);
    }
    
}
