<?php

namespace GPS\AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;
use Doctrine\Common\Collections\ArrayCollection;
use GPS\AppBundle\Model\SimpleGetterSetterTrait;

/**
 * Description of a specific position for an Employer Contact
 *
 * @MongoDB\EmbeddedDocument
 */
class EmployerContactPosition
{
    use SimpleGetterSetterTrait, AutoGetterSetterTrait, ArrayFactoryTrait;

    public function __construct()
    {
        $this->startDate = new \DateTime();
    }

    /**
     * Where the position is
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=100)
     * @Serializer\Type("string")
     */
    protected $city;

    /**
     * Where position is
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=100)
     * @Serializer\Type("string")
     */
    protected $country;

    /**
     * Where position is
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=100)
     * @Serializer\Type("string")
     */
    protected $status;

    /**
     * A name
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=100)
     * @Serializer\Type("string")
     */
    protected $title;

    /**
     * Where position is
     *
     * @MongoDB\Date
     * @Assert\DateTime
     * @Serializer\Type("DateTime<'U'>")
     */
    protected $startDate;

    /**
     * Where position is
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=100)
     * @Serializer\Type("string")
     */
    protected $salary;

    /**
     *
     *
     * @MongoDB\Collection
     * @Assert\All({
     *  @Assert\Type("string"),
     *  @Assert\Length(max=150)
     * })
     * @Serializer\Type("array<string>")
     */
    protected $desiredLanguages;

    /**
     *
     *
     * @MongoDB\Collection
     * @Assert\All({
     *  @Assert\Type("string"),
     *  @Assert\Length(max=150)
     * })
     * @Serializer\Type("array<string>")
     */
    protected $desiredCountries;

    /**
     * DEPRECATED - will be removed.
     *
     * @deprecated
     * @MongoDB\Collection
     * @Assert\All({
     *  @Assert\Type("string"),
     *  @Assert\Length(max=150)
     * })
     * @Serializer\Type("array<string>")
     */
    protected $desiredSkills;
}
