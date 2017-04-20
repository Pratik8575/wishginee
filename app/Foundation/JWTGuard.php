<?php
namespace Wishginee\Foundation;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class JWTGuard implements Guard{

    public $provider;

    public $user;

    public $jwtManager;

    public $config;

    public $rawToken;

    public $decodedToken;

    public $request;

    public $token;


    public function __construct(UserProvider $userProvider, Repository $repository, Request $request, JWTManager $jwtManager)
    {
        $this->provider  = $userProvider;
        $this->config = $repository;
        $this->request = $request;
        $this->jwtManager = $jwtManager;
        $this->init();
    }

    public function init(){
        $this->token = $this->getToken();
        if(!is_null($this->token)){
            try{
                $this->decodedToken = $this->decodeToken();
                if(!is_null($this->decodedToken)){
                    $this->setUser($this->provider->retrieveById($this->decodedToken->sub));
                }
            }catch (ExpiredException $e){
                throw new \Exception(401, "Token is expired");
            }
            catch (\Exception $e){
                throw  new HttpException(400, "Invalid Token");
            }
        }
    }

    /**
     * @return object
     * @throws \Exception
     * @internal param $token
     */
    protected function decodeToken(){
        try{
            return JWT::decode($this->token, $this->config->get('auth.jwt.secret_key'), [$this->config->get('auth.jwt.claims.alg')]);
        }catch (\Exception $e){
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }
    
    public function getToken(){
        $rawToken = $this->request->header('Authorization');
        if (!is_null($rawToken)){
            return explode(' ',$rawToken)[1];
        }else{
            return null;
        }
    }


    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check()
    {
        return !$this->guest();
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest()
    {
        return is_null($this->user());
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        return $this->user;
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|null
     */
    public function id()
    {
        return $this->decodedToken->sub;
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        return false;
    }

    /**
     * Set the current user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @return void
     */
    public function setUser(Authenticatable $user)
    {
        $this->user = $user;
    }
}