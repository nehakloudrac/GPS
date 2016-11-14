<?php

namespace GPS\AppBundle\Document\Candidate;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;
use GPS\AppBundle\Model\PropertyExistanceTrait;

/**
 * CEFR cert.
 *
 * @MongoDB\EmbeddedDocument
 */
class LanguageCertificationCEFR extends AbstractOfficialLanguageCertification
{
    use AutoGetterSetterTrait, ArrayFactoryTrait, PropertyExistanceTrait;
    
    public function __construct()
    {
        $this->scale = 'cefr';
    }
    
    /**
     * Reading score
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $reading;

    /**
     * Writing score
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $writing;

    /**
     * Listening score
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $listening;

    /**
     * Speaking score
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $spokenInteraction;
    
    /**
     * Speaking score
     *
     * @MongoDB\Int
     * @Assert\Type("int")
     * @Assert\Range(min=1, max=6)
     * @Serializer\Type("integer")
     */
    protected $spokenProduction;
    
    public function isComplete()
    {
        return $this->allPropertiesExist(['date','test','spokenProduction','spokenInteraction','listening','writing','reading']);
    }
}
