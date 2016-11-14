<?php

namespace GPS\AppBundle\Document\Candidate;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;
use GPS\AppBundle\Model\ObjectArrayHelperTrait;
use GPS\AppBundle\Model\PropertyExistanceTrait;

/**
 * A Study Abroad experience.
 *
 * @MongoDB\EmbeddedDocument
 */
class TimelineStudyAbroad extends AbstractTimelineEvent
{
    use AutoGetterSetterTrait, ArrayFactoryTrait, ObjectArrayHelperTrait, PropertyExistanceTrait;
    
    const TYPE = 'study_abroad';
    
    /**
     * 	Name of program
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=200)
     * @Serializer\Type("string")
     */
    protected $programName;

    /**
     * undocumented variable
     *
     * @MongoDB\EmbedOne(targetDocument="InstitutionReference")
     * @Assert\Valid
     * @Serializer\Type("GPS\AppBundle\Document\Candidate\InstitutionReference")
     */
    protected $hostingInstitution;

    /**
     * undocumented variable
     *
     * @MongoDB\Int
     * @Assert\Type("integer")
     * @Assert\Range(min=0, max=168)
     * @Serializer\Type("integer")
     */
    protected $weeklyActivityHours;

    /**
     * undocumented variable
     *
     * @MongoDB\Int
     * @Assert\Type("integer")
     * @Assert\Range(min=0, max=100)
     * @Serializer\Type("integer")
     */
    protected $classTimePercentLocalLang;

    public function __construct()
    {
        parent::__construct();

        // $this->hostingInstitution = new InstitutionReference();
    }

    public function isComplete()
    {
        if (
            !$this->getDuration() || !$this->getDuration()->allPropertiesExist(['start']) ||
            !$this->getInstitution() || !$this->getInstitution()->allPropertiesExist(['name'])
        ) {
            return false;
        }
        
        if (!$this->allPropertiesExist(['programName'])) {
            return false;
        }
        
        if (!$this->getCountryRefs() || count($this->getCountryRefs()) < 1) {
            return false;
        }
        
        return true;
    }
}
