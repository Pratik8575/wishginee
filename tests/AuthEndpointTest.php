<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AuthEndpointTest extends TestCase
{
    public function testCustomCreateWorks(){
        $access_token = $this->createCustomUserAndGetToken();
        $this->assertNotNull($access_token);
    }

    public function testCustomCreateFails(){
        $this->post('api/users/', $this->perfectCustomUser);
        $this->assertResponseStatus(201);
        $this->post('api/users/', $this->perfectCustomUser);
        $this->assertResponseStatus(422);
        $this->assertArrayHasKey('email', $this->lastResponse()['error']);

        $this->post('api/users/', ['first_name' => 'hardik', 'last_name' => 'Patel', 'group' => 'PU']);
        $this->assertResponseStatus(422);
        $this->assertArrayHasKey('email', $this->lastResponse()['error']);
        $this->assertArrayHasKey('mobile', $this->lastResponse()['error']);
        $this->assertArrayHasKey('password', $this->lastResponse()['error']);
    }
    
    public function testFacebookSignInWorks(){
        $fbAccessToken = $this->getFbTestUserToken();
        $this->assertNotNull($fbAccessToken);
        $token = $this->createUserUsingFbAndGetToken($fbAccessToken);
        $this->assertNotNull($token);
    }

    public function testFacebookSignInFails(){
        $this->post('/api/auth', ['authType' => 'facebook', 'fb_access_code' => null, 'group' => 'PU']);
        $this->assertResponseStatus(422);
        $this->assertArrayHasKey('fb_access_code', $this->lastResponse()['error']);
        $this->assertArrayNotHasKey('access_token', $this->lastResponse()['data']);
    }

    public function testCustomSignInWorks(){
        $token = $this->signInCustomUserAndGetToken();
        $this->assertNotNull($token);
    }

    public function testCustomSignInFails(){
        $this->post('/api/auth', ['email' => 'hardik.130410116061@gmail.com', 'password' => 'testing']);
        $this->assertResponseStatus(422);
        $this->assertArrayHasKey('authType', $this->lastResponse()['error']);
        $this->assertArrayNotHasKey('access_token', $this->lastResponse()['data']);
    }
}
