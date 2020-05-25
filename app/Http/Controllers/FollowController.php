<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Library\sHelper;
use App\Models\User;
use App\Models\UserFollowing;
use Auth;
use Illuminate\Http\Request;
use Response;


class FollowController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function follow(Request $request)
    {
        $response = array();
        $response['code'] = 400;
        $following_user_id = $request->input('following');
        $follower_user_id = $request->input('follower');
        $element = $request->input('element');
        $size = $request->input('size');
        $following = User::find($following_user_id);
        $follower = User::find($follower_user_id);

        if ($following && $follower && ($following_user_id == Auth::id() || $follower_user_id == Auth::id())){

            $relation = UserFollowing::where('following_user_id', $following_user_id)->where('follower_user_id', $follower_user_id)->get()->first();

            if ($relation){
                if ($relation->delete()){
                    $response['code'] = 200;
                    if ($following->isPrivate()) {
                        $response['refresh'] = 1;
                    }
                }
            }else{
                $relation = new UserFollowing();
                $relation->following_user_id = $following_user_id;
                $relation->follower_user_id = $follower_user_id;
                if ($following->isPrivate()){
                    $relation->allow = 0;
                }else{
                    $relation->allow = 1;
                }
                if ($relation->save()){
                    $response['code'] = 200;
                    $response['refresh'] = 0;
                }
            }

            if ($response['code'] == 200){
                $response['button'] = sHelper::followButton($following_user_id, $follower_user_id, $element, $size);
            }
        }

        return Response::json($response);
    }
}