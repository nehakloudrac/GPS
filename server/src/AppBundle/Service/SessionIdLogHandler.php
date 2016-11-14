<?php

namespace GPS\AppBundle\Service;

use Symfony\Component\HttpFoundation\Session\Session;

class SessionIdLogHandler
{
    private $session;
    private $sessionId;
    private $token;
    private $rand;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function processRecord(array $record)
    {
        if (null === $this->rand) {
            $this->rand = substr(uniqid(), -8);
        }
        
        if (null === $this->sessionId) {
            try {
                $this->sessionId = $token = $this->session->getId();
            } catch (\RuntimeException $e) {
                $this->sessionId = null;
                $token = '????????';
            }
            
            if(empty($token)) {
                $this->sessionId = null;
                $token = '????????';
            }
            
            $this->token = $token . '-' . $this->rand;
        }

        $record['extra']['token'] = $this->token;

        return $record;
    }
}
