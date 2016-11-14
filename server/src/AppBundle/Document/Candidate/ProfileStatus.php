<?php

namespace GPS\AppBundle\Document\Candidate;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Membership in an organization.
 *
 * @MongoDB\EmbeddedDocument
 */
class ProfileStatus
{
    use AutoGetterSetterTrait, ArrayFactoryTrait;
    
    public function __construct()
    {
        // $this->completeness = new ProfileCompleteness();
    }
    
    /**
     * Has the notification been shown that they've seen all sections?
     * 
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $introCompleted;

    /**
     *
     * @MongoDB\Collection
     * @Assert\All({
     *  @Assert\NotBlank,
     *  @Assert\Type("string"),
     *  @Assert\Length(max=50)
     * })
     * @Serializer\Type("array<string>")
     */
    protected $introSectionsSeen;

    /**
     *
     * @MongoDB\Collection
     * @Assert\All({
     *  @Assert\NotBlank,
     *  @Assert\Type("string"),
     *  @Assert\Length(max=50)
     * })
     * @Serializer\Type("array<string>")
     */
    protected $introSectionsSkipped;
    
    /**
     *
     * @deprecated
     * 
     * @MongoDB\Collection
     * @Assert\All({
     *  @Assert\NotBlank,
     *  @Assert\Type("string"),
     *  @Assert\Length(max=50)
     * })
     * @Serializer\Type("array<string>")
     */
    protected $sectionsSkipped;
    
    /**
     * A message is shown when a user has seen all the sections.
     *
     * It does not mean that their profile is actually "complete", per se.
     *
     * @deprecated
     *
     * 
     * @MongoDB\Collection
     * @Assert\All({
     *  @Assert\NotBlank,
     *  @Assert\Type("string"),
     *  @Assert\Length(max=50)
     * })
     * @Serializer\Type("array<string>")
     */
    protected $sectionsSeen;
    
    /**
     * Has the notification been shown that they've seen all sections?
     *
     * @deprecated
     * 
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $allSectionsSeenNotified;
    
    /**
     * Cached completeness representation
     * 
     * @MongoDB\EmbedOne(targetDocument="ProfileCompleteness")
     * @Assert\Valid
     * @Serializer\Type("GPS\AppBundle\Document\Candidate\ProfileCompleteness")
     */
    protected $completeness;
}
