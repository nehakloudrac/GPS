<?php

namespace GPS\AppBundle\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use GPS\AppBundle\Document\Candidate;
use GPS\AppBundle\Event\AppEvents;
use GPS\AppBundle\Event\UserEvent;
use GPS\AppBundle\Event\CandidateProfileEvent;
use JMS\Serializer\SerializationContext;

/**
 * Provides all the user api related actions under /candidate-profiles
 *
 * @package GPS
 * @author Evan Villemez
 *
 * @Route("/api/candidate-profiles")
 */
class CandidateProfilesController extends AbstractApiController
{
    /**
     * Get multiple profiles, with some ability to filter based on certain criteria.
     *
     * @Route("/", name="api-get-candidate-profiles")
     * @Method("GET")
     */
    public function getProfilesAction(Request $req)
    {
        //TODO: this method might get large... lots of potential filters
        //as well as authorization checks

        //in the case of institutions, may be most efficient to calculate & cache ids for profiles allowed to access
        //via paid searches, then enforce that id filter


        throw $this->createHttpException(501);
    }

    /**
     * Get a candidate profile.
     *
     * @Route("/{id}", name="api-get-candidate-profile")
     * @Method("GET")
     */
    public function getProfileAction(Request $req, $id)
    {
        //TODO: others can request profiles as well - but depends on lots of things

        $profile = $this->getRequesterProfile($id);
        $computeCompleteness = $req->query->get('completeness', false);
        $computeExperience = $req->query->get('experience', false);
        
        if ($computeCompleteness !== false) {
            $profile->computeCompleteness();
            $profile->setSerializeCompleteness(true);
        }
        
        if ($computeExperience !== false) {
            $profile->computeProfessionalExperience();
        }
        
        return $this->createServiceResponse(['profile' => $profile], 200);
    }

    /**
     * Update information in candidate profile.
     *
     * @Route("/{id}", name="api-put-candidate-profile")
     * @Method("PUT")
     */
    public function putAction(Request $req, $id)
    {
        $profile = $this->getRequesterProfile($id);

        $this->decodeRequest($req, 'GPS\AppBundle\Document\Candidate\Profile', $this->createDeserializationContext()
            ->setSerializeNested(true)
            ->setGroups(['Default'])
            ->setTarget($profile)
        );

        $this->validate($profile);

        $this->getDocumentManager()->flush();

        return $this->createServiceResponse(['profile' => $profile], 200);
    }
    
    /** 
     * Post a file and attempt to import profile data from file.
     *
     * @Route("/{id}/import-profile", name="api-post-candidate-profile-import-profile")
     * @Method("POST")
     */
    public function postProfileImportAction(Request $req, $id)
    {
        $profile = $this->getRequesterProfile($id);
        
        $parser = $this->get('gps.linkedin_parser');
        
        $uploaded = $req->files->get('file');
        if (!$uploaded->isValid()) {
            throw $this->createHttpException(422, "There was an error while handling the uploaded file.  ERR: ${$file->getError()}");
        }

        $parser->parseFileIntoProfile($uploaded->getRealPath(), $profile);
        
        $this->validate($profile);
        $this->getDocumentManager()->flush();

        return $this->createServiceResponse([
            'profile' => $profile,
            'user' => $profile->getUser()
        ], 200);
    }


    /**
     * This is a separate method because certain events need to be dispatched
     * under certain conditions.
     *
     * @Route("/{id}/short-form", name="api-put-candidate-profile-short-form")
     * @Method("PUT")
     */
    public function putShortFormAction(Request $req, $id)
    {
        $profile = $this->getRequesterProfile($id);

        $shortForm = $profile->getShortForm();

        if (!$shortForm) {
            $shortForm = new Candidate\ShortForm();
            $profile->setShortForm($shortForm);
        }

        $previouslyCompleted = $shortForm->getCompleted();

        $this->decodeRequest($req, 'GPS\AppBundle\Document\Candidate\ShortForm', $this->createDeserializationContext()
            ->setSerializeNested(true)
            ->setGroups(['Default'])
            ->setTarget($shortForm)
        );

        $shortForm->setDateModified(new \DateTime());
        $this->validate($shortForm);

        $this->getDocumentManager()->flush();

        #dispatch app event if newly completed - this can trigger several things
        if (!$previouslyCompleted && true === $shortForm->getCompleted()) {
            $this->get('event_dispatcher')->dispatch(AppEvents::CANDIDATE_SHORT_FORM_COMPLETED, new CandidateProfileEvent($profile));
        }

        return $this->createServiceResponse([
            'profile' => $profile,
            'form' => $shortForm
        ], 200);
    }

