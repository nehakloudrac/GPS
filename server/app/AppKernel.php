<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            //included in standard edition
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),

            //other thirdparty
            new Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
            new AC\WebServicesBundle\ACWebServicesBundle(),
            new Aws\Symfony\AwsBundle(),
            new Oneup\FlysystemBundle\OneupFlysystemBundle(),
            new Nelmio\ApiDocBundle\NelmioApiDocBundle(),
            new EWZ\Bundle\RecaptchaBundle\EWZRecaptchaBundle(),
            
            //app
            new GPS\AppBundle\AppBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Fidry\PsyshBundle\PsyshBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
