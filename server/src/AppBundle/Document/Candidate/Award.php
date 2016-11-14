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
 * Academic award or scholarship received.
 *
 * @MongoDB\EmbeddedDocument
 * @MongoDB\HasLifecycleCallbacks
 */
class Award
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
     * @MongoDB\Date
     * @Assert\DateTime
     * @Serializer\Type("DateTime<'U'>")
     */
    protected $date;

    /**
     * DEPRECATED: for real, actually deprecated: use "name" exclusively
     * 
     * @deprecated
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=200)
     * @Serializer\Type("string")
     */
    protected $type;

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
        return $this->allPropertiesExist(['name','date']);
    }

}
