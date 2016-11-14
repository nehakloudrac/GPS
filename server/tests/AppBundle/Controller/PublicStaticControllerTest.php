<?php

namespace GPS\Tests\AppBundle\Controller;

use GPS\AppBundle\Testing;

class PublicStaticControllerTest extends Testing\ControllerTest
{
    public function testHome()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('html:contains("Global Professional Search")')->count() > 0);
        
        // TODO: fetch links, assert no internal broken links
        $this->markTestIncomplete();
    }

    public function testEmployers()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/employers');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('html:contains("Employers")')->count() > 0);

        // TODO: fetch links, assert no internal broken links
        $this->markTestIncomplete();
    }

    public function testFaqs()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/faqs');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('html:contains("FAQ")')->count() > 0);
    }

    public function testPrivacy()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/privacy-policy');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('html:contains("Privacy Policy")')->count() > 0);
    }

    public function testTermsOfService()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/terms-of-use');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('html:contains("Terms of Use")')->count() > 0);
    }
}
