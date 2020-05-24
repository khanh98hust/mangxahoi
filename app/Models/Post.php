<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Post extends Model
{

    protected $table = 'posts';

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    private $like_count = null;
    private $comment_count = null;

    public function user(){
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function images(){
        return $this->hasMany('App\Models\PostImage', 'post_id', 'id');
    }

    public function comments(){
        return $this->hasMany('App\Models\PostComment', 'post_id', 'id');
    }

    public function likes(){
        return $this->hasMany('App\Models\PostLike', 'post_id', 'id');
    }
}