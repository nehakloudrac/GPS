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
 * Root document for a candidate profile.
 *
 * @author Evan Villemez
 *
 * @MongoDB\Document(collection="candidateProfiles")
 * @MongoDB\HasLifecycleCallbacks
 */
class Profile
{
    use AutoGetterSetterTrait, ArrayFactoryTrait, ObjectArrayHelperTrait, PropertyExistanceTrait;

    public function __construct()
    {
        $this->countries = new ArrayCollection();
        $this->languages = new ArrayCollection();
        $this->timeline = new ArrayCollection();
        $this->awards = new ArrayCollection();
        $this->certifications = new ArrayCollection();
        $this->academicOrganizations = new ArrayCollection();
        $this->organizations = new ArrayCollection();

        // $this->profileStatus = new ProfileStatus();
        // $this->shortForm = new ShortForm();
        // $this->hardSkills = new HardSkills();
        $this->idealJob = new IdealJob();
        // $this->domainSkills = new DomainSkills();
    }

    /**
     * Unique id of profile.
     *
     * @MongoDB\Id
     * @Serializer\ReadOnly
     * @Serializer\Type("string")
     */
    private $id;

    /**
     * A reference to the user who owns this candidate profile.
     *
     * @MongoDB\ReferenceOne(targetDocument="\GPS\AppBundle\Document\User", mappedBy="candidateProfile", simple=true)
     * @Serializer\Exclude
     */
    protected $user;

    /**
     * Short form, filled in before the rest of the profile
     *
     * @MongoDB\EmbedOne(targetDocument="ShortForm")
     * @Assert\Valid
     * @Serializer\Type("GPS\AppBundle\Document\Candidate\ShortForm")
     */
    protected $shortForm;

    /**
     * Small narrative about current career objective.
     *
     * @TODO
     */
    protected $objective;

    /**
     * Current job seeking status.
     *
     * @TODO
     */
    protected $availability;
    
    /**
     * A place for various status related fields... it's a bit in flux.
     *
     * @MongoDB\EmbedOne(targetDocument="ProfileStatus")
     * @Assert\Valid
     * @Serializer\Type("GPS\AppBundle\Document\Candidate\ProfileStatus")
     */
    protected $profileStatus;

    /**
     * List of notable country-specific experiences.
     *
     * @MongoDB\EmbedMany(targetDocument="CountryExperience")
     * @Assert\Valid(traverse=true)
     * @Serializer\Type("ArrayCollection<GPS\AppBundle\Document\Candidate\CountryExperience>")
     *
     * TODO: custom array unique validator by field name (country in this case)
     */
    protected $countries;

    /**
     * List of relevant languages
     *
     * @MongoDB\EmbedMany(targetDocument="Language")
     * @Assert\Valid(traverse=true)
     * @Serializer\Type("ArrayCollection<GPS\AppBundle\Document\Candidate\Language>")
     */
    protected $languages;

    /**
     * Note that this is not being validate for a reason - events are managed independently in the API and validated
     * before being modified/added to the profile.
     *
     * @MongoDB\EmbedMany(
     *  discriminatorField="type",
     *  discriminatorMap={
     *      "job"                   = "TimelineJob",
     *      "volunteer"             = "TimelineVolunteer",
     *      "military"              = "TimelineMilitary",
     *      "research"              = "TimelineResearch",
     *      "university"            = "TimelineUniversity",
     *      "study_abroad"          = "TimelineStudyAbroad",
     *      "language_acquisition"  = "TimelineLanguageAcquisition"
     *  }
     * )
     * @Serializer\Type("ArrayCollection<GPS\AppBundle\Document\Candidate\AbstractTimelineEvent>")
     */
    protected $timeline;
    
    /**
     * Description of hard skills.
     *
     * @MongoDB\EmbedOne(targetDocument="HardSkills")
     * @Assert\Valid
     * @Serializer\Type("GPS\AppBundle\Document\Candidate\HardSkills")
     */
    protected $hardSkills;

    /**
     * List of top soft skills, ordered by importance
     *
     * @MongoDB\Collection
     * @Assert\Count(max=5)
     * @Assert\Choice(multiple=true, choices={"adaptability","communication","creativity","critical_thinking","decision_making","curiosity","leadership","problem_solving","resiliency","motivation","teamwork","tolerate_ambiguity"})
     * @Serializer\Type("array<string>")
     */
    protected $softSkills;

