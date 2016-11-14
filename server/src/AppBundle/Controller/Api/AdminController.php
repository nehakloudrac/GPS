<?php

namespace GPS\AppBundle\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use GPS\AppBundle\Document;
use JMS\Serializer\SerializationContext;

/**
 * Provides all the user api related actions under /candidate-profiles
 *
 * @package GPS
 * @author Evan Villemez
 *
 * @Route("/api/admin")
 */
class AdminController extends AbstractApiController
{
    
    /** 
     * Get all admin comments about a specific user.
     * 
     * @Route("/users/{userId}/comments", name="api-admin-get-comments")
     * @Method("GET")
     */
    public function getUserAdminCommentsAction(Request $req, $userId)
    {
        $targetUser = $this->getTargetUser($userId);
        
        $comments = $this->getRepository('AppBundle:AdminComment')
            ->createQueryBuilder()
            ->field('user')->references($targetUser)
            ->field('creator')->prime(true)
            ->getQuery()
            ->execute()
        ;

        $arr = $comments->toArray();
        
        $ctx = SerializationContext::create()->setGroups(['Default','AdminComment.creator']);

        return $this->createServiceResponse(['comments' => $arr], 200, [], $ctx);
    }
    
    /** 
     * Create new comment about a user.
     * 
     * @Route("/users/{userId}/comments", name="api-admin-create-comment")
     * @Method("POST")
     */
    public function postUserAdminCommentsAction(Request $req, $userId)
    {
        $targetUser = $this->getTargetUser($userId);
        
        $dCtx = $this->createDeserializationContext()->setTarget(new Document\AdminComment());
        
        $comment = $this->decodeRequest($req, 'GPS\AppBundle\Document\AdminComment', $dCtx);
        $comment->setCreator($this->getUser());
        $comment->setUser($targetUser);
        $comment->setDateCreated(new \DateTime('now'));

        $manager = $this->getDocumentManager();
        $manager->persist($comment);
        $manager->flush();
        
        $ctx = SerializationContext::create()->setGroups(['Default']);

        return $this->createServiceResponse(['comment' => $comment], 201, [], $ctx);
    }

    /** 
     * Update comment about a user.
     * 
     * @Route("/users/{userId}/comments/{commentId}", name="api-admin-modify-comment")
     * @Method("PUT")
     */
    public function putUserAdminCommentsAction(Request $req, $userId, $commentId)
    {
        throw $this->createServiceResponse(501);
    }
    
    /** 
     * Delete comment about a user.
     * 
     * @Route("/users/{userId}/comments/{commentId}", name="api-admin-delete-comment")
     * @Method("DELETE")
     */
    public function deleteUserAdminCommentsAction(Request $req, $userId, $commentId)
    {
        $comment = $this->getRepository('AppBundle:AdminComment')->find($commentId);
        if (!$comment) {
            throw $this->createServiceException(404, "No comment by that id.");
        }
        
        // TODO: ensure user can delete comments other than own
        
        $manager = $this->getDocumentManager();
        $manager->remove($comment);
        $manager->flush();
        
        return $this->createServiceResponse([], 200);
    }
    
    /** 
     * Get user and profile destails by user id.
     * 
     * @Route("/users/{userId}/details", name="api-admin-user-details")
     * @Method("GET")
     */
    public function getUserDetailsAction(Request $req, $userId)
    {
        $manager = $this->getDocumentManager();
        $qb = $manager->createQueryBuilder('AppBundle:User')
            ->slaveOkay()
            ->field('id')->equals($userId)
        ;
        
        $user = $qb->getQuery()->getSingleResult();
        if (!$user) {
            throw $this->createHttpException(404, "User not found.");
        }
        
        $ctx = $this->createSerializationContext()->setGroups(['Default', 'User.email']);
        
        $profile = $user->getCandidateProfile();
        if ($profile) {
            $profile->computeProfessionalExperience();
        }
        
        //update user tracker w/ view info
        $manager->createQueryBuilder('AppBundle:User')
            ->update()
            ->field('id')->equals($user->getId())
            ->field('tracker.profileViewsTotal')->inc(1)
            ->field('tracker.profileLastViewed')->set(new \MongoDate(time()))
            ->getQuery()
            ->execute()
        ;
        
        return $this->createServiceResponse([
            'user' => $user,
            'profile' => $user->getCandidateProfile()
        ], 200, [], $ctx);
    }
    
