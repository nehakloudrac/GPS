<?php

namespace GPS\AppBundle\Document\Candidate;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;
use Doctrine\Common\Collections\ArrayCollection;
use GPS\AppBundle\Model\ObjectArrayHelperTrait;
use GPS\AppBundle\Model\PropertyExistanceTrait;

/**
 * Ideal job description
 *
 * @MongoDB\EmbeddedDocument
 */
class IdealJobPreferences
{
    use AutoGetterSetterTrait, ArrayFactoryTrait, ObjectArrayHelperTrait, PropertyExistanceTrait;
    
    /**
     * undocumented 
     *
     * @MongoDB\Float
     * @Assert\Type("float")
     * @Assert\Range(min="0", max="1.0")
     * @Serializer\Type("double")
     */
    protected $workWithTeam;
    
    /**
     * undocumented 
     *
     * @MongoDB\Float
     * @Assert\Type("float")
     * @Assert\Range(min="0", max="1.0")
     * @Serializer\Type("double")
     */
    protected $workInField;
    
    /**
     * undocumented 
     *
     * @MongoDB\Float
     * @Assert\Type("float")
     * @Assert\Range(min="0", max="1.0")
     * @Serializer\Type("double")
     */
    protected $travel;
    
    /**
     * undocumented 
     *
     * @MongoDB\Float
     * @Assert\Type("float")
     * @Assert\Range(min="0", max="1.0")
     * @Serializer\Type("double")
     */
    protected $multiTask;
    
    /**
     * undocumented 
     *
     * @MongoDB\Float
     * @Assert\Type("float")
     * @Assert\Range(min="0", max="1.0")
     * @Serializer\Type("double")
     */
    protected $workWithCustomers;
    
    /**
     * undocumented 
     *
     * @MongoDB\Float
     * @Assert\Type("float")
     * @Assert\Range(min="0", max="1.0")
     * @Serializer\Type("double")
     */
    protected $fewerRules;
    
    /**
     * undocumented 
     *
     * @MongoDB\Float
     * @Assert\Type("float")
     * @Assert\Range(min="0", max="1.0")
     * @Serializer\Type("double")
     */
    protected $takeRisks;
    
    /**
     * undocumented 
     *
     * @MongoDB\Float
     * @Assert\Type("float")
     * @Assert\Range(min="0", max="1.0")
     * @Serializer\Type("double")
     */
    protected $measureAgainstOthers;
    
    /**
     * undocumented 
     *
     * @MongoDB\Float
     * @Assert\Type("float")
     * @Assert\Range(min="0", max="1.0")
     * @Serializer\Type("double")
     */
    protected $socializeOneToOne;
    
    /**
     * undocumented 
     *
     * @MongoDB\Float
     * @Assert\Type("float")
     * @Assert\Range(min="0", max="1.0")
     * @Serializer\Type("double")
     */
    protected $newEnvironments;
    
    /**
     * undocumented 
     *
     * @MongoDB\Float
     * @Assert\Type("float")
     * @Assert\Range(min="0", max="1.0")
     * @Serializer\Type("double")
     */
    protected $compete;
        
    /**
     * undocumented 
     *
     * @MongoDB\Float
     * @Assert\Type("float")
     * @Assert\Range(min="0", max="1.0")
     * @Serializer\Type("double")
     */
    protected $commission;
    
    /**
     * undocumented 
     *
     * @MongoDB\Float
     * @Assert\Type("float")
     * @Assert\Range(min="0", max="1.0")
     * @Serializer\Type("double")
     */
    protected $multicultural;
    
    public function isComplete()
    {
        $props = array_keys(get_class_vars(get_class($this)));
        
        return $this->anyPropertiesExist($props);
    }
    
}