<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class UserFollowing extends Model
{

    protected $table = 'user_following';

    public $timestamps = false;


    public function follower(){
        return $this->belongsTo('App\Models\User', 'follower_user_id');
    }

    public function following(){
        return $this->belongsTo('App\Models\User', 'following_user_id');
    }

    public function getAllow(){
        return $this->allow;
    }

}