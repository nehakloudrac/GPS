<?php

namespace GPS\AppBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GPS\AppBundle\Document\Candidate;
use GPS\AppBundle\Event\AppEvents;
use GPS\AppBundle\Event\UserEvent;

/**
 * When a user is removed, the search index needs to be notified of the removal.
 */
class UserRemovalSubscriber implements EventSubscriberInterface
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public static function getSubscribedEvents()
    {
        return [
            AppEvents::USER_DELETED => [
                ['notifySearchIndex', 0]
            ],
            AppEvents::USER_REMOVED => [
                ['notifySearchIndex', 0]
            ]
        ];
    }
    
    public function notifySearchIndex(UserEvent $e)
    {
        $user = $e->getUser();
        $id = $user->getId();
        
        $messageData = [
            "clientName" => "gps",
            "clientId" => $id,
            "type" => "candidate",
            "state" => "absent",
            "data" => [],
        ];

        $sqs = $this->container->get('aws.sqs');
        $sqs->sendMessage([
            'QueueUrl' => $this->container->getParameter('aws_sqs_indexer_queue_url'),
            'MessageBody' => json_encode($messageData)
        ]);
        
        $this->container->get('monolog.logger.gps_app')->info("Published search removal request for User[$id] to index queue.");
    }
    
}