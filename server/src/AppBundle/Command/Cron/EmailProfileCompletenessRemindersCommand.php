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
class EmailProfileCompletenessRemindersCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('gps:cron:email-candidate-profile-completeness')
            ->setDescription("Send out reminder emails to candidates based on their profile status.")
            ->addOption('dry-run', null, InputOption::VALUE_NONE, "Don't actually email users, just report would be emailed.")
            ->addOption('user', null, InputOption::VALUE_REQUIRED, "Send email only to one specific user (for debugging purpose).")
        ;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->container = $this->getContainer();
        $this->docManager = $this->container->get('doctrine_mongodb')->getManager();
        $this->repo = $this->docManager->getRepository('AppBundle:User');
        $this->dryRun = $input->getOption('dry-run');
        $this->logger = $this->container->get('monolog.logger.gps_mailer');
        
        if ($this->dryRun) {
            $msg = "Profile health mailer: beginning dry run, no emails will be sent or users modified.";
            $this->logger->info($msg);
            $output->writeln($msg);
        }
        
        // check for whether or not to send test email to specific user
        $userEmail = $input->getOption('user');
        if ($userEmail) {
            $users = $this->repo->findBy(['email' => $userEmail]);
            if (count($users) == 0) {
                throw new \RuntimeException('No user found w/ email '.$userEmail);
            }
            
            $this->sendEmailToUser($users[0], false);
            
            return;
        }
        
        $now = time();
        $criteriaMatrix = [
            // registered over 24 hours, have no health emails
            ["24 hour", $now - 86400, null],
            // registered over 4 days ago, have 1 health email
            ["4 day", $now - 345600, 1],
            // registered over 2 weeks ago, have 2 health emails, 
            ["2 week", $now - 1209600, 2],
            // registered over 2 weeks, have between 3-7 health emails, and have not recieved one
            // in the last two weeks
            ["2 week recurring", $now - 1209600, ['$gte' => 3, '$lt' => 7]]
        ];

        // TODO trigger the check for the 2 week delayed email
        
        // process each batch of criteria
        $errors = [];
        $notified = 0;
        foreach ($criteriaMatrix as $criteria) {
            list($batchName, $time, $mailCount) = $criteria;
            $userIds = $this->findUserIds($time, $mailCount);

            $msg = sprintf("Profile health mailer: Batch [%s] matched [%s] users", $batchName, count($userIds));
            $output->writeln($msg);
            $this->logger->info($msg);

            // batch load user entities by id
            $users = $this->repo->createQueryBuilder()
                ->field('id')->in($userIds)
                ->getQuery()->execute()
            ;
            
            foreach ($users as $user) {
                try {
                    $this->sendEmailToUser($user);
                    $notified++;
                } catch (\Exception $e) {
                    $errmsg = sprintf(
                        "Profile health mailer: Error for User(%s): [%s] %s",
                        $user->getId(),
                        get_class($e),
                        $e->getMessage()
                    );
                    $errors[$user->getId()] = $errmsg;
                    $output->writeln($errmsg);
                    $this->logger->error($errmsg);
                }
            }
            
            // clear after each batch to conserve some memory
            $this->docManager->clear();
        }
        
        $msg = "Profile health mailer: total notified [$notified] with [".count($errors)."] errors";
        $this->logger->info($msg);
        $output->writeln($msg);
        
        if ($this->dryRun) {
            $msg = "Profile health mailer: finished dry run - no emails sent or users modified.";
            $output->writeln($msg);
            $this->logger->info($msg);
        }
        
        return;
    }
    
    private function sendEmailToUser($user, $update = true)
    {
        $mailer = $this->container->get('swiftmailer.mailer.filespooled');
        $templating = $this->container->get('templating');
        $router = $this->container->get('router');
        $dashboardLink = $this->container->getParameter('email_asset_base_url').$router->generate('dashboard-app');
        
        // assemble template vars for email template
        $emailFromAddress = $this->container->getParameter('email_from_address');
        $emailAssetUrl = $this->container->getParameter('email_asset_base_url');
        $unsubscribeToken = base64_encode(json_encode(['userId' => $user->getId(), 'emailKey' => 'profile-health']));
        $unsubscribeLink = $this->container->getParameter('email_asset_base_url').$router->generate('unsubscribe', ['token' => $unsubscribeToken]);
        
        // Switch template & subject based on whether or not they've seen the
        // profile tutorial (thus started it)
        $profile = $user->getCandidateProfile();
        
        $incompleteProfileItems = [];
        if ($profile->getProfileStatus() && true == $profile->getProfileStatus()->getIntroCompleted()) {
            
            // check for incomplete items in the profile
            $incompleteProfileItems = $this->checkIncompleteItems($profile);
            
            // are there any items to inform the user about?
            if (count($incompleteProfileItems) > 0) {
                // user has started profile
                $emailKey = 'profile-health';
                $emailSubject = "Your GPS Profile";
                $emailTemplate = 'emails/profile-health-incomplete.md.twig';
            } else {
                $emailKey = 'profile-completed';
                $emailSubject = 'Congratulations';
                $emailTemplate = 'emails/profile-health-complete.md.twig';
            }
        } else {
            // user has not really started profile yet
            $emailKey = 'profile-health';
            $emailSubject = "Global Professional Search";
            $emailTemplate = 'emails/profile-health-not-started.md.twig';
        }
        
        // parse email template
        $content = $templating->render($emailTemplate, [
            'user' => $user,
            'profile' => $profile,
            'dashboardLink' => $dashboardLink,
            'unsubscribeLink' => $unsubscribeLink,
            'email_assets_base_url' => $emailAssetUrl,
            'incomplete_items' => $incompleteProfileItems
        ]);
        $body = $templating->render('email.html.twig', [
            'email_assets_base_url' => $emailAssetUrl,
            'content' => $content
        ]);
        
        // create & send email to user
        $message = $mailer->createMessage()
            ->setSubject($emailSubject)
            ->setFrom([$emailFromAddress => "Global Professional Search"])
            ->setTo($user->getEmail())
            ->setBody($body, 'text/html')
            ->addPart($content, 'text/plain')
        ;
        
        if (!$this->dryRun) {
            $mailer->send($message);
            $msg = sprintf("Profile health mailer: Sent [%s] to User(%s)[%s]", $emailKey, $user->getId(), $user->getEmail());
            $this->logger->info($msg);

            // update users email history
            if ($update) {
                $user->getEmailHistory()->add(Document\EmailHistoryItem::createFromArray([
                    'emailKey' => $emailKey,
                    'sentAt' => new \DateTime('now')
                ]));
            }

            // may not be efficient to flush after every user, but... seems safest for now
            // will refactor when performance becomes an issue
            $this->docManager->flush();
        }
    }
    
    private function findUserIds($timestamp, $countCriteria = null)
    {
        $userCriteria = [
            'isVerified' => true,
            'isEnabled' => true,
            'preferences.allowProfileHealthEmails' => true,
            'dateCreated' => ['$lte' => new \MongoDate($timestamp)],
            'candidateProfile' => ['$exists' => true],
            '$and' => [
                ['emailHistory' => [
                    '$not' => [
                        '$elemMatch' => [
                            'emailKey' => 'profile-completed'
                        ]
                    ]
                ]],
                ['emailHistory' => [
                    '$not' => [
                        '$elemMatch' => [
                            'emailKey' => 'profile-health',
                            'sentAt' => ['$gte' => new \MongoDate($timestamp)]
                        ]
                    ]
                ]]
            ],
        ];
        
        $emailCriteria = null;
        if ($countCriteria) {
            $emailCriteria = [
                'emailHistory.emailKey' => "profile-health"
            ];
        } else {
            // if we have no count criteria, it means we only want people who have
            // recieved NO profile-health emails at all
            $userCriteria['$and'][] = [
                'emailHistory' => [
                    '$not' => [
                        '$elemMatch' => [
                            'emailKey' => 'profile-health'
                        ]
                    ]
                ]
            ];
        }
        
        return $this->repo->aggregateIdsByEmailHistoryCount($userCriteria, $emailCriteria, $countCriteria);
    }
    
    private function checkIncompleteItems($profile)
    {
        $checks = [
            'checkWorkHistory',
            'checkEducationHistory',
            'checkCountries',
            'checkLanguages',
            'checkHardSkills',
            'checkSoftSkills',
            'checkTaggedSkills',
            'checkEnvironmentPrefs',
            'checkEmployerIdeals',
        ];
        
        $items = [];
        foreach ($checks as $check) {
            if (count($items) < 3) {
                $res = $this->{$check}($profile);
                if (is_string($res)) {
                    $items[] = $res;
                }
            }
        }
        
        return $items;
    }
    
    private function checkWorkHistory($profile)
    {
        $items = $profile->getTimelineByTypes(['job','research','military','volunteer']);
        $link = $this->generateProfileUrl($profile, '/edit/professional');
        $shortFormYears = null;
        if ($profile->getShortForm()) {
            $shortFormYears = $profile->getShortForm()->getYearsWorkExperience();
        }
        
        if (count($items) == 0 && null == $shortFormYears) {
            return "Your professional history is one of the most significant criterion considered by employers. If you have limited work experience, we encourage you to include any internship or volunteer experience in your GPS profile. The more complete your profile, the more interest you will receive.  [Add professional history]($link)";
        }
        
        if (count($items) == 0 && $shortFormYears > 0 ) {
            return "We noticed that you have yet to detail your professional history. This is one of the most significant criterion for hiring employers and they often will not consider profiles that do not include sufficient detail.  [Add professional history]($link)";
        }

        $incomplete = false;
        foreach ($items as $item) {
            if (!$item->isComplete()) {
                $incomplete = true;
            }
        }
        
        if ($incomplete) {
            return "Your work history is missing some relevant information. This is one of the most significant criterion for hiring employers and they often will not consider profiles that do not include sufficient detail. [Complete professional history]($link)";
        }
    }
    
    private function checkEducationHistory($profile)
    {
        $items = $profile->getTimelineByTypes(['university', 'study_abroad', 'language_acquisition']);
        $link = $this->generateProfileUrl($profile, '/edit/education');
        if (count($items) == 0) {
            return "You have yet to detail your educational background. Employers require this information before consideration.  [Add education history]($link)";
        }
        
        $incomplete = false;
        foreach ($items as $item) {
            if (!$item->isComplete()) {
                $incomplete = true;
            }
        }
        
        if ($incomplete) {
            return "Some items in your education history are incomplete. Educational history is an important factor for prospective employers and they often will not consider profiles that do not include sufficient detail. [Complete education history]($link)";
        }
    }
    
    private function checkCountries($profile)
    {
        $countries = $profile->getCountries();
        $link = $this->generateProfileUrl($profile, '/edit/countries');
        
        if (!$countries || count($countries) == 0) {
            return "Have you spent any significant time in countries abroad? We did not see this information in your profile. Experience abroad is a key factor for employers when assessing your global skills. [Add experience abroad]($link)";
        }
        
        $incompleteCountries = [];
        foreach ($countries as $country) {
            if (!$country->isComplete()) {
                $incompleteCountries[] = $country;
            }
        }
        if (count($incompleteCountries) == 0) { return; }
        
        $countryCodes = $this->container->getParameter('gps.form.countries');
        $countryNames = [];
        foreach ($incompleteCountries as $country) {
            if (isset($countryCodes[$country->getCode()])) {
                $countryNames[] = $countryCodes[$country->getCode()];
            }
        }
        
        if (count($countryNames) > 0) {
            return "We see that you have spent significant time in ".implode(', ', $countryNames)." but we don’t see your degree of comfort with the local culture or business environment. This information is a key factor for employers when assessing your familiarity with a region. [Complete section]($link)";
        }
    }
    
    private function checkLanguages($profile)
    {
        $langs = $profile->getLanguages();
        $link = $this->generateProfileUrl($profile, '/edit/languages');
        if (!$langs || count($langs) == 0) {
            return "Do you speak, or have you studied, any foreign languages? We did not see this information in your profile. Language is a key factor for employers when assessing your global competencies. Get your language and culture to work. [Add foreign languages]($link)";
        }
        
        $incompleteLangs = [];
        foreach ($langs as $lang) {
            if (!$lang->isComplete()) {
                $incompleteLangs[] = $lang;
            }
        }
        
        if (count($incompleteLangs) == 0) {return;}
        $langNames = [];
        $langCodes = $this->container->getParameter('gps.form.languages');
        foreach($incompleteLangs as $lang) {
            if (isset($langCodes[$lang->getCode()])) {
                $langNames[] = $langCodes[$lang->getCode()];
            }
        }
        
        if (count($langNames) > 0) {
            return "You have indicated some knowledge of ".implode(', ', $langNames)." in your profile. However, employers are interested in your level of proficiency and the extent that you currently use the language. We encourage you to add this information to increase the appeal of your profile. [Complete section]($link)";
        }
    }
    
    private function checkHardSkills($profile)
    {
        if ($profile->getHardSkills() && $profile->getHardSkills()->isComplete()) {
            return;
        }
        $link = $this->generateProfileUrl($profile, '/edit/skills');
        
        return "Your hard skills are an integral component of your skill set. Be sure to include these relevant skills and further enrich your GPS profile. [Specify hard skills]($link)";
    }
    
    private function checkSoftSkills($profile)
    {
        if ($profile->getSoftSkills() && count($profile->getSoftSkills()) > 0) {
            return;
        }
        $link = $this->generateProfileUrl($profile, '/edit/skills');

        return "Your personal strengths and values are a key factor in assessing the right fit with an employer.  We encourage you to rank your soft skills in your profile for a better job match.  [Rank skills]($link)";
    }
    
    private function checkTaggedSkills($profile)
    {
        if ($profile->getDomainSkills() && $profile->getDomainSkills()->isComplete()) {
            return;
        }
        $link = $this->generateProfileUrl($profile, '/edit/skills');
        
        return "Domain skills are unique to you and your industry. We encourage you to include domain skills to increase the appeal of your GPS profile.  [Add skills]($link)";
    }
    
    private function checkEnvironmentPrefs($profile)
    {
        if (
            $profile->getIdealJob() &&
            $profile->getIdealJob()->getPreferences() &&
            $profile->getIdealJob()->getPreferences()->isComplete()
        ) {
            return;
        }
        $link = $this->generateProfileUrl($profile, '/edit/ideal-job');
        
        return "Corporate culture is an important consideration when job searching. Share your work environment preferences in order to facilitate a better job match. [Specify preferences]($link)";
    }
    
    private function checkEmployerIdeals($profile)
    {
        $ideals = $profile->getEmployerIdeals();
        if ($ideals && count($ideals) > 0) { return; }
        $link = $this->generateProfileUrl($profile, '/edit/ideal-job');

        return "Do you have particular ideals that you prefer in an employer?  Allow us to consider your ideals in assessing the best job match for you... [Specify employer ideals]($link)";
    }
    
    private function generateProfileUrl($profile, $path)
    {
        $user = $profile->getUser();
        $router = $this->container->get('router');
        $targetUrl = $router->generate('profile-app').'#'.$path;
        
        $generator = $this->container->get('gps.data_token_generator');
        return $generator->generatePrivateDataUrl([
            'action' => 'redirect',
            'userId' => $user->getId(),
            'data' => [
                'target' => $targetUrl
            ]
        ]);
    }
}
