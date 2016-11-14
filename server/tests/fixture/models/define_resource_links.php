<?php

use League\FactoryMuffin\Faker\Facade as F;
use GPS\Popov\Facade as Factory;
use Values as V;
use Helpers as H;
use GPS\AppBundle\Document as Doc;

function gps_define_resource_link_fixtures($fm)
{
    $types = ['program','article','quote'];
    $tags = ['Study Adroad','Education','Language Study','Hard Skills','Soft Skills'];
    
    $fm->definePool('ResourceLink:GPS\AppBundle\Document\ResourceLink', 50)->setAttrs([
        'type' => F::randomElement($types),
        'tags' => H::unique(H::between(1, 3, F::randomElement($tags))),
        'url' => 'http://www.example.com/',
        'description' => F::sentence(),
        'creator' => Factory::fetchRandom('User'),
        'published' => F::boolean(),
    ]);
    
    return $fm;
}
