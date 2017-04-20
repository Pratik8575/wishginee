<?php

namespace Wishginee;


use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Jenssegers\Mongodb\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;

class User extends Model implements  AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword, SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'password', 'mobile', 'group', 'fb_access_code', 'cover_photo', 'profile_photo'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    
    protected $primaryKey = '_id';

    /**
     * Return Name of the User
     * @return string
     */
    public function getNameAttribute(){
        return $this->first_name.' '.$this->last_name;
    }

    /**
     * User-Campaign Relation
     */
    public function campaigns(){
        return $this->hasMany(Campaign::class);
    }

    /**
     * User-Event Relation
     */
    public function events(){
        return $this->hasMany(Event::class);
    }

    public function followers(){
        return $this->morphMany(Follow::class,'followable');
    }

    public function followings(){
        return $this->morphMany(Follow::class,'followable', null, 'follower_id', '_id');
    }
    
    public function notifications(){
        return $this->hasMany(Notification::class,'to_user_id');
    }
}