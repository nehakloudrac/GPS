<?php

namespace GPS\AppBundle\Document\Candidate;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;
use Doctrine\Common\Collections\ArrayCollection;
use GPS\AppBundle\Model\PropertyExistanceTrait;

/**
 * Academic organization or honor society
 * 
 * @MongoDB\EmbeddedDocument
 * @MongoDB\HasLifecycleCallbacks
 */
class AcademicOrgAffiliation
{
    use AutoGetterSetterTrait, ArrayFactoryTrait, PropertyExistanceTrait;
    
    /**
     * Before initial persist, create a hash for later reference.
     *
     * @MongoDB\PrePersist
     * @MongoDB\PreUpdate
     */
    public function onPrePersist()
    {
        if (!$this->hash) {
            $this->hash = uniqid();
        }
    }

    /**
     * Unique hash for array disambiguation.
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Serializer\ReadOnly
     * @Serializer\Type("string")
     */
    private $hash;
    
    /**
     * undocumented variable
     *
     * @MongoDB\EmbedOne(targetDocument="\GPS\AppBundle\Document\DateRange")
     * @Assert\Valid
     * @Serializer\Type("GPS\AppBundle\Document\DateRange")
     */
    protected $duration;
    
    /**
     * undocumented variable
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=200)
     * @Serializer\Type("string")
     */
    protected $name;
    
    public function isComplete()
    {
        return ($this->getName() && $this->getDuration() && $this->getDuration()->getStart());
    }
    
}
