<?php

namespace GPS\AppBund\Service;

use GPS\AppBundle\Document;

/**
 * Takes care of publishing documents to the SQS indexer queue
 * and updating them accordingly.
 */
class SearchIndexPublisher
{
    protected $doctrine;
    protected $sqs;
    protected $serializer;
    protected $logger;
    
    public function __construct($doctrine, $sqs, $serializer, $logger)
    {
        $this->doctrine = $doctrine;
        $this->sqs = $sqs;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }
    
    public function publishCandidate(Doc\User $user, $remove = false)
    {
        $user->setUpdateLastModified(false);

        // TODO: 
        // - create SQS message
        // - serialize w/ proper groups
        // - call SQS, return result
        throw new \RuntimeException("Not implemented");
    }
    
    public function publishPosition()
    {
        throw new \RuntimeException("Not implemented");
    }
    
    public function createSQSMessage()
    {
        
    }
}
