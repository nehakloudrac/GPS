<?php

namespace GPS\AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;
use GPS\AppBundle\Model\SimpleGetterSetterTrait;

/**
 * Document for the employer contact form.  This will hopefully go away eventually, once
 * employers are allowed to register for the site.
 *
 * @MongoDB\Document(collection="employerContacts")
 */
class EmployerContact
{
    use SimpleGetterSetterTrait, AutoGetterSetterTrait, ArrayFactoryTrait;

    public function __construct()
    {
        $this->positions = new ArrayCollection();
        $this->institution = new Candidate\InstitutionReference();
        $this->dateSubmitted = new \DateTime();
    }

    /**
     * Unique ID for a user.
     *
     * @MongoDB\Id
     * @Serializer\ReadOnly
     * @Serializer\Type("string")
     */
    private $id;
    
    /**
     *
     * @MongoDB\Date
     * @Assert\DateTime
     * @Serializer\Type("DateTime<'U'>")
     */
    protected $dateSubmitted;

    /**
     * A name
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\NotBlank
     * @Assert\Length(max=100)
     * @Serializer\Type("string")
     */
    protected $firstName;

    /**
     * A name
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\NotBlank
     * @Assert\Length(max=100)
     * @Serializer\Type("string")
     */
    protected $lastName;

    /**
     * DEPRECATED - will be removed
     * 
     * @deprecated
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=100)
     * @Serializer\Type("string")
     */
    protected $preferredName;

    /**
     * An email address.
     *
     * @MongoDB\String
     * @Assert\Email
     * @Assert\NotBlank
     * @Assert\Length(max=100)
     * @Serializer\Type("string")
     */
    protected $email;

    /**
     * A primary phone number
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\NotBlank
     * @Assert\Length(max=16)
     * @Serializer\Type("string")
     */
    protected $phoneNumber;

    /**
     * Job title of person making contact
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=100)
     * @Serializer\Type("string")
     */
    protected $title;

    /**
     * Fields about institution
     *
     * @MongoDB\EmbedOne(targetDocument="\GPS\AppBundle\Document\Candidate\InstitutionReference")
     * @Assert\Valid
     * @Serializer\Type("GPS\AppBundle\Document\Candidate\InstitutionReference")
     */
    protected $institution;

    /**
     * Approximate number of employees in company
     *
     * @MongoDB\Int
     * @Assert\Type("integer")
     * @Serializer\Type("integer")
     */
    protected $numEmployees;

    /**
     * Approximate number of annual hires company makes
     *
     * @MongoDB\Int
     * @Assert\Type("integer")
     * @Serializer\Type("integer")
     */
    protected $numAnnualHires;

    /**
     * Number of positions needed to fill
     *
     * @MongoDB\Int
     * @Assert\Type("integer")
     * @Serializer\Type("integer")
     */
    protected $numPositions;

    /**
     * Specific job positions
     *
     * @MongoDB\EmbedMany(targetDocument="EmployerContactPosition")
     * @Assert\Valid(traverse=true)
     * @Serializer\Type("ArrayCollection<GPS\AppBundle\Document\EmployerContactPosition>")
     */
    protected $positions;

    public function removeEmptyPositions()
    {

    }
}
