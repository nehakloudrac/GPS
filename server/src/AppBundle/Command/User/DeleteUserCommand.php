<?php

namespace GPS\AppBundle\Command\User;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use GPS\AppBundle\Event as Evt;


class DeleteUserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('gps:user:delete')
            ->setDescription("Remove a user account completely.")
            ->addArgument('email', InputArgument::REQUIRED, "Email address of user.")
            ->addOption('force', null, InputOption::VALUE_NONE, "Force document removal, not just deanonymization.")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $input->getArgument('email');
        $force = $input->getOption('force');

        $manager =  $this->getContainer()->get('doctrine_mongodb')->getManager();
        $user = $manager
            ->getRepository('AppBundle:User')
            ->findOneBy(['email' => $email]);

        if (!$user) {
            throw new \InvalidArgumentException("No user found for email [$email]");
        }

        // confirm action first
        if (!$this->getHelper('dialog')->askConfirmation(
            $output,
            '<question>Are you sure you want to completely remove this user?</question>',
            false
        )) {
            $output->writeln("Canceled.");
            return;
        }
        
        // if they have completed the sort form, err on the side of anonymization of data
        // otherwise, if "--force", or there isn't much data, do a hard removal
        
        $shortFormCompleted = false;
        $profile = $user->getCandidateProfile();
        
        // Note: this logic is correct, we care about the short form, not whether or not
        // they made it through the entire walkthrough
        if ($profile) {
            $shortFormCompleted = $profile->getShortForm() ? $profile->getShortForm()->getCompleted() : false;
        }
        
        if (!$shortFormCompleted || $force) {
            $event = Evt\AppEvents::USER_DELETED;
            $manager->remove($user);
            if ($profile) {
                $manager->remove($user->getCandidateProfile());
            }
        } else {
            $event = Evt\AppEvents::USER_REMOVED;
            $user->setFirstName('');
            $user->setLastName('');
            $user->setPreferredName('');
            $user->setPhone(null);
            $user->setIsEnabled(false);
            $user->setIsVerified(false);
            $user->setEmail(uniqid()."@removed.gps");
            $user->setDateRemoved(new \DateTime('now'));
        }

        // persist changes
        $manager->flush();
        
        // notify system of user removal
        $this->getContainer()->get('event_dispatcher')->dispatch($event, new Evt\UserEvent($user));

        $output->writeln(sprintf("User [%s] was removed.", $email));
    }
}
