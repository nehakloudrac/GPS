<?php

namespace GPS\AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

use Doctrine\Common\Collections\ArrayCollection;
use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;

/**
 * Tracking various things about a user... kindof an evolving
 * thing at the moment.
 *
 * @MongoDB\EmbeddedDocument
 */
class UserTracker
{
    use AutoGetterSetterTrait, ArrayFactoryTrait;

    /**
     * undocumented variable
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Serializer\ReadOnly
     * @Serializer\Type("integer")
     */
    protected $profileViewsTotal;
    
    /**
     * undocumented variable
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Serializer\ReadOnly
     * @Serializer\Type("integer")
     */
    protected $profileViewsTotalLastMonth;

    /**
     * undocumented variable
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Serializer\ReadOnly
     * @Serializer\Type("integer")
     */
    protected $profileViewsLastMonth;
        
    /**
     * Date profile was last viewed by an admin
     *
     * @MongoDB\Date
     * @Assert\DateTime
     * @Serializer\ReadOnly
     * @Serializer\Type("DateTime<'U'>")
     */
    protected $profileLastViewed;
    
    /**
     * undocumented variable
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Serializer\ReadOnly
     * @Serializer\Type("integer")
     */
    protected $profileSearchHitsTotal;
    
    /**
     * The number of total searches the users had a month ago - this is NOT 
     * how many times they were searched last month, it's what `$profileSearchHitsTotal`
     * was a month ago.
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Serializer\ReadOnly
     * @Serializer\Type("integer")
     */
    protected $profileSearchHitsTotalLastMonth;

    /**
     * Number of times the users was searched last month.
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Serializer\ReadOnly
     * @Serializer\Type("integer")
     */    
    protected $profileSearchHitsLastMonth;
        
    /**
     * Date profile last turned up in search
     *
     * @MongoDB\Date
     * @Assert\DateTime
     * @Serializer\ReadOnly
     * @Serializer\Type("DateTime<'U'>")
     */
    protected $profileLastSearchHit;
        
    /**
     * Date stats were last computed
     *
     * @MongoDB\Date
     * @Assert\DateTime
     * @Serializer\ReadOnly
     * @Serializer\Type("DateTime<'U'>")
     */
    protected $statsLastComputed;

    /**
     * Date the user last logged in
     *
     * @MongoDB\Date
     * @Assert\DateTime
     * @Serializer\ReadOnly
     * @Serializer\Type("DateTime<'U'>")
     */
    protected $sessionLastLogin;
}
