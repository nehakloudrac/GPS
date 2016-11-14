<?php

namespace GPS\AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Some extra convenience methods for all controllers.
 *
 * @package default
 * @author Evan Villemez
 */
class AbstractController extends Controller
{
    public function generateAbsoluteUrl($name, $ops = [])
    {
        return $this->generateUrl($name, $ops, UrlGeneratorInterface::ABSOLUTE_URL);
    }
    
    protected function createHttpException($code, $msg = null, $prev = null)
    {
        return new HttpException($code, $msg, $prev);
    }
    
    /**
     * TODO: remove, should already be provided
     */
    protected function addFlash($type, $msg)
    {
        $this->getRequest()->getSession()->getFlashbag()->add($type, $msg);
    }
    
    protected function getRepository($name)
    {
        return $this->getDocumentManager()->getRepository($name);
    }
    
    protected function getDocumentManager()
    {
        return $this->get('doctrine_mongodb')->getManager();
    }
    
    protected function getMongoConnection()
    {
        return $this->get('doctrine_mongodb')->getConnection()->selectDatabase($this->container->getParameter('mongodb_database'));
    }
}