    /**
     * Create a timeline event
     *
     * @Route("/{id}/timeline", name="api-post-candidate-profile-timeline-event")
     * @Method("POST")
     */
    public function postTimelineEvent(Request $req, $id)
    {
        $type = $req->query->get('type', false);
        if (!$type || empty($type)) {
            throw $this->createHttpException(400, 'The [type] query param is required when creating a new event.');
        }

        $profile = $this->getRequesterProfile($id);

        switch ($type) {
            case 'job':                     $event = new Candidate\TimelineJob(); break;
            case 'research':                $event = new Candidate\TimelineResearch(); break;
            case 'volunteer':               $event = new Candidate\TimelineVolunteer(); break;
            case 'university':              $event = new Candidate\TimelineUniversity(); break;
            case 'study_abroad':            $event = new Candidate\TimelineStudyAbroad(); break;
            case 'language_acquisition':    $event = new Candidate\TimelineLanguageAcquisition(); break;
            case 'military':                $event = new Candidate\TimelineMilitary(); break;
            default: throw $this->createHttpException(400, "Type [$type] is not a valid timeline event type.");
        }

        $this->decodeRequest($req, 'GPS\AppBundle\Document\Candidate\AbstractTimelineEvent', $this->createDeserializationContext()
            ->setSerializeNested(true)
            ->setGroups(['Default'])
            ->setTarget($event)
        );

        $this->validate($event);

        $profile->getTimeline()->add($event);

        $this->getDocumentManager()->flush();

        return $this->createServiceResponse(['event' => $event], 201);
    }

    /**
     * Modify a timeline event
     *
     * @Route("/{id}/timeline/{hash}", name="api-put-candidate-profile-timeline-event")
     * @Method("PUT")
     */
    public function putTimelineEvent(Request $req, $id, $hash)
    {
        $profile = $this->getRequesterProfile($id);

        $event = $profile->getObjectInArrayByField('timeline', 'hash', $hash);
        if (!$event) {
            throw $this->createHttpException(404, "No event by that reference.");
        }
        
        $this->decodeRequest($req, 'GPS\AppBundle\Document\Candidate\AbstractTimelineEvent', $this->createDeserializationContext()
            ->setSerializeNested(true)
            ->setGroups(['Default'])
            ->setTarget($event)
        );
        
        $this->validate($event);

        $this->getDocumentManager()->flush();

        return $this->createServiceResponse(['event' => $event], 200);
    }

    /**
     * Delete a timeline event
     *
     * @Route("/{id}/timeline/{hash}", name="api-delete-candidate-profile-timeline-event")
     * @Method("DELETE")
     */
    public function deleteTimelineEvent(Request $req, $id, $hash)
    {
        $profile = $this->getRequesterProfile($id);

        $event = $profile->getObjectInArrayByField('timeline', 'hash', $hash);
        if (!$event) {
            throw $this->createHttpException(404, "No event by that reference.");
        }

        $profile->getTimeline()->removeElement($event);

        $this->getDocumentManager()->flush();

        return $this->createServiceResponse([], 200);
    }

