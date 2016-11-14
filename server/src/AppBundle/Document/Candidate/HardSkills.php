<?php

namespace GPS\AppBundle\Document\Candidate;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;
use Doctrine\Common\Collections\ArrayCollection;
use GPS\AppBundle\Model\PropertyExistanceTrait;

/**
 * Description of manner in which candidate was in a country.
 *
 * For reference, the labels for these values are generally: ['N/A','Once or twice','Rarely','Occasionally','Regularly','All the time']
 *
 * @MongoDB\EmbeddedDocument
 */
class HardSkills
{
    use AutoGetterSetterTrait, ArrayFactoryTrait, PropertyExistanceTrait;

    /**
     * undocumented variable
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=0, max=5)
     * @Serializer\Type("integer")
     */
    protected $accounting;
        
    /**
     * undocumented variable
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=0, max=5)
     * @Serializer\Type("integer")
     */
    protected $clientManagement;
        
    /**
     * undocumented variable
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=0, max=5)
     * @Serializer\Type("integer")
     */
    protected $contractNegotiation;
        
    /**
     * undocumented variable
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=0, max=5)
     * @Serializer\Type("integer")
     */
    protected $eventPlanning;
        
    /**
     * undocumented variable
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=0, max=5)
     * @Serializer\Type("integer")
     */
    protected $financialAnalysis;
        
    /**
     * undocumented variable
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=0, max=5)
     * @Serializer\Type("integer")
     */
    protected $fundraising;
        
    /**
     * undocumented variable
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=0, max=5)
     * @Serializer\Type("integer")
     */
    protected $marketing;
        
    /**
     * undocumented variable
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=0, max=5)
     * @Serializer\Type("integer")
     */
    protected $projectManagement;
        
    /**
     * undocumented variable
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=0, max=5)
     * @Serializer\Type("integer")
     */
    protected $reportWriting;
        
    /**
     * undocumented variable
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=0, max=5)
     * @Serializer\Type("integer")
     */
    protected $publicRelations;
        
    /**
     * undocumented variable
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=0, max=5)
     * @Serializer\Type("integer")
     */
    protected $publicSpeaking;
        
    /**
     * undocumented variable
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=0, max=5)
     * @Serializer\Type("integer")
     */
    protected $research;
        
    /**
     * undocumented variable
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=0, max=5)
     * @Serializer\Type("integer")
     */
    protected $staffManagement;
        
    /**
     * undocumented variable
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=0, max=5)
     * @Serializer\Type("integer")
     */
    protected $socialMedia;
        
    /**
     * undocumented variable
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=0, max=5)
     * @Serializer\Type("integer")
     */
    protected $writtenCommunication;
    
    public function isComplete()
    {
        $props = array_keys(get_class_vars(get_class($this)));
        
        return $this->anyPropertiesExist($props);
    }
}