    /**
     * Examples of soft skills.
     *
     * NOTE: this was used very briefly, and I believe removed before launch...
     *
     * @deprecated
     *
     * @MongoDB\Collection
     * @Assert\All({
     *  @Assert\NotBlank,
     *  @Assert\Type("string"),
     *  @Assert\Length(max=150)
     * })
     * @Serializer\Type("array<string>")
     */
    protected $softSkillExamples;

    /**
     * List of relevant domain skills
     *
     * @MongoDB\EmbedOne(targetDocument="DomainSkills")
     * @Assert\Valid
     * @Serializer\Type("GPS\AppBundle\Document\Candidate\DomainSkills")
     */
    protected $domainSkills;

    /**
     * List of hobbies.
     *
     * @MongoDB\Collection
     * @Assert\All({
     *  @Assert\NotBlank,
     *  @Assert\Type("string"),
     *  @Assert\Length(max=150)
     * })
     * @Serializer\Type("array<string>")
     */
    protected $hobbies;

    /**
     * Affiliations with any membership organizations.
     *
     * @MongoDB\EmbedMany(targetDocument="OrganizationAffiliation")
     * @Assert\Valid(traverse=true)
     * @Serializer\Type("ArrayCollection<GPS\AppBundle\Document\Candidate\OrganizationAffiliation>")
     */
    protected $organizations;

    /**
     * Rankings for ideals in a potential employer
     *
     * @MongoDB\Collection
     * @Assert\Count(max=3)
     * @Assert\Choice(multiple=true, choices={"accountability","creativity","customer_satisfaction","diversity","integrity","meritocracy","professional_growth","productivity","quality","family"})
     * @Serializer\Type("array<string>")
     */
    protected $employerIdeals;

    /**
     * List of top character traits, ordered by importance
     *
     * @MongoDB\Collection
     * @Assert\Count(max=5)
     * @Assert\Choice(multiple=true, choices={"ambitious","analytical","collaborative","competitive","confident","creative","dependable","detail_oriented","enthusiastic","entrepreneurial","flexible","hard_working","independent","logical","loyal","organized","handles_pressure","risk_taker","self_aware","self_starter"})
     * @Serializer\Type("array<string>")
     */
    protected $characterTraits;

    /**
     * Characteristics of the candidate's ideal job.
     *
     * @MongoDB\EmbedOne(targetDocument="IdealJob")
     * @Assert\Valid
     * @Serializer\Type("GPS\AppBundle\Document\Candidate\IdealJob")
     */
    protected $idealJob;

    /**
     * undocumented variable
     *
     * @MongoDB\EmbedMany(targetDocument="Award")
     * @Assert\Valid(traverse=true)
     * @Serializer\Type("ArrayCollection<GPS\AppBundle\Document\Candidate\Award>")
     */
    protected $awards;

    /**
     * undocumented variable
     *
     * @MongoDB\EmbedMany(targetDocument="Certification")
     * @Assert\Valid(traverse=true)
     * @Serializer\Type("ArrayCollection<GPS\AppBundle\Document\Candidate\Certification>")
     */
    protected $certifications;

    /**
     * undocumented variable
     *
     * @MongoDB\EmbedMany(targetDocument="AcademicOrgAffiliation")
     * @Assert\Valid(traverse=true)
     * @Serializer\Type("ArrayCollection<GPS\AppBundle\Document\Candidate\AcademicOrgAffiliation>")
     */
    protected $academicOrganizations;
    
    /**
     * Calculated from timeline, the max range
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Serializer\Type("integer")
     */
    protected $professionalHistoryMonths;
    
    /**
     * Calculated from timeline, taking into account gaps in history.
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Serializer\Type("integer")
     */
    protected $professionalHistoryMonthsWithGaps;
        
    /**
     * Time the certification was last modified.
     *
     * @MongoDB\Date
     * @Assert\DateTime
     * @Serializer\Type("DateTime<'U'>")
     * @Serializer\ReadOnly
     */
    protected $lastModified;
    
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
    
