<?php

namespace GPS\Tests\AppBundle\Controller\Api;

use GPS\AppBundle\Testing;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UserControllerProfilePhotoTest extends Testing\ControllerTest
{
    public function testPostUserProfileImageAction()
    {
        $testFilePath = GPS_FIXTURE_PATH.'/test.jpg';
        $uploadedFile = new UploadedFile(
            $testFilePath,
            'test.jpg',
            'image/jpeg',
            filesize($testFilePath)
        );

        $client = $this->createAuthClient('user@example.com', 'user');
        $client->request('POST', '/api/users/cccccccccccccccccccccccc/profile-image', [], ['file' => $uploadedFile], ['HTTPS' => true]);
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertTrue(isset($content['user']['avatarUrl']));

        // refetch to ensure it was persisted
        $client = $this->createAuthClient('user@example.com', 'user');
        $client->request('GET', '/api/users/cccccccccccccccccccccccc', [], [], ['HTTPS' => true]);
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertTrue(isset($content['user']['avatarUrl']));
    }

    /**
     * Check for previously uploaded profile photo and delete it
     *
     * @depends testPostUserProfileImageAction
     */
    public function testDeleteUserProfileImageAction()
    {
        #ensure existing profile photo
        $client = $this->createAuthClient('user@example.com', 'user');
        $client->request('GET', '/api/users/cccccccccccccccccccccccc', [], [], ['HTTPS' => true]);
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertTrue(isset($content['user']['avatarUrl']));

        #delete it
        $client = $this->createAuthClient('user@example.com', 'user');
        $client->request('DELETE', '/api/users/cccccccccccccccccccccccc/profile-image', [], [], ['HTTPS' => true]);
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertFalse(isset($content['user']['avatarUrl']));

        #refetch, ensure still missing
        $client = $this->createAuthClient('user@example.com', 'user');
        $client->request('GET', '/api/users/cccccccccccccccccccccccc', [], [], ['HTTPS' => true]);
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertFalse(isset($content['user']['avatarUrl']));
    }    
}