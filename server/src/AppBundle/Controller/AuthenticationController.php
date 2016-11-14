<?php

namespace GPS\AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * This controller lets people login and out, and reset forgotten passwords.  There isn't much
 * here as most of the logic is actually implemented by the Security Component.
 *
 * @author Evan Villemez
 */
class AuthenticationController extends Controller
{
    
    /**
     * Login form path.
     *
     * @Route("/login", name="login", defaults={"maintenance": true})
     * @Method("GET")
     */
    public function loginAction(Request $req)
    {
        $authenticationUtils = $this->get('security.authentication_utils');
        
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('public/login.html.twig', [
            'title' => 'Log In',
            'assetCacheInvalidator' => $this->container->get('gps.local_cache')->fetch('gps.deploy-tag'),
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }
    
    /**
     * Validates the login form.
     *
     * @Route("/login-check", name="login-check")
     * @Method("POST")
     */
    public function loginCheckAction()
    {
        //NOTE: nothing to do here, the security component takes care of it, just need to have the route defined.
    }
    
    /**
     * Ends a user's session.
     *
     * @Route("/logout", name="logout")
     */
    public function logoutAction()
    {
        //NOTE: nothing to do here, the security component takes care of it, just need to have the route defined.
    }    
}
