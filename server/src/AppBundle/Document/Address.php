<?php

namespace GPS\AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;
use GPS\AppBundle\Model\PropertyExistanceTrait;

/**
 * Generic address, used in multiple places.
 *
 * @MongoDB\EmbeddedDocument
 */
class Address
{
    use AutoGetterSetterTrait, ArrayFactoryTrait, PropertyExistanceTrait;
    
    /**
     * Street info 1 - generally main street information.
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=200)
     * @Serializer\Type("string")
     */
    protected $street1 = null;
    
    /**
     * Street info 2 - usually for apartment/suite information.
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=200)
     * @Serializer\Type("string")
     */
    protected $street2 = null;
    
    /**
     * Country name
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=100)
     * @Serializer\Type("string")
     */
    protected $country;
    
    /**
     * ISO Country code
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=2)
     * @Serializer\Type("string")
     */
    protected $countryCode;
    
    /**
     * Territory in given country.  In the US this would be a state.
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=100)
     * @Serializer\Type("string")
     */
    protected $territory;
    
    /**
     * Name of the city.
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=100)
     * @Serializer\Type("string")
     */
    protected $city = null;
    
    /**
     * Postal code.  In the US this is the zip code.
     * 
     * Note that for now this is a string - I'm not entirely sure I can
     * make it purely numeric.  Unsure about some international date formats.
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=100)
     * @Serializer\Type("string")
     */
    protected $postalCode;
    
    /**
     * undocumented variable
     *
     * @MongoDB\EmbedOne(targetDocument="Location")
     * @Assert\Valid
     * @Serializer\Type("GPS\AppBundle\Document\Location")
     */
    protected $location;
    
    /**
     * undocumented function
     *
     * @Serializer\SerializedName("formatted")
     * @Serializer\VirtualProperty
     */
    public function getFormattedAddress()
    {
        return <<<EOT
$this->street1
$this->street2
$this->city $this->territory, $this->postalCode
$this->country
EOT;
    }
    
    /**
     * String representaiton for convenience in some places.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getFormattedAddress();
    }
}
