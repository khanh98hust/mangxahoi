<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class PostComment extends Model
{

    protected $table = 'post_comments';

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    public function user(){
        return $this->belongsTo('App\Models\User', 'comment_user_id');
    }

    public function post(){
        return $this->belongsTo('App\Models\Post', 'post_id');
    }

}