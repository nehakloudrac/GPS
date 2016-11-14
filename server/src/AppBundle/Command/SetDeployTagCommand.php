<?php

namespace GPS\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Sets a deploy tag that is used for invalidated static assets and detecting when
 * api clients should refresh.  This should be run on any deployment, or if there is any other reason
 * to force a users browser to refresh.
 */
class SetDeployTagCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('gps:status:set-deploy-tag')
            ->setDescription("Change or update the current deploy tag.")
            ->addArgument('tag', InputArgument::REQUIRED, "Tag of deployment.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $raw = $input->getArgument('tag');
        $tag = md5($raw);
        $cache = $this->getContainer()->get('gps.local_cache');

        $res = $cache->save('gps.deploy-tag-raw', $raw);
        $cache->save('gps.deploy-tag', $tag);

        // Log this for posterity ...
        $this->getContainer()->get('monolog.logger.gps_app')->info("Set new deploy tag.", [
            'rawTag' => $raw,
            'tag' => $tag
        ]);

        $output->writeln(sprintf("Set 'gps.deploy-tag-raw': %s", $cache->fetch('gps.deploy-tag-raw')));
        $output->writeln(sprintf("Set 'gps.deploy-tag': %s", $cache->fetch('gps.deploy-tag')));
    }
}
