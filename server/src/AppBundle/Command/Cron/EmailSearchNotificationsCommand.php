<?php

namespace GPS\AppBundle\Command\Cron;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use GPS\AppBundle\Document;
use Carbon\Carbon;

/**
 * Send emails to candidates regarding search hits.
 *
 * Respect unsubscribe settings.
 */
class EmailSearchNotificationsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('gps:cron:email-search-notifications')
            ->setDescription("Send out weekly emails to users who have been searched.")
            ->addOption('dry-run', null, InputOption::VALUE_NONE, "Don't actually email users, just report would be emailed.")
        ;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->container = $c = $this->getContainer();
        $this->manager = $c->get('doctrine_mongodb')->getManager();
        $dryRun = $input->getOption('dry-run');
        
        // sure it's gross
        $this->mailer = $c->get('swiftmailer.mailer.filespooled');
        $this->templating = $c->get('templating');
        $this->router = $c->get('router');
        
        $logger = $this->container->get('monolog.logger.gps_mailer');
        $this->repo = $c->get('doctrine_mongodb')->getRepository('AppBundle:User');
        
        if ($dryRun) {
            $msg = "Search notifications mailer: beginning dry run, no emails will be sent or users modified.";
            $logger->info($msg);
            $output->writeln($msg);
        }
        
        $ids = $this->findUserIds();
        $errors = [];
        $notified = 0;
        foreach ($ids as $id) {
            try {
                $user = $this->repo->find($id);
                
                $email = $this->createEmailMessage($user);
                
                // only update history if not dry run
                if (!$dryRun) {
                    $user->getEmailHistory()->add(Document\EmailHistoryItem::createFromArray([
                        'emailKey' => 'search-notifications',
                        'sentAt' => new \DateTime('now'),
                    ]));
                    
                    $this->mailer->send($email);
                    $this->manager->flush();
                }
                
                $notified++;
            } catch (\Exception $e) {
                $msg = sprintf("Search notifications mailer: Errored on User(%s): [%s]: %s",$user->getId(), get_class($e), $e->getMessage());
                $errors[$user->getId()] = $msg;
                $output->writeln($msg);
                $logger->error($msg);
            }
        }
        
        $msg = sprintf("Search notifications mailer: notified [%s], encountered [%s] errors.", $notified, count($errors));
        $output->writeln($msg);
        $logger->info($msg);
        
        if ($dryRun) {
            $msg = "Search notifications mailer: finished dry run, no emails sent or users modified.";
            $output->writeln($msg);
            $logger->info($msg);
        }
        
        return;
    }
    
    private function createEmailMessage($user)
    {
        $emailFromAddress = $this->container->getParameter('email_from_address');
        $emailAssetUrl = $this->container->getParameter('email_asset_base_url');
        $unsubscribeToken = base64_encode(json_encode(['userId' => $user->getId(), 'emailKey' => 'search-notifications']));
        $unsubscribeLink = $this->container->getParameter('email_asset_base_url').$this->router->generate('unsubscribe', ['token' => $unsubscribeToken]);
        $dashboardLink = $this->container->getParameter('email_asset_base_url').$this->router->generate('dashboard-app');
        $tracker = $user->getTracker();
        $numSearchesLastMonth = $tracker->getProfileSearchHitsLastMonth();
        
        // parse email template
        $content = $this->templating->render('emails/search-activity.md.twig', [
            'user' => $user,
            'profile' => $user->getCandidateProfile(),
            'dashboardLink' => $dashboardLink,
            'unsubscribeLink' => $unsubscribeLink,
            'numSearchesLastMonth' => $numSearchesLastMonth,
            'email_assets_base_url' => $emailAssetUrl,
        ]);
        $body = $this->templating->render('email.html.twig', [
            'email_assets_base_url' => $emailAssetUrl,
            'content' => $content
        ]);
        
        return $this->mailer->createMessage()
            ->setSubject("Recent GPS account activity")
            ->setFrom([$emailFromAddress => "Global Professional Search"])
            ->setTo($user->getEmail())
            ->setBody($body, 'text/html')
            ->addPart($content, 'text/plain')
        ;
    }
    
    private function findUserIds()
    {
        // emails go out at the beginning of the month (in theory), and search stats
        // are rotated on the 1st of the month
        // SO... whichever month it currently is, only fetch people who have been
        // searched on/after the first of the PREVIOUS month AND who have not
        // received a search email yet after the first of THIS month
        $now = Carbon::now();
        
        $thisMonth = Carbon::now()
            ->year($now->year)
            ->month($now->month)
            ->day(1)
            ->hour(0)
            ->minute(0)
            ->second(0)
        ;
        
        $previousMonth = $thisMonth->copy()->subMonth();
        
        // only get enabled/verified users who have actually been searched
        // in the last month
        $userCriteria = [
            'isVerified' => true,
            'isEnabled' => true,
            'preferences.allowSearchActivityEmails' => true,
            'tracker.profileLastSearchHit' => ['$gte' => new \MongoDate($previousMonth->timestamp)],
            'tracker.statsLastComputed' => ['$gte' => new \MongoDate($thisMonth->timestamp)],
            'tracker.profileSearchHitsLastMonth' => ['$gt' => 0],
            'emailHistory' => [
                '$not' => [
                    '$elemMatch' => [
                        'emailKey' => 'search-notifications',
                        'sentAt' => ['$gte' => new \MongoDate($thisMonth->timestamp)]
                    ]
                ]
            ],
        ];
        
        $col = $this->repo->getMongoCollection();
        $col->setSlaveOkay(true);
        
        $idsCursor = $col->find($userCriteria, ['_id' => true]);
        
        $ids = array_map(function($item) {
            return $item['_id'];
        }, iterator_to_array($idsCursor));
        
        return $ids;
    }
}