    /**
     * Create a new country experience
     *
     * @Route("/{id}/countries", name="api-post-candidate-profile-country")
     * @Method("POST")
     */
    public function postCountryAction(Request $req, $id)
    {
        $profile = $this->getRequesterProfile($id);

        $experience = new Candidate\CountryExperience();
        $this->decodeRequest($req, 'GPS\AppBundle\Document\Candidate\CountryExperience', $this->createDeserializationContext()
            ->setSerializeNested(true)
            ->setGroups(['Default'])
            ->setTarget($experience)
        );

        $this->validate($experience);

        $profile->getCountries()->add($experience);

        $this->getDocumentManager()->flush();

        return $this->createServiceResponse(['country' => $experience], 201);
    }

    /**
     * Update specific country experience
     *
     * @Route("/{id}/countries/{hash}", name="api-put-candidate-profile-country")
     * @Method("PUT")
     */
    public function putCountryAction(Request $req, $id, $hash)
    {
        $profile = $this->getRequesterProfile($id);

        $experience = $profile->getObjectInArrayByField('countries', 'hash', $hash);

        if (!$experience) {
            throw $this->createHttpException(404, "No country by that reference.");
        }

        $this->decodeRequest($req, 'GPS\AppBundle\Document\Candidate\CountryExperience', $this->createDeserializationContext()
            ->setSerializeNested(true)
            ->setGroups(['Default'])
            ->setTarget($experience)
        );

        $this->validate($experience);

        $this->getDocumentManager()->flush();

        return $this->createServiceResponse(['country' => $experience], 200);
    }

    /**
     * Delete specific country experience
     *
     * @Route("/{id}/countries/{hash}", name="api-delete-candidate-profile-country")
     * @Method("DELETE")
     */
    public function deleteCountryAction(Request $req, $id, $hash)
    {
        $profile = $this->getRequesterProfile($id);

        $experience = $profile->getObjectInArrayByField('countries', 'hash', $hash);

        if (!$experience) {
            throw $this->createHttpException(404, "No country by that reference.");
        }

        $profile->getCountries()->removeElement($experience);

        $this->getDocumentManager()->flush();

        return $this->createServiceResponse([], 200);
    }

    /**
     * Create a new language experience.
     *
     * @Route("/{id}/languages", name="api-post-candidate-profile-language")
     * @Method("POST")
     */
    public function postLanguageAction(Request $req, $id)
    {
        $profile = $this->getRequesterProfile($id);

        $language = new Candidate\Language();
        $this->decodeRequest($req, 'GPS\AppBundle\Document\Candidate\Language', $this->createDeserializationContext()
            ->setSerializeNested(true)
            ->setGroups(['Default'])
            ->setTarget($language)
        );

        $this->validate($language);

        $profile->getLanguages()->add($language);

        $this->getDocumentManager()->flush();

        return $this->createServiceResponse(['language' => $language], 201);
    }

    /**
     * Update specific language experience.
     *
     * @Route("/{id}/languages/{hash}", name="api-put-candidate-profile-language")
     * @Method("PUT")
     */
    public function putLanguageAction(Request $req, $id, $hash)
    {
        $profile = $this->getRequesterProfile($id);

        $language = $profile->getObjectInArrayByField('languages', 'hash', $hash);

        if (!$language) {
            throw $this->createHttpException(404, "No language by that reference.");
        }

        $this->decodeRequest($req, 'GPS\AppBundle\Document\Candidate\Language', $this->createDeserializationContext()
            ->setSerializeNested(true)
            ->setGroups(['Default'])
            ->setTarget($language)
        );

        $this->validate($language);

        $this->getDocumentManager()->flush();

        return $this->createServiceResponse(['language' => $language], 200);
    }

    /**
     * Delete specific language experience
     *
     * @Route("/{id}/languages/{hash}", name="api-delete-candidate-profile-language")
     * @Method("DELETE")
     */
    public function deleteLanguageAction(Request $req, $id, $hash)
    {
        $profile = $this->getRequesterProfile($id);

        $language = $profile->getObjectInArrayByField('languages', 'hash', $hash);

        if (!$language) {
            throw $this->createHttpException(404, "No language by that reference.");
        }

        $profile->getLanguages()->removeElement($language);

        $this->getDocumentManager()->flush();

        return $this->createServiceResponse([], 200);
    }

