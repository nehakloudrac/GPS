<?php

namespace GPS\AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

use Doctrine\Common\Collections\ArrayCollection;
use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;

/**
 * User account preferences.
 *
 * @MongoDB\EmbeddedDocument
 */
class AccountPreferences
{
    use AutoGetterSetterTrait, ArrayFactoryTrait;
    
    /**
     * Whether or not to allow using a Gravatar profile image if the user
     * has not uploaded their own.
     *
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $allowGravatar;
    
    /**
     * Whether or not GPS is allowed to email users when major features
     * have been added.
     *
     * In the UI, this is labeled "Announcements"... so this may need
     * to expand into multiple fields over time.
     *
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $allowProductFeatureEmails;
    
    /**
     * Whether or not GPS is allowed to email the candidate once a week about
     * recent matches and whether or not institutions have expressed interest
     * in their profile.
     *
     * DEPRECATED: Well... maybe.  It was never used, but full removal will require 
     * a migration to remvove existing data.  On the other hand, we could choose
     * to actually use it in the future.
     *
     * @deprecated
     *
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $allowProfileInterestEmails;
    
    /**
     * Whether or not to allow emails about their profile completeness
     * 
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $allowProfileHealthEmails;
    
    /**
     * Whether or not to allow emails about search activity on their profiles.
     *
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $allowSearchActivityEmails;
    
    /**
     * Sets default values.
     */
    public function __construct()
    {
        $this->allowGravatar = true;
        $this->allowProductFeatureEmails = true;
        $this->allowProfileInterestEmails = true;
        $this->allowProfileHealthEmails = true;
        $this->allowSearchActivityEmails = true;
    }
}
