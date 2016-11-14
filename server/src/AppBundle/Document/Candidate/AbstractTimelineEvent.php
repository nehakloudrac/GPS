<?php

namespace GPS\AppBundle\Document\Candidate;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;
use GPS\AppBundle\Model\ObjectArrayHelperTrait;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Base class for timeline events.
 *
 * @MongoDB\MappedSuperclass
 * @MongoDB\HasLifecycleCallbacks
 * @Serializer\Discriminator(field = "type", map = {
 *  "job":                  "GPS\AppBundle\Document\Candidate\TimelineJob",
 *  "volunteer":            "GPS\AppBundle\Document\Candidate\TimelineVolunteer",
 *  "military":             "GPS\AppBundle\Document\Candidate\TimelineMilitary",
 *  "research":             "GPS\AppBundle\Document\Candidate\TimelineResearch",
 *  "university":           "GPS\AppBundle\Document\Candidate\TimelineUniversity",
 *  "study_abroad":         "GPS\AppBundle\Document\Candidate\TimelineStudyAbroad",
 *  "language_acquisition": "GPS\AppBundle\Document\Candidate\TimelineLanguageAcquisition"
 * })
 */
abstract class AbstractTimelineEvent
{
    use AutoGetterSetterTrait, ArrayFactoryTrait, ObjectArrayHelperTrait;

    /**
     * Used for disambiguation purposes.
     *
     * NOTE: protected instead of private in this case because inherited classes need access
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Serializer\ReadOnly
     * @Serializer\Type("string")
     */
    protected $hash;

    /**
     * DEPRECATED - I don't think this was ever actually used for anything, remove it
     * at some point.
     * 
     * Some event types can be created from multiple places, this is for tracking how
     * the event was added.
     *
     * @MongoDB\String
     * @Assert\Choice(choices={"academic","professional"})
     * @Serializer\Type("string")
     */
    protected $createdAs;

    /**
     * Almost all timeline events have at least one institution associated with them.
     *
     * @MongoDB\EmbedOne(targetDocument="InstitutionReference")
     * @Assert\Valid
     * @Serializer\Type("GPS\AppBundle\Document\Candidate\InstitutionReference")
     */
    protected $institution;

    /**
     * Date range
     *
     * @MongoDB\EmbedOne(targetDocument="\GPS\AppBundle\Document\DateRange")
     * @Assert\Valid
     * @Serializer\Type("GPS\AppBundle\Document\DateRange")
     */
    protected $duration;

    /**
     * List of activities relative to the event type.  For a job, this would be
     * list of responsibilities, for study abroad could be notable accomplishments.
     *
     * @MongoDB\Collection
     * @Assert\All({
     *  @Assert\NotBlank,
     *  @Assert\Type("string"),
     *  @Assert\Length(max=300)
     * })
     * @Serializer\Type("array<string>")
     */
    protected $activities;
    
    /**
     * A free-form text description of the item in question.  This was primarily added for 
     * compatibility with other systems that tend to treat description as one field.
     * 
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=10000)
     * @Serializer\Type("string")
     */
    protected $description;
    
    /**
     * List of related/used languages
     * 
     * @MongoDB\Collection
     * @Assert\All({
     *  @Assert\NotBlank,
     *  @Assert\Type("string"),
     *  @Assert\Length(max=300)
     * })
     * @Serializer\Type("array<string>")
     */
    protected $languageRefs;    
    
    /**
     * List of related country codes
     *
     * @MongoDB\Collection
     * @Assert\All({
     *  @Assert\NotBlank,
     *  @Assert\Type("string"),
     *  @Assert\Length(max=300)
     * })
     * @Serializer\Type("array<string>")
     */
    protected $countryRefs;

    /**
     * References to a specific country.
     *
     * @deprecated
     *
     * @MongoDB\EmbedMany(targetDocument="TimelineCountryReference")
     * @Assert\Valid(traverse=true)
     * @Serializer\Type("ArrayCollection<GPS\AppBundle\Document\Candidate\TimelineCountryReference>")
     */
    protected $countryReferences;

    /**
     * References to a specific country.
     *
     * @deprecated
     *
     * @MongoDB\EmbedMany(targetDocument="TimelineLanguageReference")
     * @Assert\Valid(traverse=true)
     * @Serializer\Type("ArrayCollection<GPS\AppBundle\Document\Candidate\TimelineLanguageReference>")
     */
    protected $languageReferences;
    
    protected $serializeCompleteness = false;

    public function __construct()
    {
        $this->ensureType();
    }
    
    public function __wakeup()
    {
        $this->ensureType();
    }

    public function getType()
    {
        return $this::TYPE;
    }
    
    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("isComplete")
     * @Serializer\Type("boolean")
     */
    public function getCompleteness()
    {
        return ($this->serializeCompleteness) ? $this->isComplete() : null;
    }
    
    public function setSerializeCompleteness($bool)
    {
        $this->serializeCompleteness = $bool;
    }
    
    public function isComplete()
    {
        return false;
    }

    /**
     * Before initial persist, create a hash for later reference.
     *
     * @MongoDB\PrePersist
     * @MongoDB\PreUpdate
     */
    public function onPrePersist()
    {
        $this->ensureType();
        
        if (!$this->hash) {
            $this->hash = uniqid();
        }
    }

    protected function ensureType()
    {
        $this->type = $this::TYPE;
    }
}
