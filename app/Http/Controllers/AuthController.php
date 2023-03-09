<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller {

    public function __construct() {
        $this->middleware('auth:api',['except' => ['user','me','login', 'register', 'emailVerify', 'routeEmailVerify']]);
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
       
        $input = $request->only('email', 'password');
        $authorized = Auth::attempt($input);
        
        if( !$authorized ){
            $code = 401;
            $output = [
                'code' => $code,
                'message' => 'User does not exist'
            ];
        } else {
            $token = $this->respondWithToken($authorized);
            $code = 201;
            $output = [
                'code' => $code,
                'message' => 'User logged in succesfully',
                'token' => $token
            ];
        }
        return response()->json($output, $code);
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
        return $this->respondWithToken($this->guard()->refresh());
    }

    public function logout(){
        $this->guard()->logout();
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


    public function routeEmailVerify(Request $request){
        $this->validate($request, [
            'token' => 'required|string',
        ]);

        $token = $request->token;

        return redirect("http://localhost:4200/email/verify?token=$token");
    }

}
