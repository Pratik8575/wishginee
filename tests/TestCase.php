<?php

use Facebook\Facebook;
use Illuminate\Support\Facades\Facade;

class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    protected $facebookConfigurations;

    protected $facebook;

    protected $perfectCustomUser = [
        'first_name' => 'Hardik',
        'last_name' => 'Patel',
        'country' => 'India',
        'city' => 'Baroda',
        'email' => 'hardik.130410116061@gmail.com',
        'mobile' => '8347317999',
        'password' => 'testing',
        'group' => 'PU',
    ];
    
    protected $socialCorporateUser = [
        'first_name' => 'Passion',
        'last_name' => 'Infinite',
        'country' => 'India',
        'city' => 'Baroda',
        'email' => 'passioninfinite@gmail.com',
        'mobile' => '9737778542',
        'password' => 'testing',
        'group' => 'SC',
    ];

    protected $adminUser = [
        'first_name' => 'Hardik',
        'last_name' => 'Patel',
        'country' => 'India',
        'city' => 'Baroda',
        'email' => 'hardikpatel.hardik36@gmail.com',
        'mobile' => '8460269474',
        'password' => 'testing',
        'group' => 'SU',
        'authType' => 'custom',
        'is_approved' => true
    ];

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $this->refreshApplication();
        return parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);
    }

    protected function setUp()
    {
        parent::setUp();
        $this->facebookConfigurations = config('facebook.config');
        $this->facebook = new Facebook($this->facebookConfigurations);
    }

    /**
     * Cleans Up Testing Database After Unit Testing is completed
     */
    protected function tearDown()
    {
        $db = $this->app->make('db');
        $db->getMongoDB()->drop();
        parent::tearDown();
        $this->refreshApplication();
    }


    /**
     * Returns the Last Response for the request
     * @return mixed
     */
    protected function lastResponse(){
        return json_decode($this->response->content(), true);
    }


    /**
     *  Creates a perfect custom user and returns token
     * @return mixed
     */
    protected function createCustomUserAndGetToken(){
        $this->post('api/users', $this->perfectCustomUser);
        $this->assertResponseStatus(201);
        $this->post('api/auth', ['email' => 'hardik.130410116061@gmail.com', 'password' => 'testing', 'authType' => 'custom']);
        $this->assertResponseStatus(200);
        $this->assertArrayHasKey('access_token', $this->lastResponse()['data']);
        return $this->lastResponse()['data']['access_token'];
    }

    protected function createCustomSocialCorporateAndGetToken(){
        $this->post('api/users', $this->socialCorporateUser);
        $this->assertResponseStatus(201);
        $this->post('api/auth', ['email' => 'passioninfinite@gmail.com', 'password' => 'testing', 'authType' => 'custom']);
        $this->assertResponseStatus(200);
        $this->assertArrayHasKey('access_token', $this->lastResponse()['data']);
        return $this->lastResponse()['data']['access_token'];
    }

    /**
     * Get userId based on the token
     * @param $token
     * @return mixed
     */
    protected function getUserId($token){
        $this->get('api/users/me', $this->authorizationToken($token));
        $this->assertResponseStatus(200);
        return $this->lastResponse()['data']['_id'];
    }


    /**
     * Get authorization header
     * @param $token
     * @return array
     */
    protected function authorizationToken($token){
        return ['Authorization' => 'Bearer '.$token];
    }

    /**
     * Get Test-User FB Token after authenticating it to the Wishginee Facebook app
     * @return mixed
     */
    public function getFbTestUserToken(){
        $graphObject = $this->facebook->get("/{$this->facebookConfigurations['app_id']}/accounts/test-users", $this->facebookConfigurations['fb_access_token']);
        return $graphObject->getDecodedBody()['data'][0]['access_token'];
    }
    

    /**
     * Signing the user in using facebook authentication and returning the wishginee token
     * @param $fbAccessToken
     * @return mixed
     */
    public function createUserUsingFbAndGetToken($fbAccessToken){
        $this->post('/api/auth', ['authType' => 'facebook', 'fb_access_code' => $fbAccessToken, 'group' => 'PU']);
        $this->assertResponseStatus(200);
        $this->assertArrayHasKey('access_token', $this->lastResponse()['data']);
        return $this->lastResponse()['data']['access_token'];
    }

    /**
     * Creates Admin and Authenticates the Admin User and returns accessToken
     * @return mixed
     */
    public function createAdminUserAndGetToken(){
        $this->post('api/users', $this->adminUser);
        $this->assertResponseStatus(201);
        $this->post('/api/auth',['email' => 'hardikpatel.hardik36@gmail.com', 'password' => 'testing', 'authType' => 'custom']);
        $this->assertResponseStatus(200);
        $this->assertArrayHasKey('access_token', $this->lastResponse()['data']);
        return $this->lastResponse()['data']['access_token'];
    }

    /**
     * Signing the user in using custom authentication and returning the wishginee token
     * @return mixed
     */
    public function signInCustomUserAndGetToken(){
        $this->post('api/users', $this->perfectCustomUser);
        $this->assertResponseStatus(201);
        $this->post('/api/auth',['email' => 'hardik.130410116061@gmail.com', 'password' => 'testing', 'authType' => 'custom']);
        $this->assertResponseStatus(200);
        $this->assertArrayHasKey('access_token', $this->lastResponse()['data']);
        return $this->lastResponse()['data']['access_token'];
    }
    
    /**
     * Creates the perfect Campaign
     */
    protected $perfectCampaign = [
        'name' => 'Dummy Campaign',
        'contact_email' => 'campaign@xyz.com',
        'contact_mobile' => '9924405852',
        'abstract' => 'this is the basic abstract of the campaign',
        'category' => 'education',
        'fund_needed' => 5000,
        'story' => 'This is the full story of the campaign',
        'date' => '1/1/2017',
        'location' => 'address or city or state'
    ];
    
    public function approveUser($token, $userId){
        $this->put("api/users/{$userId}", ["is_approved" => true], $this->authorizationToken($token));
        $this->assertResponseStatus(200);
    }
}
