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

/**
 * Main user class for everyone in the system.
 *
 * @MongoDB\Document(collection="users", repositoryClass="GPS\AppBundle\Model\UserRepository")
 * @MongoDB\HasLifecycleCallbacks
 */
class User implements UserInterface, EquatableInterface, AdvancedUserInterface, \Serializable
{
    use AutoGetterSetterTrait, ArrayFactoryTrait;

    /**
     * Lists the internal properties that are serializable for storage in the session.  Note that
     * this is not related in any way to the JMS Serializer used during API requests.
     *
     * @Serializer\Exclude
     */
    private $serializableFields = ['id','isEnabled', 'isVerified', 'password','firstName','lastName','email','salt','dateCreated','avatarUrl','roles', 'preferences'];

    /**
     * Unique ID for a user.
     *
     * @MongoDB\Id
     * @Serializer\ReadOnly
     * @Serializer\Type("string")
     */
    private $id;

    /**
     * A first name.
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=100)
     * @Serializer\Type("string")
     */
    protected $firstName;

    /**
     * A last name.
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=100)
     * @Serializer\Type("string")
     */
    protected $lastName;

    /**
     * User's preferred name, if blank we use first name.
     *
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
     * @MongoDB\UniqueIndex
     * @Assert\Email
     * @Assert\Length(max=100)
     * @Serializer\Groups({"User.email"})
     * @Serializer\Type("string")
     */
    protected $email;

    /**
     * A primary phone number
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=16)
     * @Serializer\Groups({"User.phone"})
     * @Serializer\Type("string")
     */
    protected $phone;

    /**
     * An encoded password.
     *
     * @MongoDB\String
     * @Assert\NotBlank
     * @Assert\Type("string")
     * @Assert\Length(max=4096)
     * @Serializer\Exclude
     */
    protected $password;

    /**
     * User's gender
     *
     * @MongoDB\String
     * @Assert\Choice(choices={"male","female","other","decline"})
     * @Serializer\Type("string")
     */
    protected $gender;
        
    /**
     * Workforce diversity flags
     * 
     * @MongoDB\Collection
     * @Assert\Choice(multiple=true, choices={"eth_asn_pacific","eth_african_american","eth_hispanic","eth_american_indian","lgbtq","disabled","veteran"})
     * @Serializer\Type("array<string>")
     */
    protected $diversity;

    /**
     * List of "native" language codes
     *
     * @MongoDB\Collection
     * @Assert\All({
     *  @Assert\NotBlank,
     *  @Assert\Type("string"),
     *  @Assert\Length(min=3, max=4)
     * })
     * @Serializer\Type("array<string>")
     */
    protected $languages;

    /**
     * Countries where use is allowed to work - technically not citizenship. Array of country codes.
     *
     * @MongoDB\Collection
     * @Assert\All({
     *  @Assert\NotBlank,
     *  @Assert\Type("string"),
     *  @Assert\Length(min=2, max=2)
     * })
     * @Serializer\Type("array<string>")
     */
    protected $citizenship;
    
    /**
     * If user can work in the US - type of work authorization.
     *
     * @MongoDB\String
     * @Assert\Choice(choices={"citizen","green_card","h1b","opt","tn"})
     * @Serializer\Type("string")
     */
    protected $usWorkAuthorization;
    
    /**
     * If user currently holds a US security clearance
     *
     * @MongoDB\String
     * @Assert\Choice(choices={"no","yes","confidential","secret","top_secret","polygraph"})
     * @Serializer\Type("string")
     */
    protected $usSecurityClearance;
    
    /**
     * Users current job status
     *
     * @MongoDB\String
     * @Assert\Choice(choices={"unemployed", "looking", "open", "satisfied", "happy"})
     * @Serializer\Type("string")
     */
    protected $currentJobStatus;

    /**
     * Whether or not the account has been verified after registration.
     *
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\ReadOnly
     * @Serializer\Type("boolean")
     */
    protected $isVerified;

