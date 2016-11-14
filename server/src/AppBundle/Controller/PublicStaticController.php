<?php

namespace GPS\AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Contains most static public route actions.
 *
 * @package GPS
 * @author Evan Villemez
 */
class PublicStaticController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function homeAction()
    {
        return $this->returnPublicResponse('public/home.html.twig', [
            'partners' => $this->container->getParameter('gps.partner_logos')
        ]);
    }
        
    /**
     * @Route("/employers", name="employers")
     */
    public function employersAction()
    {
        return $this->returnPublicResponse('public/employers.html.twig', [
            'isEmployer' => true,
            'showContact' => true,
            'partners' => $this->container->getParameter('gps.partner_logos'),
            'criteria' => [
                'Availability',
                'Desired compensation',
                'Work experience',
                'Preferred work environment',
                'Language proficiency',
                'Cultural/regional competency',
                'Multiculturalism',
                'Soft skills (e.g. leadership, teamwork)',
                'Hard skills (e.g. financial modeling)',
                'Computer skills',
                'Character'
            ]
        ]);
    }
    
    /**
     * @Route("/faqs", name="faqs")
     */
    public function faqsAction()
    {
        return $this->returnPublicResponse('public/faqs.html.twig', ['title' => 'FAQs']);
    }
        
    /**
     * @Route("/terms-of-use", name="terms-of-use")
     */
    public function termsOfUseAction()
    {
        $file = $this->container->getParameter('kernel.root_dir')."/Resources/data/legal/gps-terms-of-use.md";

        return $this->returnPublicResponse('public/markdown-page.html.twig', [
            'title' => 'Terms of Use',
            'text' => file_get_contents($file)
        ]);
    }
    
    /**
     * @Route("/privacy-policy", name="privacy-policy")
     */
    public function privacyPolicyAction()
    {
        $file = $this->container->getParameter('kernel.root_dir')."/Resources/data/legal/gps-privacy-policy.md";

        return $this->returnPublicResponse('public/markdown-page.html.twig', [
            'title' => 'Privacy Policy',
            'text' => file_get_contents($file)
        ]);
    }
    
    /**
     * @Route("/maintenance", name="maintenance")
     */
    public function maintenanceAction()
    {
        $cache = $this->get('gps.shared_cache');
        $message = $cache->fetch('maintenance.lock');
        if (!$message) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        return $this->returnPublicResponse('public/static-message.html.twig', [
            'title' => 'Maintenance',
            'paragraphs' =>  [$message],
        ]);
    }
    
    private function returnPublicResponse($templateName, $vars = [])
    {
        //TODO: set cache params
        
        $defaults = [
            'showContact' => false,
            'isEmployer' => false
        ];
        
        $vars = array_merge($defaults, $vars);
        
        $templateVars = array_merge($vars, [
            'assetCacheInvalidator' => $this->container->get('gps.local_cache')->fetch('gps.deploy-tag')
        ]);
        
        return $this->render($templateName, $templateVars);
    }
}
