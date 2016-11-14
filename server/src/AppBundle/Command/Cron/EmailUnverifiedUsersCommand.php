<?php

namespace GPS\AppBundle\Command\Cron;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use GPS\AppBundle\Document;

/**
 * Send emails to candidates regarding their profile status.
 *
 * Respect unsubscribe settings.
 */
class EmailUnverifiedUsersCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('gps:cron:email-unverified-users')
            ->setDescription("Send out weekly reminder emails to users who have unverified email addresses.")
            ->addOption('dry-run', null, InputOption::VALUE_NONE, "Don't actually email users, just report would be emailed.")
        ;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $manager = $container->get('doctrine_mongodb')->getManager();
        $this->repo = $manager->getRepository('AppBundle:User');
        $dryRun = $input->getOption('dry-run');
        $logger = $container->get('monolog.logger.gps_mailer');
        $mailer = $container->get('gps.mailer');
        $swiftmailer = $container->get('swiftmailer.mailer.filespooled');
        $mailer->setMailer($swiftmailer);
        
        if ($dryRun) {
            $mailer->setEnabled(false);
            $msg = "Unverified users mailer: beginning dry run, no emails will be sent or users modified.";
            $logger->info($msg);
            $output->writeln($msg);
        }
        
        $ids = $this->findUserIds();
        $errors = [];
        $notified = 0;
        foreach ($ids as $id) {
            try {
                $user = $this->repo->find($id);
                $mailer->sendAccountVerificationEmail($user, "Your GPS Account is Still Not Verified");
                
                // only update history if not dry run
                if (!$dryRun) {
                    $user->getEmailHistory()->add(Document\EmailHistoryItem::createFromArray([
                        'emailKey' => 'email-unverified',
                        'sentAt' => new \DateTime('now'),
                    ]));
                    
                    $manager->flush();
                }
                
                $notified++;
            } catch (\Exception $e) {
                $msg = sprintf("Unverified users mailer: Errored on User(%s): [%s]: %s",$user->getId(), get_class($e), $e->getMessage());
                $errors[$user->getId()] = $msg;
                $output->writeln($msg);
                $logger->error($msg);
            }
        }
        
        $msg = sprintf("Unverified users mailer: notified [%s], encountered [%s] errors.", $notified, count($errors));
        $output->writeln($msg);
        $logger->info($msg);
        
        if ($dryRun) {
            $msg = "Unverified users mailer: finished dry run, no emails sent or users modified.";
            $output->writeln($msg);
            $logger->info($msg);
        }
        
        return;
    }
    
    private function findUserIds()
    {
        // current time minus 7 days
        $timestamp = time() - 604800;
        
        // the user is not verified, registered a week or more ago, and has
        // NOT recieved a "verify your email" message within the last week
        $userCriteria = [
            'isVerified' => false,
            'isEnabled' => true,
            'dateCreated' => ['$lte' => new \MongoDate($timestamp)],
            'emailHistory' => [
                '$not' => [
                    '$elemMatch' => [
                        'emailKey' => 'email-unverified',
                        'sentAt' => ['$gte' => new \MongoDate($timestamp)]
                    ]
                ]
            ],
        ];
        
        $idsCursor = $this->repo->getMongoCollection()->find($userCriteria, ['_id' => true]);
        
        $ids = array_map(function($item) {
            return $item['_id'];
        }, iterator_to_array($idsCursor));
        
        return $ids;
    }
}
