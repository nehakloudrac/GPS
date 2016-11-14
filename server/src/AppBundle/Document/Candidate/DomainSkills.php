<?php
namespace GPS\AppBundle\Document\Candidate;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;
use Doctrine\Common\Collections\ArrayCollection;
use GPS\AppBundle\Model\PropertyExistanceTrait;

/**
 * Lists of industry or domain skills, categorized by level of proficiency
 *
 * @MongoDB\EmbeddedDocument
 */
class DomainSkills
{
    use AutoGetterSetterTrait, ArrayFactoryTrait, PropertyExistanceTrait;

    /**
     * Expert skills, weight most heavily
     *
     * @MongoDB\Collection
     * @Assert\Count(max=5)
     * @Assert\All({
     *  @Assert\NotBlank,
     *  @Assert\Type("string"),
     *  @Assert\Length(max=75)
     * })
     * @Serializer\Type("array<string>")
     */
    protected $expert;

    /**
     * Advanced skills, weighted more heavily than proficient
     *
     * @MongoDB\Collection
     * @Assert\Count(max=10)
     * @Assert\All({
     *  @Assert\NotBlank,
     *  @Assert\Type("string"),
     *  @Assert\Length(max=75)
     * })
     * @Serializer\Type("array<string>")
     */
    protected $advanced;

    /**
     * General proficent skills
     *
     * @MongoDB\Collection
     * @Assert\Count(max=15)
     * @Assert\All({
     *  @Assert\NotBlank,
     *  @Assert\Type("string"),
     *  @Assert\Length(max=75)
     * })
     * @Serializer\Type("array<string>")
     */
    protected $proficient;
    
    public function isComplete()
    {
        return $this->anyPropertiesExist(['expert','advanced','proficient']);
    }
}