    /**
     * Filter overviews by user
     *
     * @Route("/overview/users", name="api-admin-user-overview")
     * @Method("GET")
     */
    public function getUserOverviewAction(Request $req)
    {
        $q = $req->query;
        $manager = $this->getDocumentManager();
        $qb = $manager->createQueryBuilder('AppBundle:User')
            ->slaveOkay(true)
            ->sort('id', 'asc')
            ->limit($q->get('limit', 20))
            ->skip($q->get('skip', 0))
        ;
        
        // id filter
        if ($idFilter = $q->get('id', false)) {
            $qb->field('id')->in(explode(',', $idFilter));
        }
        
        // short id filter
        if ($shortIds = $q->get('shortId', false)) {
            $ids = explode(',', $shortIds);
            $ids = array_map(function($id) {
                return bin2hex(base64_decode($id));
            }, $ids);

            $qb->field('id')->in($ids);
        }
        
        // check email filter
        if ($emailFilter = $q->get('email', false)) {
            $qb->field('email')->equals(new \MongoRegex('/.*'.$emailFilter.'.*/i'));
        }
        
        // check name filter
        if ($nameFilter = $q->get('name', false)) {
            $regex = new \MongoRegex('/.*'.$nameFilter.'.*/i');
            $qb->addOr($qb->expr()->field('firstName')->equals($regex));
            $qb->addOr($qb->expr()->field('lastName')->equals($regex));
            $qb->addOr($qb->expr()->field('preferredName')->equals($regex));
        }
        
        // native language
        if ($langFilter = $q->get('languages', false)) {
            $qb->field('languages')->in(explode(',', $langFilter));
        }

        $users = $qb->getQuery()->execute();
        
        $map = [];
        $userIds = [];
        foreach ($users as $user) {
            $profile = $user->getCandidateProfile();
            
            $userIds[] = $user->getId();
            
            $map[] = [
                'user' => [
                    'id' => $user->getId(),
                    'firstName' => $user->getFirstName(),
                    'lastName' => $user->getLastName(),
                    'email' => $user->getEmail()
                ],
                'profile' => [
                    'id' => $profile->getId(),
                    'completeness' => $profile->getCompleteness()
                ]
            ];
        }
        
        // update user docs with search hits
        $manager->createQueryBuilder('AppBundle:User')
            ->update()
            ->multiple(true)
            ->field('id')->in($userIds)
            ->field('tracker.profileSearchHitsTotal')->inc(1)
            ->field('tracker.profileLastSearchHit')->set(new \MongoDate(time()))
            ->getQuery()
            ->execute()
        ;        
        
        return $this->createServiceResponse([
            'total' => $users->count(),
            'results' => $map
        ], 200);
    }
    
