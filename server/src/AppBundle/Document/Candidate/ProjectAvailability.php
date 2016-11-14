<?php

namespace GPS\AppBundle\Document\Candidate;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Availability window for project work.
 *
 * @MongoDB\EmbeddedDocument
 * @MongoDB\HasLifecycleCallbacks
 */
class ProjectAvailability
{
    use AutoGetterSetterTrait, ArrayFactoryTrait;

    public function __construct()
    {
        // $this->duration = new \GPS\AppBundle\Document\DateRange();
    }

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
     * Date range
     *
     * @MongoDB\EmbedOne(targetDocument="\GPS\AppBundle\Document\DateRange")
     * @Assert\Valid
     * @Serializer\Type("GPS\AppBundle\Document\DateRange")
     */
    protected $duration;

    /**
     * Willingness to travel internationally
     *
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $travelInternational;

    /**
     * Willingness to travel domestically
     *
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $travelDomestic;
}
