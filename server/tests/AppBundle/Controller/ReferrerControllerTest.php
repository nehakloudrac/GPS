<?php

namespace GPS\Tests\AppBundle\Controller;

use GPS\AppBundle\Testing;

class ReferrerControllerTest extends Testing\ControllerTest
{
    public function testRedirectToHomepageOnBadReferrer()
    {
        $c = static::createClient();
        $res = $this->call($c, 'GET', '/referrer/foo');
        
        $this->assertSame(302, $res->getStatusCode());
        $this->assertSame('/', $res->headers->get('location'));
    }
    
    public function testRedirectToRegistrationOnGoodReferrer()
    {
        // need to reset here to ensure eventual
        // registration actually goes through, otherwise
        // there will be duplicate users and it will fail (as expected)
        $this->resetMongo();
        
        $c = static::createClient();
        $res = $this->call($c, 'GET', '/referrer/test-referrer');
        
        $this->assertSame(302, $res->getStatusCode());
        $this->assertSame('/account/create', $res->headers->get('location'));
        
        return $c;
    }
    
    /**
     * @depends testRedirectToRegistrationOnGoodReferrer
     */
    public function testRegisterViaReferrer($c)
    {
        $res = $this->call($c, 'GET', '/account/create');
        $this->assertSame(200, $res->getStatusCode());
        
        // submit registration form, expect the redirect
        // then make api call and expect that the referrer has been properly set
        $crawler = $c->getCrawler();
        $form = $crawler->selectButton('submit')->form();
        $form['registration[firstName]'] = 'Foobert';
        $form['registration[lastName]'] = 'Bartleby';
        $form['registration[email]'] = 'foobert@example.com';
        $form['registration[password_confirm][password]'] = 'password';
        $form['registration[password_confirm][confirm]'] = 'password';
        $form['registration[acceptedTerms]'] = '1';
        $c->submit($form);
        $res = $c->getResponse();
        
        // did registration work and result in dashboard redirect?
        $this->assertSame(302, $res->getStatusCode());
        $this->assertSame('/candidate/profile', $res->headers->get('location'));
        
        // as an admin, find this user by email address, and expect
        // that their referrer was properly set during registration...
        // this is kind of roundabout, but it'll work for now
        
        $c = $this->createAuthClient('admin@example.com', 'admin');
        $json = $this->callApi($c, 'GET','/api/admin/overview/users?email=foobert@example.com');
        $res = $c->getResponse();
        $this->assertSame(200, $res->getStatusCode());
        $this->assertTrue(isset($json['results']));
        $this->assertSame(1, count($json['results']));
        
        $id = $json['results'][0]['user']['id'];
        $json = $this->callApi($c, 'GET', '/api/users/'.$id);
        $this->assertSame(200, $json['response']['code']);
        $this->assertTrue(isset($json['user']));
        $this->assertTrue(isset($json['user']['institutionReferrer']));
        $this->assertSame('test-referrer', $json['user']['institutionReferrer']);
    }
}