    /**
     * Filter overviews by profile
     * 
     * @Route("/overview/profiles", name="api-admin-profile-overview")
     * @Method("GET")
     */
    public function getProfileOverviewAction(Request $req)
    {
        $q = $req->query;
        $q = $req->query;
        $manager = $this->getDocumentManager();
        $qb = $manager->createQueryBuilder('AppBundle:Candidate\Profile')
            ->slaveOkay(true)
            ->sort('id', 'asc')
            ->limit($q->get('limit', 20))
            ->skip($q->get('skip', 0))
        ;
        
        // id filter
        if ($idFilter = $q->get('id', false)) {
            $qb->field('id')->in(explode(',', $idFilter));
        }
        
        // check language filter
        if ($langFilter = $q->get('languages', false)) {
            $qb->field('languages.code')->in(explode(',', $langFilter));
            $qb->field('shortForm.foreignLanguages')->in(explode(',', $langFilter));
        }
        
        // language macro filter
        if ($macroFilter = $q->get('macroLanguage', false)) {
            $qb->field('languages.macroCode')->in(explode(',', $macroFilter));
        }
        
        // check country filter
        if ($countryFilter = $q->get('countries', false)) {
            $qb->field('countries.code')->in(explode(',', $countryFilter));
            $qb->field('shortForm.countries')->in(explode(',', $countryFilter));
        }
        
        // check desired locations USA filter
        if ($locationsUSAFilter = $q->get('locationsUSA', false)) {
            $qb->field('idealJob.locationsUSA')->in(explode(',', $locationsUSAFilter));
        }
        
        // check desired locations abroad filter
        if ($locationsAbroadFilter = $q->get('locationsAbroad', false)) {
            $qb->field('idealJob.locationsAbroad')->in(explode(',', $locationsAbroadFilter));
        }
        
        // check industry filter
        if ($industryFilter = $q->get('industries', false)) {
            $filters = array_map(function($str) {
                return new \MongoRegex('/.*'.trim($str).'.*/i');
            }, explode(',', $industryFilter));
            $qb->addOr($qb->expr()->field('timeline.institution.industries')->in($filters));
            $qb->addOr($qb->expr()->field('shortForm.preferredIndustries')->in($filters));
        }
        
        // check university filter
        if ($universityFilters = $q->get('universities', false)) {
            $filters = array_map(function($str) {
                return new \MongoRegex('/.*'.trim($str).'.*/i');
            }, explode(',', $universityFilters));
            $qb->field('timeline.type')->equals('university');
            $qb->field('timeline.institution.name')->in($filters);
        }
        
        // check skills filter
        if ($skillsFilter = $q->get('skills', false)) {
            $filters = array_map(function($str) {
                return new \MongoRegex('/.*'.trim($str).'.*/i');
            }, explode(',', $skillsFilter));
            $qb->addOr($qb->expr()->field('domainSkills.expert')->in($filters));
            $qb->addOr($qb->expr()->field('domainSkills.advanced')->in($filters));
            $qb->addOr($qb->expr()->field('domainSkills.proficient')->in($filters));
        }
        
        $profiles = $qb->getQuery()->execute();
        $map = [];
        $userIds = [];
        foreach ($profiles as $profile) {
            $user = $profile->getUser();

            $userIds[] = $user->getId();
            
            $map[] = [
                'user' => [
                    'id' => $user->getId(),
                    'firstName' => $user->getFirstName(),
                    'lastName' => $user->getLastName(),
                    'email' => $user->getEmail()
                ],
                'profile' => [
                    'id' => $profile->getId(),
                    'completeness' => $profile->getCompleteness()
                ]
            ];
        }
        
        // update user docs with search hits
        $manager->createQueryBuilder('AppBundle:User')
            ->update()
            ->multiple(true)
            ->field('id')->in($userIds)
            ->field('tracker.profileSearchHitsTotal')->inc(1)
            ->field('tracker.profileLastSearchHit')->set(new \MongoDate(time()))
            ->getQuery()
            ->execute()
        ;
        
        return $this->createServiceResponse([
            'total' => $profiles->count(),
            'results' => $map
        ], 200);
    }
    
    /**
     * Get various counts used on dashboard overview
     * 
     * @Route("/overview/counts", name="api-admin-counts-overview")
     * @Method("GET")
     */
    public function getOverviewCountsAction(Request $req)
    {
        $manager = $this->getDocumentManager();

        // total users
        $totalUsers = $manager->createQueryBuilder('AppBundle:User')
            ->slaveOkay(true)
            ->hydrate(false)
            ->find()
            ->getQuery()
            ->execute()
            ->count()
        ;
        
        // new this week
        $newThisWeek = $manager->createQueryBuilder('AppBundle:User')
            ->slaveOkay(true)
            ->hydrate(false)
            ->field('dateCreated')->gte(\DateTime::createFromFormat('U', time() - 604800))
            ->getQuery()
            ->execute()
            ->count()
        ;
        
        // new last 24 hours
        $newLast24 = $manager->createQueryBuilder('AppBundle:User')
            ->slaveOkay(true)
            ->hydrate(false)
            ->field('dateCreated')->gte(\DateTime::createFromFormat('U', time() - 86400))
            ->getQuery()
            ->execute()
            ->count()
        ;
        
        
        return $this->createServiceResponse([
            'total' => $totalUsers,
            'thisWeek' => $newThisWeek,
            'this24' => $newLast24
        ], 200);
    }
    
    /**
     * Get distributions of profile completeness
     * 
     * @Route("/overview/distributions", name="api-admin-distributions")
     * @Method("GET")
     */
    public function getOverviewDistributions()
    {
        $cache = $this->get('gps.shared_cache');
        $distributions = $cache->fetch('gps.profile_completeness_distributions');
        
        if (!$distributions) {
            $distributions = [];
            $manager = $this->getDocumentManager();
            
            // get total profile count first
            $distributions['total'] = $manager->createQueryBuilder('AppBundle:Candidate\Profile')
                ->slaveOkay(true)
                ->hydrate(false)
                ->getQuery()
                ->execute()
                ->count()
            ;

            // total completet short forms
            $distributions['completedShortForms'] = $manager->createQueryBuilder('AppBundle:Candidate\Profile')
                ->slaveOkay(true)
                ->hydrate(false)
                ->field('shortForm.completed')->equals(true)
                ->getQuery()
                ->execute()
                ->count()
            ;
            
            $distributions['ranges'] = [];
            $ranges = [
                [0,10],
                [11,25],
                [26,50],
                [51, 75],
                [75, 90],
                [91, 100]
            ];
            
            foreach ($ranges as $range) {
                list($min, $max) = $range;
                $count = $manager->createQueryBuilder('AppBundle:Candidate\Profile')
                    ->slaveOkay(true)
                    ->hydrate(false)
                    ->field('profileStatus.completeness.percentCompleted')->gte($min)
                    ->field('profileStatus.completeness.percentCompleted')->lte($max)
                    ->getQuery()
                    ->execute()
                    ->count()
                ;
                $distributions['ranges'][] = [
                    'min' => $min,
                    'max' => $max,
                    'count' => $count
                ];
            }

            // $cache->save('gps.profile_completeness_distributions', $distributions, 3600);
        }
        
        return $this->createServiceResponse([
            'distributions' => $distributions
        ], 200);
    }
    
