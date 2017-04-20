<?php

namespace Wishginee;


use Jenssegers\Mongodb\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use SoftDeletes;
    
    const STATUS = ["pending", "approved"]; 

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'contact_mobile', 'contact_email', 'abstract', 'profile_photo', 'cover_photo', 'category', 'fund_needed', 'fund_raised', 'story', 'date', 'location', 'campaign_by', 'status', 'is_approval'
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
     * Inverse Relationship between User-Campaign
     */
    public function user(){
        return $this->belongsTo(User::class);
    }

    
    public function followers(){
        return $this->morphMany(Follow::class,'followable');
    }
    
    public function comments(){
        return $this->morphMany(Comment::class, 'commentable');
    }

}
