<?php

namespace GPS\AppBundle\Document\Candidate;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;
use Doctrine\Common\Collections\ArrayCollection;
use GPS\AppBundle\Model\ObjectArrayHelperTrait;
use GPS\AppBundle\Model\PropertyExistanceTrait;

/**
 * A description of experiences for a specific country.
 *
 * @MongoDB\EmbeddedDocument
 * @MongoDB\HasLifecycleCallbacks
 */
class CountryExperience
{
    use AutoGetterSetterTrait, ArrayFactoryTrait, ObjectArrayHelperTrait, PropertyExistanceTrait;

    public function __construct()
    {
        // $this->activities = new CountryActivities();
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
     * A random id to use for the sake of array indexing, since we can't count on values in the object, even
     * though they should be unique.
     *
     * For example: a person fills out the form for a country, but realizes they selected the wrong country and
     * want to change the name.
     *
     * NOTE: This field is NOT called "id" on purpose.  In the unlikely event we were ever to switch to a relational
     * database, I want the "id" field in nested models to be reserved for auto-inc ids used as foreign keys.
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Serializer\ReadOnly
     */
    private $hash;

    /**
     * DEPRECATED: should be using only code
     * 
     * Name of the country
     *
     * @deprecated
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=100)
     * @Serializer\Type("string")
     */
    protected $name;

    /**
     * ISO 3166-1 alpha-2 code.
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=2)
     * @Serializer\Type("string")
     */
    protected $code;

    /**
     * How familiar the candidate is with the local business culture.
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $businessFamiliarity;

    /**
     * How familiar the candidate is with the local social culture.
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $cultureFamiliarity;
    
    /**
     * Reasons the person was in country in a broad sense
     *
     * @MongoDB\Collection
     * @Assert\Choice(multiple=true, choices={"work","volunteer","military","study","teaching","dependant","research"})
     * @Serializer\Type("array<string>")
     */
    protected $purposes;
    
    /**
     * Approximate number of months spent in country
     * 
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Serializer\Type("integer")
     */
    protected $approximateNumberMonths;
    
    /**
     * When they were most recently in country
     *
     * @MongoDB\Date
     * @Assert\DateTime
     * @Serializer\Type("DateTime<'U'>")
     */
    protected $dateLastVisit;
    
    /**
     * List of cities where most time was spent
     *
     * @MongoDB\Collection
     * @Assert\All({
     *  @Assert\NotBlank,
     *  @Assert\Type("string"),
     *  @Assert\Length(max=150)
     * })
     * @Serializer\Type("array<string>")
     */
    protected $cities;

    /**
     * Overview of work related experience the candidate has in the given country.
     *
     * @MongoDB\EmbedOne(targetDocument="CountryActivities")
     * @Assert\Valid
     * @Serializer\Type("GPS\AppBundle\Document\Candidate\CountryActivities")
     */
    protected $activities;
    
    public function isComplete()
    {
        return $this->allPropertiesExist(['cultureFamiliarity','businessFamiliarity']);
    }
}
