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

    public function like(Request $request)
    {
        $user = Auth::user();

        $response = array();
        $response['code'] = 400;

        $post = Post::find($request->input('id'));

        if ($post){
            $post_like = PostLike::where('post_id', $post->id)->where('like_user_id', $user->id)->get()->first();

            if ($post_like) { // UnLike
                if ($post_like->like_user_id == $user->id) {
                    $deleted = DB::delete('delete from post_likes where post_id='.$post_like->post_id.' and like_user_id='.$post_like->like_user_id);
                    if ($deleted){
                        $response['code'] = 200;
                        $response['type'] = 'unlike';
                    }
                }
            }else{
                // Like
                $post_like = new PostLike();
                $post_like->post_id = $post->id;
                $post_like->like_user_id = $user->id;
                if ($post_like->save()){
                    $response['code'] = 200;
                    $response['type'] = 'like';
                }
            }
            if ($response['code'] == 200){
                $response['like_count'] = $post->getLikeCount();
            }
        }

        return Response::json($response);
    }

    public function likes(Request $request)
    {
        $user = Auth::user();

        $response = array();
        $response['code'] = 400;

        $post = Post::find($request->input('id'));

        if ($post){
            $response['code'] = 200;
            $html = View::make('widgets.post_detail.likes', compact('post'));
            $response['likes'] = $html->render();
        }

        return Response::json($response);
    }

    public function comment(Request $request)
    {
        $user = Auth::user();

        $response = array();
        $response['code'] = 400;

        $post = Post::find($request->input('id'));
        $text = $request->input('comment');

        if ($post && !empty($text)){
            $comment = new PostComment();
            $comment->post_id = $post->id;
            $comment->comment_user_id = $user->id;
            $comment->comment = $text;
            if ($comment->save()){
                $response['code'] = 200;
                $html = View::make('widgets.post_detail.single_comment', compact('post', 'comment'));
                $response['comment'] = $html->render();
                $html = View::make('widgets.post_detail.comments_title', compact('post', 'comment'));
                $response['comments_title'] = $html->render();
            }
        }

        return Response::json($response);
    }

    public function deleteComment(Request $request)
    {
        $response = array();
        $response['code'] = 400;

        $post_comment = PostComment::find($request->input('id'));

        if ($post_comment){
            $post = $post_comment->post;
            if ($post_comment->comment_user_id == Auth::id() || $post_comment->post->user_id == Auth::id()) {
                if ($post_comment->delete()) {
                    $response['code'] = 200;
                    $html = View::make('widgets.post_detail.comments_title', compact('post'));
                    $response['comments_title'] = $html->render();
                }
            }
        }

        return Response::json($response);
    }
}