    public function setSerializeCompleteness($bool)
    {
        if ($timeline = $this->getTimeline()) {
            foreach ($timeline as $evt) {
                $evt->setSerializeCompleteness($bool);
            }
        }
    }
    
    /**
     * Computes completeness and stores it internally.
     */
    public function computeCompleteness()
    {
        $data = $this->getCompleteness();
        
        $c = ProfileCompleteness::createFromArray([
            'lastUpdated' => new \DateTime(),
            'totalItems' => $data['totalItems'],
            'completeItems' => $data['completedItems'],
            'sectionStatus' => $data['sections'],
            'percentCompleted' => $data['percentage']
        ]);
        
        if (!$status = $this->getProfileStatus()) {
            $status = new ProfileStatus();
            $this->setProfileStatus($status);
        }
        
        $this->getProfileStatus()->setCompleteness($c);
    }
    
    /**
     * Fragile, gross, and not entirely accurate... but it is what it is.
     *
     * Computes and returns map of metrics related to profile completeness.
     */
    public function getCompleteness()
    {
        $total = 0;
        $completed = 0;
        $map = [
            'totalItems' => &$total,
            'completedItems' => &$completed,
            'percentage' => 0,
            'sections' => [
                'shortForm' => false,
                'experienceAbroad' => false,
                'languages' => false,
                'workHistory' => false,
                'idealJob' => false,
                'idealJobPreferences' => false,
                'idealJobEmployerIdeals' => false,
                'educationHistory' => false,
                'awards' => false,
                'academicOrgs' => false,
                'hardSkills' => false,
                'softSkills' => false,
                'domainSkills' => false,
                'hobbies' => false,
                'membershipOrgs' => false,
                'characterTraits' => false,
            ]
        ];
        
        // check for submitted short form
        $total++;
        if ($this->getShortForm() && $this->getShortForm()->getCompleted()) {
            $completed++;
            $map['sections']['shortForm'] = true;
        }
        
        // check experience abroad
        if ($this->getCountries() && count($this->getCountries()) > 0) {
            $sectionComplete = true;
            foreach ($this->getCountries() as $country) {
                $total++;
                if ($country->isComplete()) {
                    $completed++;
                } else {
                    $sectionComplete = false;
                }
            }
        } else {
            $total++;
            $sectionComplete = false;
        }
        if ($sectionComplete) { $map['sections']['experienceAbroad'] = true; }
        
        // check languages
        if ($this->getLanguages() && count($this->getLanguages()) > 0) {
            $sectionComplete = true;
            foreach($this->getLanguages() as $lang) {
                if (!$lang->getNativeLikeFluency()) {
                    $total++;
                    if ($lang->isComplete()) {
                        $completed++;
                    } else {
                        $sectionComplete = false;
                    }
                }
            }
        } else {
            $total++;
            $sectionComplete = false;
        }
        if ($sectionComplete) { $map['sections']['languages'] = true; }
        
        // check work history
        $sectionComplete = true;
        $hasAnyEvents = false;
        $tl = $this->getTimeline();
        if ($tl) {
            foreach ($tl->toArray() as $event) {
                if (in_array($event::TYPE, ['job','volunteer','research','military'])) {
                    $hasAnyEvents = true;
                    $total++;
                    if ($event->isComplete()) {
                        $completed++;
                    } else {
                        $sectionComplete = false;
                    }
                }
            }
        }
        if (!$hasAnyEvents) {
            $total++;
            $sectionComplete = false;
        }
        if ($sectionComplete) { $map['sections']['workHistory'] = true; }
        
        // check idealJobs
        $sectionComplete = true;
        $total++;
        if ($this->getIdealJob() && $this->getIdealJob()->isComplete()) {
            $completed++;
        } else {
            $sectionComplete = false;
        }
        if ($sectionComplete) { $map['sections']['idealJob'] = true; }
        
        // check ideal job preferences
        $sectionComplete = true;
        $total++;
        if ($this->getIdealJob() && $this->getIdealJob()->getPreferences() && $this->getIdealJob()->getPreferences()->isComplete()) {
            $completed++;
        } else {
            $sectionComplete = false;
        }
        if ($sectionComplete) { $map['sections']['idealJobPreferences'] = true; }
        
        
        // check employer ideals
        $total++;
        $sectionComplete = true;
        if ($this->getEmployerIdeals() && count($this->getEmployerIdeals()) == 3) {
            $completed++;
        } else {
            $sectionComplete = false;
        }
        if ($sectionComplete) { $map['sections']['idealJobEmployerIdeals'] = true; }

        // check education history
        $sectionComplete = true;
        $hasAnyEvents = false;
        if ($tl) {
            foreach ($tl->toArray() as $event) {
                if (in_array($event::TYPE, ['university','study_abroad','language_acquisition'])) {
                    $hasAnyEvents = true;
                    $total++;
                    if ($event->isComplete()) {
                        $completed++;
                    } else {
                        $sectionComplete = false;
                    }
                }
            }
        }
        if (!$hasAnyEvents) {
            $total++;
            $sectionComplete = false;
        }
        if ($sectionComplete) { $map['sections']['educationHistory'] = true; }
        
        // check honors/awards
        if ($this->getAwards() && count($this->getAwards()) > 0) {
            $sectionComplete = true;
            foreach ($this->getAwards() as $award) {
                $total++;
                if ($award->isComplete()) {
                    $completed++;
                } else {
                    $sectionComplete = false;
                }
            }
        } else {
            $total++;
            $sectionComplete = false;
        }
        if ($sectionComplete) { $map['sections']['awards'] = true; }

        // check academic orgs
        if ($this->getAcademicOrganizations() && count($this->getAcademicOrganizations()) > 0) {
            $sectionComplete = true;
            foreach ($this->getAcademicOrganizations() as $org) {
                $total++;
                if ($org->isComplete()) {
                    $completed++;
                } else {
                    $sectionComplete = false;
                }
            }
        } else {
            $total++;
            $sectionComplete = false;
        }
        if ($sectionComplete) { $map['sections']['academicOrgs'] = true; }
        
        // check soft skills
        $sectionComplete = true;
        $total++;
        if ($this->getSoftSkills() && count($this->getSoftSkills()) == 5) {
            $completed++;
        } else {
            $sectionComplete = false;
        }
        if ($sectionComplete) { $map['sections']['softSkills'] = true; }
        
        // check hard skills
        $sectionComplete = true;
        $total++;
        if ($this->getHardSkills() && $this->getHardSkills()->isComplete()) {
            $completed++;
        } else {
            $sectionComplete = false;
        }
        if ($sectionComplete) { $map['sections']['hardSkills'] = true; }
        
        // check domain skills
        $sectionComplete = true;
        $total++;
        if ($this->getDomainSkills() && $this->getDomainSkills()->isComplete()) {
            $completed++;
        } else {
            $sectionComplete = false;
        }
        if ($sectionComplete) { $map['sections']['domainSkills'] = true; }
        
        // check hobbies
        $sectionComplete = true;
        $total++;
        if ($this->getHobbies() && count($this->getHobbies())) {
            $completed++;
        } else {
            $sectionComplete = false;
        }
        if ($sectionComplete) { $map['sections']['hobbies'] = true; }
        
        // check memberships organizations
        if ($this->getOrganizations() && count($this->getOrganizations()) > 0) {
            $sectionComplete = true;
            foreach ($this->getOrganizations() as $org) {
                $total++;
                if ($org->isComplete()) {
                    $completed++;
                } else {
                    $sectionComplete = false;
                }
            }
        } else {
            $total++;
            $sectionComplete = false;
        }
        if ($sectionComplete) { $map['sections']['membershipOrgs'] = true; }
        
        // check character traits
        $total++;
        if ($this->getCharacterTraits() && count($this->getCharacterTraits()) == 5) {
            $completed++;
            $map['sections']['characterTraits'] = true;
        }
        
        // done, return the map of completeness
        $map['percentage'] = ceil($map['completedItems'] / $map['totalItems'] * 100);
        return $map;
    }
    
