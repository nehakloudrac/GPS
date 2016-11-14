<?php

namespace GPS\AppBundle\Service;

class DataTokenGenerator
{
    
    private $router;
    private $requestStack;
    private $baseUrl;
    private $key;
    
    public function __construct($key, $router, $requestStack, $baseUrl)
    {
        $this->key = base64_decode($key);
        
        $this->router = $router;
        $this->requestStack = $requestStack;
        $this->baseUrl = $baseUrl;
    }
    
    public function generatePublicDataUrl($data = [])
    {
        return $this->generateDataUrl('link-action-public', $data);
    }
    
    public function generatePrivateDataUrl($data = [])
    {
        return $this->generateDataUrl('link-action-private', $data);
    }
    
    protected function generateDataUrl($route, $data = [])
    {
        $token = $this->encodeDataToken($data);
        
        // TODO: use request stack only fall back to baseUrl if
        // unavailable
        return $this->baseUrl.$this->router->generate($route, [
            'token' => $token
        ]);
    }
    
    // generates a url-safe encoded string from a data array
    public function encodeDataToken($data = [])
    {
        return strtr(
            base64_encode(\Crypto::Encrypt(json_encode($data), $this->key))
        , '+/=', '-_~');
    }
    
    // decodes a string created via `encodeDataToken`
    public function decodeDataToken($token)
    {
        return json_decode(
            \Crypto::Decrypt(base64_decode(strtr($token, '-_~', '+/=')), $this->key)
        , true);
    }
}
