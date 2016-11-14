<?php

namespace GPS\Tests\AppBundle\Controller;

use GPS\AppBundle\Testing;

/**
 * Note that these tests are only for checking whether or not the Angular apps are served
 * as expected.
 *
 * Integration tests for each app should be done via protractor on the UI side.
 *
 * @package GPS
 * @author Evan Villemez
 */
class AngularAppsControllerTest extends Testing\ControllerTest
{
    public function testOldDashboardRedirect()
    {
        $client = static::createClient();
        $client->request('GET', '/dashboard');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }
    
    public function testOldProfileRedirect()
    {
        $client = static::createClient();
        $client->request('GET', '/dashboard/candidate');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }
    
    public function testDashboardApp()
    {
        //anonymous should be redirected
        $client = static::createClient();
        $client->request('GET', '/candidate/dashboard');
        $this->assertEquals(301, $client->getResponse()->getStatusCode());

        //users are forced to https
        $client = $this->createAuthClient('user@example.com', 'user');
        $crawler = $client->request('GET', '/candidate/dashboard');
        $response = $client->getResponse();
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertTrue(0 === strpos($response->getTargetUrl(), 'https'));

        //https and authentication loads page
        $client = $this->createAuthClient('user@example.com', 'user');
        $crawler = $client->request('GET', '/candidate/dashboard', [], [], ['HTTPS' => true]);
        $this->assertTrue($crawler->filter('html:contains("Loading ...")')->count() > 0);
    }

    public function testProfileApp()
    {
        //anonymous should be redirected
        $client = static::createClient();
        $client->request('GET', '/candidate/profile');
        $this->assertEquals(301, $client->getResponse()->getStatusCode());

        //authenticated users are allowed
        $client = $this->createAuthClient('user@example.com', 'user');
        $crawler = $client->request('GET', '/candidate/profile');
        $response = $client->getResponse();
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertTrue(0 === strpos($response->getTargetUrl(), 'https'));

        //https and authentication loads page
        $client = $this->createAuthClient('user@example.com', 'user');
        $crawler = $client->request('GET', '/candidate/profile', [], [], ['HTTPS' => true]);
        $this->assertTrue($crawler->filter('html:contains("Loading ...")')->count() > 0);
    }
    
    public function testResourcesApp()
    {
        //anonymous should be redirected
        $client = static::createClient();
        $client->request('GET', '/candidate/resources');
        $this->assertEquals(301, $client->getResponse()->getStatusCode());

        //authenticated users are allowed
        $client = $this->createAuthClient('user@example.com', 'user');
        $crawler = $client->request('GET', '/candidate/resources');
        $response = $client->getResponse();
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertTrue(0 === strpos($response->getTargetUrl(), 'https'));

        //https and authentication loads page
        $client = $this->createAuthClient('user@example.com', 'user');
        $crawler = $client->request('GET', '/candidate/resources', [], [], ['HTTPS' => true]);
        $this->assertTrue($crawler->filter('html:contains("Loading ...")')->count() > 0);
    }
    
    public function testAccountApp()
    {
        //anonymous should be redirected
        $client = static::createClient();
        $client->request('GET', '/candidate/account');
        $this->assertEquals(301, $client->getResponse()->getStatusCode());

        //authenticated users are allowed
        $client = $this->createAuthClient('user@example.com', 'user');
        $crawler = $client->request('GET', '/candidate/account');
        $response = $client->getResponse();
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertTrue(0 === strpos($response->getTargetUrl(), 'https'));

        //https and authentication loads page
        $client = $this->createAuthClient('user@example.com', 'user');
        $crawler = $client->request('GET', '/candidate/account', [], [], ['HTTPS' => true]);
        $this->assertTrue($crawler->filter('html:contains("Loading ...")')->count() > 0);
    }

    public function testAdminApp()
    {
        //anonymous should be redirected
        $client = static::createClient();
        $client->request('GET', '/admin');
        $this->assertEquals(301, $client->getResponse()->getStatusCode());

        //regular user should be redirected
        $client = $this->createAuthClient('user@example.com', 'user');
        $client->request('GET', '/admin', [], [], ['HTTPS' => true]);
        $this->assertEquals(403, $client->getResponse()->getStatusCode());

        //admins are allowed
        $client = $this->createAuthClient('admin@example.com', 'admin');
        $crawler = $client->request('GET', '/admin');
        $response = $client->getResponse();
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertTrue(0 === strpos($response->getTargetUrl(), 'https'));


        //https and authentication loads page
        $client = $this->createAuthClient('admin@example.com', 'admin');
        $crawler = $client->request('GET', '/admin', [], [], ['HTTPS' => true]);
        $this->assertTrue($crawler->filter('html:contains("Loading ...")')->count() > 0);
    }
}
