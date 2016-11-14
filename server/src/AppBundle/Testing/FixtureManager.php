<?php

namespace GPS\AppBundle\Testing;

/**
 * Fixture loading could be wrapped in a service that serializes the generated 
 * objects to disk cache, or alternate mongo collection, for faster resetting 
 * between tests.
 */
class FixtureManager
{
    private $factory;
    private $fixtureDir;
    private $docrine;
    private $dbName;
    
    public function __construct($doctrine, $dbName, $fixtureDir)
    {
        $this->doctrine = $doctrine;
        $this->dbName = $dbName;
        $this->fixtureDir = $fixtureDir;
    }
    
    public function load()
    {
        return $this->getFactory();
    }
    
    /**
     * Use Fixtures + Doctrine to persist objects to a collection, or
     * rebuild the collection from a cached collection if available
     */
    protected function populateCollection($doctrineClassName, $fixturePoolName = null, $useCache = false)
    {
        $manager = $this->doctrine->getManager();
        $db = $manager->getDocumentCollection($doctrineClassName)->getDatabase();
        $col = $manager->getDocumentCollection($doctrineClassName)->getMongoCollection();
        $cacheCollectionName = 'FIXTURE_'.$col->getName();
        
        // remove everything in the collection
        $col->remove([]);
        
        // can return early if the collection doesn't have fixtures to load
        if ($fixturePoolName == null) {
            return;
        }
        
        if ($useCache) {
            // batch insert into collection whatever is in the cached collection
            $cacheCol = $db->selectCollection($cacheCollectionName);
            $itemsCursor = $cacheCol->find([]);
            $items = iterator_to_array($itemsCursor);
            $col->batchInsert($items);
        } else {
            // load objects from fixtures
            $f = $this->load();
            
            //use doctrine to persist objects to collection
            foreach ($f->getPool($fixturePoolName)->fetchAll() as $profile) {
                $manager->persist($profile);
            }
            $manager->flush();
            
            // populate cache collection
            $cacheCol = $db->selectCollection($cacheCollectionName);
            $cacheCol->remove([]);
            $itemsCursor = $col->find([]);
            $items = iterator_to_array($itemsCursor);
            $cacheCol->batchInsert($items);
        }
    }
    
    public function resetMongo($useCache = false)
    {
        // Note that this is determining the persist order of the collections,
        // so the order actually matters... users have a reference to profiles,
        // so profiles should be persisted first, in theory at least
        $colPoolMap = [
            'AppBundle:Candidate\Profile' => 'Candidate\Profile',
            'AppBundle:User' => 'User',
            'AppBundle:ResourceLink' => 'ResourceLink',
            'AppBundle:EmployerContact' => null,
            'AppBundle:AdminComment' => null,
        ];
        
        foreach ($colPoolMap as $colName => $poolName) {
            $this->populateCollection($colName, $poolName, $useCache);
        }    
    }
    
    protected function getFactory()
    {
        if ($this->factory) {
            return $this->factory;
        }
        
        require_once($this->fixtureDir);
        
        $this->factory = gps_create_fixtures();
        
        return $this->factory;
    }
}
