<?php

namespace Wishginee;



use Jenssegers\Mongodb\Eloquent\Model;

class Event extends Model
{
    const STATUS = ["pending", "approved"];
    
    protected $dates = ['date'];

    public function user(){
        return $this->belongsTo(User::class);
    }
    
    public function followers(){
        return $this->morphMany(Follow::class,'followable');
    }

    public function followings(){
        return $this->morphMany(Follow::class,'followable', null, 'follower_id', '_id');
    }

    public function comments(){
        return $this->morphMany(Comment::class, 'commentable');
    }
}
