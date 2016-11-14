<?php

namespace GPS\Tests\AppBundle\Model;

use GPS\AppBundle\Document;
use Doctrine\Common\Collections\ArrayCollection;
use GPS\AppBundle\Testing;

/** 
 * This tests finding users in the database based on the number of emails they've
 * recieved that match a certain criteria.  This is primarily used on cron jobs that
 * send automated emails to certain users.
 */
class UserRepositoryTest extends Testing\ControllerTest
{
    const TIME_24_HOURS = 86400;
    const TIME_4_DAYS = 345600;
    const TIME_2_WEEKS = 1209600;
    
    public function setUp()
    {
        $this->clearUsers();
    }
    
    public function tearDown()
    {
        $this->clearUsers();
    }
    
    private function clearUsers()
    {
        $repo = $this->getContainer()->get('doctrine_mongodb')->getRepository('AppBundle:User');
        $col = $repo->getDocumentManager()->getDocumentCollection($repo->getClassName());
        $col->remove([],['multi' => true]);
    }
    
    public function testAggregateUsers()
    {
        $doctrine = $this->getContainer()->get('doctrine_mongodb');
        $manager = $doctrine->getManager();
        $repo = $doctrine->getRepository('AppBundle:User');

        $this->createTestUsers([
            ['none@foo.com', 0, 0],
            ['completed@foo.com', 0, 0, true],
            ['bar@foo.com', 0, 1],
            ['baz@foo.com', 0, 2],
            ['quux@foo.com', 0, 3],
            ['zyzzyx@foo.com', 0, 4],
            ['lots@foo.com', 0, 8],
            ['lotsAndComplete@foo.com', 0, 8, true]
        ]);        
        
        
        $assertions = function($ids, $expectedCount) use ($repo) {
            foreach ($ids as $id) {
                $user = $repo->find($id);
                $this->assertTrue($user->getIsVerified());
                $this->assertTrue($user->getPreferences()->getAllowProfileHealthEmails());
                
                $count = 0;
                foreach ($user->getEmailHistory() as $email) {
                    if ('profile-health' === $email->getEmailKey()) {
                        $count++;
                    }
                }

                $this->assertSame($expectedCount, $count);
            }
        };
        
        // 6 users matching criteria who have not recieved completed email
        $ids = $this->findUsersByDateAndEmailCount(time() - self::TIME_24_HOURS, null);
        $this->assertSame(6, count($ids));

        $ids = $this->findUsersByDateAndEmailCount(time() - self::TIME_24_HOURS, 1);
        $this->assertSame(1, count($ids));
        $assertions($ids, 1);
        
        $ids = $this->findUsersByDateAndEmailCount(time() - self::TIME_24_HOURS, 4);
        $this->assertSame(1, count($ids));
        $assertions($ids, 4);

        $ids = $this->findUsersByDateAndEmailCount(time() - self::TIME_24_HOURS, 8);
        $this->assertSame(1, count($ids));
        $assertions($ids, 8);

        $ids = $this->findUsersByDateAndEmailCount(time() - self::TIME_24_HOURS, ['$gte' => 4]);
        $this->assertSame(2, count($ids));
    }
    
    private function findUsersByDateAndEmailCount($timestamp, $countCriteria)
    {
        // this is a convoluted query on purpose - it's similar to another query used
        // elsewhere in a command, so testing it in more isolation here
        
        $userCriteria = [
            'isVerified' => true,
            'preferences.allowProfileHealthEmails' => true,
            'dateCreated' => ['$lte' => new \MongoDate($timestamp)],
            'candidateProfile' => ['$exists' => true],
            '$and' => [
                ['emailHistory' => [
                    '$not' => [
                        '$elemMatch' => [
                            'emailKey' => 'profile-completed'
                        ]
                    ]
                ]],
                ['emailHistory' => [
                    '$not' => [
                        '$elemMatch' => [
                            'emailKey' => 'profile-health',
                            'sentAt' => ['$gte' => new \MongoDate(time())]
                        ]
                    ]
                ]]
            ],
        ];
        
        $emailCriteria = null;
        if ($countCriteria) {
            $emailCriteria = [
                'emailHistory.emailKey' => "profile-health"
            ];
        }
        
        $doctrine = $this->getContainer()->get('doctrine_mongodb');
        $repo = $doctrine->getRepository('AppBundle:User');

        return $repo->aggregateIdsByEmailHistoryCount($userCriteria, $emailCriteria, $countCriteria);
    }
    
    private function createTestUsers($matrix = [])
    {
        $createUser = function($email, $date, $history = 0, $complete = false) { 
            $user = Document\User::createFromArray([
                'isVerified' => true,
                'dateCreated' => $date,
                'email' => $email,
                'candidateProfile' => Document\Candidate\Profile::createFromArray([])
            ]);
            
            for ($i = 0; $i < $history; $i++) {
                $user->getEmailHistory()->add(Document\EmailHistoryItem::createFromArray([
                    'emailKey' => 'profile-health',
                    'sentAt' => \DateTime::createFromFormat('U', 23),
                ]));
            }
            
            if ($complete) {
                $user->getEmailHistory()->add(Document\EmailHistoryItem::createFromArray([
                    'emailKey' => 'profile-completed',
                    'sentAt' => \DateTime::createFromFormat('U', 23),
                ]));
            }
            
            return $user;
        };
        
        $manager = $this->getContainer()->get('doctrine_mongodb')->getManager();

        foreach ($matrix as $args) {
            $user = call_user_func_array($createUser, $args);
            $manager->persist($user);
            $manager->persist($user->getCandidateProfile());
        }

        $manager->flush();
    }
    
}
