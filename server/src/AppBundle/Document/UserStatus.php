<?php

namespace GPS\AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

use Doctrine\Common\Collections\ArrayCollection;
use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;

/**
 * Status for tracking various things about a user... kindof an evolving
 * thing at the moment.
 *
 * @MongoDB\EmbeddedDocument
 */
class UserStatus
{
    use AutoGetterSetterTrait, ArrayFactoryTrait;
    
    /**
     *
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $seenDashboardTutorial;
    
    /**
     *
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $seenProfileViewTutorial;

    /**
     * DEPRECATED - removed separate tutorial for editing; seenProfileViewTutorial
     * implies that a user has started their full profile
     *
     * TODO: remove at some point; migrate to remove references
     *
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $seenProfileEditTutorial;
    
    /**
     * initialize default values
     */
    public function __construct()
    {
        $this->seenDashboardTutorial = false;
        $this->seenProfileViewTutorial = false;
        $this->seenProfileEditTutorial = false;
    }
}
