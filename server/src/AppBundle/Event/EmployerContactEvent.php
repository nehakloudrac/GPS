<?php

namespace GPS\AppBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use GPS\AppBundle\Document\EmployerContact;

/**
 * Exposes an EmployerContact
 */
class EmployerContactEvent extends Event
{
    private $contact;
    
    public function __construct(EmployerContact $contact)
    {
        $this->contact = $contact;
    }
    
    public function getContact()
    {
        return $this->contact;
    }
}
