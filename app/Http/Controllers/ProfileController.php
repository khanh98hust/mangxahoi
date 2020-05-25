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

    public function uploadProfilePhoto(Request $request, $username)
    {
        $response = array();
        $response['code'] = 400;
        if (!$this->secure($username, true)) return Response::json($response);

        $messages = [
            'image.required' => trans('validation.required'),
            'image.mimes' => trans('validation.mimes'),
            'image.max.file' => trans('validation.max.file'),
        ];

        $validator = Validator::make(array('image' => $request->file('image')), [
            'image' => 'required|mimes:jpeg,jpg,png,gif|max:2048'
        ], $messages);

        if ($validator->fails()) {
            $response['code'] = 400;
            $response['message'] = implode(' ', $validator->errors()->all());
        }else{
            $file = $request->file('image');

            $file_name = md5(uniqid() . time()) . '.' . $file->getClientOriginalExtension();
            if ($file->storeAs('public/uploads/profile_photos', $file_name)){
                $response['code'] = 200;
                $this->user->profile_path = $file_name;
                $this->user->save();
                $response['image_big'] = $this->user->getPhoto();
                $response['image_thumb'] = $this->user->getPhoto(200, 200);
            }else{
                $response['code'] = 400;
                $response['message'] = "Something went wrong!";
            }
        }

        return Response::json($response);
    }

    public function uploadCover(Request $request, $username)
    {
        $response = array();
        $response['code'] = 400;
        if (!$this->secure($username, true)) return Response::json($response);

        $messages = [
            'image.required' => trans('validation.required'),
            'image.mimes' => trans('validation.mimes'),
            'image.max.file' => trans('validation.max.file'),
        ];

        $validator = Validator::make(array('image' => $request->file('image')), [
            'image' => 'required|mimes:jpeg,jpg,png,gif|max:2048'
        ], $messages);

        if ($validator->fails()) {
            $response['code'] = 400;
            $response['message'] = implode(' ', $validator->errors()->all());
        }else{
            $file = $request->file('image');

            $file_name = md5(uniqid() . time()) . '.' . $file->getClientOriginalExtension();
            if ($file->storeAs('public/uploads/covers', $file_name)){
                $response['code'] = 200;
                $this->user->cover_path = $file_name;
                $this->user->save();
                $response['image'] = $this->user->getCover();
            }else{
                $response['code'] = 400;
                $response['message'] = "Something went wrong!";
            }
        }

        return Response::json($response);
    }

    public function saveInformation(Request $request, $username)
    {
        $response = array();
        $response['code'] = 400;
        if (!$this->secure($username, true)) return Response::json($response);

        $data = json_decode($request->input('information'), true);

        $data['map_info'] = json_decode($data['map_info'], true);

        $validator = Validator::make($data, [
            'sex' => 'in:0,1',
            'birthday' => 'date',
            'phone' => 'max:20',
            'bio' => 'max:140',
        ]);

        if ($validator->fails()) {
            $response['code'] = 400;
            $response['message'] = implode(' ', $validator->errors()->all());
        }else{
            $user = $this->user;
            $user->sex = $data['sex'];
            $user->birthday = $data['birthday'];
            $user->phone = $data['phone'];
            $user->bio = $data['bio'];
            $save = $user->save();
            if ($save){
                $response['code'] = 200;
            }
        }

        return Response::json($response);
    }
}