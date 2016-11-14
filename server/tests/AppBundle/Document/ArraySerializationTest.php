<?php

namespace GPS\Tests\AppBundle\Document;

use GPS\AppBundle\Testing;
use GPS\AppBundle\Document;
use JMS\Serializer\SerializationContext;

class ArraySerializationTest extends Testing\ControllerTest
{
    // trying to reproduce a case in tests where an empty array of strings
    // is being serialized out as an empty object, which breaks decoding
    // elsewhere... no luck so far, but leaving this here
    public function testSerializeEmptyArray()
    {
        $item = Document\Candidate\Profile::createFromArray([
            'hobbies' => ['foo','bar']
        ]);
        $json = $this->getContainer()->get('serializer')->serialize($item, 'json');
        $this->assertTrue(false != strpos($json, '"hobbies":["foo","bar"]'));

        $item = Document\Candidate\Profile::createFromArray([
            'hobbies' => []
        ]);
        $json = $this->getContainer()->get('serializer')->serialize($item, 'json');
        $this->assertTrue(false !== strpos($json, '"hobbies":[]'));
    }
}