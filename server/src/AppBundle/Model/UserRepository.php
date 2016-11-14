<?php

namespace GPS\AppBundle\Model;

use Doctrine\ODM\MongoDB\DocumentRepository;

class UserRepository extends DocumentRepository
{
    public function getMongoCollection()
    {
        return $this->getDocumentManager()->getDocumentCollection($this->getClassName());
    }
    
    public function aggregateIdsByEmailHistoryCount($userCriteria = [], $emailCriteria = null, $countCriteria = null)
    {
        $col = $this->getMongoCollection();
        $col->setSlaveOkay(true);
        
        // we do a normal find first, because if people with NO matching emails
        // should be returned, then the aggregation query will miss them, so we
        // return early before doing it.
        $idsResult = $col->find($userCriteria, ['_id' => true]);
        $ids = array_map(function($item) {
            return $item['_id'];
        }, iterator_to_array($idsResult));
        
        if (!$emailCriteria) {
            return $ids;
        }
                
        $ops = [
            ['$match' => ['_id' => ['$in' => array_values($ids)]]],
            ['$project' => [
                '_id' => true,
                'email' => true,
                'emailHistory' => true
            ]],
            ['$unwind' => '$emailHistory'],
            ['$match' => $emailCriteria],
            ['$group' => [
                '_id' => [
                    'id' => '$_id',
                    'email' => '$email',
                ],
                'emailCount' => ['$sum' => 1]
            ]]
        ];
        
        if (null !== $countCriteria) {
            $ops[] = ['$match' => ['emailCount' => $countCriteria]];
        }
        
        $result = $col->aggregate($ops);
        $ids = array_map(function($item) {
            return $item['_id']['id'];
        }, iterator_to_array($result));
        
        return $ids;
    }
}
