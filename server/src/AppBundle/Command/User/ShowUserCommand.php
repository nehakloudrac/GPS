<?php

namespace GPS\AppBundle\Command\User;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use JMS\Serializer\SerializationContext;


class ShowUserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('gps:user:show')
            ->setDescription("Show a user.")
            ->addArgument('email', InputArgument::REQUIRED, "Email address of user.")
            ->addOption('raw', null, InputOption::VALUE_NONE, 'Dump raw data instead of hydrating and serializing entity');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $input->getArgument('email');

        $manager =  $this->getContainer()->get('doctrine_mongodb')->getManager();
        
        // raw query
        if ($input->getOption('raw')) {
            $mongo = $manager->getConnection()->selectDatabase($this->getContainer()->getParameter('mongodb_database'));
            $user = $mongo->selectCollection('users')->findOne(['email' => $email]);
            $profile = $mongo->selectCollection('candidateProfiles')->findOne(['_id' => $user['candidateProfile']]);

            $output->writeln('User:');
            $output->writeln(json_encode($user, JSON_PRETTY_PRINT));
            $output->writeln('Profile:');
            $output->writeln(json_encode($profile, JSON_PRETTY_PRINT));
            return;
        }
        
        $user = $manager
            ->getRepository('AppBundle:User')
            ->findOneBy(['email' => $email]);

        if (!$user) {
            throw new \InvalidArgumentException("No user found for email [$email]");
        }

        $ctx = SerializationContext::create()->setGroups(['Default', 'User.candidateProfile']);

        $data = $this->getContainer()->get('serializer')->serialize($user, 'yml', $ctx);

        $output->writeln($data);
    }
}
