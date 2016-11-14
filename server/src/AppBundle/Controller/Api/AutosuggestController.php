<?php

namespace GPS\AppBundle\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use GPS\AppBundle\Document\Candidate;
use JMS\Serializer\SerializationContext;
use Elastica as ES;


class AutosuggestController extends AbstractApiController
{
    
    /** 
     * Route for suggesting values to specific fields
     *
     * @Route("/api/suggest/{{field}}")
     * 
     */
    public function suggestFieldValuesAction(Request $req, $field)
    {
        $fieldMap = [
            'industries' => 'totalIndustries.raw',
            'academicSubjects' => 'totalMajors.raw',
            'employers' => 'totalEmployers.raw',
            'skills' => 'totalSkills.raw',
        ];
        
        $field = isset($fieldMap[$field]) ? $fieldMap[$field] : $field;
        
        $query = $req->query->get('q', false);
        $limit = abs($req->query->get('limit', 20));
        
        // todo: 
        //   * load/fetch entire facet value list
        //   * cache if not cached
        //   * search values if query
        //   * return limited results
        
        
    }
}