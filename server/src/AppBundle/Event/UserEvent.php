<?php

namespace GPS\AppBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use GPS\AppBundle\Document\User;

/**
 * User event exposes the user to listeners for user related events.
 *
 */
class UserEvent extends Event
{
    private $user;
    
    public function __construct(User $user)
    {
        $this->user = $user;
    }
    
    public function getUser()
    {
        return $this->user;
    }
}
