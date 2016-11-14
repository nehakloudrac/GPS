<?php

namespace GPS\AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LinkActionController extends AbstractController
{
    /**
     * Handle public urls with encoded data.
     * 
     * @Route("/l/a/{token}", name="link-action-public")
     */
    public function handlePublicLinkAction($token)
    {
        return $this->handleToken($token);
    }
    
    /**
     * Handle urls with encoded data that require auth first.
     * 
     * @Route("/l/r/{token}", name="link-action-private")
     */
    public function handleAuthenticatedLinkAction($token)
    {
        return $this->handleToken($token);
    }
    
    protected function handleToken($token)
    {
        try {
            $data = $this->get('gps.data_token_generator')->decodeDataToken($token);
        } catch (\Exception $e) {
            throw $this->createHttpException(400, $e->getMessege());
        }
        
        // TODO: consider dispatching event instead of the switch here
        switch ($data['action']) {

            case 'redirect': $res = $this->handleRedirect($data); break;

            default: throw $this->createHttpException(400, "Invalid link action.");
        }
        
        if (!$res instanceof Response) {
            throw $this->createHttpException(500, "Resulted in invalid response.");
        }
        
        return $res;
    }
    
    private function handleRedirect($data)
    {
        $user = $this->getRepository('AppBundle:User')->find($data['userId']);
        if (!$user) {
            throw $this->createHttpException(400, "Invalid user specified.");
        }
        
        return $this->redirect($data['data']['target']);
    }
}