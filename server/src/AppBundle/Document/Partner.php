<?php

namespace GPS\AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;

/**
 * Main user class for everyone in the system.
 *
 * @MongoDB\Document(collection="partners")
 */
class Partner
{
    use AutoGetterSetterTrait, ArrayFactoryTrait;
    
    /**
     * Unique ID for partner doc.
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
     * @Assert\Length(max=100)
     * @Serializer\Type("string")
     */
    protected $key;
    
    /**
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Serializer\Type("string")
     */
    protected $name;
    
    /**
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Serializer\Type("string")
     */
    protected $url;
    
    /**
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Serializer\Type("string")
     */
    protected $description;
    
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
    protected $logoUrl;
    
    /**
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Serializer\Type("string")
     */
    protected $logoCss;
    
    /**
     *
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $isEnabled;

    /**
     *
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $isReferrerLinkEnabled;
    
    /**
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Serializer\Type("string")
     */
    protected $referrerCustomText;
    
    /**
     *
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $isPublicPartner;
    
    /**
     * Date created
     *
     * @MongoDB\Date
     * @Assert\DateTime
     * @Serializer\ReadOnly
     * @Serializer\Type("DateTime<'U'>")
     */
    protected $dateCreated;
    
    public function __construct()
    {
        $this->dateCreated = new \DateTime('now');
    }
        
}