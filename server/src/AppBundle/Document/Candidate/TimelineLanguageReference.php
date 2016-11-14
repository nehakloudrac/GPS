<?php

namespace GPS\AppBundle\Document\Candidate;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Reference to a specific language
 *
 * @MongoDB\HasLifecycleCallbacks
 * @MongoDB\EmbeddedDocument
 */
class TimelineLanguageReference
{
    use AutoGetterSetterTrait, ArrayFactoryTrait;

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
    protected $hash;

    /**
     * ISO 639-3 code.
     *
     * @MongoDB\String
     * @Assert\NotBlank
     * @Assert\Type("string")
     * @Assert\Length(min=3, max=4)
     * @Serializer\Type("string")
     */
    protected $code;
}
