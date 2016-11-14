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
 * A Research experience.
 *
 * @MongoDB\EmbeddedDocument
 */
class TimelineResearch extends AbstractTimelineEvent
{
    use AutoGetterSetterTrait, ArrayFactoryTrait, ObjectArrayHelperTrait, PropertyExistanceTrait;
    
    const TYPE = 'research';
    
    /**
     * DEPRECATED - this should be removed in a future migration
     * 
     * Name of research program
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=200)
     * @Serializer\Type("string")
     */
    protected $sponsoringProgram;

    /**
     * QUESTION - is this deprecated?  Is the note above just on the wrong field?
     * 
     * If the position was hosted at another institution.
     *
     * @MongoDB\EmbedOne(targetDocument="InstitutionReference")
     * @Assert\Valid
     * @Serializer\Type("GPS\AppBundle\Document\Candidate\InstitutionReference")
     */
    protected $hostingInstitution;

    /**
     * undocumented variable
     *
     * @MongoDB\String
     * @Assert\Choice(choices={"undergrad","postgrad","grad_student","postdoc_fellow","professional"})
     * @Serializer\Type("string")
     */
    protected $level;

    /**
     * How many hours a week
     *
     * @MongoDB\Int
     * @Assert\Type("integer")
     * @Assert\Range(min=1, max=168)
     * @Serializer\Type("integer")
     */
    protected $hoursPerWeek;

    /**
     * TODO: consider validating this more strictly
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=200)
     * @Serializer\Type("string")
     */
    protected $subject;

    public function isComplete()
    {
        if (
            !$this->getDuration() || !$this->getDuration()->allPropertiesExist(['start']) ||
            !$this->getInstitution() || !$this->getInstitution()->allPropertiesExist(['name']) ||
            !$this->getInstitution()->getAddress()
        ) {
            return false;
        }
        
        if (!$this->allPropertiesExist(['description', 'sponsoringProgram','level','subject'])) {
            return false;
        }
        
        return true;
    }
}
