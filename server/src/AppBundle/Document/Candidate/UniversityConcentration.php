<?php

namespace GPS\AppBundle\Document\Candidate;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Availability window for project work.
 *
 * @MongoDB\EmbeddedDocument
 */
class UniversityConcentration
{
    use AutoGetterSetterTrait, ArrayFactoryTrait;
    
    /**
     * Major/minor
     *
     * @MongoDB\String
     * @Assert\NotBlank
     * @Assert\Choice(choices={"major","minor"})
     * @Serializer\Type("string")
     */
    protected $type;
    
    /**
     * DEPRECATED - use `fieldName` instead
     * 
     * Subject of study
     *
     * TODO: consider validating more strictly
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=200)
     * @Serializer\Type("string")
     */
    protected $field;
    
    /**
     * Name for field - could be just about anything
     *
     * @MongoDB\String
     * @Assert\Type("string")
     * @Assert\Length(max=200)
     * @Serializer\Type("string")
     */
    protected $fieldName;
    
    /**
     * Some concentrations have other associated metadata
     *
     * @MongoDB\Hash
     * @Serializer\Type("array")
     */
    protected $meta;
    
    /**
     * Whether or not the concentration had any international focus
     *
     * @MongoDB\Boolean
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    protected $intlConcentration = false;
}