    /**
     * NOTE: This is null because the algorithm used (bcrypt) contains
     * built-in salt functionality.  If the algorithm changes, this will
     * need to return a proper random salt.
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=4096)
     * @Serializer\Groups({"User.salt"})
     */
    protected $salt;

    /**
     * If not enabled, the user account is effectively locked.  Users must click on the verification
     * link in the email they receive after registration in order to enable the account.
     *
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     * @Serializer\ReadOnly
     */
    protected $isEnabled;

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
     * Date last modified
     *
     * @MongoDB\Date
     * @Assert\DateTime
     * @Serializer\ReadOnly
     * @Serializer\Type("DateTime<'U'>")
     */
    protected $lastModified;
    
    /**
     * Date removed
     *
     * @MongoDB\Date
     * @Assert\DateTime
     * @Serializer\ReadOnly
     * @Serializer\Type("DateTime<'U'>")
     */
    protected $dateRemoved;
    
    /**
     * Date last indexed
     *
     * @MongoDB\Date
     * @Assert\DateTime
     * @Serializer\ReadOnly
     * @Serializer\Type("DateTime<'U'>")
     */
    protected $lastIndexed;

    /**
     * Used internally... generally lastModified should automatically
     * be updated whenever the document is saved, but there
     * are cases when that shouldn't happen, so there needs to be
     * a way to disable it
     */
    protected $updateLastModified = true;

    /**
     * Url to user profile image. Read only because in order to set it, a
     * file needs to be properly uploaded via the API.
     *
     * @MongoDB\String
     * @Serializer\ReadOnly
     * @Serializer\Type("string")
     */
    protected $avatarUrl;
    
    /**
     * If the user registered via an institution referrence
     * 
     * @MongoDB\String
     * @Serializer\ReadOnly
     * @Serializer\Type("string")
     */
    protected $institutionReferrer;    
    
    /**
     * How the user found out about GPS.
     * 
     * @MongoDB\String
     * @Assert\Choice(choices={"search","linkedin","facebook","twitter","career_service","program_lang","program_study_abroad","program_volunteer","word_of_mouth","other"})
     * @Serializer\Type("string")
     */
    protected $referralMediumChoice;

    /**
     * How the user found out about GPS.
     * 
     * @MongoDB\String
     * @Serializer\Type("string")
     */
    protected $referralMediumOther;

    /**
     * User roles - determine some basic authorization scenarios.
     *
     * @MongoDB\Collection
     * @Assert\Choice(multiple=true, choices={"ROLE_USER", "ROLE_ADMIN", "ROLE_SUPER_ADMIN"})
     * @Serializer\ReadOnly
     * @Serializer\Type("array<string>")
     */
    protected $roles = ['ROLE_USER'];

    /**
     * User's account preferences.
     *
     * @MongoDB\EmbedOne(targetDocument="\GPS\AppBundle\Document\AccountPreferences")
     * @Assert\Valid
     * @Serializer\Type("GPS\AppBundle\Document\AccountPreferences")
     */
    protected $preferences;

    /**
     * User's location (partial)
     * 
     * @MongoDB\EmbedOne(targetDocument="Address")
     * @Assert\Valid
     * @Serializer\Type("GPS\AppBundle\Document\Address")
     */
    protected $address;

    /**
     * A candidate profile
     *
     * @MongoDB\ReferenceOne(targetDocument="\GPS\AppBundle\Document\Candidate\Profile", inversedBy="user", simple=true)
     * @Serializer\Groups({"User.candidateProfile"})
     */
    protected $candidateProfile;
    
    /**
     * Admin comments created by the user
     * 
     * @MongoDB\ReferenceMany(targetDocument="\GPS\AppBundle\Document\AdminComment", mappedBy="creator", simple=true)
     * @Serializer\Exclude
     */
    protected $createdAdminComments;
    
    /**
     * 
     * @MongoDB\ReferenceMany(targetDocument="\GPS\AppBundle\Document\ResourceLink", mappedBy="creator", simple=true)
     * @Serializer\Exclude
     */
    protected $createdResourceLinks;
    
