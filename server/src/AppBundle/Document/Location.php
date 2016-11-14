<?php

namespace GPS\AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;

/**
 * Eventual location data for geo search.
 *
 * @MongoDB\EmbeddedDocument
 */
class Location
{
    use AutoGetterSetterTrait, ArrayFactoryTrait;
    
}