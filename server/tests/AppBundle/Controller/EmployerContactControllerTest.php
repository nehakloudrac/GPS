<?php

namespace GPS\Tests\AppBundle\Controller;

use GPS\AppBundle\Testing;

class EmployerContactControllerTest extends Testing\ControllerTest
{
    private function fillForm($form)
    {
        $form['employer_contact[firstName]'] = 'Foobert';
        $form['employer_contact[lastName]'] = 'Bartleby';
        $form['employer_contact[email]'] = 'foobert@example.com';
        $form['employer_contact[phoneNumber]'] = '5555555555';
    }
    
    public function testSubmitPlain()
    {
        // get the contact form and submit it
        $c = static::createClient();
        $res = $this->call($c, 'GET', '/employers/contact');
        $this->assertSame(200, $res->getStatusCode());
        $crawler = $c->getCrawler();
        $form = $crawler->selectButton('Submit')->form();
        $this->fillForm($form);
        $c->enableProfiler();
        $c->submit($form);

        // expect redirect
        $res = $c->getResponse();
        $this->assertSame(302, $res->getStatusCode());
        $this->assertSame('/employers/contact-success', $res->headers->get('location'));
        
        // expect that emails were sent
        $mailCollector = $c->getProfile()->getCollector('swiftmailer');
        $collectedMessages = $mailCollector->getMessages();

        $message = $collectedMessages[0];
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertEquals('Thanks for contacting GPS!', $message->getSubject());
        $this->assertEquals('foobert@example.com', key($message->getTo()));
        $message = $collectedMessages[1];
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertEquals('GPS Employer Contact', $message->getSubject());
        $this->assertEquals('kirsten@globalprofessionalsearch.com', key($message->getTo()));
        
        // expect functioning redirect confirmation page
        $res = $this->call($c, 'GET', '/employers/contact-success');
        $this->assertSame(200, $res->getStatusCode());
        $crawler = $c->getCrawler();
        $this->assertTrue($crawler->filter('html:contains("contacting GPS")')->count() > 0);
    }
    
    public function testSubmitWithPositions()
    {
        $this->markTestIncomplete();
    }
}
