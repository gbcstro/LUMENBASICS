<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Exception\BadResponseException;

class AuthController extends Controller {

    public function __construct() {
        
    }

    public function login(Request $request) {

        $email = $request->email;
        $password = $request->password;

        if (empty($email) || empty($password)){
            return response()->json([
                'status' => 'error',
                'message' => 'You must fill all fields'
            ]);
        }

    }

    public function register(Request $request) {

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|unique:users|email',
            'password' => 'required|confirmed'
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
    
}
