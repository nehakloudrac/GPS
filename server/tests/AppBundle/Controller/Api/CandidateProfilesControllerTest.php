<?php

namespace GPS\Tests\AppBundle\Controller\Api;

use GPS\AppBundle\Testing;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CandidateProfilesControllerTest extends Testing\ControllerTest
{
    use Testing\ResetFixturesHelperTrait;
    
    public function testRequireProfileOwnership()
    {
        $c = $this->createAuthClient('user2@example.com', 'user2');

        // unauthed user is denied
        $c = static::createClient();
        $res = $this->call($c, 'GET', '/api/candidate-profiles/cccccccccccccccccccccccc');
        $this->assertSame(401, $res->getStatusCode());

        // auth user is denied access to other profile
        $c = $this->createAuthClient('user2@example.com', 'user2');
        $res = $this->call($c, 'GET', '/api/candidate-profiles/cccccccccccccccccccccccc');
        $this->assertSame(403, $res->getStatusCode());

        // user is granted access to own profile
        $c = $this->createAuthClient('user@example.com', 'user');
        $res = $this->call($c, 'GET', '/api/candidate-profiles/cccccccccccccccccccccccc');
        $this->assertSame(200, $res->getStatusCode());
        
        // admin user granted access to user
        // user is granted access to own profile
        $c = $this->createAuthClient('admin@example.com', 'admin');
        $res = $this->call($c, 'GET', '/api/candidate-profiles/cccccccccccccccccccccccc');
        $this->assertSame(403, $res->getStatusCode());
    }
    
    public function testGetProfileAction()
    {
        $json = $this->get('/');
        $this->assertSame($json['profile']['id'], 'cccccccccccccccccccccccc');
        $this->assertTrue(empty($json['profile']['shortForm']));
        $this->assertTrue(empty($json['profile']['countries']));
        $this->assertTrue(empty($json['profile']['languages']));
        $this->assertTrue(empty($json['profile']['timeline']));
        $this->assertTrue(empty($json['profile']['awards']));
        $this->assertTrue(empty($json['profile']['academicOrganizations']));
        $this->assertTrue(empty($json['profile']['organizations']));
        $this->assertTrue(empty($json['profile']['hardSkills']));
        $this->assertTrue(empty($json['profile']['domainSkills']));
        $this->assertTrue(isset($json['profile']['profileStatus']));
    }
    
    public function testPutAction()
    {
        $json = $this->get('/');
        $this->assertTrue(empty($json['profile']['hobbies']));
        $this->assertFalse(isset($json['profile']['idealJob']['industries']));
        
        $json = $this->put('/', [
            'hobbies' => ['foo','bar','baz'],
            'idealJob' => [
                'industries' => ['foo','bar','baz']
            ]
        ]);
        $this->assertSame(['foo','bar','baz'], $json['profile']['hobbies']);
        $this->assertSame(['foo','bar','baz'], $json['profile']['idealJob']['industries']);
        
        $json = $this->get('/');
        $this->assertSame(['foo','bar','baz'], $json['profile']['hobbies']);
        $this->assertSame(['foo','bar','baz'], $json['profile']['idealJob']['industries']);
    }
    
    public function testPutDeeplyNestedAction()
    {
        $json = $this->get('/');
        $this->assertTrue(empty($json['profile']['idealJob']['preferences']));
        
        $expected = [
            'idealJob' => [
                'preferences' => [
                    'workWithTeam' => 0.3
                ]
            ]
        ];
        $assertions = $this->assertions($expected);
        $json = $this->put('/', $expected);
        $assertions($json['profile']);
        $json = $this->get('/');
        $assertions($json['profile']);
        
        $this->assertSame(0.3, $json['profile']['idealJob']['preferences']['workWithTeam']);
    }
    
    public function testPostProfileImportAction()
    {
        $testFilePath = GPS_FIXTURE_PATH.'/EvanVillemez.pdf';
        $uploadedFile = new UploadedFile(
            $testFilePath,
            'EvanVillemez.pdf',
            'application/pdf',
            filesize($testFilePath)
        );

        $client = $this->createAuthClient('user@example.com', 'user');
        $client->request('POST', '/api/candidate-profiles/cccccccccccccccccccccccc/import-profile', [], ['file' => $uploadedFile], ['HTTPS' => true]);
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('PHP', $content['profile']['domainSkills']['expert'][0]);
        $this->assertEquals('rus', $content['profile']['shortForm']['foreignLanguages'][0]);
        $this->assertEquals(4, count($content['profile']['timeline']));
        $this->assertEquals(2, count($content['profile']['academicOrganizations']));

        // refetch to ensure it was persisted
        $client = $this->createAuthClient('user@example.com', 'user');
        $client->request('GET', '/api/candidate-profiles/cccccccccccccccccccccccc', [], [], ['HTTPS' => true]);
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('PHP', $content['profile']['domainSkills']['expert'][0]);
        $this->assertEquals('rus', $content['profile']['shortForm']['foreignLanguages'][0]);
        
    }
    
    public function testPutShortFormAction()
    {
        $json = $this->get('/');
        $this->assertTrue(empty($json['profile']['shortForm']));
        $this->assertSame(0, count($json['profile']['countries']));
        $this->assertSame(0, count($json['profile']['languages']));
        $expected = [
            'completed' => true,
            'preferredIndustries' => ['foo','bar','baz'],
            'yearsWorkExperience' => 23,
            'lastPositionLevelHeld' => 'cxo',
            'degrees' => ['bachelors', 'masters'],
            'countries' => ['US','RU'],
            'foreignLanguages' => ['eng','rus']
        ];
        
        $assertions = $this->assertions($expected, function($obj) {
            $this->assertTrue(isset($obj['dateModified']));
        });

        $json = $this->put('/short-form', $expected);
        $assertions($json['profile']['shortForm']);
        $assertions($json['form']);

        $json = $this->get('/');
        $assertions($json['profile']['shortForm']);
        $this->assertSame(2, count($json['profile']['countries']));
        $this->assertSame(2, count($json['profile']['languages']));
    }
        
    public function testPostTimelineEvent()
    {
        $json = $this->get('/');
        $this->assertTrue(empty($json['profile']['timeline']));
        
        // require type param
        $c = $this->req('POST', '/timeline', ['foo'=>'bar']);
        $this->assertSame(400, $c->getResponse()->getStatusCode());
        
        // common assertions to all types
        $common = [
            'duration' => [
                'start' => '78348439349',
                'end' => '232344435545'
            ],
            'institution' => [
                'name' => 'Acme Inc.',
                'address' => [
                    'countryCode' => 'US',
                    'city' => "Highlands",
                    'territory' => 'TX'
                ]
            ]
        ];
        
        // create job
        $expected = array_merge($common, [
            'type' => 'job',
            'title' => 'A title',
            'department' => 'Media',
            'positionLevel' => '',
            'salary' => 60000,
            'hourlyRate' => 128,
            'status' => 'part_time',
            'activities' => ['Did stuff.', 'And other stuff.']
        ]);
        $assertions = $this->assertions($expected, function($obj) {
            $this->assertTrue(isset($obj['hash']));
        });
        $json = $this->post('/timeline?type=job', $expected);
        $assertions($json['event']);
        $json = $this->get('/');
        $assertions($json['profile']['timeline'][0]);
        
        // create research
        $expected = array_merge($common, [
            'type' => 'research',
            'sponsoringProgram' => 'Program 1',
            'hostingInstitution' => [
                'name' => 'Host',
                'address' => [
                    'countryCode' => 'RU'
                ]
            ],
            'level' => 'grad_student',
            'hoursPerWeek' => 40,
            'subject' => 'Physics',
            'activities' => ['Did stuff.', 'And other stuff.']
        ]);
        $assertions = $this->assertions($expected, function($obj) {
            $this->assertTrue(isset($obj['hash']));
        });
        $json = $this->post('/timeline?type=research', $expected);
        $assertions($json['event']);
        $json = $this->get('/');
        $assertions($json['profile']['timeline'][1]);

        // create military
        $expected = array_merge($common, [
            'type' => 'military',
            'branch' => 'us_army',
            'unit' => '3rd SOG',
            'operation' => 'Desert Storm',
            'occupationalSpecialties' => ['foo','bar','baz'],
            'geographicSpecialty' => 'USCENTCOM',
            'rankType' => 'enlisted',
            'rankValue' => 9,
            'rankLevel' => 10,
            'activities' => ['Did stuff.', 'And other stuff.']
        ]);
        $assertions = $this->assertions($expected, function($obj) {
            $this->assertTrue(isset($obj['hash']));
        });
        $json = $this->post('/timeline?type=military', $expected);
        $assertions($json['event']);
        $json = $this->get('/');
        $assertions($json['profile']['timeline'][2]);
        
        // create volunteer
        $expected = array_merge($common, [
            'type' => 'volunteer',
            'status' => 'part_time',
            'sponsoringInstitution' => [
                'name' => 'Host',
                'address' => [
                    'countryCode' => 'RU'
                ]                
            ],
            'activities' => ['Did stuff.', 'And other stuff.']
        ]);
        $assertions = $this->assertions($expected, function($obj) {
            $this->assertTrue(isset($obj['hash']));
        });
        $json = $this->post('/timeline?type=volunteer', $expected);
        $assertions($json['event']);
        $json = $this->get('/');
        $assertions($json['profile']['timeline'][3]);
        
        // create study abroad
        $expected = array_merge($common, [
            'type' => 'study_abroad',
            'programName' => 'Blah blah',
            'hostingInstitution' => [
                'name' => 'Host',
                'address' => [
                    'countryCode' => 'RU'
                ]                
            ],
            'weeklyActivityHours' => 28,
            'classTimePercentLocalLang' => 50
        ]);
        $assertions = $this->assertions($expected, function($obj) {
            $this->assertTrue(isset($obj['hash']));
        });
        $json = $this->post('/timeline?type=study_abroad', $expected);
        $assertions($json['event']);
        $json = $this->get('/');
        $assertions($json['profile']['timeline'][4]);
        
        // create lang acquisition
        $expected = array_merge($common, [
            'type' => 'language_acquisition',
            'source' => 'school',
            'hoursPerWeek' => 15
        ]);
        $assertions = $this->assertions($expected, function($obj) {
            $this->assertTrue(isset($obj['hash']));
        });
        $json = $this->post('/timeline?type=language_acquisition', $expected);
        $assertions($json['event']);
        $json = $this->get('/');
        $assertions($json['profile']['timeline'][5]);
        
        // create university
        $expected = array_merge($common, [
            'type' => 'university',
            'gpa' => 3.25,
            'degrees' => ['bachelors','masters'],
            'intlCourse' => 'Foo',
            'concentrations' => [
                [
                    'type' => 'major',
                    'fieldName' => 'International Studies',
                    'intlConcentration' => true
                ],
                [
                    'type' => 'minor',
                    'fieldName' => 'Computer Science',
                    'meta' => [
                        'langs' => ['perl','c++','java']
                    ]
                ]
            ],
        ]);
        $assertions = $this->assertions($expected, function($obj) {
            $this->assertTrue(isset($obj['hash']));
        });
        $json = $this->post('/timeline?type=university', $expected);
        $assertions($json['event']);
        $json = $this->get('/');
        $assertions($json['profile']['timeline'][6]);

        $json = $this->get('/');
        $this->assertSame(7, count($json['profile']['timeline']));
    }
    
    public function testCreateTimelineEventWithNestedFirst()
    {
        $expected = [
            'type' => 'university',
            'concentrations' => [
                [
                    'type' => 'minor',
                    'fieldName' => 'Computer Science',
                    'meta' => [
                        'langs' => ['perl','c++','java']
                    ]
                ]
            ]
        ];
        $assertions = $this->assertions($expected, function($obj) {
            $this->assertTrue(isset($obj['hash']));
        });
        $json = $this->post('/timeline?type=university', $expected);
        $assertions($json['event']);
        $json = $this->get('/');
        $assertions($json['profile']['timeline'][0]);
    }
    
    public function testPutTimelineEvent()
    {
        $expected = [
            'type' => 'job',
            'title' => 'Foo'
        ];
        $assertions = $this->assertions($expected, function($obj) {
            $this->assertTrue(isset($obj['hash']));
        });
        $json = $this->post('/timeline?type=job', $expected);
        $assertions($json['event']);
        $id = $json['event']['hash'];
        $json = $this->get('/');
        $assertions($json['profile']['timeline'][0]);
        $expected = [
            'type' => 'job',
            'institution' => [
                'address' => [
                    'city' => 'Washington'
                ]
            ],
            'activities' => ['foo', 'bar', 'baz']
        ];
        $assertions = $this->assertions($expected, function($obj) {
            $this->assertTrue(isset($obj['hash']));
        });
        $json = $this->put('/timeline/'.$id, $expected);
        $assertions($json['event']);
        $json = $this->get('/');
        $assertions($json['profile']['timeline'][0]);
    }
    
    public function testCreateAndUpdateTimelineEventWithWeirdData()
    {
        // trying to simlulate current behavior from UI
        $expected = [
            'type' => 'job'
        ];
        $assertions = $this->assertions($expected, function($obj) {
            $this->assertTrue(isset($obj['hash']));
        });
        $json = $this->post('/timeline?type=job&type=job', $expected);
        $assertions($json['event']);
        $id = $json['event']['hash'];
        
        // update
        $expected = [
            'type' => 'job',
            'institution' => [
                'address' => [
                    'city' => 'Washington'
                ]
            ]
        ];
        $assertions = $this->assertions($expected, function($obj) {
            $this->assertTrue(isset($obj['hash']));
        });
        $json = $this->put('/timeline/'.$id, $expected);
        $assertions($json['event']);
        $json = $this->get('/');
        $assertions($json['profile']['timeline'][0]);

        $json = $this->put('/timeline/'.$id, ['institution'=> [], 'type' => 'job']);
        $json = $this->put('/timeline/'.$id, ['institution'=> ['address' => ['city' => 'wat']], 'type' => 'job']);
        $this->assertSame('wat', $json['event']['institution']['address']['city']);
    }
    
    public function testDeleteTimelineEvent()
    {
        $expected = [
            'type' => 'job',
            'title' => 'Foo'
        ];
        $assertions = $this->assertions($expected, function($obj) {
            $this->assertTrue(isset($obj['hash']));
        });
        $json = $this->post('/timeline?type=job', $expected);
        $assertions($json['event']);
        $id1 = $json['event']['hash'];
        $json = $this->post('/timeline?type=job', $expected);
        $assertions($json['event']);
        $id2 = $json['event']['hash'];
        $json = $this->get('/');        
        $this->assertSame(2, count($json['profile']['timeline']));
        
        // delete first event
        $json = $this->delete('/timeline/'.$id1);
        $json = $this->get('/');        
        $this->assertSame(1, count($json['profile']['timeline']));
        $this->assertSame($id2, $json['profile']['timeline'][0]['hash']);
    }
        
    public function testCountryActions()
    {
        $expected = [
            'name' => 'Germany',
            'code' => 'GE',
            'businessFamiliarity' => 2,
            'cultureFamiliarity' => 6,
            'purposes' => ['work','study','teaching'],
            'approximateNumberMonths' => 37,
            'dateLastVisit' => '234545645',
            'cities' => ["Hamburg", "Berlin"],
            'activities' => [
                'attendClasses' => 2,
                'attendClassesLocalLang' => 2,
                'modifyCurriculum' => 2,
                'modifyCurriculumLocalLang' => 2
            ],
        ];
        
        $assertions = $this->assertions($expected, function($obj) {
            $this->assertTrue(isset($obj['hash']));
        });
        
        $json = $this->get('/');
        $this->assertSame(0, count($json['profile']['countries']));
        $json = $this->post('/countries', $expected);
        $assertions($json['country']);
        $id = $json['country']['hash'];
        $json = $this->get('/');
        $this->assertSame(1, count($json['profile']['countries']));
        $assertions($json['profile']['countries'][0]);
        
        $updates = ['cities' => ['Frankfurt']];
        $expected = array_merge($expected, $updates);
        $assertions = $this->assertions($expected);
        $json = $this->put('/countries/'.$id, $updates);
        $assertions($json['country']);        
        $json = $this->get('/');
        $this->assertSame(1, count($json['profile']['countries']));
        $assertions($json['profile']['countries'][0]);
        
        $json = $this->delete('/countries/'.$id);
        $json = $this->get('/');
        $this->assertSame(0, count($json['profile']['countries']));
    }
    
    public function testLanguageActions()
    {
        $expected = [
            'code' => 'arb',
            'macroCode' => 'ara',
            'nativeLikeFluency' => false,
            'currentUsageWork' => 3,
            'currentUsageSocial' => 4,
            'selfCertification' => [
                'peakProficiency' => '2325234342434',
                'reading' => 3,
                'readingPeak' => 3,
                'writing' => 3,
                'writingPeak' => 3,
                'listening' => 3,
                'listeningPeak' => 3,
                'interacting' => 3,
                'interactingPeak' => 3,
                'social' => 3,
                'socialPeak' => 3
            ],
        ];
        
        $assertions = $this->assertions($expected, function($obj) {
            $this->assertTrue(isset($obj['hash']));
            $this->assertTrue(isset($obj['selfCertification']['lastModified']));
        });
        
        $json = $this->get('/');
        $this->assertSame(0, count($json['profile']['languages']));
        $json = $this->post('/languages', $expected);
        $assertions($json['language']);
        $id = $json['language']['hash'];
        $json = $this->get('/');
        $this->assertSame(1, count($json['profile']['languages']));
        $assertions($json['profile']['languages'][0]);
        
        $updates = [
            'currentUsageSocial' => 2,
            'currentUsageWork' => 5
        ];
        $expected = array_merge($expected, $updates);
        $assertions = $this->assertions($expected);
        $json = $this->put('/languages/'.$id, $updates);
        $assertions($json['language']);        
        $json = $this->get('/');
        $this->assertSame(1, count($json['profile']['languages']));
        $assertions($json['profile']['languages'][0]);
        
        $json = $this->delete('/languages/'.$id);
        $json = $this->get('/');
        $this->assertSame(0, count($json['profile']['languages']));
    }
    
    public function testLanguageCertificationActions()
    {
        // TODO - consider refactoring this somehow... PUTS to update
        // a cert basically do a replace, which is unlike most other
        // API routes        
        
        $lang = [
            'code' => 'arb',
            'macroCode' => 'ara',
            'nativeLikeFluency' => false
        ];
        
        $json = $this->post('/languages', $lang);
        $langId = $json['language']['hash'];
        $json = $this->get('/');
        $this->assertSame(1, count($json['profile']['languages']));
        $this->assertTrue(empty($json['profile']['languages'][0]['officialCertifications']));
        
        $expected = [
            'scale' => 'ilr',

            // common fields
            'date' => '129832478',
            'institution' => 'DLI',
            'test' => 'dlpt',
            'testName' => 'DLPT',
            
            // test specific fields
            'reading' => 5,
            'writing' => 5,
            'listening' => 5,
            'speaking' => 5
        ];
        $assertions = $this->assertions($expected, function ($obj) {
            $this->assertTrue(isset($obj['hash']));
        });
        
        $json = $this->post('/languages/'.$langId.'/certifications', $expected);
        $assertions($json['certification']);
        $id = $json['certification']['hash'];
        $json = $this->get('/');
        $this->assertSame(1, count($json['profile']['languages'][0]['officialCertifications']));
        $certId = $json['profile']['languages'][0]['officialCertifications'][0]['hash'];
        $assertions($json['profile']['languages'][0]['officialCertifications'][0]);
        
        $expected = [
            'scale' => 'ilr',
            'test' => 'dlpt',
            'reading' => 7,
            'writing' => 8
        ];
        $assertions = $this->assertions($expected);
        $json = $this->put('/languages/'.$langId.'/certifications/'.$certId, $expected);
        $assertions($json['certification']);
        $json = $this->get('/');
        $assertions($json['profile']['languages'][0]['officialCertifications'][0]);
        
        $json = $this->delete('/languages/'.$langId.'/certifications/'.$certId);
        $json = $this->get('/');
        $this->assertSame(0, count($json['profile']['languages'][0]['officialCertifications']));
    }
        
    public function testOrganizationActions()
    {
        $expected = [
            'level' => 3,
            'institution' => [
                'name' => "Foo Inc."
            ]
        ];
        
        $assertions = $this->assertions($expected, function($obj) {
            $this->assertTrue(isset($obj['hash']));
        });
        
        $json = $this->get('/');
        $this->assertSame(0, count($json['profile']['organizations']));
        $json = $this->post('/organizations', $expected);
        $assertions($json['organization']);
        $id = $json['organization']['hash'];
        $json = $this->get('/');
        $this->assertSame(1, count($json['profile']['organizations']));
        $assertions($json['profile']['organizations'][0]);
        
        $updates = [
            'institution' => [
                'name' => "Bar LLC"
            ]
        ];
        $expected = array_merge($expected, $updates);
        $assertions = $this->assertions($expected);
        $json = $this->put('/organizations/'.$id, $updates);
        $assertions($json['organization']);        
        $json = $this->get('/');
        $this->assertSame(1, count($json['profile']['organizations']));
        $assertions($json['profile']['organizations'][0]);
        
        $json = $this->delete('/organizations/'.$id);
        $json = $this->get('/');
        $this->assertSame(0, count($json['profile']['organizations']));
    }
    
    public function testProjectAvailabilityActions()
    {
        $expected = [
            'travelInternational' => true,
            'travelDomestic' => false,
            'duration' => [
                'start' => "1287234783",
                'end' => "19823782398"
            ]
        ];
        $assertions = $this->assertions($expected, function($obj) {
            $this->assertTrue(isset($obj['hash']));
        });
        
        $json = $this->get('/');
        $this->assertSame(0, count($json['profile']['idealJob']['availability']));
        $json = $this->post('/project-availability', $expected);
        $assertions($json['availability']);
        $id = $json['availability']['hash'];
        $json = $this->get('/');
        $this->assertSame(1, count($json['profile']['idealJob']['availability']));
        $assertions($json['profile']['idealJob']['availability'][0]);
        
        $updates = [
            'duration' => [
                'start' => "1187234783"
            ]
        ];
        $expected = array_merge($expected, $updates);
        $assertions = $this->assertions($expected);
        $json = $this->put('/project-availability/'.$id, $updates);
        $assertions($json['availability']);
        $json = $this->get('/');
        $this->assertSame(1, count($json['profile']['idealJob']['availability']));
        $assertions($json['profile']['idealJob']['availability'][0]);
        
        $json = $this->delete('/project-availability/'.$id);
        $json = $this->get('/');
        $this->assertSame(0, count($json['profile']['idealJob']['availability']));
    }
    
    public function testAwardActions()
    {
        $expected = [
            'date' => '1239487238',
            'type' => 'deprecated_field_i_think',
            'name' => "Some award"
        ];
        $assertions = $this->assertions($expected, function($obj) {
            $this->assertTrue(isset($obj['hash']));
        });
        
        $json = $this->get('/');
        $this->assertSame(0, count($json['profile']['awards']));
        $json = $this->post('/awards', $expected);
        $assertions($json['award']);
        $id = $json['award']['hash'];
        $json = $this->get('/');
        $this->assertSame(1, count($json['profile']['awards']));
        $assertions($json['profile']['awards'][0]);
        
        $updates = [
            'name' => "Some other award"
        ];
        $expected = array_merge($expected, $updates);
        $assertions = $this->assertions($expected);
        $json = $this->put('/awards/'.$id, $updates);
        $assertions($json['award']);
        $json = $this->get('/');
        $this->assertSame(1, count($json['profile']['awards']));
        $assertions($json['profile']['awards'][0]);
        
        $json = $this->delete('/awards/'.$id);
        $json = $this->get('/');
        $this->assertSame(0, count($json['profile']['awards']));
    }
    
    public function testCertificationActions()
    {
        $expected = [
            'name' => 'TESOL',
            'organization' => "Maryland TESOL",
            'certId' => '4xeInR923FECK2',
            'duration' => [
                'start' => '1000000',
                'end' => '9000000'
            ]
        ];
        $assertions = $this->assertions($expected, function($obj) {
            $this->assertTrue(isset($obj['hash']));
        });
        
        $json = $this->get('/');
        $this->assertSame(0, count($json['profile']['certifications']));
        $json = $this->post('/certifications', $expected);
        $assertions($json['certification']);
        $id = $json['certification']['hash'];
        $json = $this->get('/');
        $this->assertSame(1, count($json['profile']['certifications']));
        $assertions($json['profile']['certifications'][0]);
        
        $updates = [
            'name' => "Some other cert"
        ];
        $expected = array_merge($expected, $updates);
        $assertions = $this->assertions($expected);
        $json = $this->put('/certifications/'.$id, $updates);
        $assertions($json['certification']);
        $json = $this->get('/');
        $this->assertSame(1, count($json['profile']['certifications']));
        $assertions($json['profile']['certifications'][0]);
        
        $json = $this->delete('/certifications/'.$id);
        $json = $this->get('/');
        $this->assertSame(0, count($json['profile']['certifications']));
    }

    public function testAcademicOrgActions()
    {
        $expected = [
            'name' => "Some Org",
            'duration' => [
                'start' => "1234879283",
                'end' => "1298723498"
            ]
        ];
        
        $assertions = $this->assertions($expected, function($obj) {
            $this->assertTrue(isset($obj['hash']));
        });
        
        $json = $this->get('/');
        $this->assertSame(0, count($json['profile']['academicOrganizations']));
        $json = $this->post('/academic-organizations', $expected);
        $assertions($json['organization']);
        $id = $json['organization']['hash'];
        $json = $this->get('/');
        $this->assertSame(1, count($json['profile']['academicOrganizations']));
        $assertions($json['profile']['academicOrganizations'][0]);
        
        $updates = [
            'name' => "Another Org",
        ];
        $expected = array_merge($expected, $updates);
        $assertions = $this->assertions($expected);
        $json = $this->put('/academic-organizations/'.$id, $updates);
        $assertions($json['organization']);        
        $json = $this->get('/');
        $this->assertSame(1, count($json['profile']['academicOrganizations']));
        $assertions($json['profile']['academicOrganizations'][0]);
        
        $json = $this->delete('/academic-organizations/'.$id);
        $json = $this->get('/');
        $this->assertSame(0, count($json['profile']['academicOrganizations']));
    }
    
    private function get($path)
    {
        return $this->json('GET', $path);
    }
    
    private function put($path, $body = [])
    {
        return $this->json('PUT', $path, $body);
    }
    
    private function post($path, $body = [])
    {
        return $this->json('POST', $path, $body);
    }
    
    private function delete($path)
    {
        return $this->json('DELETE', $path);
    }
    
    private function json($method, $path, $body = [])
    {
        $c = $this->req($method, $path, $body);
        
        $res = $c->getResponse();

        if (!in_array($res->getStatusCode(), [200,201])) {
            throw new \Exception(sprintf("Unexpected response: %s: %s", $res->getStatusCode(), $res->getContent()));
        }
        
        return json_decode($c->getResponse()->getContent(), true);
    }
    
    private function req($method, $path, $body)
    {
        $c = $this->createAuthClient('user@example.com', 'user');
        
        $path = rtrim('/api/candidate-profiles/cccccccccccccccccccccccc'.$path, '/');
        
        $this->callApi($c, $method, $path, $body);

        return $c;
    }
    
    private function assertions($expectations, $callable = null)
    {
        if ($callable) {
            $callable->bindTo($this, $this);
        }
        
        $test = $this;
        $assertions = function($obj) use ($test, $callable, $expectations) {
            $test->assertGraph($expectations, $obj);
            
            if ($callable) {
                $callable($obj);
            }
        };
        
        return $assertions;
    }
    
    protected function assertGraph($expected, $actual)
    {
        if (is_array($expected)) {
            if ($this->isAssoc($expected)) {
                foreach ($expected as $field => $val) {
                    $this->assertGraph($val, $actual[$field]);
                }
            } else {
                for ($i = 0; $i < count($expected); $i++) {
                    $this->assertGraph($expected[$i], $actual[$i]);
                }
            }
        } else {
            $this->assertSame($expected, $actual);
        }
    }
    
    protected function isAssoc($var)
    {
        return is_array($var) && array_diff_key($var, array_keys(array_keys($var)));
    }
}
