<?php

namespace GPS\Tests\AppBundle\Controller;

use GPS\AppBundle\Testing;

class LinkActionControllerTest extends Testing\ControllerTest
{
    public function testHandlePublicLinkAction()
    {
        $this->markTestIncomplete();
        
        //generate link
        //request link
        //exect redirect
        //follow redirect, expect proper location
    }
    
    public function testHandleAuthLinkAction()
    {
        $this->markTestIncomplete();
        
        $g = $this->getContainer()->get('gps.data_token_generator');
        
        $url = $g->generatePrivateDataUrl([
            'action' => 'redirect',
            'data' => [
                'target' => '/candidate/account'
            ]
        ]);
        
        // generate link
        // request link unauthed
        // get redirect to login
        // follow redirect
        // submit login form
        // get redirect
        // expect redirected location

    }
}
