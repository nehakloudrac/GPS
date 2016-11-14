<?php

namespace GPS\AppBundle\Model;

/**
 * Note that this absolutely makes assumptions about "getters" being available for the
 * specified fields: it does not use reflection.
 *
 * This is a little different than the AutoGetterSetterTrait from AC\ModelTraits, in
 * that it uses __get/__set instead of __call.
 *
 * @author Evan Villemez
 */
trait SimpleGetterSetterTrait
{
    private function testPropertyExistence($property)
    {
        if (!property_exists($this, $property)) {
            throw new \LogicException(sprintf("No property found for [%s] in class [%s]", $property, get_class($this)));
        }
    }
    
    public function __get($property)
    {
        $this->testPropertyExistence($property);
        
        return $this->$property;
    }
    
    public function __set($property, $value)
    {
        $this->testPropertyExistence($property);
        
        $this->$property = $value;
    }
}