    /**
     * Admin comments made about this user
     * 
     * @MongoDB\ReferenceMany(targetDocument="\GPS\AppBundle\Document\AdminComment", mappedBy="user", simple=true)
     * @Serializer\Exclude
     */
    protected $adminComments;

    /**
     * Various status related items about the user and their profile
     *
     * @MongoDB\EmbedOne(targetDocument="UserStatus")
     * @Assert\Valid
     * @Serializer\Type("GPS\AppBundle\Document\UserStatus")
     */
    protected $status;
    
    /**
     * various data points tracked about users or their profiles
     *
     * @MongoDB\EmbedOne(targetDocument="UserTracker")
     * @Assert\Valid
     * @Serializer\Type("GPS\AppBundle\Document\UserTracker")
     */
    protected $tracker;
    
    /**
     * Array of email history items - these are items that are sent out-of-band; not transactionals
     * such as registration/password-resets
     *
     * @MongoDB\EmbedMany(targetDocument="EmailHistoryItem")
     * @Serializer\Groups({"User.emailHistory"})
     */
    protected $emailHistory;

    public function __construct()
    {
        $this->salt = null;
        $this->isVerified = false;
        $this->isEnabled = false;
        $this->dateCreated = new \DateTime();
        $this->emailHistory = new ArrayCollection();
        $this->preferences = new AccountPreferences();
        
        // $this->address = new Address();
        // $this->status = new UserStatus();
        // $this->tracker = new UserTracker();

    }

    /**
     * The hash to use in Gravatar urls when applicable.
     *
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("gravatarHash")
     */
    public function getGravatarHash()
    {
        return md5(strtolower(trim($this->getEmail())));
    }
    
    /**
     * The hash to use in Gravatar urls when applicable.
     *
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("shortId")
     */
    public function getShortId()
    {
        return base64_encode(hex2bin($this->getId()));
    }

    public function isVerified()
    {
        return $this->isVerified;
    }

    /**
     * Preferred name if it's set, otherwise just first name.
     *
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("name")
     */
    public function getName()
    {
        if ($name = $this->getPreferredName()) {
            return $name;
        }

        return $this->getFirstName();
    }

    /**
     * User names are email addresses.
     *
     * @inheritDoc
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * Avoid confusion and just throw an error if this is ever used.
     */
    public function setUsername($name)
    {
        throw new \LogicException("Usernames are email addresses - use User::setEmail() instead.");
    }

    /**
     * @inheritDoc
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @inheritDoc
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * @inheritDoc
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @inheritDoc
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
        //QUESTION: should I be removing the encrypted password here or not?  Does it only really matter
        //in the case of plaintext encoding?
    }

    /**
     * This is used when users are deserialzed from a session and compared to the availabe database versions.
     *
     * List any methods which, if they were not to match, should require that the user be logged out for security reasons.
     *
     * @inheritDoc
     */
    public function isEqualTo(UserInterface $user)
    {
        foreach (['getEmail', 'getRoles', 'isEnabled', 'isVerified', 'getPassword'] as $method) {
            if ($user->$method() !== $this->$method()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function isEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * @inheritDoc
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }
        
    /**
     * Always track when last modified.
     *
     * @MongoDB\PrePersist
     * @MongoDB\PreUpdate
     */
    public function onPrePersist()
    {
        if ($this->updateLastModified) {
            $this->lastModified = new \DateTime('now');
        }
    }    

    /**
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
        $data = [];

        foreach ($this->serializableFields as $fieldName) {
            $data[$fieldName] = $this->$fieldName;
        }

        return serialize($data);
    }

    /**
     * @see \Serializable::unserialize()
     */
    public function unserialize($data)
    {
        $data = unserialize($data);

        foreach($this->serializableFields as $fieldName) {
            if(isset($data[$fieldName])) {
                $this->$fieldName = $data[$fieldName];
            }
        }
    }
}
