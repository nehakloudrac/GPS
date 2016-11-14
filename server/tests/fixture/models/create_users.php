<?php

use League\FactoryMuffin\Faker\Facade as F;
use GPS\Popov\Facade as Factory;
use Values as V;
use Helpers as H;
use GPS\AppBundle\Document as Doc;

// These are hard coded users that are referenced in some specific tests

function gps_create_user_fixtures($fm)
{
    $fm->create('User', [
        'id' => 'aaaaaaaaaaaaaaaaaaaaaaaa',
        'email' => 'superadmin@example.com',
        'password' => 'superadmin',
        'roles' => ['ROLE_SUPER_ADMIN'],
        'candidateProfile' => Factory::create('Candidate\Profile', [
            'id' => 'aaaaaaaaaaaaaaaaaaaaaaaa'
        ])
    ]);

    $fm->create('User', [
        'id' => 'bbbbbbbbbbbbbbbbbbbbbbbb',
        'email' => 'admin@example.com',
        'password' => 'admin',
        'roles' => ['ROLE_ADMIN'],
        'candidateProfile' => Factory::create('Candidate\Profile', [
            'id' => 'bbbbbbbbbbbbbbbbbbbbbbbb'
        ])
    ]);

    $fm->create('User', [
        'id' => 'cccccccccccccccccccccccc',
        'email' => 'user@example.com',
        'firstName' => 'Foobert',
        'lastName' => 'Bartleby',
        'password' => 'user',
        'roles' => ['ROLE_USER'],
        'isVerified' => false,
        'preferences' => Factory::create('AccountPrefs', [
            'allowGravatar' => true,
            'allowProfileHealthEmails' => true,
        ]),
        'candidateProfile' => Factory::create('Candidate\Profile', [
            'id' => 'cccccccccccccccccccccccc',
            'shortForm' => null,
            'countries' => null,
            'languages' => null,
            'timeline' => null,
            'awards' => null,
            'certifications' => null,
            'academicOrganizations' => null,
            'organizations' => null,
            'hardSkills' => null,
            'domainSkills' => null,
            'hobbies' => null,
            'idealJob' => new Doc\Candidate\IdealJob(),
        ])
    ]);

    $fm->create('User', [
        'id' => 'dddddddddddddddddddddddd',
        'email' => 'user2@example.com',
        'password' => 'user2',
        'roles' => ['ROLE_USER'],
        'candidateProfile' => Factory::create('Candidate\Profile', [
            'id' => 'dddddddddddddddddddddddd'
        ])
    ]);
}
