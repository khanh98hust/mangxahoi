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

}
