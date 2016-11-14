<?php

namespace GPS\AppBundle\Document\Candidate;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * Base class for official language certifications.
 *
 * @MongoDB\MappedSuperclass
 * @MongoDB\HasLifecycleCallbacks
 * @Serializer\Discriminator(field = "scale", map = {
 *  "cefr":     "GPS\AppBundle\Document\Candidate\LanguageCertificationCEFR",
 *  "alte":     "GPS\AppBundle\Document\Candidate\LanguageCertificationALTE",
 *  "ilr":      "GPS\AppBundle\Document\Candidate\LanguageCertificationILR",
 *  "actfl":    "GPS\AppBundle\Document\Candidate\LanguageCertificationACTFL",
 *  "other":    "GPS\AppBundle\Document\Candidate\LanguageCertificationOther"
 * })
 */
abstract class AbstractOfficialLanguageCertification
{
    /**
     * Used for disambiguation purposes.
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Serializer\ReadOnly
     * @Serializer\Type("string")
     */
    private $hash;

    /**
     * In the case of this type of inheritance, updating is slightly more manual, and
     * the assigned hash needs to persist accross object types, so it's allowable
     * to set this field directly.
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    }

    /**
     * Explicit getter is needed - extending classes using the AutoGetterSetterTrait won't have
     * access to the private $hash property.
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * When was the certification given, or corresponding exam adminstered?
     *
     * @MongoDB\Date
     * @Assert\DateTime
     * @Serializer\Type("DateTime<'U'>")
     */
    protected $date;

    /**
     * The institution that administered the exam.
     *
     * TODO: change to institution reference
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=150)
     * @Serializer\Type("string")
     */
    protected $institution;

    /**
     * The name of the test.  We ask this because that's usually enough
     * to tell us which actual rating scale was used.
     *
     * @MongoDB\String
     * @Assert\NotBlank
     * @Assert\Choice(choices={"dlpt","ilr-opi","aappl","telc","stamp","torfl","tocfl","custom"})
     * @Serializer\Type("string")
     */
    protected $test;

    /**
     * We ask the actual name of the test when it's a "custom" test that we do not know about yet.
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=150)
     * @Serializer\Type("string")
     */
    protected $testName;

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
}
