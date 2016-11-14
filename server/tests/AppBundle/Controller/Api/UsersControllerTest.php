<?php

namespace GPS\Tests\AppBundle\Controller\Api;

use GPS\AppBundle\Testing;

class UsersControllerTest extends Testing\ControllerTest
{
    use Testing\ResetFixturesHelperTrait;
        
    public function testGetUserAction()
    {
        // unauthed user is denied
        $c = static::createClient();
        $res = $this->call($c, 'GET', '/api/users/cccccccccccccccccccccccc');
        $this->assertSame(401, $res->getStatusCode());

        // auth user is denied access to other profile
        $c = $this->createAuthClient('user2@example.com', 'user2');
        $res = $this->call($c, 'GET', '/api/users/cccccccccccccccccccccccc');
        $this->assertSame(403, $res->getStatusCode());

        // user is granted access to own profile
        $c = $this->createAuthClient('user@example.com', 'user');
        $res = $this->call($c, 'GET', '/api/users/cccccccccccccccccccccccc');
        $this->assertSame(200, $res->getStatusCode());

        // admin user granted access to user
        // user is granted access to own profile
        $c = $this->createAuthClient('admin@example.com', 'admin');
        $res = $this->call($c, 'GET', '/api/users/cccccccccccccccccccccccc');
        $this->assertSame(200, $res->getStatusCode());
    }
    
    public function testPutUserAction()
    {
        $c = $this->createAuthClient('user@example.com', 'user');
        $json = $this->callApi($c, 'GET', '/api/users/cccccccccccccccccccccccc');
        $this->assertSame(200, $c->getResponse()->getStatusCode());
        $this->assertSame('Foobert', $json['user']['firstName']);
        $this->assertSame(true, $json['user']['preferences']['allowGravatar']);

        $c = $this->createAuthClient('user@example.com', 'user');
        $json = $this->callApi($c, 'PUT', '/api/users/cccccccccccccccccccccccc', [
            'firstName' => 'Bazil',
            'preferences' => [
                'allowGravatar' => false
            ]
        ]);
        $this->assertSame(200, $c->getResponse()->getStatusCode());
        $this->assertSame('Bazil', $json['user']['firstName']);
        $this->assertSame(false, $json['user']['preferences']['allowGravatar']);

        $c = $this->createAuthClient('user@example.com', 'user');
        $json = $this->callApi($c, 'GET', '/api/users/cccccccccccccccccccccccc');
        $this->assertSame(200, $c->getResponse()->getStatusCode());
        $this->assertSame('Bazil', $json['user']['firstName']);
        $this->assertSame(false, $json['user']['preferences']['allowGravatar']);
    }
    
    public function testPutUserEmailAction()
    {
        $c = $this->createAuthClient('user2@example.com', 'user2');
        
        // get user, ensure email
        $res = $this->callApi($c, "GET", "/api/users/dddddddddddddddddddddddd");
        $this->assertSame(200, $c->getResponse()->getStatusCode());
        $this->assertSame('user2@example.com', $res['user']['email']);
        $this->assertTrue($res['user']['isVerified']);
        
        // put same address; should error
        $res = $this->callApi($c, "PUT", "/api/users/dddddddddddddddddddddddd/email", ['email' => 'user2@example.com']);
        $this->assertSame(422, $c->getResponse()->getStatusCode());        

        // put existing email address: should be essentially a validation error
        $res = $this->callApi($c, "PUT", "/api/users/dddddddddddddddddddddddd/email", ['email' => 'admin@example.com']);
        $this->assertSame(422, $c->getResponse()->getStatusCode());
        
        // put empty string, should error
        $res = $this->callApi($c, "PUT", "/api/users/dddddddddddddddddddddddd/email", ['email' => '']);
        $this->assertSame(422, $c->getResponse()->getStatusCode());

        // put empty string, should error
        $res = $this->callApi($c, "PUT", "/api/users/dddddddddddddddddddddddd/email", ['email' => '  ']);
        $this->assertSame(422, $c->getResponse()->getStatusCode());
        
        // put invalid email address, should error
        $res = $this->callApi($c, "PUT", "/api/users/dddddddddddddddddddddddd/email", ['email' => 'invalid']);
        $this->assertSame(422, $c->getResponse()->getStatusCode());
        
        // put invalid type, should error
        $res = $this->callApi($c, "PUT", "/api/users/dddddddddddddddddddddddd/email", ['email' => 42]);
        $this->assertSame(422, $c->getResponse()->getStatusCode());

        // modify email - expect that verification email was sent and user is no longer verified
        $c->enableProfiler();
        $data = $this->callApi($c, "PUT", "/api/users/dddddddddddddddddddddddd/email", ['email' => 'changed@example.com']);
        $res = $c->getResponse();
        $this->assertSame(200, $res->getStatusCode());
        $this->assertSame('changed@example.com', $data['user']['email']);
        $this->assertFalse($data['user']['isVerified']);
        $mailCollector = $c->getProfile()->getCollector('swiftmailer');
        $collectedMessages = $mailCollector->getMessages();
        $message = $collectedMessages[0];
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertEquals('Verify your GPS Email Address', $message->getSubject());
        $this->assertEquals('changed@example.com', key($message->getTo()));
        preg_match('/\/account\/verify\/(.*)\"/', $message->getBody(), $matches);
        $this->assertTrue(is_string($matches[1]));
        
        // try creating new client with old email address - shouldn't work anymore
        $c = $this->createAuthClient('user2@example.com', 'user2');
        $data = $this->callApi($c, "GET", "/api/users/dddddddddddddddddddddddd");
        $this->assertSame(302, $c->getResponse()->getStatusCode());
        
        // new client w/ modified email - refetch user, expect persisted change
        $c = $this->createAuthClient('changed@example.com', 'user2');
        $data = $this->callApi($c, "GET", "/api/users/dddddddddddddddddddddddd");
        $res = $c->getResponse();
        $this->assertSame(200, $c->getResponse()->getStatusCode());
        $this->assertSame('changed@example.com', $data['user']['email']);
        $this->assertFalse($data['user']['isVerified']);
    }
    
    public function testPutUserPasswordAction()
    {
        $this->markTestIncomplete();
    }
    
}
