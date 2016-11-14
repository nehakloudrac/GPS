<?php

namespace GPS\AppBundle\Command\Debug;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;


class ReferrerLinksCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('gps:debug:referrer-links')
            ->setDescription("List all available referrer links.")
            ->addOption('prefix', 'p', InputOption::VALUE_OPTIONAL, 'Prefix links.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $c = $this->getContainer();
        $referrers = $c->getParameter('gps.referrers');
        $routing = $c->get('router');
        $prefix = $input->hasOption('prefix') ? $input->getOption('prefix') : '';
        
        foreach ($referrers as $key => $ref) {
            $output->writeln('');
            $output->writeln(isset($ref['registration_theme']['logo']) ? $ref['name']." (w/ logo)" : $ref['name']);
            $output->writeln($prefix.$routing->generate('initiate-referrer-session', [
                'referrerKey' => $key
            ]));
        }
    }
}
