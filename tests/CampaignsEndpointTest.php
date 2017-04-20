<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CampaignsEndpointTest extends TestCase
{
    public function testCreateCampaignWorks(){
        $token = $this->createCustomUserAndGetToken();
        $this->post('api/campaigns', $this->perfectCampaign, $this->authorizationToken($token));
        $this->assertResponseStatus(201);
        $this->assertArrayHasKey('_id', $this->lastResponse()['data']);
    }

    public function testCreateCampaignFails(){
        $token = $this->createCustomUserAndGetToken();
        $this->post('api/campaigns', $this->perfectCampaign, $this->authorizationToken('eyy not valid token'));
        $this->assertResponseStatus(400);

        $token = $this->createCustomSocialCorporateAndGetToken();
        $this->post('api/campaigns', $this->perfectCampaign, $this->authorizationToken($token));
        $this->assertResponseStatus(403);
    }

    public function testCampaignShowWorks(){
        $token = $this->createCustomUserAndGetToken();
        $this->post('api/campaigns', $this->perfectCampaign, $this->authorizationToken($token));
        $this->assertResponseStatus(201);
        $this->get('api/campaigns', $this->authorizationToken($token));
        $this->assertResponseStatus(200);
    }

    public function testUpdateStatusWorks(){
        $token = $this->createCustomUserAndGetToken();
        $this->post('api/campaigns', $this->perfectCampaign, $this->authorizationToken($token));
        $this->assertResponseStatus(201);
        $campaignId = $this->lastResponse()['data']['_id'];

        $token = $this->createAdminUserAndGetToken();
        $this->post("api/campaigns/approve/{$campaignId}",[], $this->authorizationToken($token));
        $this->assertResponseStatus(200);
        $this->assertArrayHasKey('status', $this->lastResponse()['data']);
        $this->assertEquals(\Wishginee\Campaign::STATUS[1], $this->lastResponse()['data']['status']);
    }

    public function testCampaignDeleteWorks(){
        $token = $this->createCustomUserAndGetToken();
        $this->post('api/campaigns', $this->perfectCampaign, $this->authorizationToken($token));
        $this->assertResponseStatus(201);
        $campaignId = $this->lastResponse()['data']['_id'];

        $this->delete("api/campaigns/{$campaignId}",[],$this->authorizationToken($token));
        $this->assertResponseStatus(200);
    }
}
