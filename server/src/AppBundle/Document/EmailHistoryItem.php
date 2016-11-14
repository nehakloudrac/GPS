<?php

namespace GPS\AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

use Doctrine\Common\Collections\ArrayCollection;
use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;

/**
 * Metadata for an out-of-band email sent from application to a user.
 *
 * @MongoDB\EmbeddedDocument
 */
class EmailHistoryItem
{
    use AutoGetterSetterTrait, ArrayFactoryTrait;
    
    /**
     * Date email was sent.
     * 
     * @MongoDB\Date
     * @Assert\DateTime
     * @Serializer\Type("DateTime<'U'>")
     */
    protected $sentAt;
    
    /**
     * Key of email sent, used for internal references.  All out-of-band
     * emails should have a string key associated.  They email keys are generally
     * used when creating unsubscribe links.  Perhaps a better name is "type", but
     * I'm not changing it now.
     *
     * @MongoDB\String
     * @Serializer\Type("string")
     */
    protected $emailKey;
    
    /**
     * Arbitrary string tags for filtering certain types of emails.  For example
     * product announcement emails all have the same emailKey, but different tags
     * denoting the feature/features being described.
     * 
     * @MongoDB\Collection
     * @Serializer\Type("array<string>")
     */
    protected $tags;
}
