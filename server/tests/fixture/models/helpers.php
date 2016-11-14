<?php

use Doctrine\Common\Collections\ArrayCollection;

final class Helpers
{
    public static function run($callable)
    {
        return call_user_func($callable);
    }
    
    public static function times($num, $callable)
    {
        return function () use ($num, $callable) {
            $vals = [];
            
            for ($i = 0; $i < $num; $i++) {
                $vals[] = static::run($callable);
            }

            return $vals;
        };
    }
    
    public static function between($min, $max, $callable)
    {
        return function () use ($min, $max, $callable) {
            return static::run(static::times(mt_rand($min, $max), $callable));
        };
    }
    
    public static function collection($min, $max, $callable)
    {
        return function () use ($min, $max, $callable) {
            return new ArrayCollection(static::run(static::between($min, $max, $callable)));
        };
    }
    
    public static function unique($callable)
    {
        return function () use ($callable) {
            // Note, need this to force proper numeric indexing
            // of the array - otherwise can come out in serialization
            // as an object instead of an array... gross
            return array_values(array_unique(static::run($callable)));
        };
    }
}
