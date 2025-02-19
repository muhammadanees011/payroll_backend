<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(){
        $user=Auth::user();
        $users = User::get()->map(function ($u) use ($user) {
            $u->isMe = $u->id === $user->id;
            return $u;
        });
        return response()->json($users, 200);
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string',
            'email' => 'required',
            'phone' => 'required',
            'password' => 'required|string|confirmed',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }
        $user=new User();
        $user->name=$request->full_name;
        $user->email=$request->email;
        $user->phone=$request->phone;
        $user->password=Hash::make($request->password);
        $user->save();
        return response()->json(['Successfully added user'], 200);
    }
}
