<?php

namespace GPS\AppBundle\Tests\Document;

use GPS\AppBundle\Document\User;

class UserTest extends \PHPUnit_Framework_TestCase
{
    public function testSerializeAndDeserializeUser()
    {
        $userA = User::createFromArray([
            'id' => '23sdf23adfa',
            'firstName' => 'Evan',
            'lastName' => 'Villemez'
        ]);
            
        $serialized = serialize($userA);
        
        $userB = unserialize($serialized);
        
        $this->assertSame($userA->getId(), $userB->getId());
        $this->assertSame($userA->getFirstName(), $userB->getFirstName());
        $this->assertSame($userA->getLastName(), $userB->getLastName());
    }
}
