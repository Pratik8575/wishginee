<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class EventEndpointTest extends TestCase
{
    public function testEventCreateWorks(){
        $token = $this->createAdminUserAndGetToken();
        $userId = $this->getUserId($token);
        $this->approveUser($token, $userId);

        $this->post("api/events/",[
            'name' => 'Xyz Event',
            'abstract' => 'This is the dummy xyz event',
            'contact_email' => 'hardikpatel.hardik36@gmail.com',
            'fund_needed' => 20000,
            'date' => "20/12/2017",
            'time' => "21:12:00",
            'location' => "B-2/6 Sai dham soc.",
        ], $this->authorizationToken($token));

        $this->assertResponseStatus(201);
        $this->assertArrayHasKey('_id', $this->lastResponse()['data']);
    }

    public function testEventCreateFails(){
        $token = $this->createCustomUserAndGetToken();
        $this->post("api/events/",[
            'name' => 'Xyz Event',
            'abstract' => 'This is the dummy xyz event',
            'contact_email' => 'hardikpatel.hardik36@gmail.com',
            'fund_needed' => 20000,
            'date' => "20/12/2017",
            'time' => "21:12:00",
            'location' => "B-2/6 Sai dham soc.",
        ], $this->authorizationToken($token));

        $this->assertResponseStatus(403);
    }

    public function testEventUpdateWorks(){
        $token = $this->createAdminUserAndGetToken();
        $userId = $this->getUserId($token);
        $this->approveUser($token, $userId);

        $this->post("api/events/",[
            'name' => 'Xyz Event',
            'abstract' => 'This is the dummy xyz event',
            'contact_email' => 'hardikpatel.hardik36@gmail.com',
            'fund_needed' => 20000,
            'date' => "20/12/2017",
            'time' => "21:12:00",
            'location' => "B-2/6 Sai dham soc.",
        ], $this->authorizationToken($token));
        $this->assertResponseStatus(201);
        $eventId = $this->lastResponse()['data']['_id'];

        $this->put("api/events/{$eventId}", ["name" => "PQR Event"], $this->authorizationToken($token));

        $this->assertResponseStatus(200);
        $this->assertArrayHasKey('name', $this->lastResponse()['data']);
        $this->assertEquals("PQR Event", $this->lastResponse()['data']['name']);
    }

    public function testEventUpdateFails(){
        $token = $this->createAdminUserAndGetToken();
        $userId = $this->getUserId($token);
        $this->approveUser($token, $userId);

        $this->post("api/events/",[
            'name' => 'Xyz Event',
            'abstract' => 'This is the dummy xyz event',
            'contact_email' => 'hardikpatel.hardik36@gmail.com',
            'fund_needed' => 20000,
            'date' => "20/12/2017",
            'time' => "21:12:00",
            'location' => "B-2/6 Sai dham soc.",
        ], $this->authorizationToken($token));
        $this->assertResponseStatus(201);
        $eventId = $this->lastResponse()['data']['_id'];

        $token = $this->createCustomUserAndGetToken();
        $this->put("api/events/{$eventId}", ["name" => "PQR Event"], $this->authorizationToken($token));
        $this->assertResponseStatus(403);
    }

    public function testDeleteWorks(){
        $token = $this->createAdminUserAndGetToken();
        $userId = $this->getUserId($token);
        $this->approveUser($token, $userId);

        $this->post("api/events/",[
            'name' => 'Xyz Event',
            'abstract' => 'This is the dummy xyz event',
            'contact_email' => 'hardikpatel.hardik36@gmail.com',
            'fund_needed' => 20000,
            'date' => "20/12/2017",
            'time' => "21:12:00",
            'location' => "B-2/6 Sai dham soc.",
        ], $this->authorizationToken($token));
        $this->assertResponseStatus(201);
        $eventId = $this->lastResponse()['data']['_id'];

        $this->delete("api/events/{$eventId}",[], $this->authorizationToken($token));
        $this->assertResponseStatus(200);
    }
}
