<?php

namespace GPS\AppBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ReferrersCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // merge actual configured referrers with test ones in dev/test environments
        if (in_array($container->getParameter('kernel.environment'), ['dev','test'])) {
            $merged = array_merge($container->getParameter('gps.referrers'), $container->getParameter('gps.test_referrers'));
            $container->setParameter('gps.referrers', $merged);
        }
    }
}