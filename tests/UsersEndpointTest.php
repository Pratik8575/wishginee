<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UsersEndpointTest extends TestCase
{
    public function testUserGetByIdWorks(){
        $token = $this->createCustomUserAndGetToken();
        $userId = $this->getUserId($token);
        $this->get("api/users/{$userId}", $this->authorizationToken($token));
        $this->assertResponseStatus(200);
        $this->assertArrayHasKey('first_name', $this->lastResponse()['data']);
        $this->assertArrayHasKey('email', $this->lastResponse()['data']);
        $this->assertArrayHasKey('mobile', $this->lastResponse()['data']);
        $this->assertArrayNotHasKey('access_token', $this->lastResponse()['data']);
        $this->assertArrayNotHasKey('fb_access_code', $this->lastResponse()['data']);
        $this->assertArrayNotHasKey('password', $this->lastResponse()['data']);

        $testUserToken = $this->getFbTestUserToken();
        $this->createUserUsingFbAndGetToken($testUserToken);
        $testUserAccessToken = $this->lastResponse()['data']['access_token'];
        $this->get("api/users/{$userId}", $this->authorizationToken($testUserAccessToken));
        $this->assertResponseStatus(200);
        $this->assertArrayHasKey('first_name', $this->lastResponse()['data']);
        $this->assertArrayHasKey('email', $this->lastResponse()['data']);
        $this->assertArrayHasKey('mobile', $this->lastResponse()['data']);
        $this->assertArrayNotHasKey('access_token', $this->lastResponse()['data']);
        $this->assertArrayNotHasKey('fb_access_code', $this->lastResponse()['data']);
        $this->assertArrayNotHasKey('password', $this->lastResponse()['data']);
    }

    public function testUserGetByIdFails(){
        $this->get("api/users/12345", $this->authorizationToken("eyy not valid token"));
        $this->assertResponseStatus(400);

        $token = $this->createCustomUserAndGetToken();
        $this->get("api/users/12345", $this->authorizationToken($token));
        $this->assertResponseStatus(404);
    }

    public function testUserMeWorks(){
        $adminToken = $this->createAdminUserAndGetToken();
        $token = $this->createCustomUserAndGetToken();

        $this->get('/api/users/me', $this->authorizationToken($token));
        $this->assertEquals('PU', $this->lastResponse()['data']['group']);

        $this->get('/api/users/me', $this->authorizationToken($adminToken));
        $this->assertEquals('SU', $this->lastResponse()['data']['group']);
    }

    public function testUserUpdateUserWorks(){
        $adminToken = $this->createAdminUserAndGetToken();
        $token = $this->createCustomUserAndGetToken();
        $userId = $this->getUserId($token);
        $this->put("api/users/{$userId}",["website" => "http://passionInfinite.xyz.com"], $this->authorizationToken($token));
        $this->assertResponseStatus(200);
        $this->assertArrayHasKey('website', $this->lastResponse()['data']);
        $this->assertEquals('http://passionInfinite.xyz.com', $this->lastResponse()['data']['website']);

        $this->put("api/users/{$userId}",["city" => "Baroda", "country" => "India", "website" => "http://passionInfinite.xyz.com"], $this->authorizationToken($token));
        $this->assertResponseStatus(200);
        $this->assertArrayHasKey('website', $this->lastResponse()['data']);
        $this->assertArrayHasKey('city', $this->lastResponse()['data']);
        $this->assertArrayHasKey('country', $this->lastResponse()['data']);
        $this->assertEquals('http://passionInfinite.xyz.com', $this->lastResponse()['data']['website']);
        $this->assertEquals('Baroda', $this->lastResponse()['data']['city']);
        $this->assertEquals('India', $this->lastResponse()['data']['country']);

        $this->put("api/users/{$userId}",["founders" => ["Sagar Sharma"]], $this->authorizationToken($token));
        $this->assertResponseStatus(403);

        $token = $this->createCustomSocialCorporateAndGetToken();
        $userId = $this->getUserId($token);
        $this->put("api/users/{$userId}",["founders" => ["Sagar Sharma"]], $this->authorizationToken($token));
        $this->assertResponseStatus(200);
        $this->assertArrayHasKey('founders', $this->lastResponse()['data']);
    }

    public function testUserUpdateGroupWorks(){
        $adminToken = $this->createAdminUserAndGetToken();
        $token = $this->createCustomUserAndGetToken();
        $userId = $this->getUserId($token);
        
        $this->put("api/users/{$userId}", ["group" => "SU"], $this->authorizationToken($adminToken));
        $this->assertResponseStatus(200);
    }

    public function testUserUpdateFails(){
        $token = $this->createCustomUserAndGetToken();
        $userId = $this->getUserId($token);
        $this->put("api/users/{$userId}",["group" => "SU"], $this->authorizationToken($token));
        $this->assertResponseStatus(403);
        
        $this->put("api/users/{$userId}",["group" => "SU"], $this->authorizationToken('eyy Not Valid Token'));
        $this->assertResponseStatus(400);
    }

    public function testUserUpdateGroupFails(){
        $token = $this->createCustomUserAndGetToken();
        $userId = $this->getUserId($token);

        $this->put("api/users/{$userId}", ["group" => "SU"], $this->authorizationToken($token));
        $this->assertResponseStatus(403);
    }

    public function testUserDeleteWorks(){
        $token = $this->createCustomUserAndGetToken();
        $adminToken = $this->createAdminUserAndGetToken();
        $userId = $this->getUserId($token);

        $this->delete("api/users/{$userId}",[], $this->authorizationToken($token));
        $this->assertResponseStatus(200);

        $this->delete("api/users/{$userId}",[], $this->authorizationToken($adminToken));
        $this->assertResponseStatus(404);
    }
}
