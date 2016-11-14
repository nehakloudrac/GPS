<?php

namespace GPS\AppBundle\Command\Debug;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This is to periodically clean candidate profile docs that may have suffered a bug that
 * allowed creation of a timeline event without a type.  If this happens, it breaks serialization
 * for that user's profile, thus breaking the app completely.  This is clearly a short term solution...
 */
class CleanEmptyTimelineEventsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('gps:debug:clean-empty-timeline-events')
            ->setDescription("Cleans profile docs of corrupted timeline information.")
        ;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $doctrine = $container->get('doctrine_mongodb');
        $mongo = $doctrine->getConnection()->selectDatabase($container->getParameter('mongodb_database'));
        $profileCol = $mongo->selectCollection('candidateProfiles');
        $logger = $container->get('monolog.logger.gps_app');

        // find docs that have timeline events that are missing a "type"
        $docs = $profileCol->find([
            'timeline' => [
                '$elemMatch' => [
                    'type' => [
                        '$exists' => false
                    ]
                ]
            ]
        ]);
        
        if (count($docs) == 0) {
            $logger->info("Checked profiles for typeless timeline events - all clean.");
            return;
        }
        
        // attempt to clean corrupted docs
        $logger->error(sprintf("Found %s profiles with typeless timeline events.  Attempting to clean...", count($docs)));
        
        foreach ($docs as $doc) {
            if (isset($doc['timeline']) && count($doc['timeline']) > 0) {
                $parsedTimeline = [];
                foreach ($doc['timeline'] as $evt) {
                    // only add back into the timeline if "type" is set
                    if (isset($evt['type'])) {
                        $parsedTimeline[] = $evt;
                    }
                }
                $doc['timeline'] = $parsedTimeline;
                
                try {
                    $profileCol->save($doc);
                    $logger->info(sprintf("Cleaned typeless events in profile doc [%s]", $doc['_id']));
                } catch (\Exception $e) {
                    $logger->critical(sprintf("Failed to clean profile doc [%s]: %s ", $doc['_id'], $e->getMessage()));
                }
            }
        }
    }
}
