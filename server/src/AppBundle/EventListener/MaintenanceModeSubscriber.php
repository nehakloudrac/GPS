<?php

namespace GPS\AppBundle\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MaintenanceModeSubscriber implements EventSubscriberInterface
{
    private $container;
    
    public function __construct($container)
    {
        $this->container = $container;
    }
    
    public static function getSubscribedEvents()
    {
        return [
            'kernel.request' => ['onRequest', -128],
            'ac.webservice.request' => ['onApiRequest', -128],
        ];
    }
    
    // returns json maintenance response for API requests: receiving
    // clients are expected to handle any resulting redirects
    public function onApiRequest($e)
    {
        $msg = $this->container->get('gps.shared_cache')->fetch('maintenance.lock');
        if (!$msg) {
            return;
        }
        
        $url = $this->container->get('router')->generate('maintenance');
        $data = [
            'target' => $url,
            'response' => [
                'code' => 503,
                'message' => $msg,
            ],
        ];
        
        $e->setResponse(new JsonResponse($data, 503));
    }
    
    // redirects some requests to maintenance page if the route
    // is configured for a redirect
    public function onRequest($e)
    {
        $req = $e->getRequest();
        $maintenance = $req->attributes->get('maintenance', false);

        $msg = $this->container->get('gps.shared_cache')->fetch('maintenance.lock');
        if (!$msg || !$maintenance) {
            return;
        }
        
        // create redirect response to maintenance page
        $url = $this->container->get('router')->generate('maintenance');
        $e->setResponse(new RedirectResponse($url));
    }
}
