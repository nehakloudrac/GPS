<?php

namespace GPS\Tests\AppBundle\Controller;

use GPS\AppBundle\Testing;

class AccountControllerTest extends Testing\ControllerTest
{
    protected function onNotSuccessfulTest(\Exception $e)
    {
        $this->resetMongo();
        
        throw $e;
    }
    
    public function testRegistrationRedirectsForExistingUser()
    {
        $this->resetMongo();
        
        $c = $this->createAuthClient('user@example.com', 'user');
        $res = $this->call($c, 'GET', '/account/create');
        $this->assertSame(200, $res->getStatusCode());
        
        // submit registration form, expect the redirect
        // then make api call and expect that the referrer has been properly set
        $crawler = $c->getCrawler();
        $form = $crawler->selectButton('submit')->form();
        $form['registration[firstName]'] = 'Foobert';
        $form['registration[lastName]'] = 'Bartleby';
        $form['registration[email]'] = 'user@example.com';
        $form['registration[password_confirm][password]'] = 'password';
        $form['registration[password_confirm][confirm]'] = 'password';
        $form['registration[acceptedTerms]'] = '1';
        $c->submit($form);
        $res = $c->getResponse();
        
        // did registration should have redirected to "forgot-password", because
        // they are already registered
        $this->assertSame(302, $res->getStatusCode());
        $this->assertSame('/account/forgot-password', $res->headers->get('location'));
    }

    public function testRegistrationAction()
    {
        $this->resetMongo();
        
        $c = static::createClient();
        
        $res = $this->call($c, 'GET', '/account/create');
        $this->assertSame(200, $res->getStatusCode());
        
        // submit registration form, expect the redirect and email
        $crawler = $c->getCrawler();
        $form = $crawler->selectButton('submit')->form();
        $form['registration[firstName]'] = 'Foobert';
        $form['registration[lastName]'] = 'Bartleby';
        $form['registration[email]'] = 'foobert@example.com';
        $form['registration[password_confirm][password]'] = 'password';
        $form['registration[password_confirm][confirm]'] = 'password';
        $form['registration[acceptedTerms]'] = '1';

        $c->enableProfiler();

        // submit registration form, should result in redirect
        $c->submit($form);
        $res = $c->getResponse();
        $this->assertSame(302, $res->getStatusCode());
        $this->assertSame('/candidate/profile', $res->headers->get('location'));

        // did the registration action also initiate sending an email?
        $mailCollector = $c->getProfile()->getCollector('swiftmailer');
        $collectedMessages = $mailCollector->getMessages();
        $message = $collectedMessages[0];
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertEquals('Verify Your GPS Account', $message->getSubject());
        $this->assertEquals('foobert@example.com', key($message->getTo()));
        
        // parse email body to extract the confirmation token
        preg_match('/\/account\/verify\/(.*)\"/', $message->getBody(), $matches);
        $this->assertTrue(is_string($matches[1]));
        
        return $matches[1];
    }
    
    /**
     * @depends testRegistrationAction
     */
    public function testConfirmEmailAction($confirmToken)
    {
        // in this scenario the user is NOT already logged in, so they should be
        // redirected to the login page
        $c = static::createClient();
        
        $res = $this->call($c, 'GET', '/account/verify/'.$confirmToken);
        $this->assertSame(302, $res->getStatusCode());
        $this->assertSame('/login', $res->headers->get('location'));
        $res = $this->call($c, 'GET', '/login');
        $crawler = $c->getCrawler();
        $this->assertTrue($crawler->filter('html:contains("verified")')->count() > 0);
    }
    
    public function testRequestEmailVerificationLink()
    {
        // we're using an already logged in user here to ensure
        // that the redirect takes them to the expected location...
        // this is slightly different than when the user is not
        // logged in and is redirected to the login page upon verification

        $c = $this->createAuthClient('user@example.com', 'user');
        
        $res = $this->call($c, 'GET', '/account/verify');
        $this->assertSame(200, $res->getStatusCode());
        
        // submit form, expect the redirect & email
        $crawler = $c->getCrawler();
        $form = $crawler->selectButton('submit')->form();
        $form['form[email]'] = 'user@example.com';
        $c->enableProfiler();
        $c->submit($form);
        $res = $c->getResponse();        
        $this->assertSame(302, $res->getStatusCode());
        $this->assertSame('/account/verification-email-sent', $res->headers->get('location'));
        
        // did the submit action also initiate sending an email?
        $mailCollector = $c->getProfile()->getCollector('swiftmailer');
        $collectedMessages = $mailCollector->getMessages();
        $message = $collectedMessages[0];
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertEquals('Verify Your GPS Account', $message->getSubject());
        $this->assertEquals('user@example.com', key($message->getTo()));
        
        // parse email body to extract the confirmation token
        preg_match('/\/account\/verify\/(.*)\"/', $message->getBody(), $matches);
        $this->assertTrue(is_string($matches[1]));
        
        // expect the redirected location to contain confirmation message
        $res = $this->call($c, 'GET', '/account/verification-email-sent');
        $this->assertSame(200, $res->getStatusCode());
        $crawler = $c->getCrawler();
        $this->assertTrue($crawler->filter('html:contains("sent")')->count() > 0);

        // visit verification link, expect redirect to dashboard, because
        // user is already logged in
        $res = $this->call($c, 'GET', '/account/verify/'.$matches[1]);
        $this->assertSame(302, $res->getStatusCode());
        $this->assertSame('/candidate/dashboard', $res->headers->get('location'));
        $res = $this->call($c, 'GET', '/candidate/dashboard');
        $crawler = $c->getCrawler();
        $this->assertTrue($crawler->filter('html:contains("Loading")')->count() > 0);
    }
        
