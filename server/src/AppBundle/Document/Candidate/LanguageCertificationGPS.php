<?php

namespace GPS\AppBundle\Document\Candidate;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;
use GPS\AppBundle\Model\PropertyExistanceTrait;

/**
 * A certification for a specific language.  Note that this is not quite the same
 * as the other certifications that extend AbstractOfficialLanguageCertification.
 * 
 * This one should update on preUpdate/Perist, because the date it was modified
 * is important.
 *
 * @MongoDB\EmbeddedDocument
 * @MongoDB\HasLifecycleCallbacks
 */
class LanguageCertificationGPS
{
    use AutoGetterSetterTrait, ArrayFactoryTrait, PropertyExistanceTrait;
    
    public function __wakeup()
    {
        if (!$this->lastModified) {
            $this->lastModified = new \DateTime('now');
        }
    }
    
    public function __construct()
    {
        $this->lastModified = new \DateTime('now');
    }
    
    /**
     * WARNING: it may not be enough to rely on doctrine lifecycle callbacks for this
     *
     * Informally testing it, it seems ok, though.
     *
     * @MongoDB\PrePersist
     * @MongoDB\PreUpdate
     */
    public function onPrePersist()
    {
        $this->lastModified = new \DateTime('now');
    }
    
    /**
     * Time the certification was last modified.
     *
     * @MongoDB\Date
     * @Assert\NotBlank
     * @Assert\DateTime
     * @Serializer\Type("DateTime<'U'>")
     * @Serializer\ReadOnly
     */
    protected $lastModified;
    
    /**
     * Date of peak proficiency (likely month/year, not specific)
     *
     * @MongoDB\Date
     * @Assert\DateTime
     * @Serializer\Type("DateTime<'U'>")
     */
    protected $peakProficiency;
    
    /** 
     * General peak proficiency level, not specific to any skill
     * 
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $peakProficiencyLevel;
    
    /**
     * Reading score
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $reading;
    
    /**
     *
     * @deprecated
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $readingPeak;

    /**
     * Writing score
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $writing;
    
    /**
     *
     * @deprecated
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $writingPeak;

    /**
     * Listening score
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $listening;
    
    /**
     *
     * @deprecated
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $listeningPeak;

    /**
     * Speaking... verbal interaction score
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $interacting;
    
    /**
     *
     * @deprecated
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $interactingPeak;

    /**
     * DEPRECATED - to be removed after migrations are run
     * 
     * Formal presenting score
     *
     * @deprecated
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $presenting;
    
    /**
     * DEPRECATED - to be removed after migrations are run
     *
     * @deprecated
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $presentingPeak;

    /**
     * DEPRECATED - Social media interaction score
     *
     * @deprecated
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $social;
    
    /**
     *
     * @deprecated
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $socialPeak;
    
    public function isComplete()
    {
        return $this->allPropertiesExist(['peakProficiency']) && $this->anyPropertiesExist(['social','socialPeak','interacting','interactingPeak','listening','listeningPeak','writing','writingPeak','reading','readingPeak']);
    }
}
