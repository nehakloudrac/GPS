<?php

namespace GPS\AppBundle\Model;

/**
 * Checks for whether or not properties have values.  Does not recurse
 * into nested arrays or objects.
 *
 * This makes many assumptions - namely that properties can be accessed
 * with a "getter" method.
 *
 * @author Evan Villemez
 */
trait PropertyExistanceTrait
{
    public function allPropertiesExist($list = [])
    {
        foreach ($list as $prop) {
            if (!$this->propertyExists($prop)) {
                return false;
            }
        }
        
        return true;
    }
    
    public function anyPropertiesExist($list = [])
    {
        foreach ($list as $prop) {
            if ($this->propertyExists($prop)) {
                return true;
            }
        }
        
        return false;
    }
    
    public function propertyExists($name)
    {
        $val = $this->{'get'.ucfirst($name)}();
        
        if (is_null($val)) {
            return false;
        }
        
        if (is_array($val) && count($val) == 0) {
            return false;
        }
        
        return true;
    }
}
