<?php

namespace GPS\AppBundle\Document\Candidate;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;
use GPS\AppBundle\Model\ObjectArrayHelperTrait;
use Doctrine\Common\Collections\ArrayCollection;
use GPS\AppBundle\Model\PropertyExistanceTrait;

/**
 * A university experience.
 *
 * @MongoDB\EmbeddedDocument
 */
class TimelineUniversity extends AbstractTimelineEvent
{
    use AutoGetterSetterTrait, ArrayFactoryTrait, ObjectArrayHelperTrait, PropertyExistanceTrait;
    
    const TYPE = 'university';
    
    /**
     * Majors/minors
     *
     * @MongoDB\EmbedMany(targetDocument="UniversityConcentration")
     * @Assert\Valid(traverse=true)
     * @Serializer\Type("ArrayCollection<GPS\AppBundle\Document\Candidate\UniversityConcentration>")
     */
    protected $concentrations;

    /**
     * GPA
     *
     * @MongoDB\Float
     * @Assert\Type("float")
     * @Assert\Range(min="0", max="4.0")
     * @Serializer\Type("double")
     */
    protected $gpa;

    /**
     * DEPRECATED - use `degrees` instead
     * 
     * Type of degree earned
     *
     * @deprecated
     * 
     * @MongoDB\String
     * @Assert\Choice(choices={"associates","bachelors","masters","mba","jd","phd","md","edd","none"})
     * @Serializer\Type("string")
     */
    protected $degree;
    
    /**
     * Degrees earned in program
     *
     * @MongoDB\Collection
     * @Assert\Choice(multiple=true, choices={"associates","bachelors","masters","mba","jd","phd","md","edd","none"})
     * @Serializer\Type("array<string>")
     */
    protected $degrees;

    /**
     * International courses or seminars.  These are more orientation type experiences, not
     * necessarily traditional classes.
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Serializer\Type("string")
     */
    protected $intlCourse;

    public function __construct()
    {
        parent::__construct();

        $this->concentrations = new ArrayCollection();
    }

    public function isComplete()
    {
        if (
            !$this->getDuration() || !$this->getDuration()->allPropertiesExist(['start']) ||
            !$this->getInstitution() || !$this->getInstitution()->allPropertiesExist(['name']) ||
            !$this->getInstitution()->getAddress()
        ) {
            return false;
        }
        
        if (!$this->getConcentrations() || count($this->getConcentrations()) == 0) {
            return false;
        }
        
        return true;
    }
}
