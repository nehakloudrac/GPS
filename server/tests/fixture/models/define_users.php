<?php

use League\FactoryMuffin\Faker\Facade as F;
use GPS\Popov\Facade as Factory;
use Values as V;
use Helpers as H;
use GPS\AppBundle\Document as Doc;

function gps_define_user_fixtures($fm)
{
    // Generating the User pool triggers candidate profile generation
    $fm->definePool('User:GPS\AppBundle\Document\TestUser', 100)->setAttrs([
        // creating the ID here so that we can dump the fixtures and have them work
        // as expected in the indexer
        'id' => function() { return new \MongoId(); },
        'firstName' => F::firstName(),
        'lastName' => F::lastName(),
        'email' => F::unique()->email(),
        'phone' => F::optional()->numberBetween(1000000000, 9999999999),
        'gender' => F::optional()->randomElement(V::$genders),
        'password' => 'password',
        'isEnabled' => true,
        'isVerified' => true,
        'roles' => ['ROLE_USER'],
        'languages' => H::between(1, 2, F::randomElement(V::$langCodes)),
        'citizenship' => H::between(1, 2, F::randomElement(V::$countryCodes)),
        'usWorkAuthorization' => function ($obj) { return in_array('US', $obj->getCitizenship()) ? H::run(F::optional()->randomElement(V::$usWorkAuths)) : null; },
        'usSecurityClearance' => function ($obj) { return in_array('US', $obj->getCitizenship()) ? H::run(F::optional()->randomElement(V::$usSecClearances)) : null; },
        'currentJobStatus' => F::randomElement(V::$jobStatuses),
        'dateCreated' => new \DateTime('now'),
        'lastModified' => new \DateTime('now'),
        'institutionReferrer' => F::optional()->randomElement(V::$instReferrers),
        'referralMediumChoice' => F::optional()->randomElement(V::$referralMediums),
        'referralMediumOther' => function($user) {
            if ('other' == $user->getReferralMediumChoice()) {
                return H::run(F::optional()->sentence());
            }
        },
        'address' => Factory::create('Address'),
        'preferences' => Factory::create('AccountPrefs'),
        'status' => Factory::create('UserStatus'),
        'candidateProfile' => Factory::create('Candidate\Profile'),    //Note: at some point this becomes optional
    ]);
    
    $fm->definePool('Address:GPS\AppBundle\Document\Address')->setAttrs([
        'countryCode' => F::randomElement(V::$countryCodes),
        'city' => F::city(),
        'territory' => function ($obj) { return "US" == $obj->getCountryCode() ? H::run(F::optional(0.75)->stateAbbr()) : null; }
    ]);
    
    $fm->definePool('AccountPrefs:GPS\AppBundle\Document\AccountPreferences')->setAttrs([
        'allowGravatar' => F::boolean(),
        'allowProductFeatureEmails' => F::boolean(),
        'allowProfileInterestEmails' => F::boolean(),
        'allowProfileHealthEmails' => F::boolean(),
    ]);
    
    $fm->definePool('UserStatus:GPS\AppBundle\Document\UserStatus')->setAttrs([
        'seenDashboardTutorial' => F::boolean(),
        'seenProfileViewTutorial' => F::boolean(),
        'seenProfileEditTutorial' => F::boolean(),
    ]);
    
    return $fm;
}