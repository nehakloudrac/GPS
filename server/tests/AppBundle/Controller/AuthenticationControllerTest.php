<?php

namespace GPS\Tests\AppBundle\Controller;

use GPS\AppBundle\Testing;

class AuthenticationControllerTest extends Testing\ControllerTest
{
    public function testLoginAndLogout()
    {
        $c = static::createClient();
        $res = $this->call($c, 'GET', '/candidate/dashboard');
        $this->assertSame(302, $res->getStatusCode());
        $this->assertSame('https://localhost/login', $res->headers->get('location'));

        $res = $this->call($c, 'GET', '/login');
        $this->assertSame(200, $res->getStatusCode());
        $crawler = $c->getCrawler();
        $form = $crawler->selectButton('Login')->form();
        $form['_username'] = 'user@example.com';
        $form['_password'] = 'user';
        $c->submit($form);
        
        // did login result in redirect to candidate dashboard?
        $res = $c->getResponse();
        $this->assertSame(302, $res->getStatusCode());
        $this->assertSame('https://localhost/candidate/dashboard', $res->headers->get('location'));
        $res = $this->call($c, 'GET', '/candidate/dashboard');
        $crawler = $c->getCrawler();
        $this->assertTrue($crawler->filter('html:contains("Loading")')->count() > 0);
        
        // now logout, going back to dashboard should result in redirect
        $res = $this->call($c, 'GET', '/logout');
        $this->assertSame(302, $res->getStatusCode());
        $this->assertSame('https://localhost/', $res->headers->get('location'));
        $res = $this->call($c, 'GET', '/candidate/dashboard');
        $this->assertSame(302, $res->getStatusCode());
        $this->assertSame('https://localhost/login', $res->headers->get('location'));
    }
}
