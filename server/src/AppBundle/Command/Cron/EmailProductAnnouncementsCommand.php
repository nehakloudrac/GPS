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
class EmailProductAnnouncementsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('gps:cron:email-product-announcements')
            ->setDescription("Send emails to users about new features based on their registration date.")
            ->addOption('dry-run', null, InputOption::VALUE_NONE, "Don't actually email users, just report would be emailed.")
        ;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->container = $c = $this->getContainer();
        $announcements = $c->getParameter('gps.product_announcements');
        $dryRun = $input->getOption('dry-run');
        
        // sure it's gross
        $this->mailer = $c->get('swiftmailer.mailer.filespooled');
        $this->templating = $c->get('templating');
        $this->router = $c->get('router');
        
        $logger = $this->container->get('monolog.logger.gps_mailer');
        $doctrine = $c->get('doctrine_mongodb');
        $manager = $doctrine->getManager();
        $repo = $doctrine->getRepository('AppBundle:User');
        
        // ensure each known announcement gets sent
        foreach ($announcements as $announcement) {
            // ensure that email template exists, log and skip if not
            $successes = 0;
            $errors = [];
            $anyToSend = false;
            foreach ($this->getUsersIdsForAnnouncement($announcement) as $userId) {
                $anyToSend = true;
                
                try {
                    $user = $repo->find($userId);
                    
                    $email = $this->createEmail($user, $announcement);
                    
                    $user->getEmailHistory()->add(Document\EmailHistoryItem::createFromArray([
                        'sentAt' => new \DateTime('now'),
                        'emailKey' => 'product-announcement',
                        'tags' => $announcement['tags'],
                    ]));
                    
                    if (!$dryRun) {
                        $this->mailer->send($email);
                        $manager->flush();
                    }
                    
                    $successes++;
                } catch (\Exception $e) {
                    $msg = sprintf("Product Announcements: Failed sending [%s] to User(%s): %s", implode(',', $announcement['tags']), $user->getId(), $e->getMessage());
                    $errors[$user->getId()] = $msg;
                    $output->writeln($msg);
                    $logger->error($msg);
                }
            }
            
            if ($anyToSend) {
                $msg = sprintf("Product Announcements: Sent announcement [%s]; Ok [%s], Errors [%s]", implode(',', $announcement['tags']), $successes, count($errors));
            } else {
                $msg = sprintf("Product Announcements: skipping [%s], none to send.", implode(',', $announcement['tags']));
            }
            
            $output->writeln($msg);
            $logger->info($msg);
        }
        
        return 0;
    }
    
    private function createEmail($user, $announcement)
    {
        $emailFromAddress = $this->container->getParameter('email_from_address');
        $emailAssetUrl = $this->container->getParameter('email_asset_base_url');
        $unsubscribeToken = base64_encode(json_encode(['userId' => $user->getId(), 'emailKey' => 'product-announcement']));
        $unsubscribeLink = $emailAssetUrl.$this->router->generate('unsubscribe', ['token' => $unsubscribeToken]);
        $dashboardLink = $emailAssetUrl.$this->router->generate('dashboard-app');
        
        $templateVars = [
            'user' => $user,
            'profile' => $user->getCandidateProfile(),
            'dashboardLink' => $dashboardLink,
            'unsubscribeLink' => $unsubscribeLink,
            'email_assets_base_url' => $emailAssetUrl,
        ];
        
        // different emails may want to redirect the user to a specific place
        if (isset($announcement['targetLink'])) {
            $templateVars['targetLink'] = $emailAssetUrl.$announcement['targetLink'];
        }
        
        // parse email template
        $content = $this->templating->render($announcement['template'], $templateVars);
        $body = $this->templating->render('email.html.twig', [
            'email_assets_base_url' => $emailAssetUrl,
            'content' => $content
        ]);
        
        return $this->mailer->createMessage()
            ->setSubject($announcement['subject'])
            ->setFrom([$emailFromAddress => "Global Professional Search"])
            ->setTo($user->getEmail())
            ->setBody($body, 'text/html')
            ->addPart($content, 'text/plain')
        ;
    }
    
    private function getUsersIdsForAnnouncement($announcement)
    {
        $deployDate = \DateTime::createFromFormat('Y-m-d', $announcement['deployed']);
        
        $criteria = [
            'isVerified' => true,
            'isEnabled' => true,
            'dateCreated' => ['$lte' => new \MongoDate($deployDate->getTimestamp())],
            'preferences.allowProductFeatureEmails' => true,
            'emailHistory' => [
                '$not' => [
                    '$elemMatch' => [
                        'emailKey' => 'product-announcement',
                        'tags' => ['$in' => $announcement['tags']]
                    ]
                ]
            ]
        ];
        
        // hard coded criteria takes precedence for safety
        if (isset($announcement['match']['user'])) {
            $criteria = array_merge($announcement['match']['user'], $criteria);
        }
        
        $col = $this->getContainer()
            ->get('doctrine_mongodb')
            ->getRepository('AppBundle:User')
            ->getMongoCollection()
        ;

        $col->setSlaveOkay(true);

        $idsCursor = $col->find($criteria, ['_id' => true]);
        
        return array_map(function ($doc) {
            return $doc['_id'];
        }, iterator_to_array($idsCursor));
    }
}