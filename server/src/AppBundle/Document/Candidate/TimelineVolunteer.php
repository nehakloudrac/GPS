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
 * A volunteer experience.
 *
 * @MongoDB\EmbeddedDocument
 */
class TimelineVolunteer extends AbstractTimelineEvent
{
    use AutoGetterSetterTrait, ArrayFactoryTrait, ObjectArrayHelperTrait, PropertyExistanceTrait;
    
    const TYPE = "volunteer";
    
    /**
     * Status of candidate while volunteering
     *
     * @MongoDB\String
     * @Assert\Choice(choices={"part_time","full_time"})
     * @Serializer\Type("string")
     */
    protected $status;

    /**
     * If the position was part of servie in a larger organization such as PeaceCorps or AmeriCorps
     *
     * @MongoDB\EmbedOne(targetDocument="InstitutionReference")
     * @Assert\Valid
     * @Serializer\Type("GPS\AppBundle\Document\Candidate\InstitutionReference")
     */
    protected $sponsoringInstitution;

    public function __construct()
    {
        parent::__construct();

        // $this->sponsoringInstitution = new InstitutionReference();
    }

    public function isComplete()
    {
        if (
            !$this->getDuration() || !$this->getDuration()->allPropertiesExist(['start']) ||
            !$this->getInstitution() || !$this->getInstitution()->allPropertiesExist(['name', 'industries','type']) ||
            !$this->getInstitution()->getAddress()
        ) {
            return false;
        }
        
        if (!$this->allPropertiesExist(['description', 'status'])) {
            return false;
        }
        
        return true;
    }
}
