<?php

namespace GPS\Tests\AppBundle\EventListener;

use GPS\AppBundle\Testing;

class MaintenanceModeSubscriberTest extends Testing\ControllerTest
{
    use Testing\ResetFixturesHelperTrait, Testing\FixtureHelperTrait;
    
    public function setUp()
    {
        $this->getContainer()->get('gps.shared_cache')->delete('maintenance.lock');
    }

    public function testFoo()
    {
        $this->assertTrue(true);
    }
        
    public function testMaintenanceRoute()
    {
        // disabled, route should redirect to login or dashboard
        $c = $this->createAuthClient('user@example.com', 'user');
        $res = $this->call($c, 'GET', '/maintenance');
        $this->assertSame(302, $res->getStatusCode());
        $this->assertSame('/login', $res->headers->get('location'));
        
        // enabled, should show message
        $msg = "This is the maintenance message.";
        $this->getContainer()->get('gps.shared_cache')->save('maintenance.lock', $msg);
        $crawler = $c->request('GET', '/maintenance');
        $this->assertSame(200, $c->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('html:contains("'.$msg.'")')->count() > 0);
    }
    
    // when enabled, configured routes should redirect to maintenance page
    public function testPublicPages()
    {
        
        $c = $this->createAuthClient('admin@example.com', 'admin');
        
        $urls = [
            '/login',
            '/account/create',
            '/employers/contact',
            '/candidate/dashboard',
            '/candidate/profile',
            '/candidate/resources',
            '/candidate/account',
            '/admin'
        ];
        
        // get apps, expect 200
        foreach ($urls as $url) {
            $res = $this->call($c, 'GET', $url);
            $this->assertSame(200, $res->getStatusCode(), "Checking url: $url");
        }
        
        // get home, expect 200 pre/post maintenance mode
        $res = $this->call($c, 'GET', '/');
        $this->assertSame(200, $res->getStatusCode());
        
        $this->getContainer()->get('gps.shared_cache')->save('maintenance.lock', "foo");
        $res = $this->call($c, 'GET', '/');
        $this->assertSame(200, $res->getStatusCode());
        
        // get apps, expect redirect
        foreach ($urls as $url) {
            // get url, expect 302
            $res = $this->call($c, 'GET', $url);
            $this->assertSame(302, $res->getStatusCode(), "Checking app: $url");
            $this->assertSame('/maintenance', $res->headers->get('location'));
        }
    }
    
    public function testApiResponses()
    {
        $c = $this->createAuthClient('admin@example.com', 'admin');

        $res = $this->call($c, 'GET', '/api/candidate-profiles/bbbbbbbbbbbbbbbbbbbbbbbb');
        $this->assertSame(200, $res->getStatusCode());

        // when enabled, routes should return json w/ message
        $msg = "We down, yo.";
        $this->getContainer()->get('gps.shared_cache')->save('maintenance.lock', $msg);

        $expected = [
            'target' => '/maintenance',
            'response' => [
                'code' => 503,
                'message' => $msg,
            ]
        ];
        
        $res = $this->callApi($c, 'GET', '/api/candidate-profiles/bbbbbbbbbbbbbbbbbbbbbbbb');
        $this->assertSame($expected, $res);
    }
}
