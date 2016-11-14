<?php

namespace GPS\AppBundle\Document\Candidate;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;
use GPS\AppBundle\Model\ObjectArrayHelperTrait;
use Doctrine\Common\Collections\ArrayCollection;
use GPS\AppBundle\Model\PropertyExistanceTrait;

/**
 * A Job experience.
 *
 * @MongoDB\EmbeddedDocument
 */
class TimelineJob extends AbstractTimelineEvent
{
    use AutoGetterSetterTrait, ArrayFactoryTrait, PropertyExistanceTrait;
    
    const TYPE = 'job';

    /**
     * Title of position
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=200)
     * @Serializer\Type("string")
     */
    protected $title;

    /**
     * Department or area, if applicable
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=200)
     * @Serializer\Type("string")
     */
    protected $department;

    /**
     * Level of position
     *
     * TODO: strictly validate: "president_ceo","owner_founder","principal","cxo","vp","director","manager","advanced","entry","intern"
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=50)
     * @Serializer\Type("string")
     */
    protected $positionLevel;

    /**
     * Salary, if applicable
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=0)
     * @Serializer\Type("integer")
     */
    protected $salary;

    /**
     * Hourly rate, if applicable
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=0)
     * @Serializer\Type("integer")
     */
    protected $hourlyRate;

    /**
     * Type of position
     *
     * @MongoDB\String
     * @Assert\Choice(choices={"full_time","part_time","internship","project"})
     * @Serializer\Type("string")
     */
    protected $status;
    
    public function isComplete()
    {
        if (
            !$this->getDuration() || !$this->getDuration()->allPropertiesExist(['start']) ||
            !$this->getInstitution() || !$this->getInstitution()->allPropertiesExist(['name','industries','type']) ||
            !$this->getInstitution()->getAddress()
        ) {
            return false;
        }
        
        if (!$this->allPropertiesExist(['description', 'status','title','positionLevel'])) {
            return false;
        }
        
        return true;
    }
}