    /**
     * Create a language certification.
     *
     * @Route("/{id}/languages/{hash}/certifications", name="api-post-candidate-profile-language-certification")
     * @Method("POST")
     */
    public function postLanguageCertificationAction(Request $req, $id, $hash)
    {
        $profile = $this->getRequesterProfile($id);

        $language = $profile->getObjectInArrayByField('languages', 'hash', $hash);

        if (!$language) {
            throw $this->createHttpException(404, "No language by that reference.");
        }

        $certification = $this->decodeRequest(
            $req,
            'GPS\AppBundle\Document\Candidate\AbstractOfficialLanguageCertification',
            $this->createDeserializationContext()
                ->setSerializeNested(true)
                ->setGroups(['Default'])
        );

        $this->validate($certification);

        $language->getOfficialCertifications()->add($certification);

        $this->getDocumentManager()->flush();

        return $this->createServiceResponse(['certification' => $certification], 201);
    }

    /**
     * Modify an existing language certification.
     *
     * @Route("/{id}/languages/{hash}/certifications/{certHash}", name="api-put-candidate-profile-language-certification")
     * @Method("PUT")
     */
    public function putLanguageCertificationAction(Request $req, $id, $hash, $certHash)
    {
        $profile = $this->getRequesterProfile($id);

        $language = $profile->getObjectInArrayByField('languages', 'hash', $hash);

        if (!$language) {
            throw $this->createHttpException(404, "No language by that reference.");
        }

        $oldCert = $language->getObjectInArrayByField('officialCertifications', 'hash', $certHash);

        if (!$oldCert) {
            throw $this->createHttpException(404, "No langauge certification by that reference.");
        }

        $modifiedCert = $this->decodeRequest(
            $req,
            'GPS\AppBundle\Document\Candidate\AbstractOfficialLanguageCertification',
            $this->createDeserializationContext()
                ->setSerializeNested(true)
                ->setGroups(['Default'])
        );

        //serialize into the existing cert.... note that scale must be sent
        //and cannot change, since that's the discriminator field
        $modifiedCert = $this->decodeRequest(
            $req,
            'GPS\AppBundle\Document\Candidate\AbstractOfficialLanguageCertification',
            $this->createDeserializationContext()
                ->setSerializeNested(true)
                ->setGroups(['Default'])
                ->setTarget($oldCert)
        );

        $this->validate($modifiedCert);

        $this->getDocumentManager()->flush();

        return $this->createServiceResponse(['certification' => $modifiedCert], 200);
    }

    /**
     * Delete an language certification.
     *
     * @Route("/{id}/languages/{hash}/certifications/{certHash}", name="api-delete-candidate-profile-language-certification")
     * @Method("DELETE")
     */
    public function deleteLanguageCertificationAction(Request $req, $id, $hash, $certHash)
    {
        $profile = $this->getRequesterProfile($id);

        $language = $profile->getObjectInArrayByField('languages', 'hash', $hash);

        if (!$language) {
            throw $this->createHttpException(404, "No language by that reference.");
        }

        $cert = $language->getObjectInArrayByField('officialCertifications', 'hash', $certHash);

        if (!$cert) {
            throw $this->createHttpException(404, "No langauge certification by that reference.");
        }

        $language->getOfficialCertifications()->removeElement($cert);

        $this->getDocumentManager()->flush();

        return $this->createServiceResponse([], 200);
    }

    /**
     * Create an organization affiliation.
     *
     * @Route("/{id}/organizations", name="api-post-candidate-profile-organization")
     * @Method("POST")
     */
    public function postOrganizationAction(Request $req, $id)
    {
        $profile = $this->getRequesterProfile($id);

        $org = new Candidate\OrganizationAffiliation();
        $this->decodeRequest($req, 'GPS\AppBundle\Document\Candidate\OrganizationAffiliation', $this->createDeserializationContext()
            ->setSerializeNested(true)
            ->setGroups(['Default'])
            ->setTarget($org)
        );

        $profile->getOrganizations()->add($org);

        $this->validate($org);

        $this->getDocumentManager()->flush();

        return $this->createServiceResponse(['organization' => $org], 201);
    }

