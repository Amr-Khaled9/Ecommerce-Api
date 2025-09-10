<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DeliveryAuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        // validation

        //hash password
        $password = Hash::make($request->password);
        // insert in DB
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $password,
            'type' => 'delivery'
        ]);
        // assign role based on type
        if ($user->type === 'admin') {
            $user->assignRole('admin');
        } elseif ($user->type === 'delivery') {
            $user->assignRole('delivery');
        } else {
            $user->assignRole('customer');
        }
        //generate token
        $token = $user->createToken('auth_token')->plainTextToken;  //$request->device_name
        // return response
        return response()->json(['message' => 'User register Successfully', 'user' => $user, 'token' => $token], 201);
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->firstOrFail();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User Login Successfully',
            'user' => $user->only(['id','name','email']),
            'token' => $token,
        ], 200);
    }

    public function logout(Request $request)
    {
        //revoke token
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'User logged out Successfully' ], 200);
    }
    public function profile(Request $request)
    {
        return response()->json(['message: profile',$request->user()],200);
    }

    public function getAccessToken(Request $request)
    {
        return response()->json([
            'message' => 'token',
            'token'   => $request->user()->currentAccessToken()
        ], 200);
    }

}
