<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserDirectMessage;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Response;
use View;

class MessagesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id = null)
    {
        $user = Auth::user();

        $user_list = $user->messagePeopleList();

        $show = false;

        if ($id != null){
            $friend = User::find($id);
            if ($friend){
                $show = true;
            }
        }

        return view('messages.index', compact('user', 'user_list', 'show', 'id'));
    }

    public function chat(Request $request)
    {
        $response = array();
        $response['code'] = 400;

        $friend = User::find($request->input('id'));

        $user = Auth::user();

        if ($friend){
            $response['code'] = 200;
            $message_list = UserDirectMessage::where(function ($q) use($friend, $user){
                $q->where(function ($q) use($friend, $user){
                    $q->where('sender_user_id', $user->id)->where('receiver_user_id', $friend->id)->where('sender_delete', 0);
                })->orWhere(function ($q) use($friend, $user){
                    $q->where('receiver_user_id', $user->id)->where('sender_user_id', $friend->id)->where('receiver_delete', 0);
                });
            })->orderBy('id', 'DESC')->limit(50);

            $update_all = UserDirectMessage::where('receiver_delete', 0)
                ->where('receiver_user_id', $user->id)->where('sender_user_id', $friend->id)->where('seen', 0)->update(['seen' => 1]);


            $can_send_message = true;
            if ($user->messagePeopleList()->where('follower_user_id', $friend->id)->count() == 0){
                $can_send_message = false;
            }

            $html = View::make('messages.widgets.chat', compact('user', 'friend', 'message_list', 'can_send_message'));
            $response['html'] = $html->render();
        }

        return Response::json($response);
    }

    public function deleteChat(Request $request)
    {
        $response = array();
        $response['code'] = 400;

        $friend = User::find($request->input('id'));

        $user = Auth::user();

        if ($friend){
            $response['code'] = 200;

            $update_all = UserDirectMessage::where('receiver_delete', 0)
                ->where('receiver_user_id', $user->id)->where('sender_user_id', $friend->id)->update(['receiver_delete' => 1]);
            $update_all = UserDirectMessage::where('sender_delete', 0)
                ->where('sender_user_id', $user->id)->where('receiver_user_id', $friend->id)->update(['sender_delete' => 1]);
        }

        return Response::json($response);
    }

}
