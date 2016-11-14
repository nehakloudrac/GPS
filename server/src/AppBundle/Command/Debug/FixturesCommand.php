<?php

namespace GPS\AppBundle\Command\Debug;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use JMS\Serializer\SerializationContext;

class FixturesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('gps:debug:fixtures')
            ->setDescription("Dump model fixtures in a variety of formats.")
            ->addArgument('task', InputArgument::REQUIRED, 'Action to perform with the fixtures [persist/dump/validate]')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Limit number of fixtures to dump?', null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        switch($input->getArgument('task')) {
            case 'validate': return $this->validateFixtures($input, $output);
            case 'dump': return $this->dumpFixtures($input, $output);
            case 'persist': return $this->persistFixtures($input, $output);
            default: throw new \InvalidArgumentException("Unknown task, must be one of [dump, persist, validate].");
        }
    }
    
    // run validator over all fixtures
    protected function validateFixtures($in, $out)
    {
        $c = $this->getContainer();
        $fm = $c->get('gps.fixtures');
        
        $factory = $fm->load();
        
        $validator = $c->get('validator');
        $map = [
            'users' => [],
            'profiles' => []
        ];
        
        foreach($factory->getPool('User')->fetchAll() as $user) {
            // check user
            $errors = $validator->validate($user);
            if (count($errors) > 0) {
                $map['users'][$user->getEmail()] = $errors;
            }

            // check profile
            $errors = $validator->validate($user->getCandidateProfile());
            if (count($errors) > 0) {
                $map['profiles'][$user->getEmail()] = $errors;
            }
        }
        
        if (empty($map['users']) && empty($map['profiles'])) {
            $out->writeln("No errors.");
            return 0;
        }
        
        if (!empty($map['users'])) {
            $out->writln("Found errors in user fixtures: ");
            \dump($map['users']);
        }
        
        if (!empty($map['profiles'])) {
            $out->writln("Found errors in profile fixtures: ");
            \dump($map['profiles']);
        }
    }
    
    // dump fixtures as json, mapped by collection name
    protected function dumpFixtures($in, $out, $return = false)
    {
        $c = $this->getContainer();
        $fm = $c->get('gps.fixtures');
        
        $factory = $fm->load();
        
        if ($max = $in->getOption('limit')) {
            $map = ['users' => [], 'candidateProfiles' => []];
            $allUsers = $factory->getPool('User')->fetchAll();
            $allProfiles = $factory->getPool('Candidate\Profile')->fetchAll();
            for ($i = 0; $i < $max; $i++) {
                $map['users'][] = $allUsers[$i];
                $map['candidateProfiles'][] = $allProfiles[$i];
            }
        } else {
            $map = [
                'users' => $factory->getPool('User')->fetchAll(),
                'candidateProfiles' => $factory->getPool('Candidate\Profile')->fetchAll()
            ];
        }
        

        $ctx = SerializationContext::create()->setGroups([
            'Default',
            'User.email',
            'User.phone',
        ]);

        $json = $c->get('serializer')->serialize($map, 'json', $ctx);
        
        if ($return) {
            return $json;
        }
        
        $out->writeln($json);

        return 0;
    }
    
    // reset DB by 
    protected function persistFixtures($in, $out)
    {
        $c = $this->getContainer();

        if (!$c->getParameter('gps.allow_fixture_mongo_reset', false)) {
            throw new \RuntimeException("Fixtures cannot be loaded in this environment for safety reasons.");
        }

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('This will drop existing data and reset the database.  ARE YOU SURE?  [y/n]:  ', false);

        if (!$helper->ask($in, $out, $question)) {
            $out->writeln("Cancelled.");
            return;
        }

        $out->writeln("Wiping old data...");
        
        $c->get('gps.fixtures')->resetMongo();
        
        // also write fixtures to cache location: they may be needed for
        // populating the search index for some tests
        $json = $this->dumpFixtures($in, $out, true);
        $cacheFile = $c->getParameter('kernel.cache_dir').'/gps-fixtures.json';
        file_put_contents($cacheFile, $json);
        
        $out->writeln("Mongo cleared and loaded with fixtures.  Fixtures written to cache.");
    }
}
