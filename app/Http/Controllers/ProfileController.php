<?php
namespace App\Http\Controllers;

use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Response;

class ProfileController extends Controller
{
    private $user;
    private $my_profile = false;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function secure($username, $is_owner = false)
    {
        $user = User::where('username', $username)->first();

        if ($user){
            $this->user = $user;
            $this->my_profile = (Auth::id() == $this->user->id)?true:false;
            if ($is_owner && !$this->my_profile){
                return false;
            }
            return true;
        }

        return false;
    }

    public function index($username)
    {
        if (!$this->secure($username)) return redirect('/404');

        $user = $this->user;

        $my_profile = $this->my_profile;

        $wall = [
            'new_post_group_id' => 0
        ];

        $can_see = ($my_profile)?true:$user->canSeeProfile(Auth::id());

        return view('profile.index', compact('user', 'my_profile', 'wall', 'can_see'));
    }

    public function following(Request $request, $username)
    {
        if (!$this->secure($username)) return redirect('/404');

        $user = $this->user;

        $list = $user->following()->where('allow', 1)->with('following')->get();

        $my_profile = $this->my_profile;

        $can_see = ($my_profile)?true:$user->canSeeProfile(Auth::id());

        return view('profile.following', compact('user', 'list', 'my_profile', 'can_see'));
    }

    public function followers(Request $request, $username)
    {
        if (!$this->secure($username)) return redirect('/404');

        $user = $this->user;

        $list = $user->follower()->where('allow', 1)->with('follower')->get();

        $my_profile = $this->my_profile;

        $can_see = ($my_profile)?true:$user->canSeeProfile(Auth::id());

        return view('profile.followers', compact('user', 'list', 'my_profile', 'can_see'));
    }

}