<?php

use League\FactoryMuffin\Faker\Facade as F;
use GPS\Popov\Facade as Factory;
use Values as V;
use Helpers as H;
use GPS\AppBundle\Document as Doc;

function gps_define_candidate_profile_fixtures($fm)
{
    $createOneOf = function($choices) {
        return function () use($choices) {
            return H::run(Factory::create($choices[mt_rand(0, count($choices) - 1)]));
        };
    };

    $CERT_TYPES = [
        'Candidate\LanguageCertificationACTFL',
        'Candidate\LanguageCertificationALTE',
        'Candidate\LanguageCertificationCEFR',
        'Candidate\LanguageCertificationILR',
        'Candidate\LanguageCertificationOther',
    ];

    $TIMELINE_TYPES = [
        'Candidate\TimelineJob',
        'Candidate\TimelineLanguageAcquisition',
        'Candidate\TimelineMilitary',
        'Candidate\TimelineResearch',
        'Candidate\TimelineStudyAbroad',
        'Candidate\TimelineUniversity',
        'Candidate\TimelineVolunteer',
    ];
    
    $fm->definePool('Candidate\Profile:GPS\AppBundle\Document\Candidate\Profile')->setAttrs([
        // creating the ID here so that we can dump the fixtures and have them work
        // as expected in the indexer
        'id' => function() { return new \MongoId(); },
        'shortForm' => Factory::create('Candidate\ShortForm'),
        'countries' => H::collection(1, 4, Factory::create('Candidate\CountryExperience')),
        'languages' => H::collection(0, 4, Factory::create('Candidate\Language')),
        'timeline' => H::collection(0, 4, $createOneOf($TIMELINE_TYPES)),
        'hardSkills' => Factory::create('Candidate\HardSkills'),
        'softSkills' => H::between(0, 5, F::randomElement(V::$softSkills)),
        'domainSkills' => Factory::create('Candidate\DomainSkills'),
        'hobbies' => H::between(0, 3, F::sentence()),
        'organizations' => H::collection(0, 3, Factory::create('Candidate\OrganizationAffiliation')),
        'employerIdeals' => H::between(0, 3, F::randomElement(V::$employerIdeals)),
        'characterTraits' => H::unique(H::times(3, F::randomElement(V::$characterTraits))),
        'idealJob' => Factory::create('Candidate\IdealJob'),
        'awards' => H::collection(0, 3, Factory::create('Candidate\Award')),
        'certifications' => H::collection(0, 3, Factory::create('Candidate\Certification')),
        'academicOrganizations' => H::collection(0, 2, Factory::create('Candidate\AcademicOrgAffiliation')),
        'profileStatus' => Factory::create('Candidate\ProfileStatus'),
    ]);
    
    $fm->definePool('Candidate\ShortForm:GPS\AppBundle\Document\Candidate\ShortForm')->setAttrs([
        'preferredIndustries' => H::between(0, 5, F::randomElement(V::$industries)),
        'yearsWorkExperience' => F::optional()->numberBetween(0, 20),
        'lastPositionLevelHeld' => F::optional()->randomElement(V::$positionLevels),
        'degrees' => H::between(0, 2, F::randomElement(V::$degrees)),
        'countries' => H::between(0, 3, F::randomElement(V::$countryCodes)),
        'foreignLanguages' => H::between(0, 3, F::randomElement(V::$langCodes)),
        'completed' => F::boolean(),
        'dateModified' => new \DateTime('now'),
    ]);
    
    $fm->definePool('Candidate\ProfileStatus:GPS\AppBundle\Document\Candidate\ProfileStatus')->setAttrs([
        'sectionsSkipped' => F::optional()->randomElements(['ideal-job','countries','languages']),
        'sectionsSeen' => F::optional()->randomElements(['ideal-job','countries','languages','education','professional','personal']),
        'allSectionsSeenNotified' => F::optional()->boolean(),
    ]);
    
    $fm->definePool('Candidate\CountryExperience:GPS\AppBundle\Document\Candidate\CountryExperience')->setAttrs([
        'code' => F::randomElement(V::$countryCodes),
        'businessFamiliarity' => F::optional(0.8)->numberBetween(1, 6),
        'cultureFamiliarity' => F::optional(0.8)->numberBetween(1, 6),
        'purposes' => H::between(1, 3, F::randomElement(V::$countryPurposes)),
        'approximateNumberMonths' => F::optional()->numberBetween(1, 256),
        'dateLastVisit' => F::optional()->dateTimeThisCentury(),
        'cities' => H::between(0, 4, F::city()),
        'activities' => Factory::create("Candidate\CountryActivities"),
    ]);
    
    $caDef = $fm->definePool('Candidate\CountryActivities:GPS\AppBundle\Document\Candidate\CountryActivities');
    foreach (V::$countryActivityProperties as $field) {
        $caDef->setAttr($field, F::optional(0.8)->numberBetween(1, 6));
    }
    
    $fm->definePool('Candidate\Language:GPS\AppBundle\Document\Candidate\Language')->setAttrs([
        'code' => F::randomElement(V::$langCodes),
        'macroCode' => function($obj) { return "arb" === $obj->getCode() ? "ara" : null; },
        'nativeLikeFluency' => false,
        'currentUsageWork' => F::optional()->numberBetween(1, 7),
        'currentUsageSocial' => F::optional()->numberBetween(1, 7),
        'selfCertification' => Factory::create('Candidate\LanguageCertificationGPS'),
        'officialCertifications' => function($obj) use ($fm) {return [$fm->create('Candidate\LanguageCertificationACTFL')];}, //H::collection(0, 2, $createOneOf($CERT_TYPES)),
    ]);
    
    $fm->definePool('Candidate\LanguageCertificationGPS:GPS\AppBundle\Document\Candidate\LanguageCertificationGPS')->setAttrs([
        'peakProficiency' => F::optional(0.8)->dateTimeThisDecade(),
        'reading' => F::optional(0.8)->numberBetween(1, 6),
        'readingPeak' => F::optional(0.8)->numberBetween(1, 6),
        'writing' => F::optional(0.8)->numberBetween(1, 6),
        'writingPeak' => F::optional(0.8)->numberBetween(1, 6),
        'listening' => F::optional(0.8)->numberBetween(1, 6),
        'listeningPeak' => F::optional(0.8)->numberBetween(1, 6),
        'interacting' => F::optional(0.8)->numberBetween(1, 6),
        'interactingPeak' => F::optional(0.8)->numberBetween(1, 6),
        'social' => F::optional(0.8)->numberBetween(1, 6),
        'socialPeak' => F::optional(0.8)->numberBetween(1, 6),
    ]);
    
    $baseCertAttrs = [
        'date' => F::optional(0.8)->dateTimeThisDecade(),
        // TODO: change to use InstitutionReference at some point...
        'institution' => F::optional()->sentence(4),
        'test' => 'other',
        'testName' => F::optional()->sentence(2),
    ];
    $fm->definePool('Candidate\LanguageCertificationACTFL:GPS\AppBundle\Document\Candidate\LanguageCertificationACTFL')->setAttrs(array_merge($baseCertAttrs, [
        'test' => 'aappl',
        'testName' => "ACTFL/AAPPL",
        'reading' => F::optional(0.9)->numberBetween(1, 10),
        'writing' => F::optional(0.9)->numberBetween(1, 10),
        'listening' => F::optional(0.9)->numberBetween(1, 10),
        'speaking' => F::optional(0.9)->numberBetween(1, 10),
    ]));
    $fm->definePool('Candidate\LanguageCertificationALTE:GPS\AppBundle\Document\Candidate\LanguageCertificationALTE')->setAttrs(array_merge($baseCertAttrs, [
        'reading' => F::optional(0.9)->numberBetween(1, 6),
        'writing' => F::optional(0.9)->numberBetween(1, 6),
        'listeningAndSpeaking' => F::optional(0.9)->numberBetween(1, 6),
    ]));
    $fm->definePool('Candidate\LanguageCertificationCEFR:GPS\AppBundle\Document\Candidate\LanguageCertificationCEFR')->setAttrs(array_merge($baseCertAttrs, [
        'test' => 'telc',
        'testName' => "TELC",
        'reading' => F::optional()->numberBetween(1, 6),
        'writing' => F::optional()->numberBetween(1, 6),
        'listening' => F::optional()->numberBetween(1, 6),
        'spokenInteraction' => F::optional()->numberBetween(1, 6),
        'spokenProduction' => F::optional()->numberBetween(1, 6),
    ]));
    $fm->definePool('Candidate\LanguageCertificationILR:GPS\AppBundle\Document\Candidate\LanguageCertificationILR')->setAttrs(array_merge($baseCertAttrs, [
        'test' => 'dlpt',
        'testName' => "DLPT",
        'reading' => F::optional()->numberBetween(1, 11),
        'writing' => F::optional()->numberBetween(1, 11),
        'listening' => F::optional()->numberBetween(1, 11),
        'speaking' => F::optional()->numberBetween(1, 11),
    ]));
    $fm->definePool('Candidate\LanguageCertificationOther:GPS\AppBundle\Document\Candidate\LanguageCertificationOther')->setAttrs(array_merge($baseCertAttrs, [
        'reading' => F::optional(0.9)->numberBetween(1, 6),
        'writing' => F::optional(0.9)->numberBetween(1, 6),
        'listening' => F::optional(0.9)->numberBetween(1, 6),
        'speaking' => F::optional(0.9)->numberBetween(1, 6),
    ]));
    
    $baseTimelineAttrs = [
        'institution' => Factory::create('Candidate\InstitutionReference'),
        'duration' => Factory::create('DateRange'),
        'description' => F::sentence(10),
        'countryRefs' => H::between(0, 3, F::randomElement(V::$countryCodes)),
        'languageRefs' => H::between(0, 3, F::randomElement(V::$langCodes)),
    ];
    $fm->definePool('Candidate\TimelineJob:GPS\AppBundle\Document\Candidate\TimelineJob')->setAttrs(array_merge($baseTimelineAttrs, [
        'title' => F::optional(0.9)->sentence(4),
        'department' => F::optional(0.9)->sentence(3),
        'positionLevel' => F::optional(0.9)->randomElement(V::$positionLevels),
        'salary' => F::optional(0.9)->numberBetween(10000,200000),
        'hourlyRate' => F::optional(0.9)->numberBetween(14, 500),
        'status' => F::optional(0.9)->randomElement(V::$jobTypes),
    ]));
    $fm->definePool('Candidate\TimelineLanguageAcquisition:GPS\AppBundle\Document\Candidate\TimelineLanguageAcquisition')->setAttrs(array_merge($baseTimelineAttrs, [
        'description' => null,
        'source' => F::optional(0.9)->randomElement(V::$langSources),
        'hoursPerWeek' => F::optional(0.4)->numberBetween(0, 168)
    ]));
    $fm->definePool('Candidate\TimelineMilitary:GPS\AppBundle\Document\Candidate\TimelineMilitary')->setAttrs(array_merge($baseTimelineAttrs, [
        'branch' => F::optional(0.9)->randomElement(V::$milBranches),
        'unit' => F::optional(0.3)->sentence(3),
        'operation' => F::optional(0.3)->sentence(3),
        'occupationalSpecialties' => H::between(0, 3, F::sentence(2)),
        'geographicSpecialty' => F::optional(0.7)->randomElement(V::$milGeoSpecs),
        'rankType' => F::randomElement(V::$milRankTypes),
        'rankValue' => F::numberBetween(1, 9),
        'rankLevel' => F::numberBetween(1, 9),
    ]));
    $fm->definePool('Candidate\TimelineResearch:GPS\AppBundle\Document\Candidate\TimelineResearch')->setAttrs(array_merge($baseTimelineAttrs, [
        'sponsoringProgram' => F::sentence(3),
        'hostingInstitution' => Factory::create('Candidate\InstitutionReference'),
        'level' => F::optional(0.9)->randomElement(V::$researchLevels),
        'hoursPerWeek' => F::optional()->numberBetween(1, 168),
        'subject' => F::optional(0.9)->randomElement(V::$academicSubjects),
    ]));
    $fm->definePool('Candidate\TimelineStudyAbroad:GPS\AppBundle\Document\Candidate\TimelineStudyAbroad')->setAttrs(array_merge($baseTimelineAttrs, [
        'description' => null,
        'programName' => F::sentence(3),
        'hostingInstitution' => Factory::create('Candidate\InstitutionReference'),
        'weeklyActivityHours' => F::optional()->numberBetween(1, 168),
        'classTimePercentLocalLang' => F::optional()->numberBetween(1, 100),
    ]));
    $fm->definePool('Candidate\TimelineUniversity:GPS\AppBundle\Document\Candidate\TimelineUniversity')->setAttrs(array_merge($baseTimelineAttrs, [
        'concentrations' => H::collection(1, 2, Factory::create('Candidate\UniversityConcentration')),
        'gpa' => F::optional(0.9)->randomFloat(2, 0, 4),
        'degrees' => F::optional(0.9)->randomElements(V::$degrees, H::run(F::numberBetween(1,2))),
    ]));
    $fm->definePool('Candidate\TimelineVolunteer:GPS\AppBundle\Document\Candidate\TimelineVolunteer')->setAttrs(array_merge($baseTimelineAttrs, [
        'status' => F::randomElement(V::$volunteerStatus),
        'sponsoringInstitution' => Factory::create('Candidate\InstitutionReference'),
    ]));

    $fm->definePool('DateRange:GPS\AppBundle\Document\DateRange')->setAttrs([
        'start' => F::optional(0.9)->dateTimeThisCentury('-5 years'),
        'end' => F::optional(0.75)->dateTimeThisYear(),
    ]);
    
    $fm->definePool('Candidate\InstitutionReference:GPS\AppBundle\Document\Candidate\InstitutionReference')->setAttrs([
        'address' => Factory::create('Address'),
        'name' => F::optional(0.9)->sentence(4),
        'url' => F::optional(0.3)->url(),
        'type' => F::optional(0.9)->randomElement(V::$instTypes),
        'industries' => F::optional(0.9)->randomElements(V::$industries, H::run(F::numberBetween(1, 3)))
    ]);
        
    $fm->definePool('Candidate\UniversityConcentration:GPS\AppBundle\Document\Candidate\UniversityConcentration')->setAttrs([
        'type' => F::randomElement(V::$concentrationTypes),
        'fieldName' => F::randomElement(V::$academicSubjects),
        'intlConcentration' => F::boolean(),
    ]);

    $hsDef = $fm->definePool('Candidate\HardSkills:GPS\AppBundle\Document\Candidate\HardSkills');
    foreach(V::$hardSkillProperties as $field) {
        $hsDef->setAttr($field, F::optional(0.75)->numberBetween(0, 5));
    }
    
    $fm->definePool('Candidate\DomainSkills:GPS\AppBundle\Document\Candidate\DomainSkills')->setAttrs([
        'expert' => F::optional(0.8)->words(5),
        'advanced' => F::optional(0.8)->words(10),
        'proficient' => F::optional(0.8)->words(15),
    ]);
    
    $fm->definePool('Candidate\OrganizationAffiliation:GPS\AppBundle\Document\Candidate\OrganizationAffiliation')->setAttrs([
        'institution' => Factory::create('Candidate\InstitutionReference'),
        'level' => F::optional(0.9)->numberBetween(1, 6),
    ]);
    
    $fm->definePool('Candidate\IdealJob:GPS\AppBundle\Document\Candidate\IdealJob')->setAttrs([
        'jobTypes' => H::unique(H::between(0, 3, F::randomElement(V::$idealJobTypes))),
        'employerTypes' => H::unique(H::between(0, 3, F::randomElement(V::$idealJobEmployerTypes))),
        'industries' => H::between(0, 3, F::randomElement(V::$industries)),
        'locationsUSA' => H::times(3, F::stateAbbr()),
        'locationsAbroad' => H::between(0, 3, F::randomElement(V::$countryCodes)),
        'desiredDate' => Factory::create('DateRange'),
        'availableImmediately' => F::optional()->boolean(),
        'hoursPerWeek' => F::optional()->numberBetween(1, 60),
        'payStatus' => F::optional()->randomElement([['paid'],['paid','unpaid']], 1),
        'minSalary' => F::optional()->numberBetween(10000, 200000),
        'minHourlyRate' => F::optional()->numberBetween(14, 500),
        'minDailyRate' => F::optional()->numberBetween(500, 2000),
        'minWeeklyRate' => F::optional()->numberBetween(800, 10000),
        'minMonthlyRate' => F::optional()->numberBetween(2000, 20000),
        'willingnessToTravel' => F::optional()->randomElement(V::$willingnessToTravel),
        'willingToTravelOverseas' => F::optional()->boolean(),
        'availability' => H::collection(0, 3, Factory::create('Candidate\ProjectAvailability')),
        'preferences' => Factory::create('Candidate\IdealJobPreferences'),
    ]);

    $fm->definePool('Candidate\ProjectAvailability:GPS\AppBundle\Document\Candidate\ProjectAvailability')->setAttrs([
        'duration' => Factory::create('DateRange'),
        'travelInternational' => F::optional()->boolean(),
        'travelDomestic' => F::optional()->boolean(),
    ]);

    $ijpDef = $fm->definePool('Candidate\IdealJobPreferences:GPS\AppBundle\Document\Candidate\IdealJobPreferences');
    foreach (V::$idealJobPrefProperties as $field) {
        $ijpDef->setAttr($field, F::optional()->randomFloat(2, 0, 1));
    }
    
    $fm->definePool('Candidate\Award:GPS\AppBundle\Document\Candidate\Award')->setAttrs([
        'date' => F::dateTimeThisCentury(),
        'name' => F::sentence(4),
    ]);
    
    $fm->definePool('Candidate\Certification:GPS\AppBundle\Document\Candidate\Certification')->setAttrs([
        'name' => F::words(2, true),
        'organization' => F::words(2, true),
        'certId' => F::optional()->word(),
        'duration' => Factory::create('DateRange'),
    ]);
    
    $fm->definePool('Candidate\AcademicOrgAffiliation:GPS\AppBundle\Document\Candidate\AcademicOrgAffiliation')->setAttrs([
        'duration' => Factory::create('DateRange'),
        'name' => F::sentence(2),
    ]);
    
    return $fm;
}