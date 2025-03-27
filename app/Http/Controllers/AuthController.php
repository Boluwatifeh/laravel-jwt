<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User; 

class AuthController extends Controller
{
    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'name'=>'required',
            'email'=>'required|string|email|unique:users',
            'password'=>'required|string|confirmed|min:6'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
        $user = User::create(array_merge(
            $validator->validated(),
            ['password'=>bcrypt($request->password)]
        ));
        return response()->json([
            'message'=>'User registered successfully!',
            'user'=> $user
        ], 201);
    }

    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email'=>'required|string|email',
            'password'=>'required|string|min:6'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }
        if(!$token=auth('api')->attempt($validator->validated())){
            return response()->json(['error'=>'unauthorized'], 401);
        }
        return $this->createNewToken($token);
    
    }

    public function createNewToken($token){
        return response()->json([
            'access_token'=> $token,
            'token_type'=> 'bearer',
            'expires_in'=>auth('api')->factory()->getTTL()*60,
            'user'=>auth('api')->user()
        ]);
    }
}
