<?php

namespace GPS\AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

use Doctrine\Common\Collections\ArrayCollection;
use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;

/**
 * Main user class for everyone in the system.
 *
 * @MongoDB\Document(collection="adminComments")
 */
class AdminComment
{
    use AutoGetterSetterTrait, ArrayFactoryTrait;
        
    /**
     * Unique ID for a comment.
     *
     * @MongoDB\Id
     * @Serializer\ReadOnly
     * @Serializer\Type("string")
     */
    protected $id;
    
    /**
     * Date created
     *
     * @MongoDB\Date
     * @Assert\DateTime
     * @Serializer\ReadOnly
     * @Serializer\Type("DateTime<'U'>")
     */
    protected $dateCreated;
    
    /**
     * User who created the comment
     *
     * @MongoDB\ReferenceOne(targetDocument="\GPS\AppBundle\Document\User", inversedBy="createdAdminComments", simple=true)
     * @Serializer\Groups({"AdminComment.creator"})
     */
    protected $creator;
        
    /**
     * The user who the comment is about
     *
     * @MongoDB\ReferenceOne(targetDocument="\GPS\AppBundle\Document\User", inversedBy="adminComments", simple=true)
     * @Serializer\Groups({"AdminComment.user"})
     */
    protected $user;
    
    /**
     * Content of the comment.
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=100000)
     * @Serializer\Type("string")
     */
    protected $text;
}
