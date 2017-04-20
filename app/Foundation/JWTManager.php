<?php
namespace Wishginee\Foundation;

use Carbon\Carbon;
use Firebase\JWT\JWT;
use Wishginee\User;

class JWTManager extends JWT{

   

    /**
     * Generates Access Token for particular User
     * @param User $user
     * @return string
     */
    public static function generateAccessToken(User $user){
        $claims = config('auth.jwt.claims');
        $claims['sub'] = $user->_id;
        $claims['first_name'] = $user->first_name;
        $claims['last_name'] = $user->last_name;
        $claims['group'] = $user->group;
        $claims['iat'] = Carbon::now()->getTimestamp();
        $claims['exp'] = Carbon::now()->addSeconds(config('auth.jwt.exp'))->getTimestamp();
        $claims['nbf'] = $claims['iat'];
        return JWT::encode($claims, config('auth.jwt.secret_key'), $claims['alg']);
    }
}