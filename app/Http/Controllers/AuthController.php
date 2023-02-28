<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller {

    public function __construct() {
        $this->middleware('auth:api',['except' => ['me','login', 'register'] ]);
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

    
}
