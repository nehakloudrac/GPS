<?php

namespace GPS\Tests\AppBundle\Service;

use GPS\AppBundle\Testing;

class DataTokenGeneratorTest extends Testing\ControllerTest
{
    public function testEncodeAndDecodeToken()
    {
        $g = $this->getContainer()->get('gps.data_token_generator');
        
        $data = [
            'action' => 'foo',
            'data' => [
                'foo' => 'bar',
                'baz' => 'wat'
            ]
        ];
        
        $encoded = $g->encodeDataToken($data);
        
        $decoded = $g->decodeDataToken($encoded);
        
        $this->assertSame('foo', $decoded['action']);
        $this->assertSame('bar', $decoded['data']['foo']);
        $this->assertSame('wat', $decoded['data']['baz']);
    }    
}