    public function testPasswordReset()
    {
        $this->resetMongo();
        
        // should be able to get to dashboard with current password
        $c = $this->createAuthClient('user@example.com', 'user');
        $res = $this->call($c, 'GET', '/candidate/dashboard');
        $this->assertSame(200, $res->getStatusCode());
        
        // create new client to reset the users password
        $c = static::createClient();
        
        $res = $this->call($c, 'GET', '/account/forgot-password');
        $this->assertSame(200, $res->getStatusCode());
        
        // submit form, expect the redirect & email
        $crawler = $c->getCrawler();
        $form = $crawler->selectButton('submit')->form();
        $form['form[email]'] = 'user@example.com';
        $c->enableProfiler();
        $c->submit($form);
        $res = $c->getResponse();
        $this->assertSame(302, $res->getStatusCode());
        $this->assertSame('/account/password-email-sent', $res->headers->get('location'));
        // did the submit action also initiate sending an email?
        $mailCollector = $c->getProfile()->getCollector('swiftmailer');
        $collectedMessages = $mailCollector->getMessages();
        $message = $collectedMessages[0];
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertEquals('GPS Password Reset', $message->getSubject());
        $this->assertEquals('user@example.com', key($message->getTo()));
        // parse email body to extract the confirmation token
        preg_match('/\/account\/password-reset\/(.*)\"/', $message->getBody(), $matches);
        $this->assertTrue(is_string($matches[1]));

        // check redirect to email sent verification
        $res = $this->call($c, 'GET', '/account/password-email-sent');
        $crawler = $c->getCrawler();
        $this->assertTrue($crawler->filter('html:contains("check your inbox")')->count() > 0);
        
        // visit reset password link... expect redirect to reset form
        $res = $this->call($c, 'GET', '/account/password-reset/'.$matches[1]);
        $this->assertSame(302, $res->getStatusCode());
        $this->assertSame('/account/reset-password', $res->headers->get('location'));
        $res = $this->call($c, 'GET', '/account/reset-password');
        $crawler = $c->getCrawler();
        $form = $crawler->selectButton('submit')->form();
        $form['form[password][password]'] = 'newpassword';
        $form['form[password][confirm]'] = 'newpassword';
        $c->submit($form);
        $res = $c->getResponse();
        $this->assertSame(302, $res->getStatusCode());
        $this->assertSame('/login', $res->headers->get('location'));
        $res = $this->call($c, 'GET', '/login');
        $crawler = $c->getCrawler();
        $this->assertTrue($crawler->filter('html:contains("has been updated")')->count() > 0);
        
        // test old password - shouldn't work
        $c = $this->createAuthClient('user@example.com', 'user');
        $res = $this->call($c, 'GET', '/candidate/dashboard');
        $this->assertSame(302, $res->getStatusCode());
        $this->assertSame('https://localhost/login', $res->headers->get('location'));
                
        // test new password, should work...
        // TODO: figure out why using the new password in this manner isn't working...
        // I assume it has something to do with encoding, but nothing is jumping out at me
        // $c = $this->createAuthClient('user@example.com', 'newpassword');
        // $res = $this->call($c, 'GET', '/candidate/dashboard');
        // $this->assertSame(200, $res->getStatusCode());
        // $this->assertTrue($crawler->filter('html:contains("Loading")')->count() > 0);
        
        // forcing db reset at the end of this test to undo the changes in the database
        $this->resetMongo();
    }
    
    public function testUnsubscribeConfirmAction()
    {
        $this->resetMongo();
        
        // ensure a user is subscribed
        $c = $this->createAuthClient('user@example.com','user');
        $json = $this->callApi($c, 'GET', '/api/users/cccccccccccccccccccccccc');
        $this->assertTrue($json['user']['preferences']['allowProfileHealthEmails']);
        
        // generate the unsubscribe token, and visit the url
        $unsubscribeToken = base64_encode(json_encode(['userId' => $json['user']['id'], 'emailKey' => 'profile-health']));
        $unsubscribeLink = $c->getContainer()->get('router')->generate('unsubscribe', ['token' => $unsubscribeToken]);
        $c = static::createClient();
        $this->call($c, 'GET', $unsubscribeLink);
        
        $c = $this->createAuthClient('user@example.com','user');
        $json = $this->callApi($c, 'GET', '/api/users/cccccccccccccccccccccccc');
        $this->assertFalse($json['user']['preferences']['allowProfileHealthEmails']);
    }
}
