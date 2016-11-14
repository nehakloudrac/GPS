<?php

namespace GPS\AppBundle\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use GPS\AppBundle\Document;
use JMS\Serializer\SerializationContext;

/**
 * Prodide CRUD routes for Resource Link content
 *
 * @package GPS
 * @author Evan Villemez
 *
 * @Route("/api")
 */
class ResourceLinkController extends AbstractApiController
{
    
    /** 
     * List resource links
     * 
     * @Route("/content/links", name="api-content-get-links")
     * @Method("GET")
     */
    public function getResourceLinksAction(Request $req)
    {
        $params = [];
        $cacheable = false;
        
        // enforce published filter for non-admin
        if (!$this->isGranted('ROLE_ADMIN')) {
            $params['published'] = true;
            $cacheable = true;
        }
        
        // check for type filter
        if ($types = $req->query->get('types', false)) {
            $params['type'] = explode(',', $types);
        }
        
        // check for tags filter
        if ($tags = $req->query->get('tags', false)) {
            $params['tags'] = explode(',', $tags);
        }

        // enforce limit/skip for pagination
        $limit = $req->query->get('_limit', false);
        $limit = $limit ? abs((int) $limit) : 20;
        $skip = $req->query->get('_skip', false);
        $skip = $skip ? abs((int) $skip) : 0;
        
        // configure a query builder
        $qb = $this->getDocumentManager()->createQueryBuilder('AppBundle:ResourceLink');
        foreach ($params as $key => $val) {
            if (is_array($val)) {
                $qb->field($key)->in($val);
            } else {
                $qb->field($key)->equals($val);
            }
        }
        $qb->limit($limit);
        $qb->skip($skip); 
        $qb->sort('datePublished', 'desc');
        
        $links = $qb->getQuery()->execute();
        
        $ctx = $this->createSerializationContext()->setGroups(['Default']);
        $res = $this->createServiceResponse(['links' => iterator_to_array($links, false)], 200, [], $ctx);
        
        // TODO: consider client cache... but how to set it, pass raw headers?
        // or modify "createServiceResponse" to somehow return the final HttpResponse?
        return $res;
    }
    
    /** 
     * Create a resource link
     * 
     * @Route("/content/links", name="api-content-post-link")
     * @Method("POST")
     */
    public function postResourceLinkAction(Request $req)
    {
        $this->ensureAdmin();
        $link = $this->getRepository('AppBundle:ResourceLink');
        if (!$link) {
            throw $this->createHttpException(404, 'No link found by that id.');
        }

        $dCtx = $this->createDeserializationContext()->setTarget(new Document\ResourceLink());
        
        $link = $this->decodeRequest($req, 'GPS\AppBundle\Document\ResourceLink', $dCtx);
        $link->setCreator($this->getUser());
        
        $this->validate($link);
        
        $manager = $this->getDocumentManager();
        $manager->persist($link);
        $manager->flush();
        
        return $this->createServiceResponse(['link' => $link], 201);
    }
    
    /** 
     * Get individual resource link
     * 
     * @Route("/content/links/{id}", name="api-content-get-link")
     * @Method("GET")
     */
    public function getResourceLinkAction(Request $req, $id)
    {
        $link = $this->getRepository('AppBundle:ResourceLink')->find($id);
        if (!$link) {
            throw $this->createHttpException(404, 'No link found by that id.');
        }
        
        return $this->createServiceResponse(['link' => $link], 200);
    }
    
    /** 
     * Modify a resource link
     * 
     * @Route("/content/links/{id}", name="api-content-put-link")
     * @Method("PUT")
     */
    public function putResourceLinkAction(Request $req, $id)
    {
        $this->ensureAdmin();
        $link = $this->getRepository('AppBundle:ResourceLink')->find($id);
        if (!$link) {
            throw $this->createHttpException(404, 'No link found by that id.');
        }
        
        $dCtx = $this->createDeserializationContext()->setTarget($link);
        $link = $this->decodeRequest($req, 'GPS\AppBundle\Document\ResourceLink', $dCtx);
        
        $this->validate($link);
        
        $this->getDocumentManager()->flush();

        return $this->createServiceResponse(['link' => $link], 200);
    }
    
    /** 
     * Delete a resource link
     * 
     * @Route("/content/links/{id}", name="api-content-delete-link")
     * @Method("DELETE")
     */
    public function deleteResourceLinkAction(Request $req, $id)
    {
        $this->ensureAdmin();
        $link = $this->getRepository('AppBundle:ResourceLink')->find($id);
        if (!$link) {
            throw $this->createHttpException(404, 'No link found by that id.');
        }
        
        $manager = $this->getDocumentManager();
        $manager->remove($link);
        $manager->flush();
        
        return $this->createServiceResponse([], 200);
    }
    
    private function ensureAdmin()
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createHttpException(403, 'Only admins may create or modify content.');
        }
    }
}
