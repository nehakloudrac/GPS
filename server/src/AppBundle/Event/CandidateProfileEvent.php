<?php

namespace GPS\AppBundle\Event;

use GPS\AppBundle\Document\Candidate\Profile;

class CandidateProfileEvent extends UserEvent
{
    private $profile;

    public function __construct(Profile $profile)
    {
        parent::__construct($profile->getUser());

        $this->profile = $profile;
    }

    public function getProfile()
    {
        return $this->profile;
    }
}
