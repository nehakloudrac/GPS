<?php

namespace GPS\AppBundle\Document\Candidate;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;
use Doctrine\Common\Collections\ArrayCollection;
use GPS\AppBundle\Model\ObjectArrayHelperTrait;

/**
 * @MongoDB\EmbeddedDocument
 */
class ShortForm
{
    use AutoGetterSetterTrait, ArrayFactoryTrait, ObjectArrayHelperTrait;

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
    protected $preferredIndustries;

    /**
     *
     * @MongoDB\Int
     * @Assert\Type("integer")
     * @Assert\Range(min=0, max=99)
     * @Serializer\Type("integer")
     */
    protected $yearsWorkExperience;

    /**
     * This is phrased improperly, it's more, "what's the highest position
     * level you have held" - UI directions were updated at some point, but the
     * field remains a bit of a misnomer
     *
     * TODO: strictly validate: "president_ceo","owner_founder","principal","cxo","vp","director","manager","advanced","entry","intern"
     * 
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=50)
     * @Serializer\Type("string")
     */
    protected $lastPositionLevelHeld;

    /**
     * DEPRECATED - use "degrees" field instead
     *
     * @MongoDB\String
     * @Assert\Choice(choices={"associates","bachelors","masters","mba","jd","phd","md","edd","none"})
     * @Serializer\Type("string")
     */
    protected $lastDegreeEarned;
    
    /**
     *
     *
     * @MongoDB\Collection
     * @Assert\All({
     *  @Assert\NotBlank,
     *  @Assert\Type("string"),
     *  @Assert\Choice(choices={"associates","bachelors","masters","mba","jd","phd","md","edd","none"})
     * })
     * @Serializer\Type("array<string>")
     */
    protected $degrees;

    /**
     *
     *
     * @MongoDB\Collection
     * @Assert\All({
     *  @Assert\NotBlank,
     *  @Assert\Type("string"),
     *  @Assert\Length(min=2, max=2)
     * })
     * @Serializer\Type("array<string>")
     */
    protected $countries;

    /**
     *
     * @MongoDB\Collection
     * @Assert\All({
     *  @Assert\NotBlank,
     *  @Assert\Type("string"),
     *  @Assert\Length(min=3, max=4)
     * })
     * @Serializer\Type("array<string>")
     */
    protected $foreignLanguages;

    /**
     * Whether or not the short form is "completed".  Note that, though this
     * field is still used, it's intent has changed a bit since it was added.
     *
     * Editing your profile now contains a "background" section.  This is marked
     * true once that first background section has been completed.  Previously, those
     * fields were edited in a separate form after registration, before starting
     * the actual profile.
     *
     * @MongoDB\Boolean
     * @Assert\Type("boolean")
     * @Serializer\Type("boolean")
     */
    protected $completed;

    /**
     *
     * @MongoDB\Date
     * @Assert\DateTime
     * @Serializer\Type("DateTime<'U'>")
     * @Serializer\ReadOnly
     */
    protected $dateModified;
}
