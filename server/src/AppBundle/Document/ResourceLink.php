<?php

namespace GPS\AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;

/**
 * Class for links shared in the resources section
 *
 * @MongoDB\Document(collection="resourceLinks")
 * @MongoDB\HasLifecycleCallbacks
 */
class ResourceLink
{
    use AutoGetterSetterTrait, ArrayFactoryTrait;
    
    /**
     * Unique ID for link.
     *
     * @MongoDB\Id
     * @Serializer\ReadOnly
     * @Serializer\Type("string")
     */
    private $id;

    /**
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Serializer\Type("string")
     */
    protected $title;

    /**
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Serializer\Type("string")
     */
    protected $type;
    
    /**
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Serializer\Type("string")
     */
    protected $mediaType;

    /**
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Serializer\Type("string")
     */
    protected $url;
    
    /**
     *
     * @MongoDB\Collection
     * @Assert\All({
     *  @Assert\NotBlank,
     *  @Assert\Type("string"),
     *  @Assert\Length(max=150)
     * })
     * @Serializer\Type("array<string>")
     */
    protected $tags;
    
    /**
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Serializer\Type("string")
     */
    protected $description;
    
    /**
     *
     * @MongoDB\Date
     * @Assert\DateTime
     * @Serializer\Type("DateTime<'U'>")
     * @Serializer\ReadOnly
     */
    protected $dateCreated;
    
    /**
     *
     * @MongoDB\Date
     * @Assert\DateTime
     * @Serializer\Type("DateTime<'U'>")
     * @Serializer\ReadOnly
     */
    protected $dateModified;
    
    /**
     *
     * @MongoDB\Date
     * @Assert\DateTime
     * @Serializer\Type("DateTime<'U'>")
     * @Serializer\ReadOnly
     */
    protected $datePublished;
    
    /**
     *
     * @MongoDB\Date
     * @Assert\DateTime
     * @Serializer\Type("DateTime<'U'>")
     * @Serializer\ReadOnly
     */
    protected $dateUnpublished;
    
    /**
     * 
     * @MongoDB\ReferenceOne(targetDocument="\GPS\AppBundle\Document\User", inversedBy="createdResourceLinks", simple=true)
     * @Serializer\Groups({"ResourceLink.creator"})
     */
    protected $creator;
    
    /**
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $published;
    
    /**
     * Always track dates and published state
     *
     * @MongoDB\PrePersist
     * @MongoDB\PreUpdate
     */
    public function onPrePersist()
    {
        $this->dateModified = new \DateTime('now');

        // enforce date created & date modified
        if (!$this->dateCreated) {
            $this->dateCreated = new \DateTime('now');
        }
                
        // enforce publish/unpublish dates
        if (true === $this->published) {
            $this->dateUnpublished = null;
            if (!$this->datePublished) {
                $this->datePublished = new \DateTime('now');
            }
        } else if (false === $this->published) {
            $this->datePublished = null;
            if (!$this->dateUnpublished) {
                $this->dateUnpublished =  new \DateTime('now');
            }
        }
    }
}
