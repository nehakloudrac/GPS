<?php

namespace GPS\AppBundle\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use GPS\AppBundle\Document\Candidate;
use JMS\Serializer\SerializationContext;
use Elastica as ES;

/**
 * Provides actions for querying the ElasticSearch index.
 *
 * @package GPS
 * @author Evan Villemez
 *
 * @Route("/api/admin/search")
 */
class SearchController extends AbstractApiController
{
    /**
     * Search the ES index for candidates
     * 
     * Fields are ?field1[]=val1,val2&field1[]=val2
     * Facets are ?_facets=field1,field2:5
     *
     * @Route("/candidates", name="api-admin-search-candidates")
     * @Method("GET")
     */
    public function searchCandidatesAction(Request $req)
    {
        // have to manually decode query string because array
        // arguments were being duplicated for some reason
        $args = $this->parseQueryString($req);
        $q = function ($key, $default = null) use ($args) {
            return isset($args[$key]) ? $args[$key] : $default;
        };
        $updateHits = false;
        $textSearch = false;
        
        // create the top level query; actual type of query may be set
        // later
        if ($text = $q('q', false)) {
            $text = trim($text);
            
            if (!empty($text)) {
                $textSearch = true;
                $updateHits = true;

                $fields = [
                    'userName^10',
                    'userEmail^10',
                    'clientId^10',
                    'totalTitles^6',
                    'totalMajors^6',
                    'totalIndustries^4',
                    'totalSkills^4',
                    'totalEmployers^2',
                    'totalUniversities^2',
                    'totalDescription^2',
                ];
                
                // create query, but try and direct explicit search logic in string
                if (false !== strpos($text, 'AND') || false !== strpos($text, 'OR')) {
                    $query = ES\Query::create(new ES\Query\QueryString($text));
                    $query->setFields($fields);
                } else {
                    $query = ES\Query::create(new ES\Query\SimpleQueryString($text, $fields));
                }
                
                $query->setHighlight([
                    'pre_tags' => ['<span class="search-highlight">'],
                    'post_tags' => ['</span>'],
                    'order' => 'score',
                    'fragment_size' => 150,
                    'fields' => [
                        'userName' =>           ['number_of_fragments' => 0],
                        'userEmail' =>          ['number_of_fragments' => 0],
                        'clientId' =>           ['number_of_fragments' => 0],
                        'totalTitles' =>        ['number_of_fragments' => 0],
                        'totalIndustries' =>    ['number_of_fragments' => 0],
                        'totalMajors' =>        ['number_of_fragments' => 0],
                        'totalSkills' =>        ['number_of_fragments' => 0],
                        'totalEmployers' =>     ['number_of_fragments' => 0],
                        'totalUniversities' =>  ['number_of_fragments' => 0],
                        'totalDescription' =>    ['number_of_fragments' => 5],
                    ]
                ]);

            } else {
                $query = new ES\Query();
            }
        } else {
            $query = new ES\Query();
        }
        
        // enforce positive limit/skips within certain bounds; only return the user ID from ES for now
        $limit = ($l = abs((int) $q('_limit', 10))) > 100 ? 100 : $l;
        $skip = ($s = abs((int) $q('_skip', 0))) > 1000 ? 1000 : $s;
        $query
            ->setFrom($skip)
            ->setLimit($limit)
            ->setFields(['clientId'])
        ;
        
        // check for sorts
        if ($sort = $q('_sort', false)) {
            foreach (explode(',', $sort) as $sortArg) {
                list($field, $op) = explode(':', $sortArg);
                switch ($op) {
                    case '+': $dir = 'asc'; break;
                    case '-': $dir = 'desc'; break;
                }
                
                $query->addSort([$field => ['order' => $dir]]);
            }
        }

        // parse query filters & assemble final filter
        $filters = $this->createQueryFilters($q);
        if (count($filters) > 0) {
            $updateHits = true;
            $boolQuery = new ES\Query\BoolQuery();
            foreach ($filters as $filter) {
                $boolQuery->addMust($filter);
            }
            
            // if there is a text search, use the filters as "post filter", 
            // otherwise, do a filtered query
            // 
            // NOTE: I fully realize this may end in confusion, so be prepared
            // to change it later
            //
            // NOTE: actually, always use filters as a post filter
            // unless explicitly told not to....
            // will need to add something to the API to allow specifing
            // the mode in which the filters should be used.
            if ($textSearch) {
                $query->setPostFilter($boolQuery);
            } else {
                $filteredQuery = new ES\Query\Filtered();
                $filteredQuery->setQuery($boolQuery);
                $query->setQuery($filteredQuery);                
            }
        }
        
        //parse "facets" - facets are now "terms aggregations"
        foreach ($this->createFacets($q) as $facet) {
            $query->addAggregation($facet);
        }
        
        // execute the search and parse the results, checking explicitly
        // for query syntax errors
        try {
            $res = $this->getESType()->search($query);
        } catch (\Elastica\Exception\ResponseException $e) {
            if (false !== stripos($e->getMessage(), 'Failed to parse query')) {
                throw $this->createHttpException(400, "The query contains a syntax error (probably a missing parenthesis).  Check your syntax and try again.");
            }
            
            // otherwise just rethrow, something unexpected happened
            throw $e;
        }
        
        $result = [
            'total' => $res->getTotalHits(),
            'limit' => $limit,
            'skip' => $skip,
            'hits' => $this->parseHits($res->getResults(), $limit, $skip, $updateHits),
            'facets' => $res->getAggregations(),
        ];

        return $this->createServiceResponse(["result" => $result], 200);
    }
    
