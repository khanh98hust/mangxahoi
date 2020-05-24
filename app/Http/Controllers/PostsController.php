<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostImage;
use App\Models\PostLike;
use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Response;
use View;

class PostsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create(Request $request)
    {
        $data = $request->all();
        $input = json_decode($data['data'], true);
        unset($data['data']);
        foreach ($input as $key => $value) $data[$key] = $value;

        $response = array();
        $response['code'] = 400;

        if ($request->hasFile('image')){
            $validator_data['image'] = 'required|mimes:jpeg,jpg,png,gif|max:2048';
        }else{
            $validator_data['content'] = 'required';
        }

        $validator = Validator::make($data, $validator_data);

        if ($validator->fails()) {
            $response['code'] = 400;
            $response['message'] = implode(' ', $validator->errors()->all());
        }else{

            $post = new Post();
            $post->content = !empty($data['content'])?$data['content']:'';
            $post->group_id = $data['group_id'];
            $post->user_id = Auth::user()->id;

            $file_name = '';

            if ($request->hasFile('image')) {
                $post->has_image = 1;
                $file = $request->file('image');

                $file_name = md5(uniqid() . time()) . '.' . $file->getClientOriginalExtension();
                if ($file->storeAs('public/uploads/posts', $file_name)) {
                    $process = true;
                } else {
                    $process = false;
                }
            }else{
                $process = true;
            }

            if ($process){
                if ($post->save()) {
                    if ($post->has_image == 1) {
                        $post_image = new PostImage();
                        $post_image->image_path = $file_name;
                        $post_image->post_id = $post->id;
                        if ($post_image->save()){
                            $response['code'] = 200;
                        }else{
                            $response['code'] = 400;
                            $response['message'] = "Something went wrong!";
                            $post->delete();
                        }
                    }else{
                        $response['code'] = 200;
                    }
                }
            }else{
                $response['code'] = 400;
                $response['message'] = "Something went wrong!";
            }
        }

        return Response::json($response);
    }
}