    /**
     * Get counts for field values (facets)
     * 
     * @Route("/overview/facets/{field}", name="api-admin-fields-overview")
     * @Method("GET")
     */
    public function getOverviewFieldsAction(Request $req, $field)
    {
        $q = $req->query;
        $preOps = [];
        
        // check for id filter
        if ($idFilter = $q->get('id', false)) {
            $preOps[] = ['$match' => ['_id' => ['$in' => explode(',', $idFilter)]]];
        }
        
        // check for completeness filter
        // TODO: use cache service to check
        
        switch ($field) {
            case 'industries':              $results = $this->aggregateIndustries($preOps); break;
            case 'degrees':                 $results = $this->aggregateDegrees($preOps); break;
            case 'concentrations':          $results = $this->aggregateConcentrations($preOps); break;
            case 'foreign-languages':       $results = $this->aggregateForeignLanguages($preOps); break;
            case 'native-languages':        $results = $this->aggregateNativeLanguages($preOps); break;
            case 'countries':               $results = $this->aggregateForeignCountries($preOps); break;
            case 'visas':                   $results = $this->aggregateVisaCountries($preOps); break;
            case 'residence':               $results = $this->aggregateResidenceCountries($preOps); break;
            case 'countries-ideal':         $results = $this->aggregateIdealCountries(); break;
            case 'states-residence':        $results = $this->aggregateResidenceState(); break;
            case 'states-ideal':            $results = $this->aggregateIdealStates(); break;
            case 'employers':               $results = $this->aggregateEmployers($preOps); break;
            case 'universities':            $results = $this->aggregateUniversities($preOps); break;
            case 'status':                  $results = $this->aggregateJobStatus($preOps); break;
            case 'referrer':                $results = $this->aggregateReferrer($preOps); break;
            case 'us-security-clearances':  $results = $this->aggregateSecurityClearances($preOps); break;
            case 'us-work-authorizations':  $results = $this->aggregateWorkAuthorizations($preOps); break;
            default: throw $this->createHttpException(400, "Unknown facet field.");
        }

        return $this->createServiceResponse(['data' => $results], 200);
    }
    
    private function aggregateIndustries($preOps = [])
    {
        return $this->combineAggregationSets([
            $this->aggregateSingleField('shortForm.preferredIndustries', 1, $preOps),
            $this->aggregateSingleField('idealJob.industries', 1, $preOps),
            $this->aggregateSingleField('timeline.institution.industries', 2, $preOps)
        ]);
    }
    
    private function aggregateDegrees($preOps = [])
    {
        return $this->combineAggregationSets([
            $this->aggregateSingleField('shortForm.degrees', 1, $preOps),
            $this->aggregateSingleField('timeline.degrees', 2, $preOps)
        ]);
    }
    
    private function aggregateConcentrations($preOps = [])
    {
        return $this->combineAggregationSets([
            $this->aggregateSingleField('timeline.concentrations.fieldName', 2, $preOps)
        ]);
    }
    
    private function aggregateForeignLanguages($preOps = [])
    {
        return $this->combineAggregationSets([
            $this->aggregateSingleField('languages.code', 1, $preOps)
        ]);
    }
    
    private function aggregateNativeLanguages($preOps = [])
    {
        return $this->combineAggregationSets([
            $this->aggregateSingleField('languages', 1, $preOps, 'users')
        ]);
    }
    
    private function aggregateForeignCountries($preOps = [])
    {
        return $this->combineAggregationSets([
            $this->aggregateSingleField('countries.code', 1, $preOps)
        ]);
    }
    
    private function aggregateVisaCountries($preOps = [])
    {
        return $this->combineAggregationSets([
            $this->aggregateSingleField('citizenship', 1, $preOps, 'users')
        ]);
    }
    