    private function parseHits($results, $limit, $skip, $updateHits = false)
    {
        $userIds = [];
        foreach ($results as $result) {
            $userIds[] = $result->getFields()['clientId'][0];
        }
        
        // load users by ids in result set, map by id for later
        $manager = $this->getDocumentManager();
        $users = $manager->createQueryBuilder('AppBundle:User')
            ->slaveOkay(true)
            ->field('id')->in($userIds)
            ->getQuery()
            ->execute()
        ;
        $userMap = [];
        foreach ($users as $user) {
            $userMap[$user->getId()] = $user;
        }
        
        // build final hit data merging info from both index and raw
        $hits = [];
        foreach ($results as $result) {
            if (!$user = $userMap[$result->getFields()['clientId'][0]]) {
                continue;
            }
            
            $profile = $user->getCandidateProfile();
            $hits[] = [
                'user' => [
                    'id' => $user->getId(),
                    'firstName' => $user->getFirstName(),
                    'lastName' => $user->getLastName(), 
                    'email' => $user->getEmail(),
                    'dateCreated' => $user->getDateCreated()->getTimestamp()
                ],
                'profile' => [
                    'id' => $profile->getId(),
                    'lastModified' => $profile->getLastModified()->getTimestamp(),
                    'completeness' => $profile->getCompleteness()
                ],
                'highlights' => $result->getHighlights()
            ];
        }
                
        // update user search hits if triggered to do so
        if ($updateHits) {
            $manager->createQueryBuilder('AppBundle:User')
                ->update()
                ->multiple(true)
                ->field('id')->in($userIds)
                ->field('tracker.profileSearchHitsTotal')->inc(1)
                ->field('tracker.profileLastSearchHit')->set(new \MongoDate(time()))
                ->getQuery()
                ->execute()
            ;
        }
        
        return $hits;
    }
    
    private function createFacets($q)
    {
        $facets = [];
        
        // TODO: consider some extra server side config for some facets, for example to allow
        // buckets on numeric ranges
        
        // some fields should not be forced to lower case for search (country codes)
        $noLowerSearch = [
            'visas',
            'totalCountries',
            'currentLocationCountry',
            'desiredLocationsAbroad',
        ];
        
        if ($str = $q('_facets', false)) {
            foreach (explode(';', $str) as $item) {
                $parts = explode(':', $item); 
                $term = isset($parts[0]) && !empty($parts[0]) ? $parts[0] : false;
                $search = isset($parts[2]) && !empty($parts[2]) ? $parts[2] : false;

                if (!$term) continue;

                $size = isset($parts[1]) && !empty($parts[1]) ? $parts[1] : 10;
                
                $facet = new ES\Aggregation\Terms($term);
                $facet->setField($term);
                $facet->setSize($size);
                
                // Some facets have unbounded results, so may want to search
                // the available buckets
                if ($search) {
                    if (!in_array($term, $noLowerSearch)) {
                        $search = strtolower($search);
                    }
                    
                    $includeTerms = implode('|', $this->trimArray(explode(",", $search)));
                    $facet->setInclude(".*($includeTerms).*");
                }
                
                $facets[] = $facet;
            }
        }
        
        return $facets;
    }
    
