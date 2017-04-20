<?php

namespace Wishginee;

use Jenssegers\Mongodb\Eloquent\Model;

class Comment extends Model
{
    protected $primaryKey = '_id';

    protected $hidden = ['commentable_type'];
    
    public function commentable(){
        return $this->morphTo();
    }
}
