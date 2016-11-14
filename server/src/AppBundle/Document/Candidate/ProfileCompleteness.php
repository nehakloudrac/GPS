<?php

namespace GPS\AppBundle\Document\Candidate;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * Cached representation of profile completeness - updated out-of-band generally
 *
 * @MongoDB\EmbeddedDocument
 */
class ProfileCompleteness 
{
    use AutoGetterSetterTrait, ArrayFactoryTrait;
        
    /**
     * date last saved
     *
     * @MongoDB\Date
     * @Assert\DateTime
     * @Serializer\Type("DateTime<'U'>")
     */
    protected $lastUpdated;
    
    /**
     * total items counted
     * 
     * @MongoDB\Int
     * @Assert\Type("integer")
     * @Serializer\Type("integer")
     */
    protected $totalItems;
    
    /**
     * completed items counted
     * 
     * @MongoDB\Int
     * @Assert\Type("integer")
     * @Serializer\Type("integer")
     */
    protected $completeItems;
    
    /**
     * int value for percent
     * 
     * @MongoDB\Int
     * @Assert\Type("integer")
     * @Serializer\Type("integer")
     */
    protected $percentCompleted;
    
    /**
     * map of completeness by section as seen in the UI 
     * 
     * @MongoDB\Hash
     * @Serializer\Type("array<string, boolean>")
     */
    protected $sectionStatus;
}
