<?php

namespace GPS\AppBundle\EventListener;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * In the case of a session expiration during an API request, intercept
 * and add a flash message, because the user will be redirected to the login
 * page, but won't know why if there's no message.
 */
class ApiSessionExpiredListener
{
    public function handleApiException($event)
    {
        $e = $event->getException();
        $req = $event->getRequest();

        if ($e instanceof AccessDeniedException) {
            $sess = $req->getSession();
            if ($sess) {
                $sess->getFlashBag()->add('warning', "You have been logged out due to inactivity.");
            }
        }
    }
}
