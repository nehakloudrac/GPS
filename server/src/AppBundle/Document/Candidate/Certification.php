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
 * Official certification recieved by someone
 *
 * @MongoDB\EmbeddedDocument
 * @MongoDB\HasLifecycleCallbacks
 */
class Certification
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
     * Name of cert
     * 
     * @MongoDB\String
     * @Assert\Type("string")
     * @Serializer\Type("string")
     */
    protected $name;
    
    /**
     * Name of organization that issued the cert
     * 
     * @MongoDB\String
     * @Assert\Type("string")
     * @Serializer\Type("string")
     */
    protected $organization;

    /**
     * Optional id of cert as determined by issuing organization
     * 
     * @MongoDB\String
     * @Assert\Type("string")
     * @Serializer\Type("string")
     */
    protected $certId;
    
    /**
     * Date range
     *
     * @MongoDB\EmbedOne(targetDocument="\GPS\AppBundle\Document\DateRange")
     * @Assert\Valid
     * @Serializer\Type("GPS\AppBundle\Document\DateRange")
     */
    protected $duration;
    
}