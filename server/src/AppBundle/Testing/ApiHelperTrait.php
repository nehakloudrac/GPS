<?php

namespace GPS\AppBundle\Testing;

trait ApiHelperTrait
{
    protected function call($client, $method, $path, $params = [], $files = [], $server = [], $body = null)
    {
        // all api calls should be over https
        $server = array_merge($server, ['HTTPS' => true]);

        $client->request($method, $path, $params, $files, $server, $body);

        return $client->getResponse();
    }
    
    protected function callApi($client, $method, $path, $json = [])
    {
        $body = json_encode($json);
        $server = ['CONTENT_TYPE' => 'application/json'];

        $res = $this->call($client, $method, $path, [], [], $server, $body);
        
        return json_decode($res->getContent(), true);
    }
    
}
