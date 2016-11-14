<?php

namespace GPS\AppBundle\Command\Cron;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use JMS\Serializer\SerializationContext;

class IndexCandidatesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('gps:cron:index-candidates')
            ->setDescription("Poll for candidates that should be sent to the search index via SQS.")
            ->addOption('force', null, InputOption::VALUE_NONE, "Reindex all users, regardless of when they were last indexed.")
        ;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $doctrine = $container->get('doctrine_mongodb');
        $docManager = $doctrine->getManager();
        $mongo = $doctrine->getConnection()->selectDatabase($container->getParameter('mongodb_database'));
        $cache = $container->get('gps.shared_cache');
        $force = $input->getOption('force');
        $this->logger = $container->get('monolog.logger.gps_app');
        
        // get final unique list of users based on recently modified ids 
        // of both users and profiles - if the user doesn't actually have
        // a profile, they don't count
        $candidateIds = $this->getRecentlyModifiedCandidateIds($mongo, $force);
        if (empty($candidateIds)) {
            $output->writeln("Nothing to index.");
            return 0;
        }
        $idBatches = $this->chunkIds($candidateIds, 10);
        
        // process each batch of ids
        $userRepo = $doctrine->getRepository('AppBundle:User');
        $sqs = $container->get('aws.sqs');
        $queueUrl = $container->getParameter('aws_sqs_indexer_queue_url');
        $this->serializer = $container->get('serializer');
        $numSent = 0;
        $numBatches = 0;
        
        // process each batch of users
        foreach ($idBatches as $ids) {
            $users = $userRepo->createQueryBuilder()->field('id')->in($ids)->getQuery()->execute();
            // TODO, batch fetch associated profiles

            $successes = [];
            $failures = [];
            
            // assemble sqs messages per user... note that we're NOT
            // using batch messages because of payload limits
            foreach ($users as $user) {
                // create the sqs message
                try {
                    $messageBody = $this->createSQSMessageBody($user);
                } catch (\Exception $e) {
                    $failures[] = $user;
                    $this->notifyIndexFailure($user, $e);
                    continue;
                }
                
                // send it
                // TODO: use async api to send concurrent requests in batch;
                // then wait on all to be fulfilled at end
                try {
                    $res = $sqs->sendMessage([
                        'QueueUrl' => $queueUrl,
                        'MessageBody' => $messageBody
                    ]);
                    $numSent++;
                } catch (\Exception $e) {
                    $failures[] = $user;
                    $this->notifyIndexFailure($user, $e);
                    continue;
                }
                
                $successes[] = $user;
            }
            
            // TODO "unwrap" promises here
            
            // forget the failures
            foreach ($failures as $user) {
                $docManager->detach($user);
                $docManager->detach($user->getCandidateProfile());
            }
            
            // update the successes
            $now = new \DateTime('now');
            foreach ($successes as $user) {
                $user->setLastIndexed($now);
                $user->getCandidateProfile()->setLastIndexed($now);
            }
            
            // persist changes
            $docManager->flush();
            $docManager->clear();
            $numBatches++;
            $output->writeln(sprintf("Finished batch: %s.", $numBatches));
        }
        
        $msg = sprintf("Published [%s] indexer messages.", $numSent);
        $output->writeln($msg);
        $this->logger->info($msg);
        
        return 0;
    }
    
    private function chunkIds(array $ids, $size = 10)
    {
        $final = [];
        $batch = [];
        $i = 0;
        foreach ($ids as $id) {
            if ($i == 0) {
                $batch = [];
            }

            $batch[] = $id;
            $i++;
            
            if ($i % $size == 0) {
                $i = 0;
                $final[] = $batch;
            }
        }
        
        if (!empty($batch)) {
            $final[] = $batch;
        }
        
        return $final;
    }
    
    protected function getRecentlyModifiedCandidateIds($mongo, $force = false)
    {
        $profileCol = $mongo->selectCollection('candidateProfiles');
        $userCol = $mongo->selectCollection('users');
        
        
        // only index users that actually have a profile
        $queryOps = ['candidateProfile' => ['$exists' => true]];
        
        if (!$force) {
            $relevantUserIds = $this->getRecentlyModifiedIds($userCol);
            $relevantProfileIds = $this->getRecentlyModifiedIds($profileCol);
            $queryOps['$or'] = [
                ['_id' => ['$in' => array_values($relevantUserIds)]],
                ['candidateProfile' => ['$in' => array_values($relevantProfileIds)]],
            ];
        }
        
        $candidateIdsCursor = $userCol->find($queryOps, ['_id' => true]);
        
        return array_map(function($doc) { return $doc['_id']; }, iterator_to_array($candidateIdsCursor));
    }
    
    protected function getRecentlyModifiedIds($col)
    {
        // find unindexed profile ids first
        $unindexedIdsCursor = $col->find([
            '$or' => [
                ['lastIndexed' => ['$exists' => false]],
                ['lastModified' => ['$exists' => false]],
            ]
        ], ['_id' => true]);
        $unindexedIds = array_map(function($doc) { return $doc['_id']; }, iterator_to_array($unindexedIdsCursor));
        
        // now find recently modified ones
        $recentlyModifiedIdsCursor = $col->aggregate([
            // find docs with both lastModified/Indexed, and compute
            // if it has been updated since it was last indexed
            ['$match' => [
                'lastModified' => ['$exists' => true],
                'lastIndexed' => ['$exists' => true],
            ]],
            
            // project the matched docs down the pipe, computing whether or not
            // they were modified after last index
            ['$project' => [
                'lastModified' => 1,
                'lastIndexed' => 1,
                'updated' => ['$gte' => ['$lastModified','$lastIndexed']],
            ]],
            
            // match if it has been updated
            ['$match' => [
                'updated' => true
            ]]
        ]);
        $recentlyModifiedIds = array_map(function($doc) { return $doc['_id']; }, iterator_to_array($recentlyModifiedIdsCursor));

        return array_merge($unindexedIds, $recentlyModifiedIds);
    }
    
    protected function createSQSMessageBody($user)
    {
        $messageData = [
            "clientName" => "gps",
            "clientId" => $user->getId(),
            "type" => "candidate",
            "state" => "present",
            "data" => [],
        ];
        
        // check removals & return early if so
        if ($removed = $user->getDateRemoved()) {
            $messageData["state"] = "absent";
            
            return $messageData;
        }

        $user->setUpdateLastModified(false);
        $profile = $user->getCandidateProfile();
        $profile->setUpdateLastModified(false);
        $profile->computeCompleteness();
        $profile->setSerializeCompleteness(true);
        $profile->computeProfessionalExperience();
        
        $ctx = SerializationContext::create()->setGroups([
            'Default',
            'User.email',
            'User.phone',
        ]);
        
        $messageData["data"]["user"] = $user;
        $messageData["data"]["profile"] = $profile;
        
        
        return $this->serializer->serialize($messageData, 'json', $ctx);
    }
    
    protected function notifyIndexFailure($user, \Exception $e)
    {
        $this->logger->error(sprintf("Indexer failed to publish message for user [%s] with [%s]", $user->getId(), $e->getMessage()));
    }
}