    private function createQueryFilters($q)
    {
        $filters = [];
        
        // these are all simple text-based filters; not analyzed
        // by index because values are exact
        $simpleTermsFilters = [
            'clientId',
            'desiredLocationsAbroad',
            'desiredLocationsUSA',
            'usSecClearance',
            'currentJobStatus',
            'usWorkAuthorization',
            'visas',
            'desiredEmployerTypes',
            'desiredJobTypes',
            'totalDegrees',
            'totalCountries',
            'totalLanguages',
            'totalForeignLanguages',
            'totalNativeLanguages',
            'totalCertifiedLanguages',
            'currentLocationCountry',
            'currentLocationTerritory',
            'totalPositionLevels',
            'highestPositionLevel',
            'howWillingToTravel',
            'diversity',
            'gender',
        ];
        foreach ($simpleTermsFilters as $field) {
            if ($filter = $q($field, false)) {
                foreach ((array) $filter as $params) {
                    $filters[] = new ES\Query\Terms($field, $this->trimArray(explode(',', $params)));
                }
            }
        }

        // these are text matches, analyzed by the index;
        // I assume that comma delimited means multiple
        // phrases
        // 
        // the resulting filter uses "shoulds" with "min should match"
        // set to 1... basically the equivalent of an "OR"
        $simplePhraseFilters = [
            'totalSkills',
            'totalUniversities',
            'totalEmployers',
            'userEmail',
            'userName',
            'hobbies',
            'desiredIndustries',
            'totalAwards',
            'totalIndustries',
            'totalMajors',
            'totalTitles',
            'currentLocationCity',
        ];
        foreach ($simplePhraseFilters as $field) {
            if ($filter = $q($field, false)) {
                foreach ((array) $filter as $params) {
                    $values = $this->trimArray(explode(',', $params));
                    $should = new ES\Query\BoolQuery();
                    $should->setMinimumNumberShouldMatch(1);
                    
                    foreach ($values as $val) {
                        $f = new ES\Query\MatchPhrase();
                        $f->setFieldQuery($field, $val);
                        $should->addShould($f);
                    }
                    
                    $filters[] = $should;
                }
            }
        }
        
        // TODO: change the definitions here to accomodate date searches, need
        // to wrap the val in a proper date to make "dateCreated" work as expected
        $simpleNumberFilters = [
            'desiredHoursPerWeek',
            'desiredPaySalary',
            'desiredPayHourly',
            'desiredPayDaily',
            'desiredPayWeekly',
            'desiredPayMonthly',
            'totalMonthsExperience',
            'totalMonthsExperienceGaps',
            'dateCreated',
        ];
        foreach ($simpleNumberFilters as $field) {
            if ($filter = $q($field, false)) {
                foreach ((array)$filter as $param) {
                    list($op, $val) = explode(":", $param);
                    $f = new ES\Query\Range($field, [$op => $val]);
                    $filters[] = $f;
                }
            }
        }
        
        // TODO: other less simple filters:
        // * lang proficiency
        // * industry experience
        
        return $filters;
    }
    
    private function trimArray($arr)
    {
        $new = [];
        foreach ($arr as $item) {
            $trimmed = trim($item);
            if(!empty($trimmed)) {
                $new[] = $trimmed;
            }
        }
        
        return $new;
    }
    
    // I don't know why I had to do this...  in whatever version of whatever
    // libraries I was using at the time, query string arguments with brackets
    // to signifying arrays were being duplicated upon decoding.  It was really odd behavior
    // and didn't match any documentation I could find... I eventually gave up
    // trying to figure it out and fell back to using `parse_str` manually, which
    // gave the expected result.
    private function parseQueryString($req)
    {
        $qs = $req->getRequestUri();
        $parts = explode('?', $qs);
        $args = [];
        if (isset($parts[1])) {
            parse_str($parts[1], $args);
        }
        
        return $args;
    }
    
    private function getESType()
    {
        return $this
            ->get('gps.elasticsearch')
            ->getIndex($this->container->getParameter('es_index'))
            ->getType('candidate')
        ;
    }
}
