<?php

namespace Wishginee\Http\Controllers;


use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use Wishginee\Foundation\JWTManager;
use Wishginee\Foundation\Response;
use Wishginee\Http\Requests;
use Wishginee\User;

class AuthController extends Controller
{
    /**
     * Handles Facebook Class Object
     * @var Facebook
     */
    protected $facebook;

    /**
     * Handles the facebook Configurations
     * @var mixed
     */
    protected $facebookConfigurations;

    /**
     * Handles the JWT Token Management
     * @var \Illuminate\Foundation\Application|mixed
     */
    protected $jwtManager;


    /**
     * AuthController constructor.
     */
    public function __construct()
    {
        $this->facebookConfigurations = config('facebook.config');
        $this->facebook = new Facebook($this->facebookConfigurations);
        $this->jwtManager = app(JWTManager::class);
    }
    
    /**
     * Handles The Custom SignUp Request
     * @param Request $request
     * @return Response
     */
    public function signUp(Request $request){
        $validator = \Validator::make($request->all(), [
            'first_name' => 'string|required|min:4',
            'last_name' => 'string|required|min:4',
            'email' => 'required|email|max:255|unique:users,email',
            'mobile' => 'required|max:10|min:10|unique:users,mobile',
            'password' => 'string|required|min:5',
            'group' => 'required|in:PU,SC,HH,NGO,SU'
        ]);
        if($validator->fails()){
            return Response::error(422,$validator->errors());
        }
        $user = new User();
        $user->fill(array_only($request->all(), ['first_name', 'last_name', 'email', 'mobile', 'group']));
        $user->password = bcrypt($request->get('password'));
        $user->profile_photo = Controller::PROFILE_PHOTO_URL;
        $user->cover_photo = Controller::COVER_PHOTO_URL;
        $user->is_approved = false;
        $user->save();
        return Response::raw(201, $user);
    }

    /**
     * Handles the Authentication Request
     * @param Request $request
     * @return null|Response
     * @throws \HttpException
     */
    public function authenticate(Request $request){
        $validator = \Validator::make($request->all(),[
            'authType' => 'required|in:custom,facebook',
            'fb_access_code' => 'string|required_if:authType,facebook',
            'email' => 'email|max:255|required_if:authType,custom',
            'password' => 'string|min:5|required_if:authType,custom'
        ]);

        if($validator->fails()){
            return Response::error(422, $validator->errors());
        }

        switch ($request->get('authType')){
            case 'facebook':
                $access_token = $this->handleFbRequest($request);
                if(is_null($access_token)){
                    return Response::error(406, ['message' => 'Bad Request']);
                }
                return Response::raw(200, ['access_token' => $access_token]);
                break;
            case 'custom':
                $access_token = $this->handleCustomRequest($request);
                if(is_null($access_token)){
                    return Response::error(422,['message' => 'Invalid Credentials']);
                }
                return Response::raw(200, ['access_token' => $access_token]);
                break;
            default:
                throw new \HttpException("Use Proper Authentication Provider", 406);
                break;
        }
    }
    
    /**
     * Handles the facebook authentication request
     * @param Request $request
     * @return mixed
     */
    protected function handleFbRequest(Request $request){
        $fb_access_code = $request->get('fb_access_code');
        try{
            $graphUser = $this->facebook->get('/me?fields=first_name,last_name,email,id,gender,picture,cover', $fb_access_code)->getGraphUser();
            $user = User::query()->where('uuid','=',$graphUser->getId())->first();
            if(!is_null($user)){
                $user->update(['fb_access_code' => $fb_access_code]);
                return $this->jwtManager->generateAccessToken($user);
            }else{
                $user = new User();
                $user->uuid = $graphUser->getId();
                $user->first_name = $graphUser->getFirstName();
                $user->last_name = $graphUser->getLastName();
                $user->email = $graphUser->getEmail();
                $user->group = $request->get('group');
                if($graphUser->getField('picture') == null){
                    $user->profile_photo = Controller::PROFILE_PHOTO_URL;
                }else{
                    $user->profile_photo = $graphUser->getPicture()->getUrl();
                }
                if($graphUser->getField('cover') == null){
                    $user->cover_photo = Controller::COVER_PHOTO_URL;
                }else{
                    $user->cover_photo = $graphUser->getField('cover')['source'];
                }

                $user->save();
                return $this->jwtManager->generateAccessToken($user);
            }
        }catch (FacebookSDKException $e){
            return null;
        }
    }

    /**
     * Handles the custom authentication request
     * @param Request $request
     * @return null
     */
    protected function handleCustomRequest(Request $request){
        $user = User::query()->where('email', $request->get('email'))->first();

        if( !is_null($user) && (Hash::check($request->get('password'),$user->password))){
            return $this->jwtManager->generateAccessToken($user);
        }else{
            return null;
        }
    }
}
