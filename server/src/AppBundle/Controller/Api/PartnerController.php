<?php

namespace GPS\AppBundle\Document;

namespace GPS\AppBundle\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use GPS\AppBundle\Document;
use JMS\Serializer\SerializationContext;

/**
 * Provides all the partner api related actions under /partners
 *
 * @package GPS
 * @author Evan Villemez
 *
 * @Route("/api")
 */
class PartnerController extends AbstractApiController
{

    /** 
     * Get partners.
     * 
     * @Route("/partners", name="api-get-partners")
     * @Method("GET")
     */
    public function getPartnersAction(Request $req)
    {
        $qb = $this->getRepository('AppBundle:Partner')->createQueryBuilder();
        if (!$this->isGranted('ROLE_ADMIN')) {
            $qb->field('isEnabled')->eq(true);
            $qb->field('isPublicPartner')->eq(true);
        }
        
        $partners = $qb->getQuery()->execute();

        $ctx = SerializationContext::create()->setGroups(['Default']);
        return $this->createServiceResponse(['partners' => iterator_to_array($partners, false)], 200, [], $ctx);
    }
    
    /** 
     * Create new partner.
     * 
     * @Route("/partners", name="api-create-partner")
     * @Method("POST")
     */
    public function postPartnerAction(Request $req)
    {
        $this->ensureAdmin();

        $dCtx = $this->createDeserializationContext()->setTarget(new Document\Partner());        
        $partner = $this->decodeRequest($req, 'GPS\AppBundle\Document\Partner', $dCtx);
        $partner->setDateCreated(new \DateTime('now'));
        
        $this->validate($partner);

        $manager = $this->getDocumentManager();
        $manager->persist($partner);
        $manager->flush();
        
        $ctx = SerializationContext::create()->setGroups(['Default']);
        return $this->createServiceResponse(['partner' => $partner], 201, [], $ctx);
    }
    
    /** 
     * Get partner.
     * 
     * @Route("/partners/{id}", name="api-get-partner")
     * @Method("GET")
     */
    public function getPartnerAction(Request $req, $id)
    {
        $requirePublic = true;
        if ($this->isGranted('ROLE_ADMIN')) {
            $requirePublic = false;
        }
        
        $partner = $this->getPartner($id);
        
        if ($requirePublic) {
            if (!$partner->getIsEnabled() || !$partner->getIsPublicPartner()) {
                throw $this->createHttpException(404, 'Not found.');
            }
        }
        
        $ctx = SerializationContext::create()->setGroups(['Default']);
        return $this->createServiceResponse(['partner' => $partner], 200, [], $ctx);
    }
    
    /** 
     * Modify partner.
     * 
     * @Route("/partners/{id}", name="api-put-partner")
     * @Method("PUT")
     */
    public function putPartnerAction(Request $req, $id)
    {
        $this->ensureAdmin();
        $partner = $this->getPartner($id);

        $dCtx = $this->createDeserializationContext()->setTarget($partner);
        $partner = $this->decodeRequest($req, 'GPS\AppBundle\Document\Partner', $dCtx);
        
        $this->validate($partner);
        
        $this->getDocumentManager()->flush();
        
        $ctx = SerializationContext::create()->setGroups(['Default']);
        return $this->createServiceResponse(['partner' => $partner], 200, [], $ctx);
    }
        
    /** 
     * Upload partner logo.
     * 
     * @Route("/partners/{id}/logo", name="api-post-partner-logo")
     * @Method("POST")
     */
    public function postPartnerLogoAction(Request $req, $id)
    {
        $this->ensureAdmin();
        $partner = $this->getPartner($id);

        $uploaded = $req->files->get('file');

        if (!$uploaded->isValid()) {
            throw $this->createHttpException(422, "There was an error while handling the uploaded file.  ERR: ${$file->getError()}");
        }
        
        // do resize
        $resizedPath = $this->get('gps.thumbnailer')->resize($uploaded->getRealPath(), 250, 200, 'png');

        //figure out final file name
        $finalPath = sprintf("/partner-logos/%s.png", $partner->getKey());

        //move resized file to profile-images location, ensuring that
        //previously existing file is deleted first
        $publicFS = $this->get('gps.filesystem.public');
        if ($publicFS->has($finalPath)) {
            $publicFS->delete($finalPath);
        }
        $readStream = fopen($resizedPath, 'r+');
        $publicFS->writeStream($finalPath, $readStream);
        if (is_resource($readStream)) {
            fclose($readStream);
        }
        
        //update partner doc
        $partner->setLogoUrl($finalPath);
        $this->getDocumentManager()->flush();

        $ctx = $this->createSerializationContext()->setGroups(['Default']);
        return $this->createServiceResponse(['partner' => $partner], 200, [], $ctx);
    }
        
    /** 
     * Delete partner logo.
     * 
     * @Route("/partners/{id}/logo", name="api-delete-partner-logo")
     * @Method("DELETE")
     */
    public function deletePartnerLogoAction(Reques $req, $id)
    {
        $this->ensureAdmin();
        $partner = $this->getParner($id);

        #remove the actual file from whereever
        $this->get('gps.filesystem.public')->delete($partner->getLogoUrl());

        #update and save parner doc
        $partner->setLogoUrl(null);
        $this->getDocumentManager()->flush();

        #return modified partner
        $ctx = $this->createSerializationContext()->setGroups(['Default']);
        return $this->createServiceResponse(['partner' => $partner], 200, [], $ctx);
    }
    
    /** 
     * Delete partner.
     * 
     * @Route("/partners/{id}", name="api-delete-partner")
     * @Method("DELETE")
     */
    public function deletePartnerAction(Request $req, $id)
    {
        $this->ensureAdmin();
        $partner = $this->getPartner($id);

        if (!is_null($partner->getLogoUrl())) {
            $this->get('gps.filesystem.public')->delete($partner->getLogoUrl());
        }
        
        $manager = $this->getDocumentManager();
        $manager->remove($partner);
        $manager->flush();
        
        return $this->createServiceResponse([], 200);
    }
    
    private function getPartner($id)
    {
        $partner = $this->getRepository('AppBundle:Partner')->find($id);
        if (!$partner) {
            throw $this->createHttpException(404, 'No partner by that id.');
        }
        
        return $partner;
    }
    
    private function ensureAdmin()
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createHttpException(403, 'Only admins may create or edit partners.');
        }
    }
}