    /**
     * Modify an organization affiliation.
     *
     * @Route("/{id}/organizations/{hash}", name="api-put-candidate-profile-organization")
     * @Method("PUT")
     */
    public function putOrganizationAction(Request $req, $id, $hash)
    {
        $profile = $this->getRequesterProfile($id);

        $org = $profile->getObjectInArrayByField('organizations', 'hash', $hash);

        if (!$org) {
            throw $this->createHttpException(404, "No organization by that reference.");
        }

        $this->decodeRequest($req, 'GPS\AppBundle\Document\Candidate\OrganizationAffiliation', $this->createDeserializationContext()
            ->setSerializeNested(true)
            ->setGroups(['Default'])
            ->setTarget($org)
        );

        $this->validate($org);

        $this->getDocumentManager()->flush();

        return $this->createServiceResponse(['organization' => $org], 200);
    }

    /**
     * Remove an organization affiliation.
     *
     * @Route("/{id}/organizations/{hash}", name="api-delete-candidate-profile-organization")
     * @Method("DELETE")
     */
    public function deleteOrganizationAction(Request $req, $id, $hash)
    {
        $profile = $this->getRequesterProfile($id);

        $org = $profile->getObjectInArrayByField('organizations', 'hash', $hash);

        if (!$org) {
            throw $this->createHttpException(404, "No organization by that reference.");
        }

        $profile->getOrganizations()->removeElement($org);

        $this->getDocumentManager()->flush();

        return $this->createServiceResponse([], 200);
    }

    /**
     * Create a project availability window.
     *
     * @Route("/{id}/project-availability", name="api-post-candidate-profile-project-availability")
     * @Method("POST")
     */
    public function postProjectAvailability(Request $req, $id)
    {
        $profile = $this->getRequesterProfile($id);

        $availability = new Candidate\ProjectAvailability();
        $this->decodeRequest($req, 'GPS\AppBundle\Document\Candidate\ProjectAvailability', $this->createDeserializationContext()
            ->setSerializeNested(true)
            ->setGroups(['Default'])
            ->setTarget($availability)
        );

        $this->validate($availability);

        $profile->getIdealJob()->getAvailability()->add($availability);

        $this->getDocumentManager()->flush();

        return $this->createServiceResponse(['availability' => $availability], 201);
    }

    /**
     * Modify a project availability window.
     *
     * @Route("/{id}/project-availability/{hash}", name="api-put-candidate-profile-project-availability")
     * @Method("PUT")
     */
    public function putProjectAvailability(Request $req, $id, $hash)
    {
        $profile = $this->getRequesterProfile($id);

        $availability = $profile->getIdealJob()->getObjectInArrayByField('availability', 'hash', $hash);

        if (!$availability) {
            throw $this->createHttpException(404, "No project availability by that reference.");
        }

        $this->decodeRequest($req, 'GPS\AppBundle\Document\Candidate\ProjectAvailability', $this->createDeserializationContext()
            ->setSerializeNested(true)
            ->setGroups(['Default'])
            ->setTarget($availability)
        );

        $this->validate($availability);

        $this->getDocumentManager()->flush();

        return $this->createServiceResponse(['availability' => $availability], 200);
    }

    /**
     * Remove a project availability window.
     *
     * @Route("/{id}/project-availability/{hash}", name="api-delete-candidate-profile-project-availability")
     * @Method("DELETE")
     */
    public function deleteProjectAvailability(Request $req, $id, $hash)
    {
        $profile = $this->getRequesterProfile($id);

        $availability = $profile->getIdealJob()->getObjectInArrayByField('availability', 'hash', $hash);

        if (!$availability) {
            throw $this->createHttpException(404, "No project availability by that reference.");
        }

        $profile->getIdealJob()->getAvailability()->removeElement($availability);

        $this->getDocumentManager()->flush();

        return $this->createServiceResponse([], 200);
    }

