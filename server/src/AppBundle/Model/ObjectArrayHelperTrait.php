<?php

namespace GPS\AppBundle\Model;

/**
 * Note that this absolutely makes assumptions about "getters" being available for the
 * specified fields: it does not use reflection.
 *
 * @author Evan Villemez
 */
trait ObjectArrayHelperTrait
{
    public function getObjectInArrayByField($propertyName, $fieldName, $value)
    {
        $property = $this->{'get'.ucfirst($propertyName)}();
        if (is_array($property) || $property instanceof \Traversable) {
            foreach ($property as $element) {
                if (is_object($element)) {
                    if ($val = $element->{'get'.ucfirst($fieldName)}()) {
                        if ($val === $value) {
                            return $element;
                        }
                    }
                }
            }
        }
        
        return null;
    }
}
