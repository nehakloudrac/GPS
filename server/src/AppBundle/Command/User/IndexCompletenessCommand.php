<?php

namespace GPS\AppBundle\Command\User;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

// TODO: deprecate and remove this once new search indexer functionality is in place
class IndexCompletenessCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('gps:user:index-profile-completeness')
            ->setDescription("Reindex candidate profile completness values.")
            ->addOption('dry-run', null, InputOption::VALUE_NONE, "Don't actually update users, just report totals to update.");
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $doctrine = $container->get('doctrine_mongodb');
        $docManager = $doctrine->getManager();
        $mongo = $doctrine->getConnection()->selectDatabase($container->getParameter('mongodb_database'));
        $cache = $container->get('gps.shared_cache');
        $col = $mongo->selectCollection('candidateProfiles');
        $dryRun = $input->getOption('dry-run');
        $logger = $container->get('monolog.logger.gps_app');

        // calculate time thresholds for documents to update
        $reindexDelay = $container->getParameter('gps.profile.completeness_reindex_delay_minutes');
        $updateThreshold = new \DateTime();
        $updateThreshold->sub(\DateInterval::createFromDateString("$reindexDelay minutes"));
        
        // check for last run value in cache... we only want to look at
        // profiles that have been modified since that time
        $lastRun = $cache->fetch('gps.completeness-indexer-last-run');
        if (!$lastRun) {
            $lastRun = 0;
        }
        $indexerRunTime = time();
                
        // find profiles which: 
        //      * have no lastModified date; OR
        //      * have not had completeness indexed; OR
        //      * profile.lastModified is greater than reindex threshold AND profile.lastModified is less than the update threshold; AND
        //      * profile.lastModified is greater than last completeness update
        $idsCursor = $col->find([
            '$or' => [
                ['profileStatus.completeness' => ['$exists' => false]],
                ['profileStatus.completeness.lastUpdated' => ['$exists' => false]],
                ['lastModified' => ['$exists' => false]],
                ['lastModified' => [
                    '$lt' => new \MongoDate($updateThreshold->getTimestamp()),
                    '$gt' => new \MongoDate($lastRun)]
                ],
            ]
            
        ], ['_id' => true]);
        
        $ids = array_map(function($doc) { return $doc['_id']; }, iterator_to_array($idsCursor));
        
        // return early if nothing to update
        if (empty($ids)) {
            $output->writeln("No profiles to index.");
            $logger->info("Completeness indexer: No profiles to index.");
            return;
        }
        
        // find and update matched profiles
        $profiles = $docManager->getRepository('AppBundle:Candidate\Profile')
            ->createQueryBuilder()
            ->field('id')->in($ids)
            ->getQuery()->execute()
        ;
        
        // iterate over matched profiles, saving updates in batches
        $batchSize = 20;
        $i = 0;
        $successes = 0;
        $batches = 0;
        $errorMap = [];
        
        foreach ($profiles as $profile) {
            $i++;

            try {
                $profile->setUpdateLastModified(false);
                $profile->computeCompleteness();
                $successes++;
            } catch (\Exception $e) {
                $msg = sprintf("ID(%s): [%s] %s", $profile->getId(), get_class($e), $e->getMessage());
                $output->writeln($msg);
                $errorMap[$profile->getId()] = $msg;
            }
            
            // flush if batch size is met
            if (($i % $batchSize) === 0) {
                $batches++;
                
                // only actually flush if not a dry run
                if (!$dryRun) {
                    try {
                        $docManager->flush();
                        $output->writeln(sprintf("Flushed batch: %s", $batches));
                    } catch (\Exception $e) {
                        $output->writeln(sprintf("Error flushing batch %s changes: %s", $batches, $e->getMessage()));
                    }
                }
                $docManager->clear();
            }
        }

        // process last batch
        $batches++;
        if (!$dryRun) {
            try {
                $docManager->flush();
                $output->writeln(sprintf("Flushed final batch: %s", $batches));
            } catch (\Exception $e) {
                $output->writeln(sprintf("Error on final flush: %s", $e->getMessage()));
            }
        }
        $docManager->clear();
        
        // report results
        if ($dryRun) {
            $output->writeln(sprintf(
                "Matched %s total in dry-run; no docs actually updated.",
                count($profiles)
            ));
        }
        
        $out = sprintf(
            "Updated: %s; Errors: %s; Batches: %s",
            $successes,
            count($errorMap),
            $batches
        );

        // if we ran for real, note it by saving in cache
        if (!$dryRun) {
            $cache->save('gps.completeness-indexer-last-run', $indexerRunTime);
            $logger->info($out);
            foreach ($errorMap as $id => $msg) {
                $logger->error(sprintf("Completeness indexer: Error on doc %s", $msg));
            }
        }
        
        
        $output->writeln($out);
        $output->writeln("Done.");
    }
}