    /**
     * Create an award for the candidate.
     *
     * @Route("/{id}/awards", name="api-post-candidate-profile-award")
     * @Method("POST")
     */
    public function postAwardAction(Request $req, $id)
    {
        $profile = $this->getRequesterProfile($id);

        $award = new Candidate\Award();
        $this->decodeRequest($req, 'GPS\AppBundle\Document\Candidate\Award', $this->createDeserializationContext()
            ->setSerializeNested(true)
            ->setGroups(['Default'])
            ->setTarget($award)
        );

        $this->validate($award);

        $profile->getAwards()->add($award);

        $this->getDocumentManager()->flush();

        return $this->createServiceResponse(['award' => $award], 201);
    }

    /**
     * Modify an award for the candidate.
     *
     * @Route("/{id}/awards/{hash}", name="api-put-candidate-profile-award")
     * @Method("PUT")
     */
    public function putAwardAction(Request $req, $id, $hash)
    {
        $profile = $this->getRequesterProfile($id);

        $award = $profile->getObjectInArrayByField('awards', 'hash', $hash);

        if (!$award) {
            throw $this->createHttpException(404, "No award by that reference.");
        }

        $this->decodeRequest($req, 'GPS\AppBundle\Document\Candidate\Award', $this->createDeserializationContext()
            ->setSerializeNested(true)
            ->setGroups(['Default'])
            ->setTarget($award)
        );

        $this->validate($award);

        $this->getDocumentManager()->flush();

        return $this->createServiceResponse(['award' => $award], 200);
    }

    /**
     * Delete an award for the candidate.
     *
     * @Route("/{id}/awards/{hash}", name="api-delete-candidate-profile-award")
     * @Method("DELETE")
     */
    public function deleteAwardAction(Request $req, $id, $hash)
    {
        $profile = $this->getRequesterProfile($id);

        $award = $profile->getObjectInArrayByField('awards', 'hash', $hash);

        if (!$award) {
            throw $this->createHttpException(404, "No award by that reference.");
        }

        $profile->getAwards()->removeElement($award);

        $this->getDocumentManager()->flush();

        return $this->createServiceResponse([], 200);
    }
    
    /**
     * Create a certification
     * 
     * @Route("/{id}/certifications", name="api-post-candidate-profile-certification")
     * @Method("POST")
     */
    public function postCertificationAction(Request $req, $id)
    {
        $profile = $this->getRequesterProfile($id);

        $cert = new Candidate\Certification();
        $this->decodeRequest($req, 'GPS\AppBundle\Document\Candidate\Certification', $this->createDeserializationContext()
            ->setSerializeNested(true)
            ->setGroups(['Default'])
            ->setTarget($cert)
        );

        $this->validate($cert);

        $profile->getCertifications()->add($cert);

        $this->getDocumentManager()->flush();

        return $this->createServiceResponse(['certification' => $cert], 201);
    }
    
    /**
     * Modify a certification
     * 
     * @Route("/{id}/certifications/{hash}", name="api-put-candidate-profile-certification")
     * @Method("PUT")
     */
    public function putCertificatinoAction(Request $req, $id, $hash)
    {
        $profile = $this->getRequesterProfile($id);

        $cert = $profile->getObjectInArrayByField('certifications', 'hash', $hash);

        if (!$cert) {
            throw $this->createHttpException(404, "No certification by that id.");
        }
        
        $this->decodeRequest($req, 'GPS\AppBundle\Document\Candidate\Certification', $this->createDeserializationContext()
            ->setSerializeNested(true)
            ->setGroups(['Default'])
            ->setTarget($cert)
        );

        $this->validate($cert);

        $this->getDocumentManager()->flush();

        return $this->createServiceResponse(['certification' => $cert], 200);
    }
    
