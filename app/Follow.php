<?php

namespace Wishginee;



use Jenssegers\Mongodb\Eloquent\Model;

class Follow extends Model
{
    protected $primaryKey = '_id';

    protected $hidden = [];

    public function followable(){
        return $this->morphTo();
    }
    
}
