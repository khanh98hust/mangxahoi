<?php

namespace App\Models;

use DB;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'username', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $dates = [
        'birthday'
    ];

    public function follower(){
        return $this->hasMany('App\Models\UserFollowing', 'following_user_id', 'id');
    }

    public function following(){
        return $this->hasMany('App\Models\UserFollowing', 'follower_user_id', 'id');
    }

    public function posts(){
        return $this->hasMany('App\Models\Post', 'user_id', 'id');
    }

    public function has($Model){
        // if (count($this->$Model) > []) return true;
        // return false;
    }
}
