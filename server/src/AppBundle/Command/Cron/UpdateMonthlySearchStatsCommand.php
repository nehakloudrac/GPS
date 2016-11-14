<?php

namespace GPS\AppBundle\Command\Cron;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use JMS\Serializer\SerializationContext;
use Carbon\Carbon;

/**
 * This is intended to run on the first of every month to update search stats...
 * this should execute before emails get sent out.
 */
class UpdateMonthlySearchStatsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('gps:cron:update-monthly-search-stats')
            ->setDescription("Update monthly search stats for candidate profiles.")
            ->addOption('dry-run', null, InputOption::VALUE_NONE, "Just match users who would be updated, but don't actually update.")
        ;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $logger = $container->get('monolog.logger.gps_app');
        $manager = $container->get('doctrine_mongodb')->getManager();
        $repo = $container->get('doctrine_mongodb')->getRepository('AppBundle:User');
        $dryRun = $input->getOption('dry-run');

        // find users who have been searched at some point this month, and
        // who have not had their stats rotated yet?
        $now = Carbon::now();
        $targetTimestamp = Carbon::now()
            ->year($now->year)
            ->month($now->month)
            ->day(0)
            ->hour(0)
            ->minute(0)
            ->timestamp
        ;
        
        $col = $repo->getMongoCollection();
        $col->setSlaveOkay(true);
        
        $idsCursor = $col->find([
            'isVerified' => true,
            'isEnabled' => true,
            '$or' => [
                ['tracker.statsLastComputed' => ['$exists' => false]],
                ['tracker.statsLastComputed' => ['$lt' => new \MongoDate($targetTimestamp)]],
            ]
            ,
        ], ['_id' => true]);
        
        $ids = array_map(function($item) {
            return $item['_id'];
        }, iterator_to_array($idsCursor));
        
        // fetch and update each user
        $updated = 0;
        $errors = 0;
        foreach ($ids as $id) {
            $user = $repo->find($id);
            
            if (!$user) { continue; }
            
            $tracker = $user->getTracker();
            
            // this is correct: if there is no tracker it means they've never
            // even been searched, so there's nothing to compute
            if (!$tracker) { continue; }
            
            // get total numbers for last month
            $searchTotalLastMonth = ($tracker->getProfileSearchHitsTotalLastMonth()) ? $tracker->getProfileSearchHitsTotalLastMonth() : 0;
            $viewTotalLastMonth = ($tracker->getProfileViewsTotalLastMonth()) ? $tracker->getProfileViewsTotalLastMonth() : 0;
            
            // compute new numbers for how many hits last month
            $tracker->setProfileSearchHitsLastMonth($tracker->getProfileSearchHitsTotal() - $searchTotalLastMonth);
            $tracker->setProfileViewsLastMonth($tracker->getProfileViewsTotal() - $viewTotalLastMonth);
            
            // rotate the totals, moving current counts to last months counts            
            $tracker->setProfileSearchHitsTotalLastMonth($tracker->getProfileSearchHitsTotal());
            $tracker->setProfileViewsTotalLastMonth($tracker->getProfileViewsTotal());
            $tracker->setStatsLastComputed(new \DateTime('now'));
            
            if (!$dryRun) {
                try {
                    $manager->flush();
                } catch (\Exception $e) {
                    $errors++;
                    $msg = sprintf("Stats rotator: Errored on User(%s): %s", $user->getId(), $e->getMessage());
                    $logger->error($msg);
                    $output->writeln($msg);
                }
            }
            
            $updated++;
        }
        
        $msg = sprintf("Stats rotator: Updated user search stats for the current month: ok [%s] errors [%s].", $updated, $errors);
        $logger->info($msg);
        $output->writeln($msg);
    }
}
