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
 * A Language Acquisition event
 *
 * @MongoDB\EmbeddedDocument
 */
class TimelineLanguageAcquisition extends AbstractTimelineEvent
{
    use AutoGetterSetterTrait, ArrayFactoryTrait, ObjectArrayHelperTrait, PropertyExistanceTrait;
    
    const TYPE = 'language_acquisition';
    
    /**
     * By what means the language was learned
     *
     * @MongoDB\String
     * @Assert\Choice(choices={"self_study","community","training","school"})
     * @Serializer\Type("string")
     */
    protected $source;

    /**
     * How intensive the learning was by some measure
     *
     * @MongoDB\Int
     * @Assert\Type("integer")
     * @Assert\Range(min=1, max=168)
     * @Serializer\Type("integer")
     */
    protected $hoursPerWeek;

    public function isComplete()
    {
        if (
            !$this->getDuration() || !$this->getDuration()->allPropertiesExist(['start'])
        ) {
            return false;
        }
        
        if (!$this->allPropertiesExist(['source', 'hoursPerWeek'])) {
            return false;
        }
        
        if (!$this->getLanguageRefs() || count($this->getLanguageRefs()) == 0) {
            return false;
        }
        
        return true;
    }
}
