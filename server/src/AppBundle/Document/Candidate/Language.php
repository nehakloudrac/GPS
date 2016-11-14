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
 * Description of manner in which candidate was in a country.
 *
 * @MongoDB\EmbeddedDocument
 * @MongoDB\HasLifecycleCallbacks
 */
class Language
{
    use AutoGetterSetterTrait, ArrayFactoryTrait, ObjectArrayHelperTrait, PropertyExistanceTrait;

    public function __construct()
    {
        $this->officialCertifications = new ArrayCollection();
        $this->selfCertification = new LanguageCertificationGPS();
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
     * NOTE: This field is NOT called "id" on purpose.  In the unlikely event we were ever to switch to a relational
     * database, I want the "id" field in nested models to be reserved for auto-inc ids used as foreign keys.
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Serializer\ReadOnly
     * @Serializer\Type("string")
     */
    private $hash;

    /**
     * ISO 639-3 codes.  Required.  This is technically always an array because some
     * macrolangauges may entail dialects that are similar enough to the speaker that
     * they consider them to be the same language in a practical sense.
     *
     * In the case of a speaker with more than one dialect of the same language, but different
     * skill levels, they can treat those dialects as different languages.
     *
     * The reason 4 letter codes are allowed is because in the event that we encounter dialects
     * for which there is NOT a corresponding ISO code, we will make up our own 4 letter codes. If
     * the ISO assigns a proper 3 letter code in the future, we can do a migration to covert it.
     *
     * @MongoDB\String
     * @Assert\NotBlank
     * @Assert\Type("string")
     * @Assert\Length(min=3, max=4)
     * @Serializer\Type("string")
     */
    protected $code;

    /**
     * ISO 639-3 code.  Optional, but should be an actual ISO code.  We're not looking to get
     * into the business of specifying our own macro codes.  I hope...
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(min=3, max=3)
     * @Serializer\Type("string")
     */
    protected $macroCode;

    /**
     * DEPRECATED - remove this after migration and UI update.  No new logic should
     * assume the existence of this field.  Existing logic should be refactored.
     * 
     * Is the candidate's proficiency native-like in all skills?  If so, questions of proficiency
     * and acquisition are not really important for our purposes.
     *
     * @deprecated
     *
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Assert\NotNull
     * @Serializer\Type("boolean")
     */
    protected $nativeLikeFluency;

    /**
     * Rating for current usage in a work setting.
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=7)
     * @Serializer\Type("integer")
     */
    protected $currentUsageWork;

    /**
     * Rating for current usage in a social setting.
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=7)
     * @Serializer\Type("integer")
     */
    protected $currentUsageSocial;

    /**
     * Required GPS scale self certification.
     *
     * @MongoDB\EmbedOne(targetDocument="LanguageCertificationGPS")
     * @Assert\Valid
     * @Serializer\Type("GPS\AppBundle\Document\Candidate\LanguageCertificationGPS")
     */
    protected $selfCertification;

    /**
     * Official language certification, fields vary by scale.
     *
     * @MongoDB\EmbedMany(
     *  discriminatorField="scale",
     *  discriminatorMap={
     *      "other"     = "LanguageCertificationOther",
     *      "ilr"       = "LanguageCertificationILR",
     *      "actfl"     = "LanguageCertificationACTFL",
     *      "alte"      = "LanguageCertificationALTE",
     *      "cefr"      = "LanguageCertificationCEFR"
     *  }
     * )
     * Assert\Valid(traverse=true)
     * Serializer\Type("ArrayCollection<GPS\AppBundle\Document\Candidate\AbstractOfficialLanguageCertification>")
     */
    protected $officialCertifications;
    
    public function isComplete()
    {
        if (!$this->allPropertiesExist(['currentUsageSocial', 'currentUsageWork'])) {
            return false;
        }
        
        if (!$this->getSelfCertification()->isComplete()) {
            return false;
        }
        
        foreach ($this->getOfficialCertifications() as $cert) {
            if (!$cert->isComplete()) {
                return false;
            }
        }
        
        return true;
    }
}
