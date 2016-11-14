<?php

namespace GPS\AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use GPS\AppBundle\Document;
use GPS\AppBundle\Form;
use GPS\AppBundle\Event;

/**
 * Controller for initiating tracking of partner referrers
 *
 * @author Evan Villemez
 */
class ReferrerController extends AbstractController
{

    /**
     * Stores referrer in a user's session and redirects to configured location,
     * or homepage.
     *
     * @Route("/referrer/{referrerKey}", name="initiate-referrer-session")
     */
    public function initiateReferrerSessionAction(Request $req, $referrerKey)
    {
        $referrers = $this->container->getParameter('gps.referrers');
        
        if (!isset($referrers[$referrerKey])) {
            $this->get('monolog.logger.gps_app')->error(sprintf("Invalid referrer [%s] received.", $referrerKey));
            
            return $this->redirect($this->generateUrl('homepage'));
        }
        
        // save the referrer in the user session
        $referrer = $referrers[$referrerKey];
        $referrer['key'] = $referrerKey;
        $sess = $req->getSession();
        $sess->set('gps.referrer', $referrer);
        
        // referrers could have a custom target route
        $target = (isset($referrer['target'])) ? $referrer['target'] : 'create-account';
        
        return $this->redirect($this->generateUrl($target));
    }
}
