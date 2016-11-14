<?php

namespace GPS\AppBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use GPS\AppBundle\DependencyInjection as DI;

/**
 * Some configuration is generated dynamically when the container
 * is built.
 */
class AppBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
    
        $container->addCompilerPass(new DI\ListConfigCompilerPass());
        $container->addCompilerPass(new DI\ReferrersCompilerPass());
    }
}
