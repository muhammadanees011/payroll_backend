<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Mail\EmailVerification;
use App\Mail\ForgotPasswordEmail;
use Illuminate\Support\Carbon;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    //------------REGISTER USER--------------
    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }
        // try{
        // DB::beginTransaction();
        $request['password']=Hash::make($request['password']);
        $request['remember_token'] = Str::random(10);
        $user = User::create($request->toArray());
        // DB::commit();
        $response = ['user' => $user];
        $this->send_email_verification_code($user->email);
        return response()->json($response, 200);
        // } catch (\Exception $exception) {
        //     DB::rollback();
        //     if (('APP_ENV') == 'local') {
        //         dd($exception);
        //     }
        // }
    }

    //------------LOGIN USER--------------
    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
            $user = Auth::user(); 
            $tokenResult = $user->createToken('Personal Access Token')->accessToken;
            $token = $tokenResult;
            $data['access_token'] = $token;
            $data['user'] =  $user;

            return response()->json($data);
        } 
        else{ 
            return response()->json(['errors'=>['Incorrect Email or Password']],401);
        } 
    }
    //------------LOGOUT USER--------------
    public function logout(Request $request){
        $user = Auth::user();
        if ($user) {
            if ($user->token()->revoke()) {
                return response()->json('Logout successfully!');
            } else {
                return response()->json('Failed To Logout');
            }
        }
        return response()->json('User not found.', 201);
    }
    //--------------SEND OTP---------------
    public function send_forgot_password_otp(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }
        $email_verification_code = mt_rand(100000, 999999);
        $user = User::where('email', $request->email)->first();
        if($user->email_verification_code!=null){
            return response()->json(['errors'=>['Verify your email first']], 500);
        }
        $user->reset_password_code = $email_verification_code;
        $user->save();
        //Send OTP to Email
        try {
            $mailData = [
            'reset_password_code' => $email_verification_code,
            'user_name'=> $user->name,
            ];
            Mail::to($request->email)->send(new ForgotPasswordEmail($mailData));
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
        return response()->json('Forgot Password code sent successfully', 200);
    }
    //--------------VERIFY OTP---------------
    public function forgot_password_verify_otp(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|exists:users,email',
            'reset_password_code' => 'required|exists:users,reset_password_code',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }

        $user = User::where('email', $request->email)->where('reset_password_code', $request->reset_password_code)->first();
        if ($user) {
            $user->reset_password_code=null;
            $user->save();
            return response()->json('Code verified successfully.', 200);
        } else {
            return response()->json(['message'=>'Record not found.'], 401);
        }
    }
    //--------------CHANGE PASSWORD---------------
    public function changePassword(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'old_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }
        $user = User::where('id', $request->user_id)->first();
        if($request->new_password==$request->old_password){ 
            return response()->json('New password can not be same as old password.',422);
        }else if(Hash::check($request->old_password, $user->password)){ 
            $user->password = Hash::make($request->new_password);
            $user->save();
            return response()->json('Password changed successfully.', 200);     
        }else{ 
            return response()->json('Inccorect Old Password.',422);
        } 
    }
    //--------------SET PASSWORD---------------
    public function set_new_password(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|exists:users,email',
            'password' => 'required|min:6|confirmed',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();
        return response()->json(['message'=>'Password changed successfully.'], 200);
    }
    //-------------VERIFY EMAIL---------------
    public function verify_email(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'email_verification_code' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }
        $user = User::where('email', $request->email)->where('email_verification_code', $request->email_verification_code)->first();
        if ($user) {
            $user->email_verified_at = Carbon::now();
            $user->email_verification_code = NULL;
            $user->save();
            return response()->json('Email Verified Successfully', 200);
        } else {
            return response()->json('Invalid code. Check your email and try again', 201);
        }
    }
    //-------------RESEND EMAIL VERIFICATION OTP---------------
    public function send_email_verification_code($email){
        $code = mt_rand(100000, 999999);
        $user = User::where('email', $email)->first();
        if(!$user){
            return response()->json('Incorrect Email.',404);
        }
        $user->email_verification_code = $code;
        $user->save();
        $user_name=$user->name;
        // try {
            Mail::to($email)->send(new EmailVerification($code));
        // } catch (\Exception $e) {
        //     return response()->json($e->getMessage(),500);
        // }
        return response()->json('Email Verification Code sent Successfully.',200);
    }
}
