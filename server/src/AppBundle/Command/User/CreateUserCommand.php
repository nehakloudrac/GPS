<?php

namespace GPS\AppBundle\Command\User;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateUserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('gps:user:create');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        throw new \RuntimeException("Not yet implemented.");
    }
}
