<?php

namespace GPS\AppBundle\Command\User;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use JMS\Serializer\SerializationContext;


class PromoteUserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('gps:user:promote')
            ->setDescription("Add a role to a user.")
            ->addArgument('email', InputArgument::REQUIRED, "Email address of user.")
            ->addArgument('role', InputArgument::REQUIRED, "Role to add to user.")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $input->getArgument('email');

        $manager =  $this->getContainer()->get('doctrine_mongodb')->getManager();
        $user = $manager
            ->getRepository('AppBundle:User')
            ->findOneBy(['email' => $email]);

        if (!$user) {
            throw new \InvalidArgumentException("No user found for email [$email]");
        }

        //modify roles
        $roles = $user->getRoles();
        $roles[] = $input->getArgument('role');
        $user->setRoles(array_unique($roles));
        
        $errors = $this->getContainer()->get('validator')->validate($user);
        if (count($errors) > 0) {
            \dump($errors);
            throw new \RuntimeException("User failed validation.");
        }
        
        $manager->flush();
        
        $output->writeln(sprintf("Added role %s to user %s", $input->getArgument('role'), $email));
    }
}
