<?php

namespace GPS\AppBundle\Document\Candidate;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;
use Doctrine\Common\Collections\ArrayCollection;
use GPS\AppBundle\Model\ObjectArrayHelperTrait;
use GPS\AppBundle\Model\PropertyExistanceTrait;

/**
 * Ideal job description
 *
 * @MongoDB\EmbeddedDocument
 */
class IdealJob
{
    use AutoGetterSetterTrait, ArrayFactoryTrait, ObjectArrayHelperTrait, PropertyExistanceTrait;

    public function __construct()
    {
        $this->availability = new ArrayCollection();
        $this->desiredDate = new \GPS\AppBundle\Document\DateRange();
    }

    /**
     * Desired types of jobs.
     *
     * @MongoDB\Collection
     * @Assert\Choice(multiple=true, choices={"full_time","part_time","project","internship","volunteer"})
     * @Serializer\Type("array<string>")
     */
    protected $jobTypes;

    /**
     * List of desired employer types.
     *
     * @MongoDB\Collection
     * @Assert\Choice(multiple=true, choices={"company_private","company_public","gov_enterprise","non_profit","edu","foundation","org_intl","gov_fed","gov_local","other"})
     * @Serializer\Type("array<string>")
     */
    protected $employerTypes;

    /**
     * List of desired industries.
     *
     * TODO: strict validation
     *
     * @MongoDB\Collection
     * @Assert\All({
     *  @Assert\NotBlank,
     *  @Assert\Type("string"),
     *  @Assert\Length(max=150)
     * })
     * @Serializer\Type("array<string>")
     */
    protected $industries;

    /**
     * State list.
     *
     * WARNING: this may need to be modeled differently in the future - we might need
     * a better general concept for "locationDomestic".
     *
     * Or, we may need a more general concept for locations anyway.  Perhaps a country code
     * in addition to multiple "territories" within the country.
     *
     * @MongoDB\Collection
     * @Assert\Count(max=3)
     * @Assert\All({
     *  @Assert\NotBlank,
     *  @Assert\Type("string"),
     *  @Assert\Length(max=3)
     * })
     * @Serializer\Type("array<string>")
     */
    protected $locationsUSA;

    /**
     * Country codes list.
     *
     * @MongoDB\Collection
     * @Assert\All({
     *  @Assert\NotBlank,
     *  @Assert\Type("string"),
     *  @Assert\Length(max=2)
     * })
     * @Serializer\Type("array<string>")
     */
    protected $locationsAbroad;

    /**
     * Date range - optional end date
     *
     * @MongoDB\EmbedOne(targetDocument="\GPS\AppBundle\Document\DateRange")
     * @Assert\Valid
     * @Serializer\Type("GPS\AppBundle\Document\DateRange")
     */
    protected $desiredDate;

    /**
     * Whether or not available immediately.
     *
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $availableImmediately;

    /**
     * Desired hours per week for part time
     *
     * @MongoDB\Int
     * @Assert\Type("integer")
     * @Assert\Range(min=0, max=168)
     * @Serializer\Type("integer")
     */
    protected $hoursPerWeek;

    /**
     * Desired payment types for internship, paid, unpaid, or both
     *
     * @MongoDB\Collection
     * @Assert\Choice(multiple=true, choices={"paid","unpaid"})
     * @Serializer\Type("array<string>")
     */
    protected $payStatus;

    /**
     * Min annual salary for full-time.
     *
     * @MongoDB\Int
     * @Assert\Type("integer")
     * @Assert\Range(max=99999999)
     * @Serializer\Type("integer")
     */
    protected $minSalary;

    /**
     * Min hourly rate requested for part time work.
     *
     * @MongoDB\Int
     * @Assert\Type("integer")
     * @Assert\Range(max=99999)
     * @Serializer\Type("integer")
     */
    protected $minHourlyRate;

    /**
     * Min daily rate for project work
     *
     * @MongoDB\Int
     * @Assert\Type("integer")
     * @Assert\Range(max=999999)
     * @Serializer\Type("integer")
     */
    protected $minDailyRate;

    /**
     * Min weekly rate for project work
     *
     * @MongoDB\Int
     * @Assert\Type("integer")
     * @Assert\Range(max=9999999)
     * @Serializer\Type("integer")
     */
    protected $minWeeklyRate;

    /**
     * Min monthly rate for project work
     *
     * @MongoDB\Int
     * @Assert\Type("integer")
     * @Assert\Range(max=9999999)
     * @Serializer\Type("integer")
     */
    protected $minMonthlyRate;
    
    /**
     * How often are they willing to travel for work?
     *
     * @MongoDB\String
     * @Assert\Choice(choices={"occaisionally","up_to_25","up_to_50","over_50","no"})
     * @Serializer\Type("string")
     */
    protected $willingnessToTravel;
        
    /**
     * Whether or not willing to travel overseas generally.
     *
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $willingToTravelOverseas;

    /**
     * Windows of availability for project based work.
     *
     * @MongoDB\EmbedMany(targetDocument="ProjectAvailability")
     * @Assert\Valid(traverse=true)
     * @Serializer\Type("ArrayCollection<GPS\AppBundle\Document\Candidate\ProjectAvailability>")
     */
    protected $availability;

    /**
     * Ideal job preferences
     *
     * @MongoDB\EmbedOne(targetDocument="IdealJobPreferences")
     * @Assert\Valid
     * @Serializer\Type("GPS\AppBundle\Document\Candidate\IdealJobPreferences")
     */
    protected $preferences;
    
    public function isComplete()
    {
        if ($this->anyPropertiesExist(['jobTypes','willingToTravelOverseas', 'willingnessToTravel','employerTypes','industries','locationsUSA','locationsAbroad','availableImmediately','payStatus'])) {
            return true;
        }
        
        if ($this->getDesiredDate()->getStart()) {
            return true;
        }
        
        return false;
    }
}
