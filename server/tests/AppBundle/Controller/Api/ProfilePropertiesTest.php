<?php

namespace GPS\Tests\AppBundle\Controller\Api;

use GPS\AppBundle\Testing;

/**
 * This test case is for testing specific properties in the candidate profile
 */
class ProfilePropertiesTest extends Testing\ControllerTest
{
    public function testIdealJobWillingnessToTravel()
    {
        $expectations = ['occaisionally','up_to_25','up_to_50','over_50','no'];
        foreach ($expectations as $expected) {
            $res = $this->put('/', ['idealJob' => ['willingnessToTravel' => $expected]]);
            $this->assertSame($expected, $res['profile']['idealJob']['willingnessToTravel']);
        }
    }
    
    public function testIdealJobWillingnessToTravelOverseas()
    {
        $expected = true;
        $res = $this->put('/', ['idealJob' => ['willingToTravelOverseas' => $expected]]);
        $this->assertSame($expected, $res['profile']['idealJob']['willingToTravelOverseas']);
    }
    
    private function post($path, $data)
    {
        return $this->send('POST', $path, $data);
    }
    
    private function put($path, $data)
    {
        return $this->send('PUT', $path, $data);        
    }
    
    private function send($method, $path, $data = null)
    {
        $base = '/api/candidate-profiles/cccccccccccccccccccccccc/';
        $c = $this->createAuthClient('user@example.com', 'user');
        $res = $this->callApi($c, $method, rtrim($base.$path, '/'), $data);

        return $this->callApi($c, $method, rtrim($base, '/'));
    }
    
}
