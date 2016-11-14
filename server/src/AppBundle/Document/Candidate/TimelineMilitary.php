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
 * A Military experience.
 *
 * @MongoDB\EmbeddedDocument
 */
class TimelineMilitary extends AbstractTimelineEvent
{
    use AutoGetterSetterTrait, ArrayFactoryTrait, ObjectArrayHelperTrait, PropertyExistanceTrait;

    const TYPE = 'military';

    /**
     * Branch of military
     *
     * @MongoDB\String
     * @Assert\Choice(choices={"us_navy","us_army","us_marines","us_air_force","us_coast_guard"})
     * @Serializer\Type("string")
     */
    protected $branch;
    
    /**
     * Name of assigned unit
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Serializer\Type("string")
     */
    protected $unit;
    
    /**
    * Name of operation
    *
    * @MongoDB\String
    * @Assert\Type("string")
    * @Serializer\Type("string")
    */
    protected $operation;

    /**
     * Allowing multiple - in the UI this can just be comma-delimited strings
     *
     * @MongoDB\Collection
     * @Assert\All({
     *  @Assert\NotBlank,
     *  @Assert\Type("string"),
     *  @Assert\Length(max=100)
     * })
     * @Serializer\Type("array<string>")
     */
    protected $occupationalSpecialties;

    /**
     * @MongoDB\String
     * @Assert\Choice(choices={"USAFRICOM","USCENTCOM","USEUCOM","USNORTHCOM","USPACOM","USSOUTHCOM"})
     * @Serializer\Type("string")
     */
    protected $geographicSpecialty;

    /**
     * Type of rank
     *
     * @MongoDB\String
     * @Assert\Choice(choices={"enlisted","officer"})
     * @Serializer\Type("string")
     */
    protected $rankType;

    /**
     * This is an arbitraty numerical value used in a lookup
     * table for the actual rank.  This should be used for determining
     * the name of the rank only - not sorting.
     *
     * @MongoDB\Int
     * @Assert\Type("integer")
     * @Assert\Range(min=1)
     * @Serializer\Type("integer")
     */
    protected $rankValue;

    /**
     * 1-11... These correspond to the enlisted/officer E/O1-1
     * rankings.  It has to be tracked separately because multiple
     * ranks have the same level.  This should be used for sorting,
     * not determining the name of the rank.
     *
     * @MongoDB\Int
     * @Assert\Type("integer")
     * @Assert\Range(min=1, max=11)
     * @Serializer\Type("integer")
     */
    protected $rankLevel;

    public function isComplete()
    {
        if (
            !$this->getDuration() || !$this->getDuration()->allPropertiesExist(['start']) ||
            !$this->allPropertiesExist(['branch','rankType','rankLevel','rankValue'])
        ) {
            return false;
        }
        
        return true;
    }
    
}
