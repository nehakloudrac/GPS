<?php

namespace GPS\AppBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GPS\AppBundle\Document\Candidate;
use GPS\AppBundle\Event\AppEvents;
use GPS\AppBundle\Event\CandidateProfileEvent;

/**
 * When a candidate completes their short form, their full profile
 * is pre-filled with information that can be ported, and they are
 * notified about next steps.
 */
class ShortFormCompletedSubscriber implements EventSubscriberInterface
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public static function getSubscribedEvents()
    {
        return [
            AppEvents::CANDIDATE_SHORT_FORM_COMPLETED => [
                ['populateProfile', 0]
            ]
        ];
    }

    public function populateProfile(CandidateProfileEvent $e)
    {
        $profile = $e->getProfile();
        $shortForm = $profile->getShortForm();

        //populate foreign countries
        if ($countries = $shortForm->getCountries()) {
            foreach ($countries as $countryCode) {
                $newCountry = new Candidate\CountryExperience();
                $newCountry->setCode($countryCode);
                $profile->getCountries()->add($newCountry);
            }
        }
        
        //populate foreign languages
        if ($langs = $shortForm->getForeignLanguages()) {
            foreach ($langs as $langCode) {
                $lang = new Candidate\Language();
                $lang->setCode($langCode);
                $lang->setNativeLikeFluency(false);
                $profile->getLanguages()->add($lang);
            }
        }

        //save changes
        $this->container->get('doctrine_mongodb')->getManager()->flush();
    }
}
