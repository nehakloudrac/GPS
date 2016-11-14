<?php

namespace GPS\AppBundle\Command\Debug;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;


class DocDebugCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('gps:debug:doc')
            ->setDescription("Debug an entity in a collection.")
            ->addArgument('entity', InputArgument::REQUIRED, "The entity class name to check.")
            ->addArgument('id', InputArgument::REQUIRED, "ID of doc to check.")
            ->addOption('raw', null, InputOption::VALUE_NONE, 'Dump raw data.')
            ->addOption('serialize', null, InputOption::VALUE_NONE, 'Serialize entity.')
            ->addOption('validate', null, InputOption::VALUE_NONE, 'Validate entity.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entity = $input->getArgument('entity');
        $id = $input->getArgument('id');
        $raw = $input->getOption('raw');
        $serialize = $input->getOption('serialize');
        $validate = $input->getOption('validate');
        
        if (!$raw && !$serialize && !$validate) {
            throw new \InvalidArgumentException("Need to specify an action from 'raw', 'serialize', or 'validate'. ");
        }
        
        $container = $this->getContainer();
        $manager = $container->get('doctrine_mongodb')->getManager();
        $mongo = $manager->getConnection()->selectDatabase($container->getParameter('mongodb_database'));
        $collection = $manager->getDocumentCollection($entity);
        $repository = $manager->getRepository($entity);
        
        // if raw query, return early
        if ($raw) {
            $rawDoc = $collection->findOne(['_id' => new \MongoId($id)]);
            $output->writeln(json_encode($rawDoc, JSON_PRETTY_PRINT));
            
            return;
        }
        
        // otherwise fetch entity
        $instance = $repository->find(['id' => $id]);
        if (!$instance) {
            throw new \RuntimeException(sprintf("No entity found for id: %s", $id));
        }
        
        // return yaml by default... maybe make configurable at some point
        if ($serialize) {
            $serialized = $container->get('serializer')->serialize($instance, 'yml');
            $output->writeln($serialized);
            
            return;
        }
        
        // print any validation errors
        if ($validate) {
            $errors = $container->get('validator')->validate($instance);
            
            if (count($errors) == 0) {
                $output->writeln('No validation errors.');
                return;
            }
            
            foreach ($errors as $error) {
                $output->writeln((string) $error);
            }
            
            return;
        }
    }
}