    /**
     * Delete a certification
     * 
     * @Route("/{id}/certifications/{hash}", name="api-delete-candidate-profile-certification")
     * @Method("DELETE")
     */
    public function deleteCertificationAction(Request $req, $id, $hash)
    {
        $profile = $this->getRequesterProfile($id);

        $cert = $profile->getObjectInArrayByField('certifications', 'hash', $hash);

        if (!$cert) {
            throw $this->createHttpException(404, "No certification by that id.");
        }

        $profile->getCertifications()->removeElement($cert);

        $this->getDocumentManager()->flush();

        return $this->createServiceResponse([], 200);
    }

    /**
     * Create an academic organization for the candidate.
     *
     * @Route("/{id}/academic-organizations", name="api-post-candidate-profile-academic-organization")
     * @Method("POST")
     */
    public function postAcademicOrgAction(Request $req, $id)
    {
        $profile = $this->getRequesterProfile($id);

        $org = new Candidate\AcademicOrgAffiliation();
        $this->decodeRequest($req, 'GPS\AppBundle\Document\Candidate\AcademicOrgAffiliation', $this->createDeserializationContext()
            ->setSerializeNested(true)
            ->setGroups(['Default'])
            ->setTarget($org)
        );

        $this->validate($org);

        $profile->getAcademicOrganizations()->add($org);

        $this->getDocumentManager()->flush();

        return $this->createServiceResponse(['organization' => $org], 201);
    }

    /**
     * Modify an academic organization for the candidate.
     *
     * @Route("/{id}/academic-organizations/{hash}", name="api-put-candidate-profile-academic-organization")
     * @Method("PUT")
     */
    public function putAcademicOrgAction(Request $req, $id, $hash)
    {
        $profile = $this->getRequesterProfile($id);

        $org = $profile->getObjectInArrayByField('academicOrganizations', 'hash', $hash);

        if (!$org) {
            throw $this->createHttpException(404, "No academic organization by that reference.");
        }

        $this->decodeRequest($req, 'GPS\AppBundle\Document\Candidate\AcademicOrgAffiliation', $this->createDeserializationContext()
            ->setSerializeNested(true)
            ->setGroups(['Default'])
            ->setTarget($org)
        );

        $this->validate($org);

        $this->getDocumentManager()->flush();

        return $this->createServiceResponse(['organization' => $org], 200);
    }

    /**
     * Delete an academic organization for the candidate.
     *
     * @Route("/{id}/academic-organizations/{hash}", name="api-delete-candidate-profile-academic-organization")
     * @Method("DELETE")
     */
    public function deleteAcademicOrgAction(Request $req, $id, $hash)
    {
        $profile = $this->getRequesterProfile($id);

        $org = $profile->getObjectInArrayByField('academicOrganizations', 'hash', $hash);

        if (!$org) {
            throw $this->createHttpException(404, "No academic organization by that reference.");
        }

        $profile->getAcademicOrganizations()->removeElement($org);

        $this->getDocumentManager()->flush();

        return $this->createServiceResponse([], 200);
    }

    /**
     * Convenience method to get profile, ensuring requester is owner.
     *
     * @param string $id
     * @return Candidate\Profile
     * @author Evan Villemez
     */
    private function getRequesterProfile($id)
    {
        $profile = $this->getRepository('AppBundle:Candidate\Profile')->find($id);

        if (!$profile) {
            throw $this->createHttpException(404, "No candidate profile by that id.");
        }

        $requester = $this->getUser();
        $owner = $profile->getUser();

        if ($requester->getId() !== $owner->getId()) {
            throw $this->createHttpException(403, "Users may only access their own profiles.");
        }

        return $profile;
    }

	/**
	 * Convenience override method to force some serialization options.
	 */
	protected function createServiceResponse($data, $code= 200, $headers = [], $template = null)
	{
		if ($template instanceof SerializationContext) {
			$template->setSerializeNull(true);
		}

		return parent::createServiceResponse($data, $code, $headers, $template);
	}
}
