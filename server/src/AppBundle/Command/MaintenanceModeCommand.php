<?php

namespace GPS\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Sets a deploy tag that is used for invalidated static assets and detecting when
 * api clients should refresh.  This should be run on any deployment, or if there is any other reason
 * to force a users browser to refresh.
 */
class MaintenanceModeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('gps:maintenance-mode')
            ->setDescription("Toggle maintenance mode.")
            ->addArgument('mode', InputArgument::REQUIRED, "'enable' or 'disable' the maintenance mode")
            ->addOption('message', 'm', InputOption::VALUE_REQUIRED, 'The message for end users', "GPS is currently undergoing scheduled maintenance.  Please try back later.")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $message = $input->getOption('message');
        $mode = $input->getArgument('mode');
        if (!in_array($mode, ['enable','disable'])) {
            throw new \InvalidArgumentException("Mode must be specified as one of 'enable' or 'disable'.");
        }
        $enabled = ('enable' === $mode) ? true : false;

        if (!$this->getHelper('dialog')->askConfirmation(
            $output,
            sprintf('<question>Are you sure you want to %s maintenance mode?</question>', $mode),
            false
        )) {
            $output->writeln("Canceled.");
            return;
        }

        
        // we're using shared cache for quicker sync between all servers...
        // this could have unintended consequences, but I can't think of what they
        // may be.  In the future it may be better to store the maintenance lock
        // locally on a per-server basis.  I'm not doing that now, because I think
        // the global approach is more correct.
        $cache = $this->getContainer()->get('gps.shared_cache');
        
        if ($enabled) {
            $logMsg = sprintf("Maintenence mode enabled: %s", $message);
            $cache->save('maintenance.lock', $message);
        } else {
            $logMsg = "Maintenance mode disabled.";
            $cache->delete('maintenance.lock');
        }
        
        $this->getContainer()->get('monolog.logger.gps_app')->info($logMsg);
        $output->writeln($logMsg);
    }
}
