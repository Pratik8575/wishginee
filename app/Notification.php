<?php

namespace Wishginee;



use Jenssegers\Mongodb\Eloquent\Model;

class Notification extends Model
{
    protected $guarded = [];

    public function users(){
        return $this->belongsTo(User::class);
    }
}