    private function aggregateResidenceCountries($preOps = [])
    {
        return $this->combineAggregationSets([
            $this->aggregateSingleField('address.countryCode', 0, $preOps, 'users')
        ]);
    }
    
    private function aggregateIdealCountries($preOps = [])
    {
        return $this->combineAggregationSets([
            $this->aggregateSingleField('idealJob.locationsAbroad', 0, $preOps)
        ]);
    }
    private function aggregateResidenceState($preOps = [])
    {
        return $this->combineAggregationSets([
            $this->aggregateSingleField('address.territory', 0, $preOps, 'users')
        ]);
    }
    private function aggregateIdealStates($preOps = [])
    {
        return $this->combineAggregationSets([
            $this->aggregateSingleField('idealJob.locationsUSA', 0, $preOps)
        ]);
    }
    
    private function aggregateJobStatus($preOps = [])
    {
        return $this->combineAggregationSets([
            $this->aggregateSingleField('currentJobStatus', 0, $preOps, 'users')
        ]);
    }
    
    private function aggregateReferrer($preOps = [])
    {
        return $this->combineAggregationSets([
            $this->aggregateSingleField('institutionReferrer', 0, $preOps, 'users')
        ]);
    }
    
    private function aggregateSecurityClearances($preOps = [])
    {
        return $this->combineAggregationSets([
            $this->aggregateSingleField('usSecurityClearance', 0, $preOps, 'users')
        ]);
    }
    
    private function aggregateWorkAuthorizations($preOps = [])
    {
        return $this->combineAggregationSets([
            $this->aggregateSingleField('usWorkAuthorization', 0, $preOps, 'users')
        ]);
    }

    private function aggregateEmployers($preOps = [])
    {
        // unwind timeline and only match "professional" timeline types
        $preOps[] = ['$unwind' => '$timeline'];
        $preOps[] = ['$match' => ['timeline.type' => ['$in' => ['job','volunteer','research']]]];
        
        return $this->combineAggregationSets([
            // Note that this is only unwound once, because it was already unwound in a pre op above
            $this->aggregateSingleField('timeline.institution.name', 0, $preOps)
        ]);
    }
    
    private function aggregateUniversities($preOps = [])
    {
        // unwind timeline and only match "university" timeline types
        $preOps[] = ['$unwind' => '$timeline'];
        $preOps[] = ['$match' => ['timeline.type' => ['$in' => ['university']]]];
        
        return $this->combineAggregationSets([
            // Note that this is only unwound once, because it was already unwound in a pre op above
            $this->aggregateSingleField('timeline.institution.name', 0, $preOps)
        ]);
    }
    
    private function combineAggregationSets($sets)
    {
        $index = [];
        
        foreach ($sets as $set) {
            foreach ($set as $item) {
                if (!isset($index[$item['_id']])) {
                    $index[$item['_id']] = [
                        'value' => $item['_id'],
                        'ids' => []
                    ];
                }
                
                foreach ($item['ids'] as $obj) {
                    $index[$item['_id']]['ids'][] = (string) $obj;
                }
            }
        }
        
        // dedup users add counts, then sort on count
        foreach ($index as $key => &$item) {
            $item['ids'] = array_values(array_unique($item['ids']));
            $item['count'] = count($item['ids']);
        }
        
        // sort in descending order; highest numbers first
        usort($index, function($a, $b) {
            if ($a['count'] == $b['count']) {
                return 0;
            }
            
            if ($a['count'] > $b['count']) {
                return -1;
            }
            
            return 1;
        }); 
        
        return $index;
    }
    
    private function aggregateSingleField($field, $unwinds = 0, $preOps = [], $collection = 'candidateProfiles')
    {
        $ops = [];
        
        foreach($preOps as $op) {
            $ops[] = $op;
        }
        
        $ops[] = ['$project' => ['matchField' => "$".$field]];
        
        if ($unwinds > 0) {
            for ($i = 0; $i < $unwinds; $i++) {
                $ops[] = ['$unwind' => '$matchField'];
            }
        }
        
        $ops[] = [
            '$group' => [
                '_id' => '$matchField',
                'ids' => ['$addToSet' => '$_id']
            ]
        ];
        
        $col = $this->getMongoConnection()->selectCollection($collection);
        $col->setSlaveOkay(true);

        return $col->aggregate($ops)->getCommandResult()['result'];
    }
    
    private function getTargetUser($id)
    {
        $targetUser = $this->getRepository('AppBundle:User')->find($id);
        
        if (!$targetUser) {
            throw $this->createHttpException(404, "No user by that id.");
        }
        
        return $targetUser;
    }
}