    public function computeProfessionalExperience()
    {
        $totals = $this->getTimelineProfessionalExperience();
        $this->professionalHistoryMonths = ceil($totals['total'] / 60 / 60 / 24 / 30);
        $this->professionalHistoryMonthsWithGaps = ceil($totals['gaps'] / 60 / 60 / 24 / 30);
    }
    
    // TODO: consider moving this logic to the indexer
    public function getTimelineProfessionalExperience()
    {
        $compressed = [];
        $now = new \DateTime('now');
        $durations = [];
        $totals = [
            'gaps' => 0,
            'total' => 0
        ];
        
        $tl = $this->getTimeline();
        if (!$tl) {
            return $totals;
        }
        
        // we only care about professional experiences here, trying to
        // get a rough measure of how much time they have been in the workforce
        
        foreach($tl->toArray() as $evt) {
            if (
                in_array($evt->getType(),['job','volunteer','research','military'])
                &&
                $dur = $evt->getDuration()
            ) {
                if ($dur->getStart()) {
                    $durations[] = $dur;
                }
            }
        }
        
        // sort by start date
        $this->sortDurations($durations);        

        // check each duration from the timeline
        foreach ($durations as $dur) {
            // check against compressed dates, expand an existing one or adding new
            $overlapped = false;
            foreach ($compressed as $existing) {
                $existingStart = $existing->getStart()->getTimestamp();
                $existingEnd = $existing->getEnd()->getTimestamp();
                $currentStart = $dur->getStart()->getTimestamp();
                $currentEndDate = $dur->getEnd() ? $dur->getEnd() : new \DateTime('now');
                $currentEnd = $currentEndDate->getTimestamp();
                
                // did it start between an existing duration start/end?
                if ($currentStart >= $existingStart && $currentStart <= $existingEnd) {
                    $overlapped = true;
                }
                
                // did it end between an existing duration start/end?
                if ($currentEnd >= $existingStart && $currentEnd <= $existingEnd) {
                    $overlapped = true;
                }
                
                // does it encompass entirely an existing duration? Meaning, did it
                // start before AND end after
                if ($currentStart <= $existingStart && $currentEnd >= $existingEnd) {
                    $overlapped = true;
                }
                
                // if it overlapped, immediately modify the existing duration by
                // expanding its bounds, if applicable, and skip to the next 
                // duration that should be processed
                if ($overlapped) {
                    if ($currentStart < $existingStart) {
                        $existing->setStart($dur->getStart());
                    }

                    if ($currentEnd > $existingEnd) {
                        $existing->setEnd($currentEndDate);
                    }
                    
                    continue 2;
                }
            }
            
            // if we got this far, this date range did not overlap
            // an existing date range, so add it as a new compressed date range
            // for future comparisons - note that we're copying the data to ensure
            // that actual existing date ranges aren't modified directly... this
            // was the source of a previous bug
            $range = new \GPS\AppBundle\Document\DateRange();
            $newStart = new \DateTime();
            $newStart->setTimestamp($dur->getStart()->getTimestamp());
            $range->setStart($newStart);
            $range->setEnd($dur->getEnd() ? $dur->getEnd() : new \DateTime('now'));
            $compressed[] = $range;
        }
        
        //now compute the compressed ranges, one including gaps, one not
        $this->sortDurations($compressed);
        $count = count($compressed);
        
        if ($count == 0) {
            return $totals;
        }
        
        $totals['total'] = $compressed[$count - 1]->getEnd()->getTimestamp() - $compressed[0]->getStart()->getTimestamp();
        
        foreach ($compressed as $range) {
            $totals['gaps'] += $range->getEnd()->getTimestamp() - $range->getStart()->getTimestamp();
        }
        
        return $totals;
    }
    
    public function getTimelineByTypes($types = [])
    {
        $items = [];
        $timeline = $this->getTimeline();
        
        if ($timeline && count($timeline) > 0) {
            foreach ($timeline as $event) {
                if (in_array($event->getType(), $types)) {
                    $items[] = $event;
                }
            }
        }
        
        return $items;
    }
    
    private function sortDurations(array &$durations)
    {
        usort($durations, function($a, $b) {
            $startA = $a->getStart()->getTimestamp();
            $startB = $b->getStart()->getTimestamp();
            
            if ($startA == $startB) {
                return 0;
            }
            
            return ($startA < $startB) ? -1 : 1;
        });
    }
}
