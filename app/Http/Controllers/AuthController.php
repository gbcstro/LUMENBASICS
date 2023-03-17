<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PasswordReset;

use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ResetPassword;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller {

    public function __construct() {
        $this->middleware('auth:api',['except' => [
            'user',
            'me',
            'login', 
            'register',
            'logout', 
            'emailVerify', 
            'routeEmailVerify', 
            'requestForgotPassword',
            'resetPassword',
            'routeResetPassword'
        ]]);
    }

    public function login(Request $request) {
        
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required'
        ]);

        if($validator->fails()) {
            return array(
                'success' => false,
                'message' => $validator->errors()->all()
            );
        }

        if(!User::where('email',$request->email)->exists()){

            return response()->json([
                'success' => false,
                'message' => 'User does not exist'
            ]);
        }
       
        $input = $request->only('email', 'password');
        $authorized = Auth::attempt($input);
        
        if( !$authorized ){
            $code = 401;
            $output = [
                'success' => false,
                'code' => $code,
                'message' => 'Incorrect Credentials'
            ];
        } else {
            $token = $this->respondWithToken($authorized);
            $code = 201;
            $output = [
                'success' => true,
                'code' => $code,
                'message' => 'User logged in succesfully',
                'token' => $token
            ];
        }
        return response()->json($output);
    }

    public function register(Request $request) {

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:user',
            'password' => 'required'
        ]);

        if($validator->fails()) {
            return array(
                'success' => false,
                'message' => $validator->errors()->all()
            );
        }

        $user = new User;
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $password = $request->password;

        $user->password = app('hash')->make($password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully'
        ], 200);

    }

    public function user(){
        return response()->json(User::select('first_name','last_name')->get());
    }

    public function me(){
        return response()->json($this->guard()->user());
    }

    public function refresh(){
        return $this->respondWithToken(Auth::refresh());
    }

    public function logout(){
        Auth::logout();
        return response()->json(['message' => 'Logged Out!']);
    }

    public function guard(){
        return Auth::guard();
    }

    public function emailRequestVerification(Request $request){
        if ( $request->user()->hasVerifiedEmail() ) {
            return response()->json('Email address is already verified.');
        }
        
        $request->user()->sendEmailVerificationNotification();
        
        return response()->json('Email request verification sent to '. Auth::user()->email);
    
    }

    public function routeEmailVerify(Request $request){
        $this->validate($request, [
            'token' => 'required|string',
        ]);

        $token = $request->token;

        return redirect("http://localhost:4200/email/verify?token=$token");
    }

    public function emailVerify(Request $request) {
        
        $this->validate($request, [
        'token' => 'required|string',
        ]);

        \Tymon\JWTAuth\Facades\JWTAuth::getToken();
        \Tymon\JWTAuth\Facades\JWTAuth::parseToken()->authenticate();
        
        if ( ! $request->user() ) {
            return response()->json('Invalid token', 401);
        }
        
        if ( $request->user()->hasVerifiedEmail() ) {
            return response()->json('Email address '.$request->user()->getEmailForVerification().' is already verified.');
        }

        $request->user()->markEmailAsVerified();
        return response()->json('Email address '. $request->user()->email.' successfully verified.');
    }

    public function routeResetPassword(Request $request){

        $this->validate($request, [
            'token' => 'required|string',
        ]);

        $token = $request->token;

        return redirect("http://localhost:4200/email/password/reset?token=$token");

    }

    public function requestForgotPassword(Request $request){

        if(User::where('email',$request->email)->exists()) {

            $user = User::where('email',$request->email)->first();
            $token = Str::random(40);
            $datetime = Carbon::now()->format('Y-m-d H:i:s');
            PasswordReset::updateOrCreate(
                ['email' => $user->email],
                [
                    'email' => $user->email,
                    'token' => $token,
                    'created_at' => $datetime 
                ]
            );

            Notification::send($user, new ResetPassword($token));

            return response()->json([
                'success' => true,
                'message' => 'Reset password request was sent to your email address '. $request->email
            ]);
 
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Email address '. $request->email. ' does not exist!'
            ]);
        }
    }

    public function resetPassword(Request $request){

        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'password' => 'required'
        ]);

        if($validator->fails()) {
            return array(
                'success' => false,
                'message' => $validator->errors()->all()
            );
        }

        if(PasswordReset::where('token', $request->token)->exists()){
            $resetData = PasswordReset::where('token', $request->token)->get();
            $user = User::where('email',$resetData[0]['email'])->first();
            
            $password = $request->password;
            $user->password = app('hash')->make($password);
            $user->save();

            PasswordReset::where('email',$user->email)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Password has been reset successfully'
            ]);

        } else {
            return response()->json([
                'success' => false,
                'message' => 'Token does not exist or expired'
            ]);
        }
    }

}
