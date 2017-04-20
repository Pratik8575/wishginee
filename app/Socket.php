<?php

namespace Wishginee;


use Jenssegers\Mongodb\Eloquent\Model;

class Socket extends Model
{
    protected $primaryKey = "user_id";
    
    protected $guarded = [];
}
